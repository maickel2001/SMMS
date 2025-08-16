<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$page_title = 'Mon profil';
$page_description = 'Gérez vos informations personnelles et votre mot de passe';

// Vérifier que l'utilisateur est connecté
requireLogin();

$user = $_SESSION['user'];
$errors = [];
$success_message = '';

// Traitement de la mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    checkCSRF();
    
    if ($_POST['action'] === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        // Validation
        if (empty($name)) {
            $errors['name'] = 'Le nom est requis';
        }
        
        if (empty($email)) {
            $errors['email'] = 'L\'email est requis';
        } elseif (!validateEmail($email)) {
            $errors['email'] = 'L\'email n\'est pas valide';
        }
        
        if (empty($phone)) {
            $errors['phone'] = 'Le téléphone est requis';
        }
        
        // Vérifier si l'email existe déjà (sauf pour l'utilisateur actuel)
        if (empty($errors)) {
            $existing_user = db()->select("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $user['id']]);
            if (!empty($existing_user)) {
                $errors['email'] = 'Cet email est déjà utilisé par un autre compte';
            }
        }
        
        // Mettre à jour le profil si pas d'erreurs
        if (empty($errors)) {
            $updated = updateProfile($user['id'], [
                'name' => $name,
                'email' => $email,
                'phone' => $phone
            ]);
            
            if ($updated) {
                // Mettre à jour la session
                $_SESSION['user']['name'] = $name;
                $_SESSION['user']['email'] = $email;
                $_SESSION['user']['phone'] = $phone;
                
                $success_message = 'Profil mis à jour avec succès !';
            } else {
                $errors['general'] = 'Erreur lors de la mise à jour du profil';
            }
        }
    }
    
    // Traitement du changement de mot de passe
    elseif ($_POST['action'] === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($current_password)) {
            $errors['current_password'] = 'Le mot de passe actuel est requis';
        }
        
        if (empty($new_password)) {
            $errors['new_password'] = 'Le nouveau mot de passe est requis';
        } elseif (strlen($new_password) < 8) {
            $errors['new_password'] = 'Le nouveau mot de passe doit contenir au moins 8 caractères';
        }
        
        if (empty($confirm_password)) {
            $errors['confirm_password'] = 'La confirmation du mot de passe est requise';
        } elseif ($new_password !== $confirm_password) {
            $errors['confirm_password'] = 'Les mots de passe ne correspondent pas';
        }
        
        // Vérifier le mot de passe actuel
        if (empty($errors)) {
            $user_check = db()->select("SELECT password FROM users WHERE id = ?", [$user['id']]);
            if (empty($user_check) || !password_verify($current_password, $user_check[0]['password'])) {
                $errors['current_password'] = 'Le mot de passe actuel est incorrect';
            }
        }
        
        // Changer le mot de passe si pas d'erreurs
        if (empty($errors)) {
            $changed = changePassword($user['id'], $current_password, $new_password);
            
            if ($changed) {
                $success_message = 'Mot de passe changé avec succès !';
                // Vider les champs
                $_POST['current_password'] = '';
                $_POST['new_password'] = '';
                $_POST['confirm_password'] = '';
            } else {
                $errors['general'] = 'Erreur lors du changement de mot de passe';
            }
        }
    }
}

include '../templates/header.php';
?>

