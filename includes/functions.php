<?php
/**
 * Fonctions utilitaires pour la plateforme SMM
 * Gère les fonctions communes utilisées dans toute l'application
 */

require_once 'config.php';
require_once 'database.php';

// =====================================================
// FONCTIONS D'AUTHENTIFICATION ET SESSIONS
// =====================================================

/**
 * Vérifier si l'utilisateur est connecté
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Vérifier si l'utilisateur est admin
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Rediriger vers une page
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Rediriger avec un message d'erreur
 */
function redirectWithError($url, $error) {
    $_SESSION['error'] = $error;
    redirect($url);
}

/**
 * Rediriger avec un message de succès
 */
function redirectWithSuccess($url, $message) {
    $_SESSION['success'] = $message;
    redirect($url);
}

/**
 * Obtenir l'utilisateur connecté
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return db()->selectOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
}

// =====================================================
// FONCTIONS DE VALIDATION ET SÉCURITÉ
// =====================================================

/**
 * Valider une adresse email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valider un numéro de téléphone
 */
function validatePhone($phone) {
    // Format international: +2250700000000
    return preg_match('/^\+[1-9]\d{1,14}$/', $phone);
}

/**
 * Valider une URL
 */
function validateUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Générer un hash sécurisé pour le mot de passe
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Vérifier un mot de passe
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Générer un token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifier un token CSRF
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Nettoyer une entrée utilisateur
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// =====================================================
// FONCTIONS DE FORMATAGE ET AFFICHAGE
// =====================================================

/**
 * Formater un montant en FCFA
 */
function formatCurrency($amount) {
    return number_format($amount, 0, ',', ' ') . ' FCFA';
}

/**
 * Formater une date
 */
function formatDate($date, $format = 'd/m/Y H:i') {
    if (is_string($date)) {
        $date = new DateTime($date);
    }
    return $date->format($format);
}

/**
 * Formater un statut de commande
 */
function formatOrderStatus($status) {
    $statuses = [
        'pending' => 'En attente',
        'paid' => 'Payée',
        'processing' => 'En cours',
        'completed' => 'Terminée',
        'cancelled' => 'Annulée'
    ];
    
    return $statuses[$status] ?? $status;
}

/**
 * Obtenir la classe CSS pour un statut
 */
function getStatusClass($status) {
    $classes = [
        'pending' => 'badge-warning',
        'paid' => 'badge-info',
        'processing' => 'badge-primary',
        'completed' => 'badge-success',
        'cancelled' => 'badge-danger'
    ];
    
    return $classes[$status] ?? 'badge-secondary';
}

/**
 * Limiter le texte à une certaine longueur
 */
function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

// =====================================================
// FONCTIONS DE GESTION DES FICHIERS
// =====================================================

/**
 * Uploader un fichier de capture de paiement
 */
function uploadPaymentScreenshot($file) {
    $allowedExtensions = explode(',', ALLOWED_EXTENSIONS);
    $maxFileSize = MAX_FILE_SIZE;
    
    // Vérifier les erreurs
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Erreur lors de l\'upload du fichier'];
    }
    
    // Vérifier la taille
    if ($file['size'] > $maxFileSize) {
        return ['success' => false, 'message' => 'Le fichier est trop volumineux (max ' . formatFileSize($maxFileSize) . ')'];
    }
    
    // Vérifier l'extension
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedExtensions)) {
        return ['success' => false, 'message' => 'Type de fichier non autorisé. Extensions autorisées: ' . implode(', ', $allowedExtensions)];
    }
    
    // Générer un nom de fichier unique
    $fileName = 'payment_' . time() . '_' . uniqid() . '.' . $fileExtension;
    $uploadPath = UPLOADS_PATH . '/payments/' . $fileName;
    
    // Déplacer le fichier
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'filename' => $fileName, 'path' => $uploadPath];
    }
    
    return ['success' => false, 'message' => 'Erreur lors du déplacement du fichier'];
}

/**
 * Formater la taille d'un fichier
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Supprimer un fichier
 */
function deleteFile($filePath) {
    if (file_exists($filePath)) {
        return unlink($filePath);
    }
    return false;
}

// =====================================================
// FONCTIONS DE CALCUL ET VALIDATION
// =====================================================

/**
 * Calculer le prix d'un service
 */
function calculateServicePrice($serviceId, $quantity) {
    $service = db()->selectOne("SELECT price_per_1000, min_quantity, max_quantity FROM services WHERE id = ?", [$serviceId]);
    
    if (!$service) {
        return false;
    }
    
    // Vérifier les limites de quantité
    if ($quantity < $service['min_quantity'] || $quantity > $service['max_quantity']) {
        return false;
    }
    
    // Calculer le prix total
    $pricePer1000 = $service['price_per_1000'];
    $totalPrice = ($quantity / 1000) * $pricePer1000;
    
    return round($totalPrice, 2);
}

/**
 * Vérifier si une quantité est valide pour un service
 */
function validateServiceQuantity($serviceId, $quantity) {
    $service = db()->selectOne("SELECT min_quantity, max_quantity FROM services WHERE id = ?", [$serviceId]);
    
    if (!$service) {
        return false;
    }
    
    return $quantity >= $service['min_quantity'] && $quantity <= $service['max_quantity'];
}

