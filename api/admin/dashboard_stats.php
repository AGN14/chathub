<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getPDO();

    // Récupérer le nombre total d'utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $usersCount = (int)$stmt->fetchColumn();

    // Récupérer le nombre total de posts
    $stmt = $pdo->query("SELECT COUNT(*) FROM posts");
    $postsCount = (int)$stmt->fetchColumn();

    // Récupérer le nombre total de commentaires
    $stmt = $pdo->query("SELECT COUNT(*) FROM comments");
    $commentsCount = (int)$stmt->fetchColumn();

    // Récupérer le nombre total de likes
    $stmt = $pdo->query("SELECT COUNT(*) FROM likes");
    $likesCount = (int)$stmt->fetchColumn();

    // Exemple de stats supplémentaires: utilisateurs vérifiés
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE is_verified = 1");
    $verifiedUsersCount = (int)$stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'stats' => [
            'users' => $usersCount,
            'posts' => $postsCount,
            'comments' => $commentsCount,
            'likes' => $likesCount,
            'verified_users' => $verifiedUsersCount
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur : ' . $e->getMessage()
    ]);
}
