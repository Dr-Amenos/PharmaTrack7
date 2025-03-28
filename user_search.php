<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: user_register.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaTrack - Recherche de Médicaments</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="particles" id="particles-js"></div>
    <div class="container">
        <div class="floating-circles"></div>
        <div class="floating-circles"></div>
        <h1>Recherche de Médicaments</h1>
        <div class="user-info">
            <p>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?></p>
        </div>
        <div class="search-container">
            <div class="form-group">
                <input type="text" id="searchInput" placeholder="Entrez le nom du médicament...">
            </div>
            <div id="suggestions"></div>
            <div class="form-group">
                <button id="searchButton" class="search-button">
                    <span class="button-icon">🔍</span>
                    <span class="button-text">Rechercher</span>
                </button>
            </div>
            <div class="form-group">
                <a href="index.html" class="back-link">
                    <button type="button" class="back-button">
                        <span class="button-icon">⬅️</span>
                        <span class="button-text">Retour</span>
                    </button>
                </a>
            </div>
        </div>
        <div id="result"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('searchInput');
            const searchButton = document.getElementById('searchButton');
            const resultDiv = document.getElementById('result');

            // Gestion de la recherche
            function performSearch() {
                const medicament = searchInput.value.trim();
                if (!medicament) {
                    resultDiv.innerHTML = '<p class="error-message">Veuillez entrer un nom de médicament.</p>';
                    return;
                }

                if (!userLocation) {
                    resultDiv.innerHTML = '<p class="error-message">Veuillez autoriser la géolocalisation.</p>';
                    return;
                }

                fetch(`includes/search.php?medicament=${encodeURIComponent(medicament)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            resultDiv.innerHTML = `<p class="error-message">${data.error}</p>`;
                            return;
                        }

                        const sortedPharmacies = sortPharmaciesByDistance(data, userLocation);
                        displaySearchResults(sortedPharmacies);
                    })
                    .catch(error => {
                        resultDiv.innerHTML = '<p class="error-message">Une erreur est survenue lors de la recherche.</p>';
                        console.error('Erreur:', error);
                    });
            }

            // Événements de recherche
            searchButton.addEventListener('click', performSearch);
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });

            // Gestion des suggestions
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    handleMedicamentSuggestions(searchInput);
                }, 300);
            });
        });
    </script>
</body>
</html> 