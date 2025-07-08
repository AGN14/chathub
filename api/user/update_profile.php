<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once __DIR__ . '/../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);

// Vérification des données requises
if (
    empty($data['user_id']) ||
    empty($data['first_name']) ||
    empty($data['last_name'])
) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Données manquantes']);
    exit;
}

try {
    $pdo = getPDO();

    // Mise à jour des infos utilisateur (sauf email, password)
    $sql = "UPDATE users SET first_name = :first_name, last_name = :last_name WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':first_name' => trim($data['first_name']),
        ':last_name' => trim($data['last_name']),
        ':id' => (int)$data['user_id']
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Profil mis à jour avec succès']);
    } else {
        // Pas de modification (peut-être même si les valeurs sont identiques)
        echo json_encode(['success' => true, 'message' => 'Aucune modification détectée']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur : ' . $e->getMessage()]);
}
