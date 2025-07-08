<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getPDO();

    // Pagination
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;

    // Tri (par défaut : id)
    $validSortFields = ['id', 'first_name', 'last_name', 'email', 'created_at'];
    $sort = isset($_GET['sort']) && in_array($_GET['sort'], $validSortFields) ? $_GET['sort'] : 'id';
    $order = (isset($_GET['order']) && strtolower($_GET['order']) === 'desc') ? 'DESC' : 'ASC';

    // Total utilisateurs pour pagination
    $totalStmt = $pdo->query("SELECT COUNT(*) FROM users");
    $totalUsers = $totalStmt->fetchColumn();

    // Récupération utilisateurs
    $sql = "SELECT id, first_name, last_name, email, avatar, is_verified, created_at FROM users ORDER BY $sort $order LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'page' => $page,
        'per_page' => $limit,
        'total' => $totalUsers,
        'users' => $users
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur : ' . $e->getMessage()
    ]);
}
