<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$page_title = 'Mes commandes';
$page_description = 'Suivez l\'état de vos commandes SMM';

// Vérifier que l'utilisateur est connecté
requireLogin();

$user = $_SESSION['user'];

// Paramètres de pagination et filtres
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$status_filter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

// Construire la requête avec filtres
$where_conditions = ['o.user_id = ?'];
$params = [$user['id']];

if ($status_filter) {
    $where_conditions[] = 'o.status = ?';
    $params[] = $status_filter;
}

if ($search) {
    $where_conditions[] = '(s.name LIKE ? OR o.url LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = implode(' AND ', $where_conditions);

// Récupérer le total des commandes
$total_orders = db()->select("
    SELECT COUNT(*) as total
    FROM orders o
    JOIN services s ON o.service_id = s.id
    WHERE $where_clause
", $params)[0]['total'];

$total_pages = ceil($total_orders / $limit);
$offset = ($page - 1) * $limit;

// Récupérer les commandes
$orders = db()->select("
    SELECT o.*, s.name as service_name, s.icon, c.name as category_name
    FROM orders o
    JOIN services s ON o.service_id = s.id
    JOIN categories c ON s.category_id = c.id
    WHERE $where_clause
    ORDER BY o.created_at DESC
    LIMIT ? OFFSET ?
", array_merge($params, [$limit, $offset]));

// Statistiques des commandes
$stats = db()->select("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid,
        SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
    FROM orders 
    WHERE user_id = ?
", [$user['id']]);

$user_stats = $stats[0] ?? [];

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
                <li class="nav-item active">
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
                    <h1>Mes commandes</h1>
                    <p>Suivez l'état de vos commandes et leur progression</p>
                </div>
                <div class="header-actions">
                    <a href="/new-order.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Nouvelle commande
                    </a>
                </div>
            </div>
        </header>

        <!-- Statistiques rapides -->
        <section class="stats-section">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $user_stats['total'] ?? 0; ?></h3>
                        <p class="stat-label">Total commandes</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $user_stats['pending'] ?? 0; ?></h3>
                        <p class="stat-label">En attente</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon processing">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $user_stats['processing'] ?? 0; ?></h3>
                        <p class="stat-label">En cours</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon completed">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $user_stats['completed'] ?? 0; ?></h3>
                        <p class="stat-label">Terminées</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Filtres et recherche -->
        <section class="filters-section">
            <div class="filters-container">
                <form method="GET" class="filters-form">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label for="search" class="filter-label">Rechercher</label>
                            <input type="text" 
                                   id="search" 
                                   name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   class="form-input" 
                                   placeholder="Service ou URL...">
                        </div>
                        
                        <div class="filter-group">
                            <label for="status" class="filter-label">Statut</label>
                            <select id="status" name="status" class="form-select">
                                <option value="">Tous les statuts</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>En attente</option>
                                <option value="paid" <?php echo $status_filter === 'paid' ? 'selected' : ''; ?>>Payée</option>
                                <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>En cours</option>
                                <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Terminée</option>
                                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Annulée</option>
                            </select>
                        </div>
                        
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                                Filtrer
                            </button>
                            <a href="/my-orders.php" class="btn btn-outline">
                                <i class="fas fa-times"></i>
                                Réinitialiser
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <!-- Liste des commandes -->
        <section class="orders-section">
            <div class="section-header">
                <h2>Liste des commandes</h2>
                <p class="results-count">
                    <?php echo $total_orders; ?> commande<?php echo $total_orders > 1 ? 's' : ''; ?> trouvée<?php echo $total_orders > 1 ? 's' : ''; ?>
                </p>
            </div>
            
            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3>Aucune commande trouvée</h3>
                    <p>
                        <?php if ($search || $status_filter): ?>
                            Aucune commande ne correspond à vos critères de recherche.
                        <?php else: ?>
                            Vous n'avez pas encore passé de commande.
                        <?php endif; ?>
                    </p>
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
                                    <th>URL/ID</th>
                                    <th>Quantité</th>
                                    <th>Prix</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>
                                            <div class="service-info">
                                                <div class="service-icon">
                                                    <i class="<?php echo htmlspecialchars($order['icon']); ?>"></i>
                                                </div>
                                                <div class="service-details">
                                                    <span class="service-name"><?php echo htmlspecialchars($order['service_name']); ?></span>
                                                    <span class="service-category"><?php echo htmlspecialchars($order['category_name']); ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="url-cell">
                                                <span class="url-text"><?php echo htmlspecialchars($order['url']); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="quantity"><?php echo number_format($order['quantity']); ?></span>
                                        </td>
                                        <td>
                                            <span class="price"><?php echo number_format($order['total_price']); ?> FCFA</span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="date-info">
                                                <span class="date"><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></span>
                                                <span class="time"><?php echo date('H:i', strtotime($order['created_at'])); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="actions">
                                                <a href="/order-details.php?id=<?php echo $order['id']; ?>" 
                                                   class="btn btn-sm btn-outline" 
                                                   title="Voir les détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <?php if ($order['status'] === 'pending'): ?>
                                                    <a href="/payment.php?order_id=<?php echo $order['id']; ?>" 
                                                       class="btn btn-sm btn-primary" 
                                                       title="Payer">
                                                        <i class="fas fa-credit-card"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>" 
                               class="pagination-item">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>" 
                               class="pagination-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>" 
                               class="pagination-item">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>
</div>

<style>
/* Stats Section */
.stats-section {
    margin-bottom: 2rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

/* Filters Section */
.filters-section {
    margin-bottom: 2rem;
}

.filters-container {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 1.5rem;
}

.filters-grid {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 1.5rem;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-label {
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

.filter-actions {
    display: flex;
    gap: 0.75rem;
}

/* Orders Section */
.orders-section {
    margin-bottom: 2rem;
}

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

.results-count {
    color: #cccccc;
    margin: 0;
}

/* Orders Table */
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

.service-details {
    display: flex;
    flex-direction: column;
}

.service-name {
    color: #ffffff;
    font-weight: 600;
}

.service-category {
    color: #ff7a00;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.url-cell {
    max-width: 200px;
}

.url-text {
    color: #cccccc;
    word-break: break-all;
}

.quantity {
    color: #ffffff;
    font-weight: 600;
}

.price {
    color: #ff7a00;
    font-weight: 600;
}

.date-info {
    display: flex;
    flex-direction: column;
}

.date {
    color: #ffffff;
    font-weight: 600;
}

.time {
    color: #999999;
    font-size: 0.875rem;
}

.actions {
    display: flex;
    gap: 0.5rem;
}

.btn-sm {
    padding: 0.5rem 0.75rem;
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

/* Pagination */
.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin: 2rem 0;
}

.pagination-item {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    color: #cccccc;
    text-decoration: none;
    transition: all 0.3s ease;
}

.pagination-item:hover,
.pagination-item.active {
    background: #ff7a00;
    color: #ffffff;
    border-color: #ff7a00;
}

/* Responsive Design */
@media (max-width: 768px) {
    .filters-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .filter-actions {
        justify-content: center;
    }
    
    .section-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .table-container {
        font-size: 0.875rem;
    }
    
    .table th,
    .table td {
        padding: 0.75rem 0.5rem;
    }
    
    .service-info {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
    
    .actions {
        flex-direction: column;
        gap: 0.25rem;
    }
}
</style>

<?php include '../templates/footer.php'; ?>