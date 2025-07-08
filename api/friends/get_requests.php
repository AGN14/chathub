<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$pdo = getPDO();

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'ID requis']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT fr.id, u.id as sender_id, u.first_name, u.last_name, u.avatar
    FROM friend_requests fr
    JOIN users u ON fr.sender_id = u.id
    WHERE fr.receiver_id = ? AND fr.status = 'pending'
");
$stmt->execute([$data['user_id']]);
$requests = $stmt->fetchAll();

echo json_encode(['success' => true, 'requests' => $requests]);
