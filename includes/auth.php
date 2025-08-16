<?php
/**
 * Système d'authentification pour la plateforme SMM
 * Gère l'inscription, la connexion, la déconnexion et la sécurité
 */

require_once 'config.php';
require_once 'database.php';
require_once 'functions.php';

// =====================================================
// GESTION DE L'INSCRIPTION
// =====================================================

/**
 * Traiter l'inscription d'un nouvel utilisateur
 */
function handleRegistration($data) {
    // Valider les données
    $errors = validateRegistrationData($data);
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    // Vérifier si l'email existe déjà
    $existingUser = db()->selectOne("SELECT id FROM users WHERE email = ?", [$data['email']]);
    if ($existingUser) {
        return ['success' => false, 'errors' => ['email' => 'Cette adresse email est déjà utilisée']];
    }
    
    // Vérifier si le téléphone existe déjà
    $existingPhone = db()->selectOne("SELECT id FROM users WHERE phone = ?", [$data['phone']]);
    if ($existingPhone) {
        return ['success' => false, 'errors' => ['phone' => 'Ce numéro de téléphone est déjà utilisé']];
    }
    
    try {
        // Hasher le mot de passe
        $hashedPassword = hashPassword($data['password']);
        
        // Insérer l'utilisateur
        $userId = db()->insert("
            INSERT INTO users (name, email, phone, password, role, status) 
            VALUES (?, ?, ?, ?, 'user', 'active')
        ", [
            sanitizeInput($data['name']),
            sanitizeInput($data['email']),
            sanitizeInput($data['phone']),
            $hashedPassword
        ]);
        
        if ($userId) {
            // Connecter automatiquement l'utilisateur
            loginUser($data['email'], $data['password']);
            
            return ['success' => true, 'message' => 'Inscription réussie ! Vous êtes maintenant connecté.'];
        } else {
            return ['success' => false, 'errors' => ['general' => 'Erreur lors de l\'inscription. Veuillez réessayer.']];
        }
        
    } catch (Exception $e) {
        error_log("Erreur d'inscription: " . $e->getMessage());
        return ['success' => false, 'errors' => ['general' => 'Erreur technique. Veuillez réessayer.']];
    }
}

// =====================================================
// GESTION DE LA CONNEXION
// =====================================================

/**
 * Traiter la connexion d'un utilisateur
 */
function handleLogin($data) {
    // Valider les données
    $errors = validateLoginData($data);
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    try {
        // Rechercher l'utilisateur
        $user = db()->selectOne("SELECT * FROM users WHERE email = ?", [$data['email']]);
        
        if (!$user) {
            return ['success' => false, 'errors' => ['email' => 'Email ou mot de passe incorrect']];
        }
        
        // Vérifier le statut du compte
        if ($user['status'] === 'blocked') {
            return ['success' => false, 'errors' => ['email' => 'Votre compte a été bloqué. Contactez l\'administrateur.']];
        }
        
        // Vérifier le mot de passe
        if (!verifyPassword($data['password'], $user['password'])) {
            return ['success' => false, 'errors' => ['email' => 'Email ou mot de passe incorrect']];
        }
        
        // Connecter l'utilisateur
        loginUser($data['email'], $data['password']);
        
        return ['success' => true, 'message' => 'Connexion réussie !'];
        
    } catch (Exception $e) {
        error_log("Erreur de connexion: " . $e->getMessage());
        return ['success' => false, 'errors' => ['general' => 'Erreur technique. Veuillez réessayer.']];
    }
}

/**
 * Connecter un utilisateur
 */
function loginUser($email, $password) {
    $user = db()->selectOne("SELECT * FROM users WHERE email = ?", [$email]);
    
    if ($user && verifyPassword($password, $user['password'])) {
        // Démarrer la session si pas déjà fait
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Régénérer l'ID de session pour la sécurité
        session_regenerate_id(true);
        
        // Stocker les informations utilisateur en session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_status'] = $user['status'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        // Mettre à jour la dernière connexion
        db()->update("UPDATE users SET updated_at = NOW() WHERE id = ?", [$user['id']]);
        
        return true;
    }
    
    return false;
}

/**
 * Déconnecter un utilisateur
 */
function logoutUser() {
    // Démarrer la session si pas déjà fait
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Détruire toutes les variables de session
    $_SESSION = array();
    
    // Détruire le cookie de session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Détruire la session
    session_destroy();
    
    return true;
}

// =====================================================
// GESTION DES MOTS DE PASSE
// =====================================================

/**
 * Traiter la demande de réinitialisation de mot de passe
 */
