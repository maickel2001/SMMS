<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$page_title = 'Inscription';
$page_description = 'Créez votre compte SMM Platform pour commencer à utiliser nos services';

// Rediriger si déjà connecté
if (isLoggedIn()) {
    redirect('/dashboard.php');
}

$errors = [];
$form_data = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'password' => '',
    'confirm_password' => ''
];

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = [
        'name' => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? ''
    ];
    
    // Validation des données
    $validation_result = validateRegistrationData($form_data);
    
    if ($validation_result['success']) {
        // Tentative d'inscription
        $result = handleRegistration($form_data);
        
        if ($result['success']) {
            setFlashMessage('success', 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.');
            redirect('/login.php');
        } else {
            $errors['general'] = $result['message'];
        }
    } else {
        $errors = $validation_result['errors'];
    }
}

include '../templates/header.php';
?>

<div class="auth-page">
    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h1 class="auth-title">Créer un compte</h1>
                <p class="auth-subtitle">
                    Rejoignez SMM Platform et boostez votre présence sur les réseaux sociaux
                </p>
            </div>

            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($errors['general']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form" novalidate>
                <div class="form-group">
                    <label for="name" class="form-label">
                        <i class="fas fa-user"></i>
                        Nom complet
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="<?php echo htmlspecialchars($form_data['name']); ?>"
                        class="form-input <?php echo isset($errors['name']) ? 'error' : ''; ?>"
                        placeholder="Votre nom complet"
                        required
                        autocomplete="name"
                    >
                    <?php if (isset($errors['name'])): ?>
                        <span class="form-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($errors['name']); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i>
                        Adresse email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?php echo htmlspecialchars($form_data['email']); ?>"
                        class="form-input <?php echo isset($errors['email']) ? 'error' : ''; ?>"
                        placeholder="votre@email.com"
                        required
                        autocomplete="email"
                    >
                    <?php if (isset($errors['email'])): ?>
                        <span class="form-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($errors['email']); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">
                        <i class="fas fa-phone"></i>
                        Numéro de téléphone
                    </label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        value="<?php echo htmlspecialchars($form_data['phone']); ?>"
                        class="form-input <?php echo isset($errors['phone']) ? 'error' : ''; ?>"
                        placeholder="+225 0700000000"
                        required
                        autocomplete="tel"
                    >
                    <?php if (isset($errors['phone'])): ?>
                        <span class="form-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($errors['phone']); ?>
                        </span>
                    <?php endif; ?>
                    <small class="form-help">
                        Format international recommandé (ex: +225 0700000000)
                    </small>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i>
                        Mot de passe
                    </label>
                    <div class="password-input-group">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input <?php echo isset($errors['password']) ? 'error' : ''; ?>"
                            placeholder="Votre mot de passe"
                            required
                            autocomplete="new-password"
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <span class="form-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($errors['password']); ?>
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
                    <label for="confirm_password" class="form-label">
                        <i class="fas fa-lock"></i>
                        Confirmer le mot de passe
                    </label>
                    <div class="password-input-group">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            class="form-input <?php echo isset($errors['confirm_password']) ? 'error' : ''; ?>"
                            placeholder="Confirmez votre mot de passe"
                            required
                            autocomplete="new-password"
                        >
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

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="terms" id="terms" required>
                        <span class="checkmark"></span>
                        J'accepte les <a href="/terms.php" target="_blank">conditions d'utilisation</a> et la 
                        <a href="/privacy.php" target="_blank">politique de confidentialité</a>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-user-plus"></i>
                    Créer mon compte
                </button>
            </form>

            <div class="auth-footer">
                <p class="auth-footer-text">
                    Déjà un compte ? 
                    <a href="/login.php" class="auth-footer-link">Se connecter</a>
                </p>
            </div>

            <div class="auth-divider">
                <span>ou</span>
            </div>

            <div class="social-login">
                <button class="btn btn-social btn-whatsapp" onclick="contactWhatsApp()">
                    <i class="fab fa-whatsapp"></i>
                    Contacter le support
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.auth-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    display: flex;
    align-items: center;
    padding: 2rem 0;
}

