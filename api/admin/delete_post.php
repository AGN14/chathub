<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getPDO();

    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['post_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID du post manquant']);
        exit;
    }

    $post_id = (int)$input['post_id'];

    // Supprimer le post
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = :post_id");
    $stmt->execute([':post_id' => $post_id]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'error' => 'Post non trouvé']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Post supprimé avec succès']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur : ' . $e->getMessage()]);
}
