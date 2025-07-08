<?php
// En-têtes HTTP
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Inclure la configuration PDO
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getPDO(); // Assure-toi que getPDO() est bien définie dans database.php

    // Requête SQL principale
    $sql = "
        SELECT 
            p.id, p.content, p.image, p.created_at,
            u.id AS user_id, u.first_name, u.last_name, u.avatar,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS likes_count,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comments_count
        FROM posts p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC
        LIMIT 50
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'posts' => $posts
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur base de données : ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur : ' . $e->getMessage()
    ]);
}
