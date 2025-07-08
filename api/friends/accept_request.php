<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['request_id'])) {
    echo json_encode(['success' => false, 'error' => 'ID manquant']);
    exit;
}

$pdo = getPDO();
$stmt = $pdo->prepare("UPDATE friend_requests SET status = 'accepted' WHERE id = ?");
$stmt->execute([$data['request_id']]);

echo json_encode(['success' => true, 'message' => 'Invitation acceptée']);
