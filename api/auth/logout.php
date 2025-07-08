<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

session_start();

if (session_status() === PHP_SESSION_ACTIVE) {
    // Supprimer toutes les variables de session
    $_SESSION = [];

    // Supprimer le cookie de session si possible
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Détruire la session
    session_destroy();

    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Déconnecté avec succès']);
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Aucune session active']);
}
