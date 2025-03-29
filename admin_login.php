<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Si la pharmacie est déjà connectée, rediriger vers le tableau de bord
if (isset($_SESSION['pharmacy_id'])) {
    header('Location: admin_dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaTrack - Connexion Pharmacie</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="particles" id="particles-js"></div>
    <div class="container">
        <div class="floating-circles"></div>
        <div class="floating-circles"></div>
        <h1>Connexion Pharmacie</h1>
        <form id="adminLoginForm" action="includes/admin_login.php" method="POST">
            <div class="form-group">
                <input type="email" name="email" id="email" placeholder="Email de la pharmacie" required>
            </div>
            <div class="form-group">
                <input type="password" name="mot_de_passe" id="mot_de_passe" placeholder="Mot de passe" required>
            </div>
            <div class="form-group">
                <button type="submit" class="submit-button">
                    <span class="button-icon">🔑</span>
                    <span class="button-text">Se connecter</span>
                </button>
            </div>
            <div class="form-group">
                <a href="admin_signup.php" class="register-link">
                    <button type="button" class="register-button">
                        <span class="button-icon">📝</span>
                        <span class="button-text">Créer un compte</span>
                    </button>
                </a>
            </div>
            <div class="form-group">
                <a href="index.html" class="back-link">
                    <button type="button" class="back-button">
                        <span class="button-icon">⬅️</span>
                        <span class="button-text">Retour</span>
                    </button>
                </a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        document.getElementById('adminLoginForm').addEventListener('submit', (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            
            fetch('includes/admin_login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'admin_dashboard.php';
                } else {
                    alert(data.error || 'Identifiants incorrects');
                }
            })
            .catch(error => {
                alert('Une erreur est survenue lors de la connexion');
                console.error('Erreur:', error);
            });
        });
    </script>
</body>
</html> 