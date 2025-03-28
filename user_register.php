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
$prenom = cleanInput($_POST['prenom'] ?? '');
$region_id = intval($_POST['region'] ?? 0);
$latitude = floatval($_POST['latitude'] ?? 0);
$longitude = floatval($_POST['longitude'] ?? 0);

// Validation des données
if (empty($nom) || empty($prenom) || $region_id === 0 || $latitude === 0 || $longitude === 0) {
    echo json_encode(['success' => false, 'error' => 'Tous les champs sont obligatoires']);
    exit;
}

try {
    // Vérification si la région existe
    $stmt = $pdo->prepare("SELECT id FROM regions WHERE id = ?");
    $stmt->execute([$region_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Région invalide']);
        exit;
    }

    // Insertion de l'utilisateur
    $stmt = $pdo->prepare("
        INSERT INTO utilisateurs (nom, prenom, region_id, latitude, longitude)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([$nom, $prenom, $region_id, $latitude, $longitude]);
    
    // Stockage de l'ID de l'utilisateur dans la session
    $_SESSION['user_id'] = $pdo->lastInsertId();
    $_SESSION['user_nom'] = $nom;
    $_SESSION['user_prenom'] = $prenom;
    $_SESSION['user_region_id'] = $region_id;
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    error_log("Erreur d'inscription utilisateur: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Une erreur est survenue lors de l\'inscription']);
} 