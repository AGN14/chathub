<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$pdo = getPDO();

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'ID utilisateur manquant']);
    exit;
}

$user_id = (int)$data['user_id'];

$stmt = $pdo->prepare("SELECT id, first_name, last_name, avatar FROM users WHERE id != ?");
$stmt->execute([$user_id]);
$users = $stmt->fetchAll();

echo json_encode(['success' => true, 'users' => $users]);
