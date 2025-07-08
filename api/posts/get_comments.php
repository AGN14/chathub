<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$post_id = $_GET['post_id'] ?? null;
if (!$post_id || !is_numeric($post_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID de post invalide']);
    exit;
}
 
try {
    $pdo = getPDO();

    $stmt = $pdo->prepare("
        SELECT c.id, c.content, c.created_at,
               u.first_name, u.last_name
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.post_id = ?
        ORDER BY c.created_at ASC
    ");
    $stmt->execute([(int)$post_id]);
    $comments = $stmt->fetchAll();

    echo json_encode(['success' => true, 'comments' => $comments]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()]);
}
