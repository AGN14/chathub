<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getPDO();

    // Récupérer données JSON POST
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['user_id']) || empty($input['role_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Données manquantes']);
        exit;
    }

    $user_id = (int)$input['user_id'];
    $role_id = (int)$input['role_id'];

    // Optionnel : empêcher modification du rôle admin principal (id=1)
    if ($user_id === 1) {
        echo json_encode(['success' => false, 'error' => 'Modification non autorisée']);
        exit;
    }

    // Vérifier que le role_id existe dans la table roles
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE id = :role_id");
    $stmt->execute([':role_id' => $role_id]);
    if ($stmt->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'error' => 'Rôle invalide']);
        exit;
    }

    // Mettre à jour le rôle de l'utilisateur
    $stmt = $pdo->prepare("UPDATE users SET role_id = :role_id WHERE id = :user_id");
    $stmt->execute([
        ':role_id' => $role_id,
        ':user_id' => $user_id
    ]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'error' => 'Utilisateur non trouvé ou rôle inchangé']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Rôle mis à jour avec succès']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur : ' . $e->getMessage()]);
}
