<?php
// config/mailer.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Chargement automatique des classes via Composer
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Envoie un email HTML via SMTP avec PHPMailer.
 * 
 * @param string $to Email du destinataire
 * @param string $subject Sujet de l'email
 * @param string $body Contenu HTML de l'email
 * @param string|null $altBody Texte alternatif en cas de non affichage HTML
 * @return array ['success' => bool, 'error' => string|null]
 */
function sendEmail(string $to, string $subject, string $body, ?string $altBody = null): array
{
    $mail = new PHPMailer(true);

    try {
        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';          // Serveur SMTP (ex: Gmail)
        $mail->SMTPAuth   = true;
        $mail->Username   = 'arnoldomolola@gmail.com';     // Ton adresse Gmail SMTP
        $mail->Password   = 'fpyz acmx dxfq dbkw';    // Ton mot de passe d'application Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Utilisation SSL
        $mail->Port       = 465;

        // Expéditeur
        $mail->setFrom('arnoldomolola@gmail.com', 'Chathub');

        // Destinataire
        $mail->addAddress($to);

        // Contenu de l'email
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $altBody ?? 'Merci de consulter cet email dans un client compatible HTML.';

        // Envoi
        $mail->send();

        return ['success' => true, 'error' => null];
    } catch (Exception $e) {
        return ['success' => false, 'error' => "Mailer Error: {$mail->ErrorInfo}"];
    }
}
