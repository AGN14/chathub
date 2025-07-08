<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__.'/../config/database.php';

try {
    $pdo = getPDO();

    // Récupérer user_id du client (envoyé en POST JSON)
    $input = json_decode(file_get_contents('php://input'), true);
    if (empty($input['user_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID utilisateur manquant']);
        exit;
    }
    $user_id = (int)$input['user_id'];

    // Requête pour récupérer toutes les conversations de l'utilisateur (en tant qu'envoyeur ou receveur)
    // avec infos sur interlocuteur, dernier message et date
    // Ici on suppose une table messages avec sender_id, receiver_id, content, created_at, read_status
    // Et on construit une liste unique de conversations (en gros, pairs d'utilisateurs)

    $sql = "
    SELECT 
        c.conversation_id,
        u.id AS interlocutor_id,
        u.first_name,
        u.last_name,
        u.avatar,
        m.content AS last_message,
        m.created_at AS last_message_date,
        SUM(CASE WHEN m.receiver_id = :user_id AND m.read_status = 0 THEN 1 ELSE 0 END) AS unread_count
    FROM (
        SELECT
            CASE
                WHEN sender_id = :user_id THEN receiver_id
                ELSE sender_id
            END AS interlocutor_id,
            CONCAT(LEAST(sender_id, receiver_id), '_', GREATEST(sender_id, receiver_id)) AS conversation_id,
            MAX(created_at) AS last_msg_date
        FROM messages
        WHERE sender_id = :user_id OR receiver_id = :user_id
        GROUP BY interlocutor_id, conversation_id
    ) AS c
    JOIN messages m ON CONCAT(LEAST(m.sender_id, m.receiver_id), '_', GREATEST(m.sender_id, m.receiver_id)) = c.conversation_id AND m.created_at = c.last_msg_date
    JOIN users u ON u.id = c.interlocutor_id
    GROUP BY c.conversation_id, u.id, u.first_name, u.last_name, u.avatar, m.content, m.created_at
    ORDER BY last_message_date DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $conversations = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'conversations' => $conversations
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur : ' . $e->getMessage()]);
}
