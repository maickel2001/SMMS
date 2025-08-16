-- =====================================================
-- PLATEFORME SMM - STRUCTURE DE LA BASE DE DONNÉES
-- =====================================================

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS smm_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smm_platform;

-- Table des utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    status ENUM('active', 'blocked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des catégories de services
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des services
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price_per_1000 DECIMAL(10,2) NOT NULL,
    min_quantity INT NOT NULL DEFAULT 1000,
    max_quantity INT NOT NULL DEFAULT 100000,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Table des commandes
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    target_url VARCHAR(500) NOT NULL,
    status ENUM('pending', 'paid', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    payment_screenshot VARCHAR(255),
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- Table des paramètres du système
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertion des données initiales
INSERT INTO categories (name, description, icon) VALUES
('Instagram', 'Services pour Instagram : followers, likes, commentaires', 'instagram'),
('TikTok', 'Services pour TikTok : followers, likes, vues', 'tiktok'),
('YouTube', 'Services pour YouTube : abonnés, vues, likes', 'youtube');

INSERT INTO services (category_id, name, description, price_per_1000, min_quantity, max_quantity) VALUES
(1, 'Followers Instagram', 'Followers réels pour votre compte Instagram', 5000.00, 1000, 100000),
(1, 'Likes Instagram', 'Likes sur vos posts Instagram', 2000.00, 1000, 50000),
(1, 'Commentaires Instagram', 'Commentaires personnalisés sur vos posts', 15000.00, 100, 10000),
(2, 'Followers TikTok', 'Followers pour votre compte TikTok', 4000.00, 1000, 100000),
(2, 'Likes TikTok', 'Likes sur vos vidéos TikTok', 1500.00, 1000, 50000),
(2, 'Vues TikTok', 'Vues sur vos vidéos TikTok', 1000.00, 1000, 100000),
(3, 'Abonnés YouTube', 'Abonnés pour votre chaîne YouTube', 8000.00, 1000, 50000),
(3, 'Vues YouTube', 'Vues sur vos vidéos YouTube', 2000.00, 1000, 100000),
(3, 'Likes YouTube', 'Likes sur vos vidéos YouTube', 3000.00, 1000, 50000);

INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'SMM Platform'),
('site_description', 'Plateforme de services SMM premium'),
('momo_number', '+225 0700000000'),
('momo_operator', 'MoMo'),
('currency', 'FCFA'),
('smtp_host', ''),
('smtp_port', '587'),
('smtp_username', ''),
('smtp_password', ''),
('smtp_from_email', ''),
('smtp_from_name', 'SMM Platform'),
('landing_title', 'Boostez vos réseaux sociaux'),
('landing_subtitle', 'Services SMM premium pour Instagram, TikTok et YouTube'),
('primary_color', '#ff7a00');

-- Création d'un utilisateur admin par défaut (mot de passe: admin123)
INSERT INTO users (name, email, phone, password, role) VALUES
('Administrateur', 'admin@smmplatform.com', '+225 0700000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');