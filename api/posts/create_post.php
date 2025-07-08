<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Inclure config DB
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getPDO();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
        exit;
    }

    // Récupérez les données comme ceci :
    $user_id = $_POST['user_id'] ?? null;
    $content = $_POST['content'] ?? null;
    $imageBase64 = $_POST['image'] ?? null;  // Image en base64
    $uploadedFile = $_FILES['image'] ?? null; // Fichier uploadé classique

    // Validation

    if (!$user_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'user_id manquant']);
    exit;
    }

    if (!$user_id || (!$content && !$image)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Données manquantes']);
        echo json_encode([
        'success' => true,
        'user_id' => $user_id,
        'content' => $content,
        'image' => $image ? $image['name'] : null
        
    ]);
        exit;
    }

    $imageFilename = null;

    // Traitement de l'image (base64 ou fichier uploadé)
    if ($imageBase64) {
        // Décoder l'image base64
        if (preg_match('/^data:image\/(\w+);base64,/', $imageBase64, $type)) {
            $imageData = substr($imageBase64, strpos($imageBase64, ',') + 1);
            $imageData = base64_decode($imageData);

            if ($imageData === false) {
                throw new Exception('Base64 image data is invalid');
            }

            // Vérifier l'extension de l'image
            $ext = strtolower($type[1]);
            $validExt = ['png', 'jpg', 'jpeg', 'gif'];
            
            if (!in_array($ext, $validExt)) {
                throw new Exception('Unsupported image type');
            }

            // Créer le dossier uploads si inexistant
            $uploadDir = __DIR__ . '/../../uploads/posts/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $imageFilename = uniqid('img_') . '.' . $ext;
            file_put_contents($uploadDir . $imageFilename, $imageData);
        } else {
            throw new Exception('Invalid base64 image format');
        }
    } elseif ($uploadedFile && $uploadedFile['error'] === UPLOAD_ERR_OK) {
        // Traitement classique pour fichier uploadé
        $ext = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
        $validExt = ['png', 'jpg', 'jpeg', 'gif'];
        
        if (!in_array($ext, $validExt)) {
            throw new Exception('Unsupported file type');
        }

        $uploadDir = __DIR__ . '/../../uploads/posts/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $imageFilename = uniqid('img_') . '.' . $ext;
        move_uploaded_file($uploadedFile['tmp_name'], $uploadDir . $imageFilename);
    }


    // Insertion dans la BDD
    $sql = "INSERT INTO posts (user_id, content, image, created_at) VALUES (:user_id, :content, :image, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id' => $user_id,
        ':content' => $content,
        ':image' => $imageFilename
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Post créé avec succès',
        'post_id' => $pdo->lastInsertId()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur : ' . $e->getMessage()
    ]);
}
