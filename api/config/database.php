<?php

/**
 * Établit une connexion PDO sécurisée à la base de données MySQL.
 * @return PDO
 */
function getPDO() {
    // Paramètres de connexion à adapter selon ta config locale ou serveur distant
    $host = 'localhost';
    $db   = 'chathub_db';   // Nom de ta base de données
    $user = 'root';         // Nom d'utilisateur MySQL
    $pass = 'root';             
    $charset = 'utf8mb4';   // Charset pour le support UTF-8 étendu (émoticônes, etc.)

    // Chaîne de connexion DSN
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

    // Options PDO pour la sécurité et la fiabilité
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lève des exceptions en cas d'erreur
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Résultats en tableau associatif
        PDO::ATTR_EMULATE_PREPARES   => false                   // Utilisation des vraies requêtes préparées
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Erreur de connexion à la base de données : ' . $e->getMessage()
        ]);
        exit;
    }
}
