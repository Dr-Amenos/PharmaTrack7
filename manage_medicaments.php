<?php
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

// Vérification si la pharmacie est connectée
if (!isset($_SESSION['pharmacy_id'])) {
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

// Vérification de la méthode de requête
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

// Récupération de l'action
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'add':
            // Validation des données
            $nom = cleanInput($_POST['nom'] ?? '');
            $quantite = intval($_POST['quantite'] ?? 0);
            $prix = floatval($_POST['prix'] ?? 0);

            if (empty($nom) || $quantite < 0 || $prix < 0) {
                throw new Exception('Données invalides');
            }

            // Vérification si le médicament existe déjà
            $stmt = $pdo->prepare("SELECT id FROM medicaments WHERE nom = ?");
            $stmt->execute([$nom]);
            $medicament = $stmt->fetch();

            if ($medicament) {
                // Mise à jour du stock existant
                $stmt = $pdo->prepare("
                    UPDATE stocks_pharmacies 
                    SET quantite = quantite + ?, prix = ?
                    WHERE pharmacie_id = ? AND medicament_id = ?
                ");
                $stmt->execute([$quantite, $prix, $_SESSION['pharmacy_id'], $medicament['id']]);
            } else {
                // Création du nouveau médicament
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("INSERT INTO medicaments (nom) VALUES (?)");
                $stmt->execute([$nom]);
                $medicament_id = $pdo->lastInsertId();

                $stmt = $pdo->prepare("
                    INSERT INTO stocks_pharmacies (pharmacie_id, medicament_id, quantite, prix)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$_SESSION['pharmacy_id'], $medicament_id, $quantite, $prix]);

                $pdo->commit();
            }

            echo json_encode(['success' => true]);
            break;

        case 'update':
            // Validation des données
            $id = intval($_POST['id'] ?? 0);
            $nom = cleanInput($_POST['nom'] ?? '');
            $quantite = intval($_POST['quantite'] ?? 0);
            $prix = floatval($_POST['prix'] ?? 0);

            if (empty($id) || empty($nom) || $quantite < 0 || $prix < 0) {
                throw new Exception('Données invalides');
            }

            // Vérification que le médicament appartient à la pharmacie
            $stmt = $pdo->prepare("
                SELECT m.id 
                FROM medicaments m
                JOIN stocks_pharmacies sp ON m.id = sp.medicament_id
                WHERE m.id = ? AND sp.pharmacie_id = ?
            ");
            $stmt->execute([$id, $_SESSION['pharmacy_id']]);
            
            if (!$stmt->fetch()) {
                throw new Exception('Médicament non trouvé');
            }

            // Mise à jour du médicament
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE medicaments SET nom = ? WHERE id = ?");
            $stmt->execute([$nom, $id]);

            $stmt = $pdo->prepare("
                UPDATE stocks_pharmacies 
                SET quantite = ?, prix = ?
                WHERE pharmacie_id = ? AND medicament_id = ?
            ");
            $stmt->execute([$quantite, $prix, $_SESSION['pharmacy_id'], $id]);

            $pdo->commit();

            echo json_encode(['success' => true]);
            break;

        case 'delete':
            // Validation des données
            $id = intval($_POST['id'] ?? 0);

            if (empty($id)) {
                throw new Exception('ID invalide');
            }

            // Vérification que le médicament appartient à la pharmacie
            $stmt = $pdo->prepare("
                SELECT m.id 
                FROM medicaments m
                JOIN stocks_pharmacies sp ON m.id = sp.medicament_id
                WHERE m.id = ? AND sp.pharmacie_id = ?
            ");
            $stmt->execute([$id, $_SESSION['pharmacy_id']]);
            
            if (!$stmt->fetch()) {
                throw new Exception('Médicament non trouvé');
            }

            // Suppression du stock
            $stmt = $pdo->prepare("
                DELETE FROM stocks_pharmacies 
                WHERE pharmacie_id = ? AND medicament_id = ?
            ");
            $stmt->execute([$_SESSION['pharmacy_id'], $id]);

            // Vérification si d'autres pharmacies ont ce médicament
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM stocks_pharmacies 
                WHERE medicament_id = ?
            ");
            $stmt->execute([$id]);
            $result = $stmt->fetch();

            // Si aucune autre pharmacie n'a ce médicament, on le supprime
            if ($result['count'] == 0) {
                $stmt = $pdo->prepare("DELETE FROM medicaments WHERE id = ?");
                $stmt->execute([$id]);
            }

            echo json_encode(['success' => true]);
            break;

        default:
            throw new Exception('Action non valide');
    }
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 