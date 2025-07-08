<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['post_id'], $data['user_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Données manquantes']);
    exit;
}

$post_id = (int)$data['post_id'];
$user_id = (int)$data['user_id'];

try {
    $pdo = getPDO();

    // Vérifier si like existe déjà
    $stmt = $pdo->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$post_id, $user_id]);
    $like = $stmt->fetch();

    if ($like) {
        // Supprimer le like (toggle)
        $del = $pdo->prepare("DELETE FROM likes WHERE id = ?");
        $del->execute([$like['id']]);
    } else {
        // Ajouter un like
        $ins = $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
        $ins->execute([$post_id, $user_id]);
    }

    // Compter likes à jour
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
    $countStmt->execute([$post_id]);
    $likes_count = (int)$countStmt->fetchColumn();

    echo json_encode(['success' => true, 'new_likes_count' => $likes_count]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()]);
}
