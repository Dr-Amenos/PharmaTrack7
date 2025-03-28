// Configuration des particules
particlesJS("particles-js", {
    "particles": {
        "number": {
            "value": 80,
            "density": {
                "enable": true,
                "value_area": 800
            }
        },
        "color": {
            "value": "#ffffff"
        },
        "shape": {
            "type": "circle",
            "stroke": {
                "width": 0,
                "color": "#000000"
            }
        },
        "opacity": {
            "value": 0.5,
            "random": false,
            "anim": {
                "enable": false,
                "speed": 1,
                "opacity_min": 0.1,
                "sync": false
            }
        },
        "size": {
            "value": 3,
            "random": true,
            "anim": {
                "enable": false,
                "speed": 40,
                "size_min": 0.1,
                "sync": false
            }
        },
        "line_linked": {
            "enable": true,
            "distance": 150,
            "color": "#ffffff",
            "opacity": 0.4,
            "width": 1
        },
        "move": {
            "enable": true,
            "speed": 6,
            "direction": "none",
            "random": false,
            "straight": false,
            "out_mode": "out",
            "bounce": false,
            "attract": {
                "enable": false,
                "rotateX": 600,
                "rotateY": 1200
            }
        }
    },
    "interactivity": {
        "detect_on": "canvas",
        "events": {
            "onhover": {
                "enable": true,
                "mode": "repulse"
            },
            "onclick": {
                "enable": true,
                "mode": "push"
            },
            "resize": true
        },
        "modes": {
            "grab": {
                "distance": 400,
                "line_linked": {
                    "opacity": 1
                }
            },
            "bubble": {
                "distance": 400,
                "size": 40,
                "duration": 2,
                "opacity": 8,
                "speed": 3
            },
            "repulse": {
                "distance": 200,
                "duration": 0.4
            },
            "push": {
                "particles_nb": 4
            },
            "remove": {
                "particles_nb": 2
            }
        }
    },
    "retina_detect": true
});

// Variables globales
let userLocation = null;
let searchTimeout = null;

// Fonction pour obtenir la localisation de l'utilisateur
function getUserLocation() {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            reject(new Error('La géolocalisation n\'est pas supportée par votre navigateur'));
            return;
        }

        navigator.geolocation.getCurrentPosition(
            position => {
                userLocation = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                };
                resolve(userLocation);
            },
            error => {
                reject(error);
            }
        );
    });
}

// Fonction pour vérifier si un numéro de téléphone est valide
function isValidPhoneNumber(phone) {
    return /^7[0-9]{7}$/.test(phone);
}

// Fonction pour vérifier si un mot de passe est valide
function isValidPassword(password) {
    return password.length >= 8;
}

// Fonction pour vérifier si les mots de passe correspondent
function passwordsMatch(password, confirmPassword) {
    return password === confirmPassword;
}

