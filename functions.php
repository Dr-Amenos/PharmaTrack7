<?php
require_once 'config.php';

/**
 * Fonction pour nettoyer les entrées utilisateur
 */
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Fonction pour vérifier si un email existe déjà
 */
function emailExists($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pharmacies WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Fonction pour calculer la distance entre deux points
 */
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Rayon de la Terre en km

    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);

    $deltaLat = $lat2 - $lat1;
    $deltaLon = $lon2 - $lon1;

    $a = sin($deltaLat/2) * sin($deltaLat/2) +
         cos($lat1) * cos($lat2) *
         sin($deltaLon/2) * sin($deltaLon/2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $earthRadius * $c;
}

/**
 * Fonction pour obtenir les suggestions de médicaments
 */
function getMedicamentSuggestions($query) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT DISTINCT nom FROM medicaments WHERE nom LIKE ? LIMIT 10");
    $stmt->execute(["%$query%"]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Fonction pour vérifier les tentatives de connexion
 */
function checkLoginAttempts($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT tentatives_connexion, compte_bloque FROM pharmacies WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

/**
 * Fonction pour mettre à jour les tentatives de connexion
 */
function updateLoginAttempts($email) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE pharmacies SET tentatives_connexion = tentatives_connexion + 1 WHERE email = ?");
    $stmt->execute([$email]);
}

/**
 * Fonction pour bloquer un compte
 */
function blockAccount($email) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE pharmacies SET compte_bloque = TRUE WHERE email = ?");
    $stmt->execute([$email]);
}

/**
 * Fonction pour réinitialiser les tentatives de connexion
 */
function resetLoginAttempts($email) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE pharmacies SET tentatives_connexion = 0 WHERE email = ?");
    $stmt->execute([$email]);
}

/**
 * Fonction pour vérifier si un numéro de téléphone est valide
 */
function isValidPhoneNumber($phone) {
    return preg_match('/^7[0-9]{7}$/', $phone);
}

/**
 * Fonction pour vérifier si une pharmacie est ouverte
 */
function isPharmacyOpen($jourRepos) {
    $jourActuel = date('l');
    $joursSemaine = [
        'Monday' => 'Lundi',
        'Tuesday' => 'Mardi',
        'Wednesday' => 'Mercredi',
        'Thursday' => 'Jeudi',
        'Friday' => 'Vendredi',
        'Saturday' => 'Samedi',
        'Sunday' => 'Dimanche'
    ];
    
    return $jourRepos !== $joursSemaine[$jourActuel];
}

/**
 * Fonction pour vérifier si un mot de passe est valide
 */
function isValidPassword($password) {
    return strlen($password) >= 8;
}

/**
 * Fonction pour vérifier si les mots de passe correspondent
 */
function passwordsMatch($password, $confirmPassword) {
    return $password === $confirmPassword;
}
