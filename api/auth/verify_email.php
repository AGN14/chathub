<?php
require_once("../config/database.php");
header('Content-Type: text/html; charset=utf-8');

if (!isset($_GET['token'])) {
    echo "<h2>❌ Lien invalide.</h2>";
    exit;
}

$token = $_GET['token'];
$conn = getPDO();

$stmt = $conn->prepare("SELECT id FROM users WHERE verification_token = ?");
$stmt->execute([$token]);

if ($stmt->rowCount() === 0) {
    echo "<h2>❌ Lien invalide ou déjà utilisé.</h2>";
    exit;
}

$update = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE verification_token = ?");
$update->execute([$token]);

echo "
    <div style='font-family: Arial; text-align: center; padding-top: 50px;'>
        <h2 style='color: green;'>✅ Compte activé avec succès !</h2>
        <p><a href='login.php'>👉 Cliquez ici pour vous connecter</a></p>
    </div>
";