// Fonction pour gérer les suggestions de médicaments
function handleMedicamentSuggestions(input) {
    const query = input.value.trim().toLowerCase();
    const suggestionsDiv = document.getElementById('suggestions');
    suggestionsDiv.innerHTML = '';

    if (query.length > 0) {
        fetch(`includes/get_suggestions.php?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(suggestions => {
                suggestions.forEach(medicament => {
                    const div = document.createElement('div');
                    div.className = 'suggestion-item';
                    div.textContent = medicament;
                    div.onclick = () => {
                        input.value = medicament;
                        suggestionsDiv.innerHTML = '';
                        if (document.getElementById('searchButton')) {
                            document.getElementById('searchButton').click();
                        }
                    };
                    suggestionsDiv.appendChild(div);
                });
            })
            .catch(error => console.error('Erreur:', error));
    }
}

// Fonction pour calculer la distance entre deux points
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Rayon de la Terre en km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = 
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
        Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

// Fonction pour trier les pharmacies par distance
function sortPharmaciesByDistance(pharmacies, userLocation) {
    return pharmacies.sort((a, b) => {
        const distA = calculateDistance(
            userLocation.latitude,
            userLocation.longitude,
            a.latitude,
            a.longitude
        );
        const distB = calculateDistance(
            userLocation.latitude,
            userLocation.longitude,
            b.latitude,
            b.longitude
        );
        return distA - distB;
    });
}

// Fonction pour afficher les résultats de recherche
function displaySearchResults(pharmacies) {
    const resultDiv = document.getElementById('result');
    resultDiv.innerHTML = '';

    if (pharmacies.length === 0) {
        resultDiv.innerHTML = '<p class="error-message">Aucune pharmacie trouvée avec ce médicament.</p>';
        return;
    }

    pharmacies.forEach((pharmacie, index) => {
        const div = document.createElement('div');
        div.className = 'pharmacie';
        div.style.animationDelay = `${index * 0.2}s`;

        const distance = calculateDistance(
            userLocation.latitude,
            userLocation.longitude,
            pharmacie.latitude,
            pharmacie.longitude
        );

        div.innerHTML = `
            <p><strong>Pharmacie:</strong> ${pharmacie.nom}</p>
            <p><strong>Distance:</strong> ${distance.toFixed(2)} km</p>
            <p><strong>Stock disponible:</strong> ${pharmacie.quantite}</p>
            <p><strong>Prix:</strong> ${pharmacie.prix} TND</p>
            <p><strong>Jour de repos:</strong> ${pharmacie.jour_repos}</p>
            <a href="https://www.google.com/maps?q=${pharmacie.latitude},${pharmacie.longitude}" target="_blank">
                <button class="map-button">Voir sur Google Maps</button>
            </a>
        `;
        resultDiv.appendChild(div);
    });
}

// Fonction pour gérer la recherche de médicaments
function handleSearch() {
    const searchInput = document.getElementById('searchInput');
    const medicament = searchInput.value.trim();

    if (!medicament) {
        document.getElementById('result').innerHTML = 
            '<p class="error-message">Veuillez entrer un nom de médicament.</p>';
        return;
    }

    if (!userLocation) {
        document.getElementById('result').innerHTML = 
            '<p class="error-message">Veuillez autoriser la géolocalisation.</p>';
        return;
    }

    fetch(`includes/search.php?medicament=${encodeURIComponent(medicament)}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                document.getElementById('result').innerHTML = 
                    `<p class="error-message">${data.error}</p>`;
                return;
            }

            const sortedPharmacies = sortPharmaciesByDistance(data, userLocation);
            displaySearchResults(sortedPharmacies);
        })
        .catch(error => {
            document.getElementById('result').innerHTML = 
                '<p class="error-message">Une erreur est survenue lors de la recherche.</p>';
            console.error('Erreur:', error);
        });
}

// Fonction pour gérer l'inscription d'une pharmacie
function handlePharmacySignup(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Validation des champs
    if (!isValidPhoneNumber(formData.get('telephone'))) {
        alert('Le numéro de téléphone doit commencer par 7 et contenir 8 chiffres');
        return;
    }
    
    if (!isValidPassword(formData.get('mot_de_passe'))) {
        alert('Le mot de passe doit contenir au moins 8 caractères');
        return;
    }
    
    if (!passwordsMatch(formData.get('mot_de_passe'), formData.get('confirm_mot_de_passe'))) {
        alert('Les mots de passe ne correspondent pas');
        return;
    }

    // Obtenir la localisation
    getUserLocation()
        .then(location => {
            formData.append('latitude', location.latitude);
            formData.append('longitude', location.longitude);

            // Envoyer les données au serveur
            fetch('includes/pharmacy_signup.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Inscription réussie !');
                    window.location.href = 'admin_login.php';
                } else {
                    alert(data.error || 'Une erreur est survenue lors de l\'inscription');
                }
            })
            .catch(error => {
                alert('Une erreur est survenue lors de l\'inscription');
                console.error('Erreur:', error);
            });
        })
        .catch(error => {
            alert('Veuillez autoriser la géolocalisation pour continuer');
        });
}

// Fonction pour gérer la connexion admin
function handleAdminLogin(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
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
}

// Fonction pour gérer la gestion des médicaments
function handleMedicamentManagement(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    fetch('includes/manage_medicaments.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Opération réussie !');
            form.reset();
        } else {
            alert(data.error || 'Une erreur est survenue');
        }
    })
    .catch(error => {
        alert('Une erreur est survenue');
        console.error('Erreur:', error);
    });
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    // Obtenir la localisation de l'utilisateur
    getUserLocation().catch(error => {
        console.error('Erreur de géolocalisation:', error);
    });

    // Gérer les suggestions de médicaments
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                handleMedicamentSuggestions(searchInput);
            }, 300);
        });
    }

    // Gérer les formulaires
    const pharmacySignupForm = document.getElementById('pharmacySignupForm');
    if (pharmacySignupForm) {
        pharmacySignupForm.addEventListener('submit', handlePharmacySignup);
    }

    const adminLoginForm = document.getElementById('adminLoginForm');
    if (adminLoginForm) {
        adminLoginForm.addEventListener('submit', handleAdminLogin);
    }

    const medicamentManagementForm = document.getElementById('medicamentManagementForm');
    if (medicamentManagementForm) {
        medicamentManagementForm.addEventListener('submit', handleMedicamentManagement);
    }
});
