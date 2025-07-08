<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

if (!isset($_POST['user_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID utilisateur manquant']);
    exit;
}

$user_id = (int) $_POST['user_id'];

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Erreur lors du téléchargement de l\'avatar']);
    exit;
}

$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $_FILES['avatar']['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedMimeTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Format d\'image non supporté']);
    exit;
}

// Générer un nom unique pour l'image
$ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
$filename = uniqid('avatar_') . '.' . $ext;
$uploadDir = __DIR__ . "/../../uploads/avatars/";
$uploadPath = $uploadDir . $filename;

if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur lors de la sauvegarde du fichier']);
    exit;
}

try {
    $pdo = getPDO();

    // Optionnel: récupérer l'ancien avatar pour éventuellement le supprimer (sauf default.png)
    $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        // Supprimer le fichier uploadé car utilisateur inconnu
        unlink($uploadPath);
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Utilisateur non trouvé']);
        exit;
    }

    $oldAvatar = $user['avatar'];

    // Mettre à jour la base avec le nouveau nom de fichier
    $stmt = $pdo->prepare("UPDATE users SET avatar = :avatar WHERE id = :id");
    $stmt->execute([':avatar' => $filename, ':id' => $user_id]);

    // Supprimer l'ancien avatar s'il n'est pas 'default.png'
    if ($oldAvatar && $oldAvatar !== 'default.png' && file_exists($uploadDir . $oldAvatar)) {
        unlink($uploadDir . $oldAvatar);
    }

    echo json_encode(['success' => true, 'message' => 'Avatar mis à jour', 'avatar' => $filename]);
} catch (Exception $e) {
    // En cas d'erreur, supprimer l'image uploadée pour éviter les fichiers orphelins
    if (file_exists($uploadPath)) {
        unlink($uploadPath);
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur : ' . $e->getMessage()]);
}