// =====================================================
// FONCTIONS DE NOTIFICATION ET EMAIL
// =====================================================

/**
 * Envoyer un email
 */
function sendEmail($to, $subject, $message, $from = null) {
    if (!$from) {
        $from = SMTP_FROM_EMAIL;
    }
    
    $headers = [
        'From: ' . SMTP_FROM_NAME . ' <' . $from . '>',
        'Reply-To: ' . $from,
        'Content-Type: text/html; charset=UTF-8',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    return mail($to, $subject, $message, implode("\r\n", $headers));
}

/**
 * Envoyer une notification de nouvelle commande à l'admin
 */
function notifyAdminNewOrder($orderId) {
    $order = db()->selectOne("
        SELECT o.*, u.name as user_name, u.email as user_email, s.name as service_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        JOIN services s ON o.service_id = s.id 
        WHERE o.id = ?
    ", [$orderId]);
    
    if (!$order) {
        return false;
    }
    
    $subject = "Nouvelle commande #" . $orderId;
    $message = "
        <h2>Nouvelle commande reçue</h2>
        <p><strong>Commande:</strong> #{$orderId}</p>
        <p><strong>Client:</strong> {$order['user_name']} ({$order['user_email']})</p>
        <p><strong>Service:</strong> {$order['service_name']}</p>
        <p><strong>Quantité:</strong> {$order['quantity']}</p>
        <p><strong>Prix:</strong> " . formatCurrency($order['total_price']) . "</p>
        <p><strong>URL cible:</strong> {$order['target_url']}</p>
        <p><strong>Date:</strong> " . formatDate($order['created_at']) . "</p>
    ";
    
    return sendEmail(ADMIN_EMAIL, $subject, $message);
}

/**
 * Envoyer une notification de changement de statut au client
 */
function notifyClientStatusChange($orderId, $newStatus) {
    $order = db()->selectOne("
        SELECT o.*, u.name as user_name, u.email as user_email, s.name as service_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        JOIN services s ON o.service_id = s.id 
        WHERE o.id = ?
    ", [$orderId]);
    
    if (!$order) {
        return false;
    }
    
    $statusText = formatOrderStatus($newStatus);
    $subject = "Mise à jour de votre commande #" . $orderId;
    $message = "
        <h2>Mise à jour de votre commande</h2>
        <p>Bonjour {$order['user_name']},</p>
        <p>Le statut de votre commande a été mis à jour :</p>
        <p><strong>Commande:</strong> #{$orderId}</p>
        <p><strong>Service:</strong> {$order['service_name']}</p>
        <p><strong>Nouveau statut:</strong> {$statusText}</p>
        <p>Vous recevrez une notification dès que votre commande sera terminée.</p>
        <p>Cordialement,<br>L'équipe " . SITE_NAME . "</p>
    ";
    
    return sendEmail($order['user_email'], $subject, $message);
}

// =====================================================
// FONCTIONS DE GESTION DES SESSIONS
// =====================================================

/**
 * Définir un message flash
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

/**
 * Obtenir un message flash
 */
function getFlashMessage($type) {
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

/**
 * Vérifier s'il y a des messages flash
 */
function hasFlashMessages() {
    return isset($_SESSION['flash']) && !empty($_SESSION['flash']);
}

// =====================================================
// FONCTIONS DE VALIDATION DES FORMULAIRES
// =====================================================

/**
 * Valider les données d'inscription
 */
function validateRegistrationData($data) {
    $errors = [];
    
    if (empty($data['name']) || strlen($data['name']) < 2) {
        $errors['name'] = 'Le nom doit contenir au moins 2 caractères';
    }
    
    if (empty($data['email']) || !validateEmail($data['email'])) {
        $errors['email'] = 'Adresse email invalide';
    }
    
    if (empty($data['phone']) || !validatePhone($data['phone'])) {
        $errors['phone'] = 'Numéro de téléphone invalide';
    }
    
    if (empty($data['password']) || strlen($data['password']) < 6) {
        $errors['password'] = 'Le mot de passe doit contenir au moins 6 caractères';
    }
    
    if ($data['password'] !== $data['confirm_password']) {
        $errors['confirm_password'] = 'Les mots de passe ne correspondent pas';
    }
    
    return $errors;
}

/**
 * Valider les données de connexion
 */
function validateLoginData($data) {
    $errors = [];
    
    if (empty($data['email'])) {
        $errors['email'] = 'L\'email est requis';
    }
    
    if (empty($data['password'])) {
        $errors['password'] = 'Le mot de passe est requis';
    }
    
    return $errors;
}

/**
 * Valider les données de commande
 */
function validateOrderData($data) {
    $errors = [];
    
    if (empty($data['service_id'])) {
        $errors['service_id'] = 'Veuillez sélectionner un service';
    }
    
    if (empty($data['quantity']) || !is_numeric($data['quantity']) || $data['quantity'] < 1) {
        $errors['quantity'] = 'La quantité doit être un nombre positif';
    }
    
    if (empty($data['target_url']) || !validateUrl($data['target_url'])) {
        $errors['target_url'] = 'URL cible invalide';
    }
    
    return $errors;
}
?>