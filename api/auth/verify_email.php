<?php
require_once("../config/database.php");

if (!isset($_GET['token'])) {
    echo "Lien invalide.";
    exit;
}

$token = $_GET['token'];
$conn = getPDO();

// Vérifie si le token existe
$stmt = $conn->prepare("SELECT id FROM users WHERE verification_token = ?");
$stmt->execute([$token]);

if ($stmt->rowCount() === 0) {
    echo "Lien invalide ou déjà utilisé.";
    exit;
}

// Met à jour l'utilisateur
$update = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE verification_token = ?");
$update->execute([$token]);

echo "<h2>✅ Compte activé !</h2><p>Vous pouvez maintenant vous connecter.</p>";
