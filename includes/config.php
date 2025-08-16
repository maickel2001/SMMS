<?php
/**
 * Configuration principale de la plateforme SMM
 * Gère les variables d'environnement et la configuration globale
 */

// Désactiver l'affichage des erreurs en production
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Configuration des sessions
session_start();
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);

// Chargement des variables d'environnement
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Supprimer les guillemets
            if (preg_match('/^(["\'])(.*)\1$/', $value, $matches)) {
                $value = $matches[2];
            }
            
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
    return true;
}

// Charger le fichier .env
loadEnv(__DIR__ . '/../.env');

// Configuration de la base de données
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'smm_platform');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

// Configuration SMTP
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? 'smtp-relay.sendinblue.com');
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? '587');
define('SMTP_USERNAME', $_ENV['SMTP_USERNAME'] ?? '');
define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD'] ?? '');
define('SMTP_FROM_EMAIL', $_ENV['SMTP_FROM_EMAIL'] ?? 'noreply@smmplatform.com');
define('SMTP_FROM_NAME', $_ENV['SMTP_FROM_NAME'] ?? 'SMM Platform');

// Configuration Mobile Money
define('MOMO_NUMBER', $_ENV['MOMO_NUMBER'] ?? '+2250700000000');
define('MOMO_OPERATOR', $_ENV['MOMO_OPERATOR'] ?? 'MoMo');

// Configuration du site
define('SITE_NAME', $_ENV['SITE_NAME'] ?? 'SMM Platform');
define('SITE_URL', $_ENV['SITE_URL'] ?? 'http://localhost');
define('CURRENCY', $_ENV['CURRENCY'] ?? 'FCFA');

// Sécurité
define('JWT_SECRET', $_ENV['JWT_SECRET'] ?? 'default_secret_key_change_in_production');
define('SESSION_SECRET', $_ENV['SESSION_SECRET'] ?? 'default_session_secret');

// Upload
define('MAX_FILE_SIZE', $_ENV['MAX_FILE_SIZE'] ?? 5242880); // 5MB
define('ALLOWED_EXTENSIONS', $_ENV['ALLOWED_EXTENSIONS'] ?? 'jpg,jpeg,png');
define('UPLOAD_PATH', $_ENV['UPLOAD_PATH'] ?? 'uploads/payments/');

// Configuration admin
define('ADMIN_EMAIL', $_ENV['ADMIN_EMAIL'] ?? 'admin@smmplatform.com');
define('ADMIN_PASSWORD', $_ENV['ADMIN_PASSWORD'] ?? 'admin123');

// Constantes globales
define('ROOT_PATH', dirname(__DIR__));
define('INCLUDES_PATH', __DIR__);
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('TEMPLATES_PATH', ROOT_PATH . '/templates');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');

// Configuration des fuseaux horaires
date_default_timezone_set('Africa/Abidjan');

// Configuration des erreurs personnalisées
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $error_message = date('Y-m-d H:i:s') . " - Error [$errno]: $errstr in $errfile on line $errline\n";
    error_log($error_message, 3, ROOT_PATH . '/logs/error.log');
    
    return true;
}

set_error_handler('customErrorHandler');

// Créer le dossier de logs s'il n'existe pas
if (!is_dir(ROOT_PATH . '/logs')) {
    mkdir(ROOT_PATH . '/logs', 0755, true);
}

// Créer le dossier d'upload s'il n'existe pas
if (!is_dir(UPLOADS_PATH)) {
    mkdir(UPLOADS_PATH, 0755, true);
}

if (!is_dir(UPLOADS_PATH . '/payments')) {
    mkdir(UPLOADS_PATH . '/payments', 0755, true);
}

// Fonction utilitaire pour obtenir une variable d'environnement
function env($key, $default = null) {
    return $_ENV[$key] ?? $default;
}

// Fonction pour vérifier si on est en mode développement
function isDevelopment() {
    return $_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1';
}

// Activer l'affichage des erreurs en développement
if (isDevelopment()) {
    ini_set('display_errors', 1);
}
?>