<?php
require_once 'config.php';

// Destruction de la session
session_destroy();

// Redirection vers la page d'accueil
header('Location: ../index.html');
exit; 