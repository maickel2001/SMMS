<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$page_title = 'Nouvelle commande';
$page_description = 'Passez une nouvelle commande pour booster votre présence sur les réseaux sociaux';

// Vérifier que l'utilisateur est connecté
requireLogin();

$user = $_SESSION['user'];
$errors = [];
$success_message = '';

// Récupérer les catégories
$categories = db()->select("SELECT * FROM categories ORDER BY name");

// Récupérer les services
$services = db()->select("SELECT * FROM services ORDER BY name");

// Récupérer le service présélectionné si passé en paramètre
$selected_service_id = $_GET['service'] ?? null;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = (int)($_POST['service_id'] ?? 0);
    $url = trim($_POST['url'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 0);
    
    // Validation
    if (empty($service_id)) {
        $errors['service_id'] = 'Veuillez sélectionner un service';
    }
    
    if (empty($url)) {
        $errors['url'] = 'Veuillez saisir l\'URL ou l\'ID';
    } elseif (!validateURL($url) && !preg_match('/^[a-zA-Z0-9_.]+$/', $url)) {
        $errors['url'] = 'L\'URL ou l\'ID n\'est pas valide';
    }
    
    if (empty($quantity) || $quantity < 1) {
        $errors['quantity'] = 'La quantité doit être supérieure à 0';
    }
    
    // Si pas d'erreurs, créer la commande
    if (empty($errors)) {
        try {
            // Récupérer les informations du service
            $service = db()->select("SELECT * FROM services WHERE id = ?", [$service_id]);
            if (empty($service)) {
                $errors['general'] = 'Service non trouvé';
            } else {
                $service = $service[0];
                
                // Calculer le prix total
                $total_price = calculateServicePrice($service_id, $quantity);
                
                // Créer la commande
                $order_id = db()->insert("
                    INSERT INTO orders (user_id, service_id, url, quantity, unit_price, total_price, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
                ", [
                    $user['id'],
                    $service_id,
                    $url,
                    $quantity,
                    $service['price'],
                    $total_price
                ]);
                
                if ($order_id) {
                    // Notifier l'admin
                    notifyAdminNewOrder($order_id);
                    
                    // Rediriger vers la page de paiement
                    redirect("/payment.php?order_id=" . $order_id);
                } else {
                    $errors['general'] = 'Erreur lors de la création de la commande';
                }
            }
        } catch (Exception $e) {
            $errors['general'] = 'Erreur système : ' . $e->getMessage();
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
                <li class="nav-item active">
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
                    <h1>Nouvelle commande</h1>
                    <p>Créez une nouvelle commande pour booster votre présence</p>
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

        <div class="order-form-container">
            <form method="POST" class="order-form" id="orderForm">
                <div class="form-grid">
                    <!-- Sélection du service -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-cogs"></i>
                            Sélection du service
                        </h3>
                        
                        <div class="form-group">
                            <label for="category_id" class="form-label">Catégorie</label>
                            <select id="category_id" class="form-select" onchange="filterServices()">
                                <option value="">Toutes les catégories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="service_id" class="form-label">Service *</label>
                            <select name="service_id" id="service_id" class="form-select" required onchange="updatePrice()">
                                <option value="">Sélectionnez un service</option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?php echo $service['id']; ?>" 
                                            data-price="<?php echo $service['price']; ?>"
                                            data-unit="<?php echo htmlspecialchars($service['unit']); ?>"
                                            data-category="<?php echo $service['category_id']; ?>"
                                            <?php echo ($selected_service_id == $service['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($service['name']); ?> - 
                                        <?php echo number_format($service['price']); ?> FCFA/<?php echo $service['unit']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['service_id'])): ?>
                                <span class="form-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?php echo htmlspecialchars($errors['service_id']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Détails de la commande -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-edit"></i>
                            Détails de la commande
                        </h3>
                        
                        <div class="form-group">
                            <label for="url" class="form-label">URL ou ID *</label>
                            <input type="text" 
                                   id="url" 
                                   name="url" 
                                   class="form-input" 
                                   placeholder="Ex: https://instagram.com/username ou username"
                                   required>
                            <?php if (isset($errors['url'])): ?>
                                <span class="form-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?php echo htmlspecialchars($errors['url']); ?>
                                </span>
                            <?php endif; ?>
                            <small class="form-help">
                                Saisissez l'URL complète ou juste l'identifiant (username, ID, etc.)
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="quantity" class="form-label">Quantité *</label>
                            <input type="number" 
                                   id="quantity" 
                                   name="quantity" 
                                   class="form-input" 
                                   min="1" 
                                   value="100"
                                   required 
                                   onchange="updatePrice()">
                            <?php if (isset($errors['quantity'])): ?>
                                <span class="form-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?php echo htmlspecialchars($errors['quantity']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Résumé et prix -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-calculator"></i>
                            Résumé de la commande
                        </h3>
                        
                        <div class="order-summary">
                            <div class="summary-item">
                                <span class="summary-label">Service sélectionné :</span>
                                <span class="summary-value" id="selectedService">-</span>
                            </div>
                            
                            <div class="summary-item">
                                <span class="summary-label">Prix unitaire :</span>
                                <span class="summary-value" id="unitPrice">-</span>
                            </div>
                            
                            <div class="summary-item">
                                <span class="summary-label">Quantité :</span>
                                <span class="summary-value" id="summaryQuantity">-</span>
                            </div>
                            
                            <div class="summary-item total">
                                <span class="summary-label">Prix total :</span>
                                <span class="summary-value" id="totalPrice">-</span>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-full">
                                <i class="fas fa-credit-card"></i>
                                Continuer vers le paiement
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Informations importantes -->
        <div class="info-section">
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="info-content">
                    <h3>Informations importantes</h3>
                    <ul class="info-list">
                        <li>Vos commandes sont traitées dans les 24h suivant la validation du paiement</li>
                        <li>Assurez-vous que l'URL ou l'ID saisi est correct et accessible</li>
                        <li>Le paiement se fait exclusivement via Mobile Money (MoMo)</li>
                        <li>Vous recevrez une notification par email à chaque étape</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
/* Order Form Container */
.order-form-container {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
}

.form-section {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 15px;
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

/* Form Elements */
.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    color: #ffffff;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.form-input,
.form-select {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.05);
    color: #ffffff;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-input:focus,
.form-select:focus {
    outline: none;
    border-color: #ff7a00;
    box-shadow: 0 0 0 3px rgba(255, 122, 0, 0.1);
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

.form-help {
    display: block;
    color: #999999;
    font-size: 0.875rem;
    margin-top: 0.5rem;
    font-style: italic;
}

/* Order Summary */
.order-summary {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.summary-item:last-child {
    border-bottom: none;
}

.summary-item.total {
    border-top: 2px solid rgba(255, 122, 0, 0.3);
    padding-top: 1rem;
    margin-top: 0.5rem;
    font-weight: 700;
    font-size: 1.1rem;
}

.summary-label {
    color: #cccccc;
}

.summary-value {
    color: #ffffff;
    font-weight: 600;
}

.summary-item.total .summary-value {
    color: #ff7a00;
    font-size: 1.25rem;
}

/* Form Actions */
.form-actions {
    text-align: center;
}

.btn-full {
    width: 100%;
}

/* Info Section */
.info-section {
    margin-bottom: 2rem;
}

.info-card {
    background: rgba(23, 162, 184, 0.1);
    border: 1px solid rgba(23, 162, 184, 0.3);
    border-radius: 15px;
    padding: 1.5rem;
    display: flex;
    gap: 1rem;
}

.info-icon {
    width: 50px;
    height: 50px;
    background: rgba(23, 162, 184, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #17a2b8;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.info-content h3 {
    color: #17a2b8;
    margin-bottom: 1rem;
}

.info-list {
    list-style: none;
    padding: 0;
}

.info-list li {
    color: #cccccc;
    margin-bottom: 0.5rem;
    padding-left: 1.5rem;
    position: relative;
}

.info-list li::before {
    content: '•';
    color: #17a2b8;
    position: absolute;
    left: 0;
    font-weight: bold;
}

/* Responsive Design */
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .order-form-container {
        padding: 1rem;
    }
    
    .form-section {
        padding: 1rem;
    }
    
    .info-card {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<script>
// Filtrer les services par catégorie
function filterServices() {
    const categoryId = document.getElementById('category_id').value;
    const serviceSelect = document.getElementById('service_id');
    const options = serviceSelect.querySelectorAll('option');
    
    options.forEach(option => {
        if (option.value === '') return; // Garder l'option par défaut
        
        const serviceCategory = option.dataset.category;
        if (!categoryId || serviceCategory === categoryId) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    });
    
    // Réinitialiser la sélection
    serviceSelect.value = '';
    updatePrice();
}

// Mettre à jour le prix
function updatePrice() {
    const serviceSelect = document.getElementById('service_id');
    const quantityInput = document.getElementById('quantity');
    const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
    
    if (selectedOption.value && quantityInput.value) {
        const unitPrice = parseFloat(selectedOption.dataset.price);
        const quantity = parseInt(quantityInput.value);
        const total = unitPrice * quantity;
        
        // Mettre à jour le résumé
        document.getElementById('selectedService').textContent = selectedOption.text;
        document.getElementById('unitPrice').textContent = unitPrice.toLocaleString() + ' FCFA/' + selectedOption.dataset.unit;
        document.getElementById('summaryQuantity').textContent = quantity.toLocaleString() + ' ' + selectedOption.dataset.unit;
        document.getElementById('totalPrice').textContent = total.toLocaleString() + ' FCFA';
    } else {
        // Réinitialiser le résumé
        document.getElementById('selectedService').textContent = '-';
        document.getElementById('unitPrice').textContent = '-';
        document.getElementById('summaryQuantity').textContent = '-';
        document.getElementById('totalPrice').textContent = '-';
    }
}

// Initialiser le prix au chargement
document.addEventListener('DOMContentLoaded', function() {
    updatePrice();
    
    // Pré-sélectionner le service si passé en paramètre
    const urlParams = new URLSearchParams(window.location.search);
    const serviceId = urlParams.get('service');
    if (serviceId) {
        document.getElementById('service_id').value = serviceId;
        updatePrice();
    }
});

// Validation du formulaire
document.getElementById('orderForm').addEventListener('submit', function(e) {
    const serviceId = document.getElementById('service_id').value;
    const url = document.getElementById('url').value;
    const quantity = document.getElementById('quantity').value;
    
    if (!serviceId || !url || !quantity) {
        e.preventDefault();
        alert('Veuillez remplir tous les champs obligatoires');
        return false;
    }
    
    if (quantity < 1) {
        e.preventDefault();
        alert('La quantité doit être supérieure à 0');
        return false;
    }
});
</script>

<?php include '../templates/footer.php'; ?>