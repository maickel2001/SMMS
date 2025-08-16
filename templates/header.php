<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : 'Plateforme de services SMM premium pour Instagram, TikTok et YouTube'; ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">
    
    <!-- CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <!-- Logo -->
                <div class="logo">
                    <a href="/">
                        <img src="/assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" class="logo-img">
                        <span class="logo-text"><?php echo SITE_NAME; ?></span>
                    </a>
                </div>
                
                <!-- Navigation principale -->
                <nav class="nav-main">
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="/#services" class="nav-link">Services</a>
                        </li>
                        <li class="nav-item">
                            <a href="/#how-it-works" class="nav-link">Comment ça marche</a>
                        </li>
                        <li class="nav-item">
                            <a href="/#testimonials" class="nav-link">Témoignages</a>
                        </li>
                        <li class="nav-item">
                            <a href="/#faq" class="nav-link">FAQ</a>
                        </li>
                        <li class="nav-item">
                            <a href="/#contact" class="nav-link">Contact</a>
                        </li>
                    </ul>
                </nav>
                
                <!-- Boutons d'action -->
                <div class="header-actions">
                    <?php if (isLoggedIn()): ?>
                        <div class="user-menu">
                            <button class="user-menu-toggle" id="userMenuToggle">
                                <i class="fas fa-user-circle"></i>
                                <span><?php echo $_SESSION['user_name']; ?></span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="user-dropdown" id="userDropdown">
                                <a href="/dashboard.php" class="dropdown-item">
                                    <i class="fas fa-tachometer-alt"></i>
                                    Dashboard
                                </a>
                                <a href="/new-order.php" class="dropdown-item">
                                    <i class="fas fa-plus-circle"></i>
                                    Nouvelle commande
                                </a>
                                <a href="/my-orders.php" class="dropdown-item">
                                    <i class="fas fa-list"></i>
                                    Mes commandes
                                </a>
                                <a href="/profile.php" class="dropdown-item">
                                    <i class="fas fa-user"></i>
                                    Profil
                                </a>
                                <a href="/support.php" class="dropdown-item">
                                    <i class="fas fa-headset"></i>
                                    Support
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="/logout.php" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt"></i>
                                    Déconnexion
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="/login.php" class="btn btn-outline">Se connecter</a>
                        <a href="/register.php" class="btn btn-primary">S'inscrire</a>
                    <?php endif; ?>
                </div>
                
                <!-- Menu mobile -->
                <button class="mobile-menu-toggle" id="mobileMenuToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
        
        <!-- Menu mobile -->
        <div class="mobile-menu" id="mobileMenu">
            <nav class="mobile-nav">
                <ul class="mobile-nav-list">
                    <li class="mobile-nav-item">
                        <a href="/#services" class="mobile-nav-link">Services</a>
                    </li>
                    <li class="mobile-nav-item">
                        <a href="/#how-it-works" class="mobile-nav-link">Comment ça marche</a>
                    </li>
                    <li class="mobile-nav-item">
                        <a href="/#testimonials" class="mobile-nav-link">Témoignages</a>
                    </li>
                    <li class="mobile-nav-item">
                        <a href="/#faq" class="mobile-nav-link">FAQ</a>
                    </li>
                    <li class="mobile-nav-item">
                        <a href="/#contact" class="mobile-nav-link">Contact</a>
                    </li>
                </ul>
            </nav>
            
            <?php if (!isLoggedIn()): ?>
                <div class="mobile-actions">
                    <a href="/login.php" class="btn btn-outline btn-block">Se connecter</a>
                    <a href="/register.php" class="btn btn-primary btn-block">S'inscrire</a>
                </div>
            <?php endif; ?>
        </div>
    </header>
    
    <!-- Messages flash -->
    <?php if (hasFlashMessages()): ?>
        <div class="flash-messages">
            <?php if ($success = getFlashMessage('success')): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if ($error = getFlashMessage('error')): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if ($warning = getFlashMessage('warning')): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $warning; ?>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if ($info = getFlashMessage('info')): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <?php echo $info; ?>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Contenu principal -->
    <main class="main-content">