<div class="dashboard-page">
    <!-- Sidebar -->
    <aside class="dashboard-sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fas fa-chart-line"></i>
                <span>SMM Platform</span>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="/dashboard.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/new-order.php" class="nav-link">
                        <i class="fas fa-plus-circle"></i>
                        <span>Nouvelle commande</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/my-orders.php" class="nav-link">
                        <i class="fas fa-list-alt"></i>
                        <span>Mes commandes</span>
                    </a>
                </li>
                <li class="nav-item active">
                    <a href="/profile.php" class="nav-link">
                        <i class="fas fa-user"></i>
                        <span>Profil</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/support.php" class="nav-link">
                        <i class="fas fa-headset"></i>
                        <span>Support</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="sidebar-footer">
            <a href="/logout.php" class="sidebar-logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </div>
    </aside>

    <!-- Main content -->
    <main class="dashboard-main">
        <header class="dashboard-header">
            <div class="header-content">
                <div class="header-title">
                    <h1>Mon profil</h1>
                    <p>Gérez vos informations personnelles et votre sécurité</p>
                </div>
                <div class="header-actions">
                    <a href="/dashboard.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i>
                        Retour au dashboard
                    </a>
                </div>
            </div>
        </header>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($errors['general'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($errors['general']); ?>
            </div>
        <?php endif; ?>

        <div class="profile-container">
            <div class="profile-grid">
                <!-- Informations personnelles -->
                <div class="profile-section">
                    <h3 class="section-title">
                        <i class="fas fa-user-edit"></i>
                        Informations personnelles
                    </h3>
                    
                    <div class="profile-card">
                        <form method="POST" class="profile-form">
                            <?php echo generateCSRFToken(); ?>
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="form-group">
                                <label for="name" class="form-label">Nom complet *</label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? $user['name']); ?>"
                                       class="form-input <?php echo isset($errors['name']) ? 'error' : ''; ?>"
                                       required>
                                <?php if (isset($errors['name'])): ?>
                                    <span class="form-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <?php echo htmlspecialchars($errors['name']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="email" class="form-label">Adresse email *</label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? $user['email']); ?>"
                                       class="form-input <?php echo isset($errors['email']) ? 'error' : ''; ?>"
                                       required>
                                <?php if (isset($errors['email'])): ?>
                                    <span class="form-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <?php echo htmlspecialchars($errors['email']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone" class="form-label">Numéro de téléphone *</label>
                                <input type="tel" 
                                       id="phone" 
                                       name="phone" 
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? $user['phone']); ?>"
                                       class="form-input <?php echo isset($errors['phone']) ? 'error' : ''; ?>"
                                       required>
                                <?php if (isset($errors['phone'])): ?>
                                    <span class="form-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <?php echo htmlspecialchars($errors['phone']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Mettre à jour le profil
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Changement de mot de passe -->
                <div class="profile-section">
                    <h3 class="section-title">
                        <i class="fas fa-lock"></i>
                        Sécurité du compte
                    </h3>
                    
                    <div class="profile-card">
                        <form method="POST" class="profile-form">
                            <?php echo generateCSRFToken(); ?>
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="form-group">
                                <label for="current_password" class="form-label">Mot de passe actuel *</label>
                                <div class="password-input-group">
                                    <input type="password" 
                                           id="current_password" 
                                           name="current_password" 
                                           class="form-input <?php echo isset($errors['current_password']) ? 'error' : ''; ?>"
                                           required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <?php if (isset($errors['current_password'])): ?>
                                    <span class="form-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <?php echo htmlspecialchars($errors['current_password']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password" class="form-label">Nouveau mot de passe *</label>
                                <div class="password-input-group">
                                    <input type="password" 
                                           id="new_password" 
                                           name="new_password" 
                                           class="form-input <?php echo isset($errors['new_password']) ? 'error' : ''; ?>"
                                           required
                                           onchange="checkPasswordStrength(this.value)">
                                    <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <?php if (isset($errors['new_password'])): ?>
                                    <span class="form-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <?php echo htmlspecialchars($errors['new_password']); ?>
                                    </span>
                                <?php endif; ?>
                                
                                <div class="password-strength" id="password-strength">
                                    <div class="strength-bar">
                                        <div class="strength-fill" id="strength-fill"></div>
                                    </div>
                                    <span class="strength-text" id="strength-text">Force du mot de passe</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe *</label>
                                <div class="password-input-group">
                                    <input type="password" 
                                           id="confirm_password" 
                                           name="confirm_password" 
                                           class="form-input <?php echo isset($errors['confirm_password']) ? 'error' : ''; ?>"
                                           required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <?php if (isset($errors['confirm_password'])): ?>
                                    <span class="form-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <?php echo htmlspecialchars($errors['confirm_password']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-key"></i>
                                    Changer le mot de passe
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Informations du compte -->
                <div class="profile-section">
                    <h3 class="section-title">
                        <i class="fas fa-info-circle"></i>
                        Informations du compte
                    </h3>
                    
                    <div class="profile-card">
                        <div class="account-info">
                            <div class="info-item">
                                <span class="info-label">ID utilisateur :</span>
                                <span class="info-value">#<?php echo $user['id']; ?></span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Rôle :</span>
                                <span class="info-value">
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Statut :</span>
                                <span class="info-value">
                                    <span class="status-badge status-<?php echo $user['status']; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Membre depuis :</span>
                                <span class="info-value"><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Dernière connexion :</span>
                                <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($user['updated_at'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="account-actions">
                            <a href="/dashboard.php" class="btn btn-outline">
                                <i class="fas fa-tachometer-alt"></i>
                                Retour au dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
/* Profile Container */
.profile-container {
    margin-bottom: 2rem;
}

.profile-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
}

/* Profile Section */
.profile-section {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 1.5rem;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: #ffffff;
    margin-bottom: 1.5rem;
    font-size: 1.25rem;
}

.section-title i {
    color: #ff7a00;
}

.profile-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 15px;
    padding: 1.5rem;
}

