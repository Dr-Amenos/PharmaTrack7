# PharmaTrack

PharmaTrack est une application web permettant aux utilisateurs de rechercher des médicaments dans les pharmacies de Tunisie en fonction de leur localisation.

## Fonctionnalités

- Interface utilisateur et administrateur
- Recherche de médicaments par nom
- Géolocalisation des utilisateurs et des pharmacies
- Gestion des stocks de médicaments
- Système de suggestions de médicaments
- Interface responsive et moderne
- Animations et effets visuels

## Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache, Nginx, etc.)
- Navigateur web moderne avec support JavaScript

## Installation

1. Clonez le dépôt :
```bash
git clone https://github.com/votre-username/pharmatrack.git
cd pharmatrack
```

2. Créez la base de données :
- Ouvrez MySQL Workbench
- Exécutez le script `database/schema.sql`

3. Configurez la connexion à la base de données :
- Ouvrez le fichier `includes/config.php`
- Modifiez les paramètres de connexion selon votre configuration :
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'votre_utilisateur');
define('DB_PASS', 'votre_mot_de_passe');
define('DB_NAME', 'pharmatrack');
```

4. Configurez votre serveur web :
- Assurez-vous que le dossier racine pointe vers le répertoire du projet
- Activez le module mod_rewrite si vous utilisez Apache
- Vérifiez que PHP a les permissions nécessaires pour écrire dans les dossiers

## Structure du projet

```
pharmatrack/
├── css/
│   └── style.css
├── js/
│   └── main.js
├── includes/
│   ├── config.php
│   ├── functions.php
│   ├── user_register.php
│   ├── pharmacy_signup.php
│   ├── admin_login.php
│   ├── search.php
│   ├── get_suggestions.php
│   ├── manage_medicaments.php
│   └── logout.php
├── database/
│   └── schema.sql
├── index.html
├── user_register.php
├── user_search.php
├── admin_signup.php
├── admin_login.php
└── admin_dashboard.php
```

## Utilisation

### Pour les utilisateurs

1. Accédez à la page d'accueil
2. Cliquez sur "Utilisateur"
3. Remplissez le formulaire d'inscription
4. Autorisez la géolocalisation
5. Recherchez des médicaments

### Pour les pharmacies

1. Accédez à la page d'accueil
2. Cliquez sur "Administrateur"
3. Créez un compte pharmacie
4. Connectez-vous
5. Gérez votre stock de médicaments

## Sécurité

- Les mots de passe sont hashés avec password_hash()
- Les entrées utilisateur sont nettoyées
- Protection contre les injections SQL avec PDO
- Gestion des sessions sécurisée
- Limitation des tentatives de connexion

## Contribution

Les contributions sont les bienvenues ! N'hésitez pas à :

1. Fork le projet
2. Créer une branche pour votre fonctionnalité
3. Commiter vos changements
4. Pousser vers la branche
5. Ouvrir une Pull Request

## Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## Support

Pour toute question ou problème, veuillez ouvrir une issue dans le dépôt GitHub. 