<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getPDO();

    // Récupérer données JSON POST
    $input = json_decode(file_get_contents('php://input'), true);

    // Vérification : user_id et interlocutor_id obligatoires
    if (empty($input['user_id']) || empty($input['interlocutor_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Données utilisateur manquantes']);
        exit;
    }

    $user_id = (int)$input['user_id'];
    $interlocutor_id = (int)$input['interlocutor_id'];

    // Construire identifiant unique conversation (ordre indifférent)
    $conv_id = ($user_id < $interlocutor_id) 
        ? $user_id . '_' . $interlocutor_id 
        : $interlocutor_id . '_' . $user_id;

    // Récupérer tous les messages entre user et interlocuteur
    $sql = "
        SELECT 
            m.id,
            m.sender_id,
            m.receiver_id,
            m.content,
            m.created_at,
            u.first_name,
            u.last_name,
            u.avatar
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE (m.sender_id = :user_id AND m.receiver_id = :interlocutor_id)
           OR (m.sender_id = :interlocutor_id AND m.receiver_id = :user_id)
        ORDER BY m.created_at ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id' => $user_id,
        ':interlocutor_id' => $interlocutor_id
    ]);

    $messages = $stmt->fetchAll();

    // Optionnel: marquer les messages reçus comme lus
    $updateSql = "
        UPDATE messages 
        SET read_status = 1
        WHERE receiver_id = :user_id 
          AND sender_id = :interlocutor_id 
          AND read_status = 0
    ";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([
        ':user_id' => $user_id,
        ':interlocutor_id' => $interlocutor_id
    ]);

    echo json_encode([
        'success' => true,
        'messages' => $messages
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur : ' . $e->getMessage()
    ]);
}
