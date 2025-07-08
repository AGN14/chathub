<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getPDO();
    $input = json_decode(file_get_contents('php://input'), true);

    // Champs obligatoires
    $required = ['first_name', 'last_name', 'email', 'password', 'username', 'birth_date', 'gender', 'city', 'country'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Le champ '$field' est requis."]);
            exit;
        }
    }

    // Validation email
    $email = filter_var(trim($input['email']), FILTER_VALIDATE_EMAIL);
    if (!$email) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Email invalide.']);
        exit;
    }

    // Préparation des données
    $first_name = trim($input['first_name']);
    $last_name  = trim($input['last_name']);
    $username   = trim($input['username']);
    $password   = $input['password'];
    $phone      = !empty($input['phone']) ? trim($input['phone']) : null;
    $birth_date = $input['birth_date'];
    $gender     = trim($input['gender']);
    $city       = trim($input['city']);
    $country    = trim($input['country']);
    $bio        = !empty($input['bio']) ? trim($input['bio']) : null;
    $newsletter = !empty($input['newsletter']) ? 1 : 0;

    // Vérifier l'unicité de l'email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'Email déjà utilisé.']);
        exit;
    }

    // Vérifier l’unicité du nom d'utilisateur
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'Nom d’utilisateur déjà pris.']);
        exit;
    }

    // Hasher le mot de passe
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Générer token de vérification (optionnel)
    $token = bin2hex(random_bytes(32));

    // Insertion dans la base de données
    $stmt = $pdo->prepare("
        INSERT INTO users (
            first_name, last_name, username, email, password, phone, birth_date, gender, city, country,
            bio, newsletter, avatar, is_verified, verification_token, created_at,
        ) VALUES (
            :first_name, :last_name, :username, :email, :password, :phone, :birth_date, :gender, :city, :country,
            :bio, :newsletter, 'default.png', 0, :token, NOW()
        )
    ");

    $stmt->execute([
        ':first_name' => $first_name,
        ':last_name'  => $last_name,
        ':username'   => $username,
        ':email'      => $email,
        ':password'   => $passwordHash,
        ':phone'      => $phone,
        ':birth_date' => $birth_date,
        ':gender'     => $gender,
        ':city'       => $city,
        ':country'    => $country,
        ':bio'        => $bio,
        ':newsletter' => $newsletter,
        ':token'      => $token
    ]);

    echo json_encode(['success' => true, 'message' => 'Inscription réussie !']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur : ' . $e->getMessage()]);
}
