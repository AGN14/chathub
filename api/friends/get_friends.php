<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'ID requis']);
    exit;
}

$user_id = $data['user_id'];
$pdo = getPDO();

$stmt = $pdo->prepare("
    SELECT u.id, u.first_name, u.last_name, u.avatar
    FROM users u
    WHERE u.id IN (
        SELECT sender_id FROM friend_requests WHERE receiver_id = ? AND status = 'accepted'
        UNION
        SELECT receiver_id FROM friend_requests WHERE sender_id = ? AND status = 'accepted'
    )
");
$stmt->execute([$user_id, $user_id]);
$friends = $stmt->fetchAll();

echo json_encode(['success' => true, 'friends' => $friends]);
