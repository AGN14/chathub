<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getPDO();

    // Récupérer les données JSON POST
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['user_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID utilisateur manquant']);
        exit;
    }

    $user_id = (int)$input['user_id'];

    // Optionnel : empêcher la suppression de l’admin principal (id=1 par ex)
    if ($user_id === 1) {
        echo json_encode(['success' => false, 'error' => 'Suppression de cet utilisateur non autorisée']);
        exit;
    }

    // Supprimer utilisateur
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :user_id");
    $stmt->execute([':user_id' => $user_id]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'error' => 'Utilisateur non trouvé']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé avec succès']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur : ' . $e->getMessage()]);
}
