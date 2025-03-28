<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // À modifier selon votre configuration
define('DB_PASS', '');      // À modifier selon votre configuration
define('DB_NAME', 'pharmatrack');

// Connexion à la base de données
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Configuration des sessions
session_start();

// Configuration du fuseau horaire
date_default_timezone_set('Africa/Tunis');

// Configuration des constantes pour les chemins
define('ROOT_PATH', dirname(__DIR__));
define('CSS_PATH', '/css');
define('JS_PATH', '/js');
define('IMAGES_PATH', '/images');
