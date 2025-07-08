<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['post_id'], $data['user_id'], $data['content'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Données manquantes']);
    exit;
}

$post_id = (int)$data['post_id'];
$user_id = (int)$data['user_id'];
$content = trim($data['content']);

if ($content === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Le contenu du commentaire est vide']);
    exit;
}

try {
    $pdo = getPDO();

    // Insérer commentaire
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$post_id, $user_id, $content]);

    // Compter commentaires à jour
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ?");
    $countStmt->execute([$post_id]);
    $comments_count = (int)$countStmt->fetchColumn();

    echo json_encode(['success' => true, 'new_comments_count' => $comments_count]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()]);
}
