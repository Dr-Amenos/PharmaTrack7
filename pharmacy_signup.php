<?php
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

// Récupération et nettoyage des données
$nom = cleanInput($_POST['nom'] ?? '');
$email = cleanInput($_POST['email'] ?? '');
$mot_de_passe = $_POST['mot_de_passe'] ?? '';
$confirm_mot_de_passe = $_POST['confirm_mot_de_passe'] ?? '';
$telephone = cleanInput($_POST['telephone'] ?? '');
$region = intval($_POST['region'] ?? 0);
$jour_repos = cleanInput($_POST['jour_repos'] ?? '');
$latitude = floatval($_POST['latitude'] ?? 0);
$longitude = floatval($_POST['longitude'] ?? 0);

// Validation des données
if (empty($nom) || empty($email) || empty($mot_de_passe) || empty($confirm_mot_de_passe) || 
    empty($telephone) || empty($region) || empty($jour_repos) || empty($latitude) || empty($longitude)) {
    echo json_encode(['success' => false, 'error' => 'Tous les champs sont obligatoires']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Email invalide']);
    exit;
}

if (emailExists($email)) {
    echo json_encode(['success' => false, 'error' => 'Cet email est déjà utilisé']);
    exit;
}

if (!isValidPhoneNumber($telephone)) {
    echo json_encode(['success' => false, 'error' => 'Numéro de téléphone invalide']);
    exit;
}

if (!isValidPassword($mot_de_passe)) {
    echo json_encode(['success' => false, 'error' => 'Le mot de passe doit contenir au moins 8 caractères']);
    exit;
}

if (!passwordsMatch($mot_de_passe, $confirm_mot_de_passe)) {
    echo json_encode(['success' => false, 'error' => 'Les mots de passe ne correspondent pas']);
    exit;
}

try {
    // Vérification de la région
    $stmt = $pdo->prepare("SELECT id FROM regions WHERE id = ?");
    $stmt->execute([$region]);
    if (!$stmt->fetch()) {
        throw new Exception('Région invalide');
    }

    // Hashage du mot de passe
    $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

    // Insertion dans la base de données
    $stmt = $pdo->prepare("
        INSERT INTO pharmacies (nom, email, mot_de_passe, telephone, region_id, jour_repos, latitude, longitude)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $nom,
        $email,
        $mot_de_passe_hash,
        $telephone,
        $region,
        $jour_repos,
        $latitude,
        $longitude
    ]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 