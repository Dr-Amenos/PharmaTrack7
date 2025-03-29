<?php
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

// Récupération et nettoyage des données
$email = cleanInput($_POST['email'] ?? '');
$mot_de_passe = $_POST['mot_de_passe'] ?? '';

// Validation des données
if (empty($email) || empty($mot_de_passe)) {
    echo json_encode(['success' => false, 'error' => 'Tous les champs sont obligatoires']);
    exit;
}

try {
    // Vérification des tentatives de connexion
    $loginAttempts = checkLoginAttempts($email);
    if ($loginAttempts['compte_bloque']) {
        echo json_encode(['success' => false, 'error' => 'Votre compte est bloqué. Veuillez le recréer.']);
        exit;
    }

    // Recherche de la pharmacie
    $stmt = $pdo->prepare("SELECT * FROM pharmacies WHERE email = ?");
    $stmt->execute([$email]);
    $pharmacy = $stmt->fetch();

    if (!$pharmacy || !password_verify($mot_de_passe, $pharmacy['mot_de_passe'])) {
        // Mise à jour des tentatives de connexion
        updateLoginAttempts($email);
        
        // Vérification si le compte doit être bloqué
        if ($loginAttempts['tentatives_connexion'] >= 2) {
            blockAccount($email);
        }

        echo json_encode(['success' => false, 'error' => 'Email ou mot de passe incorrect']);
        exit;
    }

    // Réinitialisation des tentatives de connexion en cas de succès
    resetLoginAttempts($email);

    // Stockage des informations de la pharmacie dans la session
    $_SESSION['pharmacy_id'] = $pharmacy['id'];
    $_SESSION['pharmacy_nom'] = $pharmacy['nom'];
    $_SESSION['pharmacy_email'] = $pharmacy['email'];

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    error_log("Erreur de connexion: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Une erreur est survenue lors de la connexion']);
}