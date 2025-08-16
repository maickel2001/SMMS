<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$page_title = 'Dashboard';
$page_description = 'Tableau de bord de votre compte SMM Platform';

// Vérifier que l'utilisateur est connecté
requireLogin();

// Récupérer les informations de l'utilisateur
$user = $_SESSION['user'];

// Récupérer les statistiques de l'utilisateur
$stats = db()->select("
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_orders,
        SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders
    FROM orders 
    WHERE user_id = ?
", [$user['id']]);

$user_stats = $stats[0] ?? [];

// Récupérer les commandes récentes
$recent_orders = db()->select("
    SELECT o.*, s.name as service_name, s.icon, c.name as category_name
    FROM orders o
    JOIN services s ON o.service_id = s.id
    JOIN categories c ON s.category_id = c.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
    LIMIT 5
", [$user['id']]);

// Récupérer les services populaires
$popular_services = db()->select("
    SELECT s.*, c.name as category_name
    FROM services s
    JOIN categories c ON s.category_id = c.id
    WHERE s.is_popular = 1
    ORDER BY s.price ASC
    LIMIT 3
");

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
                <li class="nav-item active">
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
        <!-- Header -->
        <header class="dashboard-header">
            <div class="header-content">
                <div class="header-title">
                    <h1>Tableau de bord</h1>
                    <p>Bienvenue, <?php echo htmlspecialchars($user['name']); ?> !</p>
                </div>
                <div class="header-actions">
                    <a href="/new-order.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Nouvelle commande
                    </a>
                </div>
            </div>
        </header>

        <!-- Stats Cards -->
        <section class="stats-section">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $user_stats['total_orders'] ?? 0; ?></h3>
                        <p class="stat-label">Total commandes</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $user_stats['pending_orders'] ?? 0; ?></h3>
                        <p class="stat-label">En attente</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon processing">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $user_stats['processing_orders'] ?? 0; ?></h3>
                        <p class="stat-label">En cours</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon completed">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $user_stats['completed_orders'] ?? 0; ?></h3>
                        <p class="stat-label">Terminées</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Recent Orders -->
        <section class="recent-orders-section">
            <div class="section-header">
                <h2>Commandes récentes</h2>
                <a href="/my-orders.php" class="btn btn-outline">Voir toutes</a>
            </div>
            
            <?php if (empty($recent_orders)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3>Aucune commande</h3>
                    <p>Vous n'avez pas encore passé de commande. Commencez maintenant !</p>
                    <a href="/new-order.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Première commande
                    </a>
                </div>
            <?php else: ?>
                <div class="orders-table">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Catégorie</th>
                                    <th>Quantité</th>
                                    <th>Prix</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td>
                                            <div class="service-info">
                                                <div class="service-icon">
                                                    <i class="<?php echo htmlspecialchars($order['icon']); ?>"></i>
                                                </div>
                                                <span><?php echo htmlspecialchars($order['service_name']); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($order['category_name']); ?></td>
                                        <td><?php echo number_format($order['quantity']); ?></td>
                                        <td><?php echo number_format($order['total_price']); ?> FCFA</td>
                                        <td>
                                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <a href="/order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline">
                                                <i class="fas fa-eye"></i>
                                                Voir
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </section>

        <!-- Popular Services -->
        <section class="popular-services-section">
            <div class="section-header">
                <h2>Services populaires</h2>
                <a href="/new-order.php" class="btn btn-outline">Voir tous</a>
            </div>
            
            <div class="services-grid">
                <?php foreach ($popular_services as $service): ?>
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                        </div>
                        <div class="service-content">
                            <h3 class="service-title"><?php echo htmlspecialchars($service['name']); ?></h3>
                            <p class="service-category"><?php echo htmlspecialchars($service['category_name']); ?></p>
                            <p class="service-description"><?php echo htmlspecialchars($service['description']); ?></p>
                            <div class="service-price">
                                <span class="price-amount"><?php echo number_format($service['price']); ?> FCFA</span>
                                <span class="price-unit">/ <?php echo $service['unit']; ?></span>
                            </div>
                        </div>
                        <div class="service-actions">
                            <a href="/new-order.php?service=<?php echo $service['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i>
                                Commander
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Quick Actions -->
        <section class="quick-actions-section">
            <div class="section-header">
                <h2>Actions rapides</h2>
            </div>
            
            <div class="actions-grid">
                <a href="/new-order.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <h3>Nouvelle commande</h3>
                    <p>Passez une nouvelle commande pour booster votre présence</p>
                </a>
                
                <a href="/profile.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <h3>Modifier le profil</h3>
                    <p>Mettez à jour vos informations personnelles</p>
                </a>
                
                <a href="/support.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>Contacter le support</h3>
                    <p>Besoin d'aide ? Contactez notre équipe</p>
                </a>
                
                <a href="/my-orders.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-list-alt"></i>
                    </div>
                    <h3>Voir mes commandes</h3>
                    <p>Consultez l'historique de vos commandes</p>
                </a>
            </div>
        </section>
    </main>
</div>

<style>
/* Dashboard Layout */
.dashboard-page {
    display: flex;
    min-height: 100vh;
    background: #0f0f0f;
}

/* Sidebar */
.dashboard-sidebar {
    width: 280px;
    background: #1a1a1a;
    border-right: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    flex-direction: column;
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    z-index: 100;
}

.sidebar-header {
    padding: 2rem 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.25rem;
    font-weight: 700;
    color: #ffffff;
}

.sidebar-logo i {
    color: #ff7a00;
    font-size: 1.5rem;
}

.sidebar-nav {
    flex: 1;
    padding: 1.5rem 0;
}

.nav-list {
    list-style: none;
}

.nav-item {
    margin-bottom: 0.5rem;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.5rem;
    color: #cccccc;
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.nav-link:hover,
.nav-link.active {
    background: rgba(255, 255, 255, 0.05);
    color: #ffffff;
    border-left-color: #ff7a00;
}

.nav-link i {
    width: 20px;
    text-align: center;
    color: #ff7a00;
}

.sidebar-footer {
    padding: 1.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-logout {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: #dc3545;
    text-decoration: none;
    transition: all 0.3s ease;
}

.sidebar-logout:hover {
    color: #c82333;
}

/* Main Content */
.dashboard-main {
    flex: 1;
    margin-left: 280px;
    padding: 2rem;
}

.dashboard-header {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-title h1 {
    margin-bottom: 0.5rem;
    color: #ffffff;
}

.header-title p {
    color: #cccccc;
    margin: 0;
}

/* Stats Section */
.stats-section {
    margin-bottom: 3rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.stat-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
}

.stat-card:hover {
    border-color: rgba(255, 122, 0, 0.3);
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #ffffff;
    background: #ff7a00;
}

.stat-icon.pending {
    background: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.stat-icon.processing {
    background: rgba(23, 162, 184, 0.1);
    color: #17a2b8;
}

.stat-icon.completed {
    background: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

.stat-content h3 {
    font-size: 2rem;
    margin-bottom: 0.25rem;
    color: #ffffff;
}

.stat-label {
    color: #cccccc;
    margin: 0;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Section Headers */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.section-header h2 {
    color: #ffffff;
    margin: 0;
}

/* Recent Orders */
.recent-orders-section {
    margin-bottom: 3rem;
}

.orders-table {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    overflow: hidden;
}

.table-container {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.table th {
    background: rgba(255, 255, 255, 0.05);
    color: #ffffff;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.875rem;
    letter-spacing: 0.5px;
}

.table tr:hover {
    background: rgba(255, 255, 255, 0.05);
}

.service-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.service-icon {
    width: 32px;
    height: 32px;
    background: #ff7a00;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 0.875rem;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
}

.empty-icon {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2rem;
    color: #999999;
}

.empty-state h3 {
    color: #ffffff;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #cccccc;
    margin-bottom: 1.5rem;
}

/* Popular Services */
.popular-services-section {
    margin-bottom: 3rem;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.service-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 1.5rem;
    transition: all 0.3s ease;
}

.service-card:hover {
    border-color: rgba(255, 122, 0, 0.3);
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
}

.service-card .service-icon {
    width: 60px;
    height: 60px;
    background: #ff7a00;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    font-size: 1.5rem;
    color: #ffffff;
}

.service-title {
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
    color: #ffffff;
}

.service-category {
    color: #ff7a00;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.75rem;
}

.service-description {
    color: #cccccc;
    margin-bottom: 1rem;
    line-height: 1.5;
}

.service-price {
    display: flex;
    align-items: baseline;
    gap: 0.25rem;
    margin-bottom: 1rem;
}

.price-amount {
    font-size: 1.5rem;
    font-weight: 700;
    color: #ff7a00;
}

.price-unit {
    color: #999999;
    font-size: 0.875rem;
}

.service-actions {
    text-align: center;
}

/* Quick Actions */
.quick-actions-section {
    margin-bottom: 3rem;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.action-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 1.5rem;
    text-decoration: none;
    transition: all 0.3s ease;
    text-align: center;
}

.action-card:hover {
    border-color: rgba(255, 122, 0, 0.3);
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
}

.action-icon {
    width: 60px;
    height: 60px;
    background: #ff7a00;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.5rem;
    color: #ffffff;
}

.action-card h3 {
    color: #ffffff;
    margin-bottom: 0.5rem;
}

.action-card p {
    color: #cccccc;
    margin: 0;
    font-size: 0.875rem;
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

.btn-outline {
    background: transparent;
    color: #ff7a00;
    border-color: #ff7a00;
}

.btn-outline:hover {
    background: #ff7a00;
    color: #ffffff;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

/* Status badges */
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

.status-pending {
    background: rgba(255, 193, 7, 0.1);
    color: #ffc107;
    border: 1px solid rgba(255, 193, 7, 0.3);
}

.status-paid {
    background: rgba(40, 167, 69, 0.1);
    color: #28a745;
    border: 1px solid rgba(40, 167, 69, 0.3);
}

.status-processing {
    background: rgba(23, 162, 184, 0.1);
    color: #17a2b8;
    border: 1px solid rgba(23, 162, 184, 0.3);
}

.status-completed {
    background: rgba(40, 167, 69, 0.1);
    color: #28a745;
    border: 1px solid rgba(40, 167, 69, 0.3);
}

.status-cancelled {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
    border: 1px solid rgba(220, 53, 69, 0.3);
}

/* Responsive Design */
@media (max-width: 1024px) {
    .dashboard-sidebar {
        transform: translateX(-100%);
        transition: all 0.3s ease;
    }
    
    .dashboard-sidebar.active {
        transform: translateX(0);
    }
    
    .dashboard-main {
        margin-left: 0;
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
}

@media (max-width: 768px) {
    .dashboard-main {
        padding: 1rem;
    }
    
    .header-content {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .services-grid {
        grid-template-columns: 1fr;
    }
    
    .actions-grid {
        grid-template-columns: 1fr;
    }
    
    .section-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
}
</style>

<script>
// Toggle sidebar on mobile
function toggleSidebar() {
    const sidebar = document.querySelector('.dashboard-sidebar');
    sidebar.classList.toggle('active');
}

// Close sidebar when clicking outside
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.dashboard-sidebar');
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    
    if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
        sidebar.classList.remove('active');
    }
});

// Auto-hide sidebar on mobile after navigation
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function() {
        if (window.innerWidth <= 1024) {
            document.querySelector('.dashboard-sidebar').classList.remove('active');
        }
    });
});
</script>

<?php include '../templates/footer.php'; ?>