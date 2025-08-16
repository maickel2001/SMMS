<?php
/**
 * Fonctions d'administration pour la plateforme SMM
 * Gère toutes les fonctionnalités administratives
 */

require_once 'config.php';
require_once 'database.php';
require_once 'functions.php';
require_once 'auth.php';

// =====================================================
// GESTION DES COMMANDES
// =====================================================

/**
 * Obtenir toutes les commandes avec pagination
 */
function getOrders($page = 1, $limit = 20, $status = null, $search = null) {
    $offset = ($page - 1) * $limit;
    $whereConditions = [];
    $params = [];
    
    if ($status && $status !== 'all') {
        $whereConditions[] = "o.status = ?";
        $params[] = $status;
    }
    
    if ($search) {
        $whereConditions[] = "(u.name LIKE ? OR u.email LIKE ? OR o.target_url LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
    
    // Requête pour le total
    $countQuery = "
        SELECT COUNT(*) as total 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        $whereClause
    ";
    
    $totalResult = db()->selectOne($countQuery, $params);
    $total = $totalResult['total'];
    
    // Requête pour les données
    $dataQuery = "
        SELECT o.*, u.name as user_name, u.email as user_email, 
               s.name as service_name, c.name as category_name
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        JOIN services s ON o.service_id = s.id 
        JOIN categories c ON s.category_id = c.id 
        $whereClause
        ORDER BY o.created_at DESC 
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $orders = db()->select($dataQuery, $params);
    
    return [
        'orders' => $orders,
        'total' => $total,
        'pages' => ceil($total / $limit),
        'current_page' => $page
    ];
}

/**
 * Obtenir une commande par ID
 */
function getOrderById($orderId) {
    return db()->selectOne("
        SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone,
               s.name as service_name, s.price_per_1000, c.name as category_name
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        JOIN services s ON o.service_id = s.id 
        JOIN categories c ON s.category_id = c.id 
        WHERE o.id = ?
    ", [$orderId]);
}

/**
 * Mettre à jour le statut d'une commande
 */
function updateOrderStatus($orderId, $newStatus, $adminNotes = null) {
    try {
        $result = db()->update("
            UPDATE orders 
            SET status = ?, admin_notes = ?, updated_at = NOW() 
            WHERE id = ?
        ", [$newStatus, $adminNotes, $orderId]);
        
        if ($result) {
            // Notifier le client du changement de statut
            notifyClientStatusChange($orderId, $newStatus);
            
            return ['success' => true, 'message' => 'Statut de la commande mis à jour'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la mise à jour du statut'];
        }
        
    } catch (Exception $e) {
        error_log("Erreur update order status: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur technique. Veuillez réessayer.'];
    }
}

/**
 * Supprimer une commande
 */
function deleteOrder($orderId) {
    try {
        // Récupérer le fichier de capture de paiement
        $order = db()->selectOne("SELECT payment_screenshot FROM orders WHERE id = ?", [$orderId]);
        
        if ($order && $order['payment_screenshot']) {
            $filePath = UPLOADS_PATH . '/payments/' . $order['payment_screenshot'];
            deleteFile($filePath);
        }
        
        $result = db()->delete("DELETE FROM orders WHERE id = ?", [$orderId]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Commande supprimée avec succès'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la suppression de la commande'];
        }
        
    } catch (Exception $e) {
        error_log("Erreur delete order: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur technique. Veuillez réessayer.'];
    }
}

// =====================================================
// GESTION DES SERVICES
// =====================================================

/**
 * Obtenir tous les services avec pagination
 */
function getServices($page = 1, $limit = 20, $categoryId = null) {
    $offset = ($page - 1) * $limit;
    $whereConditions = ["s.status = 'active'"];
    $params = [];
    
    if ($categoryId) {
        $whereConditions[] = "s.category_id = ?";
        $params[] = $categoryId;
    }
    
    $whereClause = "WHERE " . implode(" AND ", $whereConditions);
    
    // Requête pour le total
    $countQuery = "
        SELECT COUNT(*) as total 
        FROM services s 
        JOIN categories c ON s.category_id = c.id 
        $whereClause
    ";
    
    $totalResult = db()->selectOne($countQuery, $params);
    $total = $totalResult['total'];
    
    // Requête pour les données
    $dataQuery = "
        SELECT s.*, c.name as category_name 
        FROM services s 
        JOIN categories c ON s.category_id = c.id 
        $whereClause
        ORDER BY c.name, s.name 
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $services = db()->select($dataQuery, $params);
    
    return [
        'services' => $services,
        'total' => $total,
        'pages' => ceil($total / $limit),
        'current_page' => $page
    ];
}

/**
 * Obtenir un service par ID
 */
function getServiceById($serviceId) {
    return db()->selectOne("
        SELECT s.*, c.name as category_name 
        FROM services s 
        JOIN categories c ON s.category_id = c.id 
        WHERE s.id = ?
    ", [$serviceId]);
}

/**
 * Créer un nouveau service
 */
function createService($data) {
    $errors = [];
    
    if (empty($data['name'])) {
        $errors['name'] = 'Le nom du service est requis';
    }
    
    if (empty($data['category_id'])) {
        $errors['category_id'] = 'La catégorie est requise';
    }
    
    if (empty($data['price_per_1000']) || !is_numeric($data['price_per_1000']) || $data['price_per_1000'] < 0) {
        $errors['price_per_1000'] = 'Le prix par 1000 doit être un nombre positif';
    }
    
    if (empty($data['min_quantity']) || !is_numeric($data['min_quantity']) || $data['min_quantity'] < 1) {
        $errors['min_quantity'] = 'La quantité minimale doit être un nombre positif';
    }
    
    if (empty($data['max_quantity']) || !is_numeric($data['max_quantity']) || $data['max_quantity'] < 1) {
        $errors['max_quantity'] = 'La quantité maximale doit être un nombre positif';
    }
    
    if ($data['min_quantity'] >= $data['max_quantity']) {
        $errors['max_quantity'] = 'La quantité maximale doit être supérieure à la quantité minimale';
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    try {
        $serviceId = db()->insert("
            INSERT INTO services (category_id, name, description, price_per_1000, min_quantity, max_quantity, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'active')
        ", [
            $data['category_id'],
            sanitizeInput($data['name']),
            sanitizeInput($data['description']),
            $data['price_per_1000'],
            $data['min_quantity'],
            $data['max_quantity']
        ]);
        
        if ($serviceId) {
            return ['success' => true, 'message' => 'Service créé avec succès', 'id' => $serviceId];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la création du service'];
        }
        
    } catch (Exception $e) {
        error_log("Erreur create service: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur technique. Veuillez réessayer.'];
    }
}

/**
 * Mettre à jour un service
 */
function updateService($serviceId, $data) {
    $errors = [];
    
    if (empty($data['name'])) {
        $errors['name'] = 'Le nom du service est requis';
    }
    
    if (empty($data['category_id'])) {
        $errors['category_id'] = 'La catégorie est requise';
    }
    
    if (empty($data['price_per_1000']) || !is_numeric($data['price_per_1000']) || $data['price_per_1000'] < 0) {
        $errors['price_per_1000'] = 'Le prix par 1000 doit être un nombre positif';
    }
    
    if (empty($data['min_quantity']) || !is_numeric($data['min_quantity']) || $data['min_quantity'] < 1) {
        $errors['min_quantity'] = 'La quantité minimale doit être un nombre positif';
    }
    
    if (empty($data['max_quantity']) || !is_numeric($data['max_quantity']) || $data['max_quantity'] < 1) {
        $errors['max_quantity'] = 'La quantité maximale doit être un nombre positif';
    }
    
    if ($data['min_quantity'] >= $data['max_quantity']) {
        $errors['max_quantity'] = 'La quantité maximale doit être supérieure à la quantité minimale';
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    try {
        $result = db()->update("
            UPDATE services 
            SET category_id = ?, name = ?, description = ?, price_per_1000 = ?, 
                min_quantity = ?, max_quantity = ?, status = ?
            WHERE id = ?
        ", [
            $data['category_id'],
            sanitizeInput($data['name']),
            sanitizeInput($data['description']),
            $data['price_per_1000'],
            $data['min_quantity'],
            $data['max_quantity'],
            $data['status'] ?? 'active',
            $serviceId
        ]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Service mis à jour avec succès'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la mise à jour du service'];
        }
        
    } catch (Exception $e) {
        error_log("Erreur update service: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur technique. Veuillez réessayer.'];
    }
}

/**
 * Supprimer un service
 */
function deleteService($serviceId) {
    try {
        // Vérifier s'il y a des commandes pour ce service
        $ordersCount = db()->count("SELECT COUNT(*) FROM orders WHERE service_id = ?", [$serviceId]);
        
        if ($ordersCount > 0) {
            return ['success' => false, 'message' => 'Impossible de supprimer ce service car il a des commandes associées'];
        }
        
        $result = db()->delete("DELETE FROM services WHERE id = ?", [$serviceId]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Service supprimé avec succès'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la suppression du service'];
        }
        
    } catch (Exception $e) {
        error_log("Erreur delete service: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur technique. Veuillez réessayer.'];
    }
}

// =====================================================
// GESTION DES CATÉGORIES
// =====================================================

/**
 * Obtenir toutes les catégories
 */
function getCategories() {
    return db()->select("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
}

/**
 * Obtenir une catégorie par ID
 */
function getCategoryById($categoryId) {
    return db()->selectOne("SELECT * FROM categories WHERE id = ?", [$categoryId]);
}

/**
 * Créer une nouvelle catégorie
 */
function createCategory($data) {
    $errors = [];
    
    if (empty($data['name'])) {
        $errors['name'] = 'Le nom de la catégorie est requis';
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    try {
        $categoryId = db()->insert("
            INSERT INTO categories (name, description, icon, status) 
            VALUES (?, ?, ?, 'active')
        ", [
            sanitizeInput($data['name']),
            sanitizeInput($data['description']),
            sanitizeInput($data['icon'] ?? '')
        ]);
        
        if ($categoryId) {
            return ['success' => true, 'message' => 'Catégorie créée avec succès', 'id' => $categoryId];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la création de la catégorie'];
        }
        
    } catch (Exception $e) {
        error_log("Erreur create category: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur technique. Veuillez réessayer.'];
    }
}

/**
 * Mettre à jour une catégorie
 */
function updateCategory($categoryId, $data) {
    $errors = [];
    
    if (empty($data['name'])) {
        $errors['name'] = 'Le nom de la catégorie est requis';
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    try {
        $result = db()->update("
            UPDATE categories 
            SET name = ?, description = ?, icon = ?, status = ?
            WHERE id = ?
        ", [
            sanitizeInput($data['name']),
            sanitizeInput($data['description']),
            sanitizeInput($data['icon'] ?? ''),
            $data['status'] ?? 'active',
            $categoryId
        ]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Catégorie mise à jour avec succès'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la mise à jour de la catégorie'];
        }
        
    } catch (Exception $e) {
        error_log("Erreur update category: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur technique. Veuillez réessayer.'];
    }
}

/**
 * Supprimer une catégorie
 */
function deleteCategory($categoryId) {
    try {
        // Vérifier s'il y a des services pour cette catégorie
        $servicesCount = db()->count("SELECT COUNT(*) FROM services WHERE category_id = ?", [$categoryId]);
        
        if ($servicesCount > 0) {
            return ['success' => false, 'message' => 'Impossible de supprimer cette catégorie car elle a des services associés'];
        }
        
        $result = db()->delete("DELETE FROM categories WHERE id = ?", [$categoryId]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Catégorie supprimée avec succès'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la suppression de la catégorie'];
        }
        
    } catch (Exception $e) {
        error_log("Erreur delete category: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur technique. Veuillez réessayer.'];
    }
}

// =====================================================
// GESTION DES UTILISATEURS
// =====================================================

/**
 * Obtenir tous les utilisateurs avec pagination
 */
function getUsers($page = 1, $limit = 20, $status = null, $search = null) {
    $offset = ($page - 1) * $limit;
    $whereConditions = [];
    $params = [];
    
    if ($status && $status !== 'all') {
        $whereConditions[] = "status = ?";
        $params[] = $status;
    }
    
    if ($search) {
        $whereConditions[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
    
    // Requête pour le total
    $countQuery = "SELECT COUNT(*) as total FROM users $whereClause";
    $totalResult = db()->selectOne($countQuery, $params);
    $total = $totalResult['total'];
    
    // Requête pour les données
    $dataQuery = "
        SELECT * FROM users 
        $whereClause
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $users = db()->select($dataQuery, $params);
    
    return [
        'users' => $users,
        'total' => $total,
        'pages' => ceil($total / $limit),
        'current_page' => $page
    ];
}

/**
 * Obtenir un utilisateur par ID
 */
function getUserById($userId) {
    return db()->selectOne("SELECT * FROM users WHERE id = ?", [$userId]);
}

/**
 * Bloquer/Débloquer un utilisateur
 */
function toggleUserStatus($userId) {
    try {
        $user = getUserById($userId);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Utilisateur non trouvé'];
        }
        
        if ($user['role'] === 'admin') {
            return ['success' => false, 'message' => 'Impossible de bloquer un administrateur'];
        }
        
        $newStatus = $user['status'] === 'active' ? 'blocked' : 'active';
        
        $result = db()->update("UPDATE users SET status = ? WHERE id = ?", [$newStatus, $userId]);
        
        if ($result) {
            $statusText = $newStatus === 'active' ? 'débloqué' : 'bloqué';
            return ['success' => true, 'message' => "Utilisateur $statusText avec succès"];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la mise à jour du statut'];
        }
        
    } catch (Exception $e) {
        error_log("Erreur toggle user status: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur technique. Veuillez réessayer.'];
    }
}

// =====================================================
// STATISTIQUES ET RAPPORTS
// =====================================================

/**
 * Obtenir les statistiques du dashboard admin
 */
function getAdminStats() {
    try {
        // Total des commandes
        $totalOrders = db()->count("SELECT COUNT(*) FROM orders");
        
        // Commandes en attente
        $pendingOrders = db()->count("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
        
        // Commandes payées
        $paidOrders = db()->count("SELECT COUNT(*) FROM orders WHERE status = 'paid'");
        
        // Commandes en cours
        $processingOrders = db()->count("SELECT COUNT(*) FROM orders WHERE status = 'processing'");
        
        // Commandes terminées
        $completedOrders = db()->count("SELECT COUNT(*) FROM orders WHERE status = 'completed'");
        
        // Total des utilisateurs
        $totalUsers = db()->count("SELECT COUNT(*) FROM users WHERE role = 'user'");
        
        // Chiffre d'affaires total
        $totalRevenue = db()->selectOne("SELECT SUM(total_price) as total FROM orders WHERE status IN ('paid', 'processing', 'completed')");
        $revenue = $totalRevenue['total'] ?? 0;
        
        // Commandes du jour
        $todayOrders = db()->count("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()");
        
        // Chiffre d'affaires du jour
        $todayRevenue = db()->selectOne("SELECT SUM(total_price) as total FROM orders WHERE DATE(created_at) = CURDATE() AND status IN ('paid', 'processing', 'completed')");
        $todayRev = $todayRevenue['total'] ?? 0;
        
        return [
            'total_orders' => $totalOrders,
            'pending_orders' => $pendingOrders,
            'paid_orders' => $paidOrders,
            'processing_orders' => $processingOrders,
            'completed_orders' => $completedOrders,
            'total_users' => $totalUsers,
            'total_revenue' => $revenue,
            'today_orders' => $todayOrders,
            'today_revenue' => $todayRev
        ];
        
    } catch (Exception $e) {
        error_log("Erreur get admin stats: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtenir les commandes récentes
 */
function getRecentOrders($limit = 10) {
    return db()->select("
        SELECT o.*, u.name as user_name, s.name as service_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        JOIN services s ON o.service_id = s.id 
        ORDER BY o.created_at DESC 
        LIMIT ?
    ", [$limit]);
}

/**
 * Obtenir les utilisateurs récents
 */
function getRecentUsers($limit = 10) {
    return db()->select("
        SELECT * FROM users 
        WHERE role = 'user' 
        ORDER BY created_at DESC 
        LIMIT ?
    ", [$limit]);
}
?>