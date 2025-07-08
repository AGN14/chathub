<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';
// require_once __DIR__ . '/../config/mailer.php'; // Si tu utilises PHPMailer, sinon adapte l'envoi d'email

// Pour envoyer l'email, on peut utiliser mail() en local ou une lib comme PHPMailer en prod

try {
    $pdo = getPDO();

    $email = $_POST['email'] ?? null;
    if (empty($email)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Email requis']);
        exit;
    }

    // Vérifier si email existe
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Pour sécurité on ne dit pas que l'email n'existe pas
        echo json_encode(['success' => true, 'message' => 'Si cet email est enregistré, un lien de réinitialisation vous a été envoyé.']);
        exit;
    }

    // Générer token unique
    $token = bin2hex(random_bytes(32));
    $token_hash = hash('sha256', $token); // Hash du token pour sécurité
    $expire_at = date('Y-m-d H:i:s', strtotime('+1 hour')); // token valide 1h

    // On peut stocker token et expire_at dans la table users (ajouter colonnes) ou table reset_password_tokens

    $req = $pdo->prepare("UPDATE users SET verification_token = :token , expire_at = :expire_at  WHERE email = :email");

    $req->execute([
        ':token' => $token_hash,
        ':expire_at' => $expire_at,
        ':email' => $email
    ]);

    // Ici, supposons qu'on a ajouté dans users : reset_token, reset_token_expire

    $update = $pdo->prepare("UPDATE users SET reset_token = :token, reset_token_expire = :expire WHERE id = :id");
    $update->execute([
        ':token' => $token_hash,
        ':expire' => $expire_at,
        ':id' => $user['id']
    ]);

    // Construire URL de réinitialisation (adapter l'URL à ton frontend)
    $resetUrl = "http://localhost/chathub/api/auth/verify_email.php?token=".$token_hash;
    
    require_once __DIR__ . '/../config/mailer.php'; // Assure-toi que ce fichier existe et est configuré
    
    $temp = "http://localhost/chathub/vues/clients/pwd_email_temp.html";
    
    $temp = file_get_contents($temp);
    $temp = str_replace('{{NOM}}', htmlspecialchars($user['last_name']), $temp);
    $temp = str_replace('{{LIEN_DE_CONFIRMATION}}', $resetUrl, $temp);
    $temp = str_replace('{{NOM_DU_SITE}}', "Chathub", $temp);
    
    sendEmail($email, "Reinitialisation de mot de passe" , $temp);


    // Envoyer email (exemple simple mail())
   /*  $subject = "Réinitialisation de votre mot de passe Chathub";
    $message = "Bonjour " . htmlspecialchars($user['first_name']) . ",\n\n";
    $message .= "Pour réinitialiser votre mot de passe, veuillez cliquer sur ce lien ou le copier dans votre navigateur :\n";
    $message .= $resetUrl . "\n\n";
    $message .= "Ce lien est valable 1 heure.\n\n";
    $message .= "Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.\n\n";
    $message .= "Cordialement,\nL'équipe Chathub";

    $headers = "From: no-reply@chathub.com";

    // Envoi mail (à configurer selon ton environnement)
    mail($email, $subject, $message, $headers); */

    echo json_encode(['success' => true, 'message' => "Si cet email est enregistré, un lien de réinitialisation vous a été envoyé.", 'user'=> $user ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur : ' . $e->getMessage()]);
}