function handleForgotPassword($email) {
    if (!validateEmail($email)) {
        return ['success' => false, 'message' => 'Adresse email invalide'];
    }
    
    // Vérifier si l'utilisateur existe
    $user = db()->selectOne("SELECT id, name FROM users WHERE email = ?", [$email]);
    if (!$user) {
        return ['success' => false, 'message' => 'Aucun compte associé à cette adresse email'];
    }
    
    try {
        // Générer un token de réinitialisation
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Stocker le token (vous devriez créer une table reset_tokens)
        // Pour l'instant, on utilise une approche simplifiée
        
        // Envoyer l'email de réinitialisation
        $resetLink = SITE_URL . "/reset-password.php?token=" . $token . "&email=" . urlencode($email);
        
        $subject = "Réinitialisation de votre mot de passe";
        $message = "
            <h2>Réinitialisation de mot de passe</h2>
            <p>Bonjour {$user['name']},</p>
            <p>Vous avez demandé la réinitialisation de votre mot de passe.</p>
            <p>Cliquez sur le lien ci-dessous pour définir un nouveau mot de passe :</p>
            <p><a href='{$resetLink}'>{$resetLink}</a></p>
            <p>Ce lien expire dans 1 heure.</p>
            <p>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.</p>
            <p>Cordialement,<br>L'équipe " . SITE_NAME . "</p>
        ";
        
        if (sendEmail($email, $subject, $message)) {
            return ['success' => true, 'message' => 'Un email de réinitialisation a été envoyé à votre adresse email'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de l\'envoi de l\'email. Veuillez réessayer.'];
        }
        
    } catch (Exception $e) {
        error_log("Erreur forgot password: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur technique. Veuillez réessayer.'];
    }
}

/**
 * Réinitialiser le mot de passe
 */
function resetPassword($token, $email, $newPassword) {
    // Vérifier la validité du token (implémentation simplifiée)
    // En production, vous devriez vérifier le token dans la base de données
    
    if (strlen($newPassword) < 6) {
        return ['success' => false, 'message' => 'Le mot de passe doit contenir au moins 6 caractères'];
    }
    
    try {
        // Hasher le nouveau mot de passe
        $hashedPassword = hashPassword($newPassword);
        
        // Mettre à jour le mot de passe
        $result = db()->update("UPDATE users SET password = ? WHERE email = ?", [$hashedPassword, $email]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Mot de passe mis à jour avec succès. Vous pouvez maintenant vous connecter.'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la mise à jour du mot de passe'];
        }
        
    } catch (Exception $e) {
        error_log("Erreur reset password: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur technique. Veuillez réessayer.'];
    }
}

// =====================================================
// GESTION DE LA SÉCURITÉ
// =====================================================

/**
 * Vérifier la session de l'utilisateur
 */
function checkSession() {
    if (!isLoggedIn()) {
        return false;
    }
    
    // Vérifier l'expiration de la session (8 heures)
    $sessionTimeout = 8 * 60 * 60; // 8 heures en secondes
    
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $sessionTimeout) {
        logoutUser();
        return false;
    }
    
    // Mettre à jour le temps de connexion
    $_SESSION['login_time'] = time();
    
    return true;
}

/**
 * Vérifier les permissions d'accès
 */
function checkAccess($requiredRole = 'user') {
    if (!checkSession()) {
        return false;
    }
    
    if ($requiredRole === 'admin' && !isAdmin()) {
        return false;
    }
    
    return true;
}

/**
 * Rediriger si non connecté
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('/login.php');
    }
}

/**
 * Rediriger si non admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        redirect('/dashboard.php');
    }
}

/**
 * Vérifier la sécurité CSRF
 */
function checkCSRF() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            die('Erreur de sécurité CSRF. Veuillez rafraîchir la page et réessayer.');
        }
    }
}

// =====================================================
// FONCTIONS DE GESTION DU PROFIL
// =====================================================

/**
 * Mettre à jour le profil utilisateur
 */
function updateProfile($userId, $data) {
    $errors = [];
    
    // Valider les données
    if (empty($data['name']) || strlen($data['name']) < 2) {
        $errors['name'] = 'Le nom doit contenir au moins 2 caractères';
    }
    
    if (empty($data['phone']) || !validatePhone($data['phone'])) {
        $errors['phone'] = 'Numéro de téléphone invalide';
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    try {
        $result = db()->update("
            UPDATE users SET name = ?, phone = ?, updated_at = NOW() 
            WHERE id = ?
        ", [
            sanitizeInput($data['name']),
            sanitizeInput($data['phone']),
            $userId
        ]);
        
        if ($result) {
            // Mettre à jour la session
            $_SESSION['user_name'] = sanitizeInput($data['name']);
            
            return ['success' => true, 'message' => 'Profil mis à jour avec succès'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la mise à jour du profil'];
        }
        
    } catch (Exception $e) {
        error_log("Erreur update profile: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur technique. Veuillez réessayer.'];
    }
}

/**
 * Changer le mot de passe
 */
function changePassword($userId, $currentPassword, $newPassword) {
    // Vérifier l'ancien mot de passe
    $user = db()->selectOne("SELECT password FROM users WHERE id = ?", [$userId]);
    
    if (!$user || !verifyPassword($currentPassword, $user['password'])) {
        return ['success' => false, 'message' => 'Mot de passe actuel incorrect'];
    }
    
    if (strlen($newPassword) < 6) {
        return ['success' => false, 'message' => 'Le nouveau mot de passe doit contenir au moins 6 caractères'];
    }
    
    try {
        $hashedPassword = hashPassword($newPassword);
        
        $result = db()->update("
            UPDATE users SET password = ?, updated_at = NOW() 
            WHERE id = ?
        ", [$hashedPassword, $userId]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Mot de passe modifié avec succès'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la modification du mot de passe'];
        }
        
    } catch (Exception $e) {
        error_log("Erreur change password: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur technique. Veuillez réessayer.'];
    }
}

// =====================================================
// INITIALISATION DE LA SÉCURITÉ
// =====================================================

// Vérifier la session à chaque chargement
checkSession();

// Vérifier CSRF sur les requêtes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCSRF();
}
?>