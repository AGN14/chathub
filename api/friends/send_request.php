<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['sender_id'], $data['receiver_id'])) {
    echo json_encode(['success' => false, 'error' => 'Paramètres requis']);
    exit;
}

$pdo = getPDO();

try {
    $stmt = $pdo->prepare("INSERT INTO friend_requests (sender_id, receiver_id) VALUES (?, ?)");
    $stmt->execute([$data['sender_id'], $data['receiver_id']]);

    echo json_encode(['success' => true, 'message' => 'Invitation envoyée']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Déjà envoyé ou erreur.']);
}
