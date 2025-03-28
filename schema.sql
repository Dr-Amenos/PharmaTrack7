CREATE DATABASE IF NOT EXISTS pharmatrack;
USE pharmatrack;

-- Table des régions
CREATE TABLE regions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(50) NOT NULL
);

-- Table des pharmacies
CREATE TABLE pharmacies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone VARCHAR(20) NOT NULL,
    region_id INT,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    jour_repos ENUM('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche', 'Aucun'),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (region_id) REFERENCES regions(id)
);

-- Table des utilisateurs
CREATE TABLE utilisateurs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    region_id INT,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (region_id) REFERENCES regions(id)
);

-- Table des médicaments
CREATE TABLE medicaments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des stocks de pharmacies
CREATE TABLE stocks_pharmacies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pharmacie_id INT,
    medicament_id INT,
    quantite INT NOT NULL DEFAULT 0,
    prix DECIMAL(10, 2) NOT NULL,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pharmacie_id) REFERENCES pharmacies(id),
    FOREIGN KEY (medicament_id) REFERENCES medicaments(id)
);

-- Insertion des régions tunisiennes
INSERT INTO regions (nom) VALUES 
('Ariana'), ('Beja'), ('Ben Arous'), ('Bizerte'), ('Gabes'),
('Gafsa'), ('Jendouba'), ('Kairouan'), ('Kasserine'), ('Kebili'),
('Kef'), ('Mahdia'), ('Manouba'), ('Medenine'), ('Monastir'),
('Nabeul'), ('Sfax'), ('Sidi Bouzid'), ('Siliana'), ('Sousse'),
('Tataouine'), ('Tozeur'), ('Tunis'), ('Zaghouan');