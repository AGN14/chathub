<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';

try {
    // On récupère la connexion PDO
    $pdo = getPDO();

    // Lecture des données JSON reçues
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['user_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID utilisateur manquant']);
        exit;
    }

    $user_id = (int) $input['user_id'];

    // Préparer et exécuter la requête
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, avatar, is_verified, created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);

    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Utilisateur non trouvé']);
        exit;
    }

    // Retourner les données utilisateur (sans le mot de passe)
    echo json_encode(['success' => true, 'user' => $user]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur : ' . $e->getMessage()]);
}
