<?php
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

if (!isset($_GET['medicament'])) {
    echo json_encode(['error' => 'Paramètre médicament manquant']);
    exit;
}

$medicament = cleanInput($_GET['medicament']);

try {
    // Recherche des pharmacies qui ont le médicament en stock
    $stmt = $pdo->prepare("
        SELECT 
            p.nom,
            p.latitude,
            p.longitude,
            p.jour_repos,
            sp.quantite,
            sp.prix,
            r.nom as region_nom
        FROM pharmacies p
        JOIN stocks_pharmacies sp ON p.id = sp.pharmacie_id
        JOIN medicaments m ON sp.medicament_id = m.id
        JOIN regions r ON p.region_id = r.id
        WHERE m.nom LIKE ? AND sp.quantite >= 1
        ORDER BY p.nom
    ");
    
    $stmt->execute(["%$medicament%"]);
    $pharmacies = $stmt->fetchAll();

    if (empty($pharmacies)) {
        echo json_encode(['error' => 'Aucune pharmacie trouvée avec ce médicament']);
        exit;
    }

    // Vérification si les pharmacies sont ouvertes
    foreach ($pharmacies as &$pharmacie) {
        $pharmacie['est_ouverte'] = isPharmacyOpen($pharmacie['jour_repos']);
    }

    echo json_encode($pharmacies);
} catch (PDOException $e) {
    error_log("Erreur de recherche: " . $e->getMessage());
    echo json_encode(['error' => 'Une erreur est survenue lors de la recherche']);
} 