/* Form Elements */
.profile-form {
    margin-bottom: 0;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    color: #ffffff;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.form-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.05);
    color: #ffffff;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-input:focus {
    outline: none;
    border-color: #ff7a00;
    box-shadow: 0 0 0 3px rgba(255, 122, 0, 0.1);
}

.form-input.error {
    border-color: #dc3545;
}

.form-input::placeholder {
    color: #999999;
}

.form-error {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.5rem;
}

.password-input-group {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #999999;
    cursor: pointer;
    padding: 0.25rem;
    transition: color 0.3s ease;
}

.password-toggle:hover {
    color: #ff7a00;
}

.password-strength {
    margin-top: 0.5rem;
}

.strength-bar {
    width: 100%;
    height: 4px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.strength-fill {
    height: 100%;
    width: 0%;
    transition: all 0.3s ease;
    border-radius: 2px;
}

.strength-fill.weak {
    width: 25%;
    background: #dc3545;
}

.strength-fill.fair {
    width: 50%;
    background: #ffc107;
}

.strength-fill.good {
    width: 75%;
    background: #28a745;
}

.strength-fill.strong {
    width: 100%;
    background: #20c997;
}

.strength-text {
    color: #999999;
    font-size: 0.875rem;
}

.form-actions {
    text-align: center;
}

/* Account Info */
.account-info {
    margin-bottom: 1.5rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    color: #cccccc;
    font-weight: 500;
}

.info-value {
    color: #ffffff;
    font-weight: 600;
}

.role-badge,
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.75rem;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.role-admin {
    background: rgba(255, 122, 0, 0.1);
    color: #ff7a00;
    border: 1px solid rgba(255, 122, 0, 0.3);
}

.role-user {
    background: rgba(23, 162, 184, 0.1);
    color: #17a2b8;
    border: 1px solid rgba(23, 162, 184, 0.3);
}

.status-active {
    background: rgba(40, 167, 69, 0.1);
    color: #28a745;
    border: 1px solid rgba(40, 167, 69, 0.3);
}

.status-blocked {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
    border: 1px solid rgba(220, 53, 69, 0.3);
}

.account-actions {
    text-align: center;
    padding-top: 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    font-size: 1rem;
    border: 2px solid transparent;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    justify-content: center;
}

.btn-primary {
    background: #ff7a00;
    color: #ffffff;
    border-color: #ff7a00;
}

.btn-primary:hover {
    background: #e66a00;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 122, 0, 0.3);
}

.btn-warning {
    background: #ffc107;
    color: #000000;
    border-color: #ffc107;
}

.btn-warning:hover {
    background: #e0a800;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
}

.btn-outline {
    background: transparent;
    color: #ff7a00;
    border-color: #ff7a00;
}

.btn-outline:hover {
    background: #ff7a00;
    color: #ffffff;
}

/* Alerts */
.alert {
    padding: 1rem 1.5rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border: 1px solid transparent;
}

.alert-success {
    background: rgba(40, 167, 69, 0.1);
    border-color: rgba(40, 167, 69, 0.3);
    color: #28a745;
}

.alert-error {
    background: rgba(220, 53, 69, 0.1);
    border-color: rgba(220, 53, 69, 0.3);
    color: #dc3545;
}

/* Responsive Design */
@media (max-width: 768px) {
    .profile-grid {
        grid-template-columns: 1fr;
    }
    
    .profile-section {
        padding: 1rem;
    }
    
    .profile-card {
        padding: 1rem;
    }
    
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}
</style>

<script>
// Toggle password visibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const toggle = input.nextElementSibling;
    const icon = toggle.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Check password strength
function checkPasswordStrength(password) {
    let strength = 0;
    let feedback = '';
    
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/)) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;
    
    const strengthFill = document.getElementById('strength-fill');
    const strengthText = document.getElementById('strength-text');
    
    strengthFill.className = 'strength-fill';
    
    if (strength <= 1) {
        strengthFill.classList.add('weak');
        feedback = 'Très faible';
    } else if (strength <= 2) {
        strengthFill.classList.add('fair');
        feedback = 'Faible';
    } else if (strength <= 3) {
        strengthFill.classList.add('good');
        feedback = 'Bon';
    } else {
        strengthFill.classList.add('strong');
        feedback = 'Très fort';
    }
    
    strengthText.textContent = feedback;
}

// Validation en temps réel
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (newPassword !== confirmPassword && confirmPassword.length > 0) {
        this.classList.add('error');
    } else {
        this.classList.remove('error');
    }
});
</script>

<?php include '../templates/footer.php'; ?>