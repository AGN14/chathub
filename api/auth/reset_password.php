<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getPDO();

    // Récupérer les données JSON POST
    $input = json_decode(file_get_contents('php://input'), true);

    // Validation des données reçues
    if (empty($input['reset_token']) || empty($input['new_password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Données manquantes']);
        exit;
    }

    $reset_token = $input['reset_token'];
    $new_password = $input['new_password'];

    // Vérifier que le token est valide et non expiré
    $stmt = $pdo->prepare("SELECT id, reset_token_expire FROM users WHERE reset_token = :token");
    $stmt->execute([':token' => $reset_token]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Token invalide']);
        exit;
    }

    // Vérifier expiration du token
    if (new DateTime() > new DateTime($user['reset_token_expire'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Token expiré']);
        exit;
    }

    // Hasher le nouveau mot de passe
    $passwordHash = password_hash($new_password, PASSWORD_DEFAULT);

    // Mettre à jour le mot de passe et supprimer le token
    $stmt = $pdo->prepare("
        UPDATE users 
        SET password = :password, reset_token = NULL, reset_token_expire = NULL
        WHERE id = :id
    ");
    $stmt->execute([
        ':password' => $passwordHash,
        ':id' => $user['id']
    ]);

    echo json_encode(['success' => true, 'message' => 'Mot de passe mis à jour avec succès']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur : ' . $e->getMessage()]);
}