.auth-container {
    max-width: 500px;
    margin: 0 auto;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 20px;
    padding: 3rem 2rem;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

.auth-header {
    text-align: center;
    margin-bottom: 2rem;
}

.auth-logo {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #ff7a00, #ff9500);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2.5rem;
    color: #ffffff;
}

.auth-title {
    font-size: 2rem;
    color: #ffffff;
    margin-bottom: 0.5rem;
    font-weight: 700;
}

.auth-subtitle {
    color: #cccccc;
    font-size: 1rem;
    line-height: 1.5;
}

.alert {
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-error {
    background: rgba(220, 53, 69, 0.1);
    border: 1px solid rgba(220, 53, 69, 0.3);
    color: #dc3545;
}

.auth-form {
    margin-bottom: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #ffffff;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.form-label i {
    color: #ff7a00;
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

.form-help {
    display: block;
    color: #999999;
    font-size: 0.875rem;
    margin-top: 0.5rem;
    font-style: italic;
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

.form-error {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.5rem;
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

.form-options {
    margin-bottom: 1.5rem;
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    color: #cccccc;
    cursor: pointer;
    font-size: 0.9rem;
    line-height: 1.4;
}

.checkbox-label input[type="checkbox"] {
    display: none;
}

.checkbox-label a {
    color: #ff7a00;
    text-decoration: none;
    transition: color 0.3s ease;
}

.checkbox-label a:hover {
    color: #ff9500;
    text-decoration: underline;
}

.checkmark {
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 4px;
    position: relative;
    transition: all 0.3s ease;
    flex-shrink: 0;
    margin-top: 0.125rem;
}

.checkbox-label input[type="checkbox"]:checked + .checkmark {
    background: #ff7a00;
    border-color: #ff7a00;
}

.checkbox-label input[type="checkbox"]:checked + .checkmark::after {
    content: '\f00c';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #ffffff;
    font-size: 0.75rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    cursor: pointer;
    font-size: 1rem;
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

.btn-full {
    width: 100%;
    justify-content: center;
}

.auth-footer {
    text-align: center;
    margin-bottom: 1.5rem;
}

.auth-footer-text {
    color: #cccccc;
    margin: 0;
}

.auth-footer-link {
    color: #ff7a00;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.auth-footer-link:hover {
    color: #ff9500;
    text-decoration: underline;
}

.auth-divider {
    text-align: center;
    margin: 1.5rem 0;
    position: relative;
}

.auth-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: rgba(255, 255, 255, 0.2);
}

.auth-divider span {
    background: rgba(26, 26, 26, 0.8);
    padding: 0 1rem;
    color: #999999;
    font-size: 0.9rem;
}

.social-login {
    display: flex;
    justify-content: center;
}

.btn-social {
    background: transparent;
    color: #ffffff;
    border-color: rgba(255, 255, 255, 0.2);
}

.btn-social:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.3);
}

.btn-whatsapp {
    color: #25d366;
    border-color: rgba(37, 211, 102, 0.3);
}

.btn-whatsapp:hover {
    background: rgba(37, 211, 102, 0.1);
    border-color: rgba(37, 211, 102, 0.5);
}

@media (max-width: 768px) {
    .auth-container {
        margin: 1rem;
        padding: 2rem 1.5rem;
    }
    
    .auth-title {
        font-size: 1.75rem;
    }
}
</style>

<script>
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

function contactWhatsApp() {
    const phone = '<?php echo MOMO_NUMBER; ?>';
    const message = 'Bonjour, j\'ai besoin d\'aide pour créer mon compte SMM Platform.';
    const url = `https://wa.me/${phone.replace(/\D/g, '')}?text=${encodeURIComponent(message)}`;
    window.open(url, '_blank');
}

// Écouter les changements du mot de passe
document.getElementById('password').addEventListener('input', function() {
    checkPasswordStrength(this.value);
});

// Validation en temps réel
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (password !== confirmPassword && confirmPassword.length > 0) {
        this.classList.add('error');
    } else {
        this.classList.remove('error');
    }
});
</script>

<?php include '../templates/footer.php'; ?>