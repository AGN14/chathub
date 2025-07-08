<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getPDO();

    // Récupérer les données JSON envoyées
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['email']) || empty($input['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Email et mot de passe requis']);
        exit;
    }

    $email = trim($input['email']);
    $password = $input['password'];

    // Requête pour récupérer l'utilisateur admin/modérateur avec ce mail
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, password, role_id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Utilisateur non trouvé']);
        exit;
    }

    // Vérifier que l'utilisateur a un rôle admin ou modérateur
    // Exemple : on considère rôle 1 = admin, 2 = modérateur
    if (!in_array($user['role_id'], [1, 2])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Accès refusé']);
        exit;
    }

    // Vérifier le mot de passe (supposé hashé avec password_hash)
    if (!password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Mot de passe incorrect']);
        exit;
    }

    // Connexion réussie : retourner les infos utilisateur (sans le mdp)
    unset($user['password']);

    echo json_encode([
        'success' => true,
        'message' => 'Connexion réussie',
        'user' => $user
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur : ' . $e->getMessage()]);
}
