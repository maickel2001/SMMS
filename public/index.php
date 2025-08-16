<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$page_title = 'Accueil';
$page_description = 'Plateforme SMM leader pour l\'achat de followers, likes et vues sur Instagram, TikTok et YouTube';

// Récupérer les services populaires
$popular_services = db()->select("
    SELECT s.*, c.name as category_name 
    FROM services s 
    JOIN categories c ON s.category_id = c.id 
    WHERE s.is_popular = 1 
    ORDER BY s.price ASC 
    LIMIT 6
");

// Récupérer les statistiques du site
$stats = db()->select("
    SELECT 
        COUNT(DISTINCT o.id) as total_orders,
        COUNT(DISTINCT o.user_id) as total_clients,
        COUNT(DISTINCT s.id) as total_services
    FROM orders o 
    CROSS JOIN services s
", []);

$total_orders = $stats[0]['total_orders'] ?? 0;
$total_clients = $stats[0]['total_clients'] ?? 0;
$total_services = $stats[0]['total_services'] ?? 0;

include '../templates/header.php';
?>

<!-- Bannière principale -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">
                    Boostez votre présence sur les réseaux sociaux
                </h1>
                <p class="hero-subtitle">
                    Achetez des followers, likes et vues authentiques pour Instagram, TikTok et YouTube. 
                    Résultats garantis en moins de 24h !
                </p>
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo number_format($total_orders); ?>+</span>
                        <span class="stat-label">Commandes</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo number_format($total_clients); ?>+</span>
                        <span class="stat-label">Clients satisfaits</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo number_format($total_services); ?>+</span>
                        <span class="stat-label">Services</span>
                    </div>
                </div>
                <div class="hero-actions">
                    <?php if (isLoggedIn()): ?>
                        <a href="/dashboard.php" class="btn btn-primary btn-large">
                            <i class="fas fa-rocket"></i>
                            Commencer maintenant
                        </a>
                    <?php else: ?>
                        <a href="/register.php" class="btn btn-primary btn-large">
                            <i class="fas fa-rocket"></i>
                            Commencer maintenant
                        </a>
                        <a href="/login.php" class="btn btn-outline btn-large">
                            <i class="fas fa-sign-in-alt"></i>
                            Se connecter
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-image">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Services -->
<section class="services-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Nos Services</h2>
            <p class="section-subtitle">
                Choisissez parmi nos services premium pour booster votre présence en ligne
            </p>
        </div>
        
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">
                    <i class="fab fa-instagram"></i>
                </div>
                <h3 class="service-title">Instagram</h3>
                <p class="service-description">
                    Followers, likes, commentaires et vues pour vos posts et stories Instagram
                </p>
                <ul class="service-features">
                    <li><i class="fas fa-check"></i> Followers réels</li>
                    <li><i class="fas fa-check"></i> Likes instantanés</li>
                    <li><i class="fas fa-check"></i> Commentaires personnalisés</li>
                </ul>
                <a href="/register.php" class="btn btn-outline">Voir les tarifs</a>
            </div>
            
            <div class="service-card featured">
                <div class="service-icon">
                    <i class="fab fa-tiktok"></i>
                </div>
                <h3 class="service-title">TikTok</h3>
                <p class="service-description">
                    Followers, likes et vues pour vos vidéos TikTok. Viralisez votre contenu !
                </p>
                <ul class="service-features">
                    <li><i class="fas fa-check"></i> Followers actifs</li>
                    <li><i class="fas fa-check"></i> Vues organiques</li>
                    <li><i class="fas fa-check"></i> Likes authentiques</li>
                </ul>
                <a href="/register.php" class="btn btn-primary">Voir les tarifs</a>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fab fa-youtube"></i>
                </div>
                <h3 class="service-title">YouTube</h3>
                <p class="service-description">
                    Abonnés, likes, commentaires et vues pour vos vidéos YouTube
                </p>
                <ul class="service-features">
                    <li><i class="fas fa-check"></i> Abonnés fidèles</li>
                    <li><i class="fas fa-check"></i> Vues de qualité</li>
                    <li><i class="fas fa-check"></i> Commentaires engageants</li>
                </ul>
                <a href="/register.php" class="btn btn-outline">Voir les tarifs</a>
            </div>
        </div>
    </div>
</section>

<!-- Section Comment ça marche -->
<section class="how-it-works-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Comment ça marche ?</h2>
            <p class="section-subtitle">
                En 3 étapes simples, boostez votre présence sur les réseaux sociaux
            </p>
        </div>
        
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <div class="step-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h3 class="step-title">Inscrivez-vous</h3>
                <p class="step-description">
                    Créez votre compte en quelques secondes avec votre email et téléphone
                </p>
            </div>
            
            <div class="step-card">
                <div class="step-number">2</div>
                <div class="step-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <h3 class="step-title">Passez commande</h3>
                <p class="step-description">
                    Choisissez votre service, saisissez l'URL et payez via Mobile Money
                </p>
            </div>
            
            <div class="step-card">
                <div class="step-number">3</div>
                <div class="step-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="step-title">Recevez vos résultats</h3>
                <p class="step-description">
                    Vos followers, likes ou vues arrivent en moins de 24h
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Section Témoignages -->
<section class="testimonials-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Ce que disent nos clients</h2>
            <p class="section-subtitle">
                Découvrez les avis de nos clients satisfaits
            </p>
        </div>
        
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">
                    "Service exceptionnel ! J'ai reçu mes 1000 followers Instagram en moins de 12h. 
                    Très professionnel et résultats garantis."
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="author-info">
                        <h4 class="author-name">Sarah K.</h4>
                        <span class="author-location">Abidjan, Côte d'Ivoire</span>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">
                    "Ma vidéo TikTok a explosé grâce aux vues et likes ! 
                    L'équipe est réactive et les prix sont très compétitifs."
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="author-info">
                        <h4 class="author-name">Marc D.</h4>
                        <span class="author-location">Lyon, France</span>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">
                    "Parfait pour mon canal YouTube ! Les abonnés sont de qualité et 
                    l'engagement a vraiment augmenté. Je recommande !"
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="author-info">
                        <h4 class="author-name">Amina B.</h4>
                        <span class="author-location">Dakar, Sénégal</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section CTA finale -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2 class="cta-title">Prêt à booster votre présence ?</h2>
            <p class="cta-subtitle">
                Rejoignez des milliers de clients satisfaits et commencez dès aujourd'hui !
            </p>
            <div class="cta-actions">
                <?php if (isLoggedIn()): ?>
                    <a href="/dashboard.php" class="btn btn-primary btn-large">
                        <i class="fas fa-rocket"></i>
                        Aller au dashboard
                    </a>
                <?php else: ?>
                    <a href="/register.php" class="btn btn-primary btn-large">
                        <i class="fas fa-rocket"></i>
                        Commencer gratuitement
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    padding: 6rem 0;
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23ff7a00" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.hero-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
    position: relative;
    z-index: 2;
}

.hero-title {
    font-size: 3.5rem;
    color: #ffffff;
    margin-bottom: 1.5rem;
    font-weight: 700;
    line-height: 1.2;
}

.hero-subtitle {
    font-size: 1.2rem;
    color: #cccccc;
    margin-bottom: 2rem;
    line-height: 1.6;
}

.hero-stats {
    display: flex;
    gap: 2rem;
    margin-bottom: 2rem;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 2rem;
    font-weight: 700;
    color: #ff7a00;
}

.stat-label {
    font-size: 0.9rem;
    color: #999999;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.hero-actions {
    display: flex;
    gap: 1rem;
}

.btn-large {
    padding: 1rem 2rem;
    font-size: 1.1rem;
}

.hero-visual {
    display: flex;
    justify-content: center;
    align-items: center;
}

.hero-image {
    width: 300px;
    height: 300px;
    background: linear-gradient(135deg, #ff7a00, #ff9500);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 8rem;
    color: #ffffff;
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

/* Services Section */
.services-section {
    padding: 6rem 0;
    background: #0f0f0f;
}

.section-header {
    text-align: center;
    margin-bottom: 4rem;
}

.section-title {
    font-size: 3rem;
    color: #ffffff;
    margin-bottom: 1rem;
    font-weight: 700;
}

.section-subtitle {
    font-size: 1.2rem;
    color: #cccccc;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.6;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
}

.service-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 2.5rem;
    text-align: center;
    transition: all 0.3s ease;
    position: relative;
}

.service-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(255, 122, 0, 0.2);
    border-color: rgba(255, 122, 0, 0.3);
}

.service-card.featured {
    border-color: #ff7a00;
    background: linear-gradient(135deg, rgba(255, 122, 0, 0.1), rgba(255, 149, 0, 0.05));
}

.service-icon {
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

.service-title {
    font-size: 1.8rem;
    color: #ffffff;
    margin-bottom: 1rem;
    font-weight: 600;
}

.service-description {
    color: #cccccc;
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.service-features {
    list-style: none;
    padding: 0;
    margin: 0 0 2rem;
}

.service-features li {
    color: #ffffff;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.service-features i {
    color: #ff7a00;
}

/* How it works Section */
.how-it-works-section {
    padding: 6rem 0;
    background: #1a1a1a;
}

.steps-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.step-card {
    text-align: center;
    padding: 2rem;
}

.step-number {
    width: 60px;
    height: 60px;
    background: #ff7a00;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 1.5rem;
    font-weight: 700;
    color: #ffffff;
}

.step-icon {
    width: 80px;
    height: 80px;
    background: rgba(255, 122, 0, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2rem;
    color: #ff7a00;
}

.step-title {
    font-size: 1.5rem;
    color: #ffffff;
    margin-bottom: 1rem;
    font-weight: 600;
}

.step-description {
    color: #cccccc;
    line-height: 1.6;
}

/* Testimonials Section */
.testimonials-section {
    padding: 6rem 0;
    background: #0f0f0f;
}

.testimonials-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
}

.testimonial-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 2rem;
    transition: all 0.3s ease;
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(255, 122, 0, 0.1);
}

.testimonial-rating {
    margin-bottom: 1rem;
}

.testimonial-rating i {
    color: #ff7a00;
    margin-right: 0.25rem;
}

.testimonial-text {
    color: #cccccc;
    line-height: 1.6;
    margin-bottom: 1.5rem;
    font-style: italic;
}

.testimonial-author {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.author-avatar {
    width: 50px;
    height: 50px;
    background: rgba(255, 122, 0, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ff7a00;
}

.author-name {
    color: #ffffff;
    margin: 0;
    font-size: 1.1rem;
}

.author-location {
    color: #999999;
    font-size: 0.9rem;
}

/* CTA Section */
.cta-section {
    padding: 6rem 0;
    background: linear-gradient(135deg, #ff7a00, #ff9500);
    text-align: center;
}

.cta-title {
    font-size: 3rem;
    color: #ffffff;
    margin-bottom: 1rem;
    font-weight: 700;
}

.cta-subtitle {
    font-size: 1.2rem;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 2rem;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.cta-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-content {
        grid-template-columns: 1fr;
        text-align: center;
        gap: 2rem;
    }
    
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-stats {
        justify-content: center;
    }
    
    .hero-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .services-grid {
        grid-template-columns: 1fr;
    }
    
    .steps-grid {
        grid-template-columns: 1fr;
    }
    
    .testimonials-grid {
        grid-template-columns: 1fr;
    }
    
    .cta-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .section-title {
        font-size: 2.5rem;
    }
    
    .cta-title {
        font-size: 2.5rem;
    }
}
</style>

<?php include '../templates/footer.php'; ?>