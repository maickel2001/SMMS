<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$page_title = 'Paiement Mobile Money';
$page_description = 'Effectuez votre paiement via Mobile Money pour valider votre commande';

// Vérifier que l'utilisateur est connecté
requireLogin();

$user = $_SESSION['user'];
$errors = [];
$success_message = '';

// Récupérer l'ID de la commande
$order_id = (int)($_GET['order_id'] ?? 0);
if (!$order_id) {
    redirect('/dashboard.php');
}

// Récupérer les détails de la commande
$order = db()->select("
    SELECT o.*, s.name as service_name, s.icon, c.name as category_name, u.name as user_name, u.email
    FROM orders o
    JOIN services s ON o.service_id = s.id
    JOIN categories c ON s.category_id = c.id
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.user_id = ?
", [$order_id, $user['id']]);

if (empty($order)) {
    redirect('/dashboard.php');
}

$order = $order[0];

// Vérifier que la commande est en attente de paiement
if ($order['status'] !== 'pending') {
    setFlashMessage('warning', 'Cette commande a déjà été traitée.');
    redirect('/my-orders.php');
}

// Traitement du formulaire de paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le CSRF
    checkCSRF();
    
    // Traitement de l'upload de la capture d'écran
    if (isset($_FILES['payment_screenshot']) && $_FILES['payment_screenshot']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadPaymentScreenshot($_FILES['payment_screenshot']);
        
        if ($upload_result['success']) {
            // Mettre à jour la commande avec le screenshot et changer le statut
            $updated = db()->update("
                UPDATE orders 
                SET status = 'paid', 
                    payment_screenshot = ?, 
                    payment_date = NOW(),
                    updated_at = NOW()
                WHERE id = ?
            ", [$upload_result['filename'], $order_id]);
            
            if ($updated) {
                // Notifier l'admin du nouveau paiement
                notifyAdminNewOrder($order_id);
                
                // Notifier le client
                notifyClientStatusChange($order_id, 'paid');
                
                setFlashMessage('success', 'Paiement reçu ! Votre commande est maintenant en cours de traitement.');
                redirect('/my-orders.php');
            } else {
                $errors['general'] = 'Erreur lors de la mise à jour de la commande';
            }
        } else {
            $errors['payment_screenshot'] = $upload_result['message'];
        }
    } else {
        $errors['payment_screenshot'] = 'Veuillez sélectionner une capture d\'écran du paiement';
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
                <li class="nav-item">
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
                    <h1>Paiement Mobile Money</h1>
                    <p>Finalisez votre commande en effectuant le paiement</p>
                </div>
                <div class="header-actions">
                    <a href="/dashboard.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i>
                        Retour au dashboard
                    </a>
                </div>
            </div>
        </header>

        <?php if (isset($errors['general'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($errors['general']); ?>
            </div>
        <?php endif; ?>

        <div class="payment-container">
            <div class="payment-grid">
                <!-- Résumé de la commande -->
                <div class="order-summary-section">
                    <h3 class="section-title">
                        <i class="fas fa-shopping-cart"></i>
                        Résumé de votre commande
                    </h3>
                    
                    <div class="order-card">
                        <div class="order-header">
                            <div class="service-info">
                                <div class="service-icon">
                                    <i class="<?php echo htmlspecialchars($order['icon']); ?>"></i>
                                </div>
                                <div class="service-details">
                                    <h4><?php echo htmlspecialchars($order['service_name']); ?></h4>
                                    <span class="category"><?php echo htmlspecialchars($order['category_name']); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="order-details">
                            <div class="detail-item">
                                <span class="label">URL/ID :</span>
                                <span class="value"><?php echo htmlspecialchars($order['url']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Quantité :</span>
                                <span class="value"><?php echo number_format($order['quantity']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Prix unitaire :</span>
                                <span class="value"><?php echo number_format($order['unit_price']); ?> FCFA</span>
                            </div>
                            <div class="detail-item total">
                                <span class="label">Total à payer :</span>
                                <span class="value"><?php echo number_format($order['total_price']); ?> FCFA</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instructions de paiement -->
                <div class="payment-instructions-section">
                    <h3 class="section-title">
                        <i class="fas fa-mobile-alt"></i>
                        Instructions de paiement
                    </h3>
                    
                    <div class="payment-card">
                        <div class="momo-info">
                            <div class="momo-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div class="momo-details">
                                <h4>Mobile Money (MoMo)</h4>
                                <p class="momo-number"><?php echo MOMO_NUMBER; ?></p>
                                <p class="momo-operator"><?php echo MOMO_OPERATOR; ?></p>
                            </div>
                        </div>
                        
                        <div class="payment-steps">
                            <h5>Étapes à suivre :</h5>
                            <ol class="steps-list">
                                <li>Ouvrez votre application Mobile Money</li>
                                <li>Effectuez un transfert de <strong><?php echo number_format($order['total_price']); ?> FCFA</strong></li>
                                <li>Envoyez à <strong><?php echo MOMO_NUMBER; ?></strong></li>
                                <li>Prenez une capture d'écran du reçu</li>
                                <li>Uploadez la capture ci-dessous</li>
                            </ol>
                        </div>
                        
                        <div class="payment-note">
                            <i class="fas fa-info-circle"></i>
                            <p>Votre commande sera traitée dès que le paiement sera validé par notre équipe.</p>
                        </div>
                    </div>
                </div>

                <!-- Upload de la capture d'écran -->
                <div class="upload-section">
                    <h3 class="section-title">
                        <i class="fas fa-upload"></i>
                        Confirmer le paiement
                    </h3>
                    
                    <div class="upload-card">
                        <form method="POST" enctype="multipart/form-data" class="upload-form">
                            <?php echo generateCSRFToken(); ?>
                            
                            <div class="form-group">
                                <label for="payment_screenshot" class="form-label">
                                    <i class="fas fa-camera"></i>
                                    Capture d'écran du paiement *
                                </label>
                                <div class="file-upload-area" id="uploadArea">
                                    <input type="file" 
                                           id="payment_screenshot" 
                                           name="payment_screenshot" 
                                           accept="image/*"
                                           class="file-input"
                                           required
                                           onchange="previewImage(this)">
                                    <div class="upload-placeholder">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p>Cliquez ou glissez-déposez votre capture d'écran</p>
                                        <small>Formats acceptés : JPG, PNG (max 5 Mo)</small>
                                    </div>
                                </div>
                                
                                <?php if (isset($errors['payment_screenshot'])): ?>
                                    <span class="form-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <?php echo htmlspecialchars($errors['payment_screenshot']); ?>
                                    </span>
                                <?php endif; ?>
                                
                                <div class="upload-preview" id="uploadPreview" style="display: none;">
                                    <img id="previewImage" src="" alt="Aperçu">
                                    <button type="button" class="remove-image" onclick="removeImage()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary btn-full">
                                    <i class="fas fa-check-circle"></i>
                                    Confirmer le paiement
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations supplémentaires -->
        <div class="additional-info">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="info-content">
                        <h4>Paiement sécurisé</h4>
                        <p>Vos informations de paiement sont protégées et ne sont jamais stockées sur nos serveurs.</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="info-content">
                        <h4>Traitement rapide</h4>
                        <p>Votre commande sera traitée dans les 24h suivant la validation du paiement.</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="info-content">
                        <h4>Support disponible</h4>
                        <p>Besoin d'aide ? Contactez notre équipe support disponible 24h/24.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
/* Payment Container */
.payment-container {
    margin-bottom: 2rem;
}

.payment-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
}

/* Order Summary Section */
.order-summary-section {
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

.order-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 15px;
    padding: 1.5rem;
}

.order-header {
    margin-bottom: 1.5rem;
}

.service-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.service-icon {
    width: 50px;
    height: 50px;
    background: #ff7a00;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 1.25rem;
}

.service-details h4 {
    color: #ffffff;
    margin-bottom: 0.25rem;
}

.category {
    color: #ff7a00;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.order-details {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 1rem;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-item.total {
    border-top: 2px solid rgba(255, 122, 0, 0.3);
    padding-top: 1rem;
    margin-top: 0.5rem;
    font-weight: 700;
    font-size: 1.1rem;
}

.detail-item .label {
    color: #cccccc;
}

.detail-item .value {
    color: #ffffff;
    font-weight: 600;
}

.detail-item.total .value {
    color: #ff7a00;
    font-size: 1.25rem;
}

/* Payment Instructions Section */
.payment-instructions-section {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 1.5rem;
}

.payment-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 15px;
    padding: 1.5rem;
}

.momo-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.momo-icon {
    width: 60px;
    height: 60px;
    background: #25d366;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 1.5rem;
}

.momo-details h4 {
    color: #ffffff;
    margin-bottom: 0.5rem;
}

.momo-number {
    color: #25d366;
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.momo-operator {
    color: #cccccc;
    font-size: 0.875rem;
}

.payment-steps {
    margin-bottom: 1.5rem;
}

.payment-steps h5 {
    color: #ffffff;
    margin-bottom: 1rem;
}

.steps-list {
    list-style: none;
    padding: 0;
}

.steps-list li {
    color: #cccccc;
    margin-bottom: 0.75rem;
    padding-left: 1.5rem;
    position: relative;
    line-height: 1.5;
}

.steps-list li::before {
    content: counter(list-counter);
    counter-increment: list-counter;
    position: absolute;
    left: 0;
    top: 0;
    width: 20px;
    height: 20px;
    background: #ff7a00;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 0.75rem;
    font-weight: 700;
}

.steps-list {
    counter-reset: list-counter;
}

.payment-note {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    background: rgba(23, 162, 184, 0.1);
    border: 1px solid rgba(23, 162, 184, 0.3);
    border-radius: 10px;
    padding: 1rem;
}

.payment-note i {
    color: #17a2b8;
    margin-top: 0.125rem;
}

.payment-note p {
    color: #cccccc;
    margin: 0;
    font-size: 0.875rem;
    line-height: 1.5;
}

/* Upload Section */
.upload-section {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 1.5rem;
}

.upload-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 15px;
    padding: 1.5rem;
}

.upload-form {
    margin-bottom: 0;
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
    margin-bottom: 0.75rem;
}

.form-label i {
    color: #ff7a00;
}

.file-upload-area {
    border: 2px dashed rgba(255, 255, 255, 0.3);
    border-radius: 15px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
}

.file-upload-area:hover {
    border-color: #ff7a00;
    background: rgba(255, 122, 0, 0.05);
}

.file-input {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.upload-placeholder {
    pointer-events: none;
}

.upload-placeholder i {
    font-size: 3rem;
    color: #999999;
    margin-bottom: 1rem;
}

.upload-placeholder p {
    color: #cccccc;
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
}

.upload-placeholder small {
    color: #999999;
    font-size: 0.875rem;
}

.upload-preview {
    position: relative;
    margin-top: 1rem;
    text-align: center;
}

.upload-preview img {
    max-width: 100%;
    max-height: 300px;
    border-radius: 10px;
    border: 2px solid rgba(255, 255, 255, 0.1);
}

.remove-image {
    position: absolute;
    top: -10px;
    right: -10px;
    width: 30px;
    height: 30px;
    background: #dc3545;
    border: none;
    border-radius: 50%;
    color: #ffffff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.remove-image:hover {
    background: #c82333;
    transform: scale(1.1);
}

.form-error {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.5rem;
}

.form-actions {
    text-align: center;
}

.btn-full {
    width: 100%;
}

/* Additional Info */
.additional-info {
    margin-bottom: 2rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.info-item {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    padding: 1.5rem;
    display: flex;
    gap: 1rem;
    transition: all 0.3s ease;
}

.info-item:hover {
    border-color: rgba(255, 122, 0, 0.3);
    transform: translateY(-2px);
}

.info-icon {
    width: 50px;
    height: 50px;
    background: rgba(255, 122, 0, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ff7a00;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.info-content h4 {
    color: #ffffff;
    margin-bottom: 0.5rem;
}

.info-content p {
    color: #cccccc;
    margin: 0;
    font-size: 0.875rem;
    line-height: 1.5;
}

/* Responsive Design */
@media (max-width: 768px) {
    .payment-grid {
        grid-template-columns: 1fr;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .momo-info {
        flex-direction: column;
        text-align: center;
    }
    
    .info-item {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<script>
// Prévisualisation de l'image
function previewImage(input) {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImage').src = e.target.result;
            document.getElementById('uploadPreview').style.display = 'block';
            document.getElementById('uploadArea').style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
}

// Supprimer l'image
function removeImage() {
    document.getElementById('payment_screenshot').value = '';
    document.getElementById('uploadPreview').style.display = 'none';
    document.getElementById('uploadArea').style.display = 'block';
}

// Drag and drop
const uploadArea = document.getElementById('uploadArea');

uploadArea.addEventListener('dragover', function(e) {
    e.preventDefault();
    this.style.borderColor = '#ff7a00';
    this.style.background = 'rgba(255, 122, 0, 0.05)';
});

uploadArea.addEventListener('dragleave', function(e) {
    e.preventDefault();
    this.style.borderColor = 'rgba(255, 255, 255, 0.3)';
    this.style.background = 'transparent';
});

uploadArea.addEventListener('drop', function(e) {
    e.preventDefault();
    this.style.borderColor = 'rgba(255, 255, 255, 0.3)';
    this.style.background = 'transparent';
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        document.getElementById('payment_screenshot').files = files;
        previewImage(document.getElementById('payment_screenshot'));
    }
});

// Validation du formulaire
document.querySelector('.upload-form').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('payment_screenshot');
    if (!fileInput.files.length) {
        e.preventDefault();
        alert('Veuillez sélectionner une capture d\'écran du paiement');
        return false;
    }
    
    const file = fileInput.files[0];
    const maxSize = 5 * 1024 * 1024; // 5 MB
    
    if (file.size > maxSize) {
        e.preventDefault();
        alert('Le fichier est trop volumineux. Taille maximum : 5 Mo');
        return false;
    }
    
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!allowedTypes.includes(file.type)) {
        e.preventDefault();
        alert('Format de fichier non supporté. Utilisez JPG ou PNG');
        return false;
    }
});
</script>

<?php include '../templates/footer.php'; ?>