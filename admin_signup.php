<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Récupération des régions
$stmt = $pdo->query("SELECT * FROM regions ORDER BY nom");
$regions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaTrack - Inscription Pharmacie</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="particles" id="particles-js"></div>
    <div class="container">
        <div class="floating-circles"></div>
        <div class="floating-circles"></div>
        <h1>Inscription Pharmacie</h1>
        
        <div class="form-container">
            <form id="pharmacyForm">
                <div class="form-group">
                    <input type="text" id="nom" name="nom" placeholder="Nom de la pharmacie" required>
                </div>
                <div class="form-group">
                    <input type="email" id="email" name="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Mot de passe" required>
                </div>
                <div class="form-group">
                    <input type="password" id="confirm_mot_de_passe" name="confirm_mot_de_passe" placeholder="Confirmer le mot de passe" required>
                </div>
                <div class="form-group">
                    <input type="tel" id="telephone" name="telephone" placeholder="Numéro de téléphone (7 chiffres)" required>
                </div>
                <div class="form-group">
                    <select id="region" name="region" required>
                        <option value="">Sélectionnez votre région</option>
                        <?php foreach ($regions as $region): ?>
                            <option value="<?php echo $region['id']; ?>"><?php echo htmlspecialchars($region['nom']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <select id="jour_repos" name="jour_repos" required>
                        <option value="">Sélectionnez votre jour de repos</option>
                        <option value="Lundi">Lundi</option>
                        <option value="Mardi">Mardi</option>
                        <option value="Mercredi">Mercredi</option>
                        <option value="Jeudi">Jeudi</option>
                        <option value="Vendredi">Vendredi</option>
                        <option value="Samedi">Samedi</option>
                        <option value="Dimanche">Dimanche</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="button" id="locationBtn" class="location-button">
                        <span class="button-icon">📍</span>
                        <span class="button-text">Autoriser la géolocalisation</span>
                    </button>
                </div>
                <div class="form-group">
                    <button type="submit" class="submit-button">
                        <span class="button-icon">✅</span>
                        <span class="button-text">S'inscrire</span>
                    </button>
                </div>
                <div class="form-group">
                    <a href="index.html" class="back-link">
                        <button type="button" class="back-button">
                            <span class="button-icon">⬅️</span>
                            <span class="button-text">Retour à l'accueil</span>
                        </button>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        // Configuration des particules
        particlesJS('particles-js', {
            particles: {
                number: { value: 80, density: { enable: true, value_area: 800 } },
                color: { value: '#ffffff' },
                shape: { type: 'circle' },
                opacity: { value: 0.5, random: false },
                size: { value: 3, random: true },
                line_linked: {
                    enable: true,
                    distance: 150,
                    color: '#ffffff',
                    opacity: 0.4,
                    width: 1
                },
                move: {
                    enable: true,
                    speed: 6,
                    direction: 'none',
                    random: false,
                    straight: false,
                    out_mode: 'out',
                    bounce: false
                }
            },
            interactivity: {
                detect_on: 'canvas',
                events: {
                    onhover: { enable: true, mode: 'repulse' },
                    onclick: { enable: true, mode: 'push' },
                    resize: true
                }
            },
            retina_detect: true
        });

        // Gestion de la géolocalisation
        const locationBtn = document.getElementById('locationBtn');
        let locationAuthorized = false;
        let userLocation = null;

        locationBtn.addEventListener('click', () => {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        locationAuthorized = true;
                        userLocation = {
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude
                        };
                        locationBtn.classList.add('success');
                        locationBtn.innerHTML = `
                            <span class="button-icon">✅</span>
                            <span class="button-text">Géolocalisation autorisée</span>
                        `;
                    },
                    (error) => {
                        alert('Erreur de géolocalisation : ' + error.message);
                    }
                );
            } else {
                alert('La géolocalisation n\'est pas supportée par votre navigateur');
            }
        });

        // Gestion du formulaire
        document.getElementById('pharmacyForm').addEventListener('submit', (e) => {
            e.preventDefault();

            if (!locationAuthorized) {
                alert('Veuillez autoriser la géolocalisation avant de continuer');
                return;
            }

            const formData = new FormData(e.target);
            formData.append('latitude', userLocation.latitude);
            formData.append('longitude', userLocation.longitude);

            fetch('includes/pharmacy_signup.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Inscription réussie ! Vous pouvez maintenant vous connecter.');
                    window.location.href = 'admin_login.php';
                } else {
                    alert(data.error || 'Une erreur est survenue lors de l\'inscription');
                }
            })
            .catch(error => {
                alert('Une erreur est survenue');
                console.error('Erreur:', error);
            });
        });
    </script>
</body>
</html> 