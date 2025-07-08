<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Vérifier que le fichier est bien envoyé
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Image non reçue ou erreur de téléchargement']);
        exit;
    }

    $file = $_FILES['image'];

    // Valider le type MIME de l'image
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedMimeTypes)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Format d\'image non supporté']);
        exit;
    }

    // Valider la taille (exemple max 5MB)
    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Fichier trop volumineux (max 5MB)']);
        exit;
    }

    // Générer un nom de fichier unique avec extension
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('chatimg_') . '.' . strtolower($ext);

    // Dossier de destination
    $uploadDir = __DIR__ . "/../../uploads/chat_images/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $destination = $uploadDir . $filename;

    // Déplacer le fichier uploadé
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Erreur lors de la sauvegarde du fichier']);
        exit;
    }

    // Répondre succès avec nom de fichier
    echo json_encode([
        'success' => true,
        'message' => 'Image uploadée avec succès',
        'filename' => $filename
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur : ' . $e->getMessage()
    ]);
}
