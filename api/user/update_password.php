<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once __DIR__ . '/../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);

if (
    empty($data['user_id']) ||
    empty($data['current']) ||
    empty($data['new'])
) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Données manquantes']);
    exit;
}

try {
    $pdo = getPDO();

    // Récupérer le hash du mot de passe actuel
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([(int)$data['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Utilisateur non trouvé']);
        exit;
    }

    // Vérifier mot de passe actuel
    if (!password_verify($data['current'], $user['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Mot de passe actuel incorrect']);
        exit;
    }

    // Hasher le nouveau mot de passe
    $newPasswordHash = password_hash($data['new'], PASSWORD_DEFAULT);

    // Mettre à jour le mot de passe
    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
    $stmt->execute([
        ':password' => $newPasswordHash,
        ':id' => (int)$data['user_id']
    ]);

    echo json_encode(['success' => true, 'message' => 'Mot de passe mis à jour avec succès']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur : ' . $e->getMessage()]);
}
