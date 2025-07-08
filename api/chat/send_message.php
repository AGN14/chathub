<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getPDO();

    // Récupérer données JSON POST
    $input = json_decode(file_get_contents('php://input'), true);

    // Vérifications
    if (empty($input['sender_id']) || empty($input['receiver_id']) || !isset($input['content'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Données manquantes']);
        exit;
    }

    $sender_id = (int)$input['sender_id'];
    $receiver_id = (int)$input['receiver_id'];
    $content = trim($input['content']);

    if ($content === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Le contenu du message est vide']);
        exit;
    }

    // Insertion message
    $sql = "INSERT INTO messages (sender_id, receiver_id, content, created_at, read_status) 
            VALUES (:sender_id, :receiver_id, :content, NOW(), 0)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':sender_id' => $sender_id,
        ':receiver_id' => $receiver_id,
        ':content' => $content
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Message envoyé avec succès',
        'message_id' => $pdo->lastInsertId()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur : ' . $e->getMessage()
    ]);
}
