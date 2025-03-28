<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Vérification si la pharmacie est connectée
if (!isset($_SESSION['pharmacy_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Récupération des médicaments de la pharmacie
$stmt = $pdo->prepare("
    SELECT 
        m.id,
        m.nom,
        sp.quantite,
        sp.prix
    FROM medicaments m
    JOIN stocks_pharmacies sp ON m.id = sp.medicament_id
    WHERE sp.pharmacie_id = ?
    ORDER BY m.nom
");
$stmt->execute([$_SESSION['pharmacy_id']]);
$medicaments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaTrack - Tableau de Bord Pharmacie</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="particles" id="particles-js"></div>
    <div class="container">
        <div class="floating-circles"></div>
        <div class="floating-circles"></div>
        <h1>Tableau de Bord</h1>
        <div class="pharmacy-info">
            <p>Pharmacie : <?php echo htmlspecialchars($_SESSION['pharmacy_nom']); ?></p>
        </div>

        <div class="dashboard-actions">
            <button id="addMedicamentBtn" class="action-button">
                <span class="button-icon">➕</span>
                <span class="button-text">Ajouter un médicament</span>
            </button>
            <button id="logoutBtn" class="action-button logout">
                <span class="button-icon">🚪</span>
                <span class="button-text">Se déconnecter</span>
            </button>
        </div>

        <div class="medicaments-list">
            <h2>Médicaments en stock</h2>
            <?php if (empty($medicaments)): ?>
                <p class="no-data">Aucun médicament en stock</p>
            <?php else: ?>
                <?php foreach ($medicaments as $medicament): ?>
                    <div class="medicament-item" data-id="<?php echo $medicament['id']; ?>">
                        <div class="medicament-info">
                            <h3><?php echo htmlspecialchars($medicament['nom']); ?></h3>
                            <p>Quantité : <?php echo $medicament['quantite']; ?></p>
                            <p>Prix : <?php echo number_format($medicament['prix'], 3); ?> TND</p>
                        </div>
                        <div class="medicament-actions">
                            <button class="edit-btn" title="Modifier">
                                <span class="button-icon">✏️</span>
                            </button>
                            <button class="delete-btn" title="Supprimer">
                                <span class="button-icon">🗑️</span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Modal pour ajouter/modifier un médicament -->
        <div id="medicamentModal" class="modal">
            <div class="modal-content">
                <h2 id="modalTitle">Ajouter un médicament</h2>
                <form id="medicamentForm">
                    <input type="hidden" id="medicamentId">
                    <div class="form-group">
                        <input type="text" id="medicamentNom" placeholder="Nom du médicament" required>
                    </div>
                    <div class="form-group">
                        <input type="number" id="medicamentQuantite" placeholder="Quantité en stock" min="0" required>
                    </div>
                    <div class="form-group">
                        <input type="number" id="medicamentPrix" placeholder="Prix en TND" min="0" step="0.001" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="submit-button">
                            <span class="button-icon">💾</span>
                            <span class="button-text">Enregistrer</span>
                        </button>
                    </div>
                    <div class="form-group">
                        <button type="button" class="cancel-button" id="cancelBtn">
                            <span class="button-icon">❌</span>
                            <span class="button-text">Annuler</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        // Gestion du modal
        const modal = document.getElementById('medicamentModal');
        const addBtn = document.getElementById('addMedicamentBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const medicamentForm = document.getElementById('medicamentForm');
        const modalTitle = document.getElementById('modalTitle');

        function openModal(title = 'Ajouter un médicament') {
            modalTitle.textContent = title;
            modal.style.display = 'block';
        }

        function closeModal() {
            modal.style.display = 'none';
            medicamentForm.reset();
        }

        addBtn.addEventListener('click', () => openModal());
        cancelBtn.addEventListener('click', closeModal);

        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });

        // Gestion des actions sur les médicaments
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const medicamentItem = e.target.closest('.medicament-item');
                const id = medicamentItem.dataset.id;
                const nom = medicamentItem.querySelector('h3').textContent;
                const quantite = medicamentItem.querySelector('p:nth-child(2)').textContent.split(': ')[1];
                const prix = medicamentItem.querySelector('p:nth-child(3)').textContent.split(': ')[1].split(' ')[0];

                document.getElementById('medicamentId').value = id;
                document.getElementById('medicamentNom').value = nom;
                document.getElementById('medicamentQuantite').value = quantite;
                document.getElementById('medicamentPrix').value = prix;

                openModal('Modifier le médicament');
            });
        });

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                if (confirm('Êtes-vous sûr de vouloir supprimer ce médicament ?')) {
                    const medicamentItem = e.target.closest('.medicament-item');
                    const id = medicamentItem.dataset.id;

                    fetch('includes/manage_medicaments.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=delete&id=${id}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.error || 'Une erreur est survenue lors de la suppression');
                        }
                    })
                    .catch(error => {
                        alert('Une erreur est survenue');
                        console.error('Erreur:', error);
                    });
                }
            });
        });

        // Gestion du formulaire
        medicamentForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('action', document.getElementById('medicamentId').value ? 'update' : 'add');
            formData.append('id', document.getElementById('medicamentId').value);
            formData.append('nom', document.getElementById('medicamentNom').value);
            formData.append('quantite', document.getElementById('medicamentQuantite').value);
            formData.append('prix', document.getElementById('medicamentPrix').value);

            fetch('includes/manage_medicaments.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Une erreur est survenue');
                }
            })
            .catch(error => {
                alert('Une erreur est survenue');
                console.error('Erreur:', error);
            });
        });

        // Gestion de la déconnexion
        document.getElementById('logoutBtn').addEventListener('click', () => {
            if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                window.location.href = 'includes/logout.php';
            }
        });
    </script>
</body>
</html> 