<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Déconnexion de l'utilisateur
logoutUser();

// Redirection vers la page d'accueil avec un message de succès
setFlashMessage('success', 'Vous avez été déconnecté avec succès.');
redirect('/');
?>