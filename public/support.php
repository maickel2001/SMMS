<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$page_title = 'Support';
$page_description = 'Contactez notre équipe support pour toute assistance';

// Vérifier que l'utilisateur est connecté
requireLogin();

$user = $_SESSION['user'];

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
                <li class="nav-item active">
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
                    <h1>Support & Assistance</h1>
                    <p>Notre équipe est là pour vous aider 24h/24</p>
                </div>
                <div class="header-actions">
                    <a href="/dashboard.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i>
                        Retour au dashboard
                    </a>
                </div>
            </div>
        </header>

        <div class="support-container">
            <div class="support-grid">
                <!-- Contact direct -->
                <div class="support-section">
                    <h3 class="section-title">
                        <i class="fas fa-headset"></i>
                        Contact direct
                    </h3>
                    
                    <div class="support-card">
                        <div class="contact-methods">
                            <div class="contact-method">
                                <div class="method-icon whatsapp">
                                    <i class="fab fa-whatsapp"></i>
                                </div>
                                <div class="method-content">
                                    <h4>WhatsApp</h4>
                                    <p>Support instantané 24h/24</p>
                                    <a href="https://wa.me/<?php echo str_replace(['+', ' ', '-'], '', SUPPORT_WHATSAPP); ?>?text=Bonjour, j'ai besoin d'aide avec ma commande SMM" 
                                       target="_blank" 
                                       class="btn btn-success btn-full">
                                        <i class="fab fa-whatsapp"></i>
                                        Contacter sur WhatsApp
                                    </a>
                                </div>
                            </div>
                            
                            <div class="contact-method">
                                <div class="method-icon email">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="method-content">
                                    <h4>Email</h4>
                                    <p>Réponse sous 2h</p>
                                    <a href="mailto:<?php echo SUPPORT_EMAIL; ?>" class="btn btn-outline btn-full">
                                        <i class="fas fa-envelope"></i>
                                        Envoyer un email
                                    </a>
                                </div>
                            </div>
                            
                            <div class="contact-method">
                                <div class="method-icon phone">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="method-content">
                                    <h4>Téléphone</h4>
                                    <p>Support vocal disponible</p>
                                    <a href="tel:<?php echo SUPPORT_PHONE; ?>" class="btn btn-outline btn-full">
                                        <i class="fas fa-phone"></i>
                                        Appeler maintenant
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAQ -->
                <div class="support-section">
                    <h3 class="section-title">
                        <i class="fas fa-question-circle"></i>
                        Questions fréquentes
                    </h3>
                    
                    <div class="support-card">
                        <div class="faq-list">
                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFAQ(this)">
                                    <span>Comment fonctionne le système de commande ?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>1. Choisissez votre service et saisissez l'URL/ID cible<br>
                                    2. Effectuez le paiement via Mobile Money<br>
                                    3. Uploadez la capture d'écran du paiement<br>
                                    4. Notre équipe traite votre commande sous 24h</p>
                                </div>
                            </div>
                            
                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFAQ(this)">
                                    <span>Quels sont les délais de livraison ?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Nos délais de livraison varient selon le service :<br>
                                    • Followers : 24-48h<br>
                                    • Likes/Vues : 2-6h<br>
                                    • Commentaires : 12-24h</p>
                                </div>
                            </div>
                            
                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFAQ(this)">
                                    <span>Comment effectuer le paiement ?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Le paiement se fait exclusivement via Mobile Money :<br>
                                    1. Recevez les instructions de paiement<br>
                                    2. Effectuez le transfert sur le numéro indiqué<br>
                                    3. Uploadez la capture d'écran du reçu</p>
                                </div>
                            </div>
                            
                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFAQ(this)">
                                    <span>Que faire si ma commande est en retard ?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Si votre commande dépasse les délais annoncés :<br>
                                    1. Contactez-nous immédiatement<br>
                                    2. Nous accélérons le traitement<br>
                                    3. Compensation possible selon les cas</p>
                                </div>
                            </div>
                            
                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFAQ(this)">
                                    <span>Mes données sont-elles sécurisées ?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Absolument ! Nous respectons strictement :<br>
                                    • Chiffrement SSL/TLS<br>
                                    • Protection des données personnelles<br>
                                    • Conformité RGPD<br>
                                    • Aucun partage de données</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations de contact -->
                <div class="support-section">
                    <h3 class="section-title">
                        <i class="fas fa-info-circle"></i>
                        Informations de contact
                    </h3>
                    
                    <div class="support-card">
                        <div class="contact-info">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="info-content">
                                    <h4>Horaires de support</h4>
                                    <p>24h/24, 7j/7</p>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-globe"></i>
                                </div>
                                <div class="info-content">
                                    <h4>Site web</h4>
                                    <p><?php echo SITE_URL; ?></p>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="info-content">
                                    <h4>Adresse</h4>
                                    <p><?php echo COMPANY_ADDRESS; ?></p>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="info-content">
                                    <h4>Entreprise</h4>
                                    <p><?php echo COMPANY_NAME; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section d'aide rapide -->
            <div class="quick-help-section">
                <h3 class="section-title">
                    <i class="fas fa-lightbulb"></i>
                    Besoin d'aide rapide ?
                </h3>
                
                <div class="help-grid">
                    <div class="help-card">
                        <div class="help-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h4>Problème de commande</h4>
                        <p>Votre commande ne se lance pas ou rencontre une erreur ?</p>
                        <a href="https://wa.me/<?php echo str_replace(['+', ' ', '-'], '', SUPPORT_WHATSAPP); ?>?text=J'ai un problème avec ma commande SMM" 
                           target="_blank" 
                           class="btn btn-outline btn-sm">
                            <i class="fab fa-whatsapp"></i>
                            Demander de l'aide
                        </a>
                    </div>
                    
                    <div class="help-card">
                        <div class="help-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h4>Problème de paiement</h4>
                        <p>Difficulté avec le paiement Mobile Money ou validation ?</p>
                        <a href="https://wa.me/<?php echo str_replace(['+', ' ', '-'], '', SUPPORT_WHATSAPP); ?>?text=J'ai un problème de paiement SMM" 
                           target="_blank" 
                           class="btn btn-outline btn-sm">
                            <i class="fab fa-whatsapp"></i>
                            Demander de l'aide
                        </a>
                    </div>
                    
                    <div class="help-card">
                        <div class="help-icon">
                            <i class="fas fa-user-lock"></i>
                        </div>
                        <h4>Problème de compte</h4>
                        <p>Vous ne pouvez pas vous connecter ou accéder à votre compte ?</p>
                        <a href="https://wa.me/<?php echo str_replace(['+', ' ', '-'], '', SUPPORT_WHATSAPP); ?>?text=J'ai un problème avec mon compte SMM" 
                           target="_blank" 
                           class="btn btn-outline btn-sm">
                            <i class="fab fa-whatsapp"></i>
                            Demander de l'aide
                        </a>
                    </div>
                    
                    <div class="help-card">
                        <div class="help-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h4>Autre question</h4>
                        <p>Vous avez une autre question ou besoin d'assistance ?</p>
                        <a href="https://wa.me/<?php echo str_replace(['+', ' ', '-'], '', SUPPORT_WHATSAPP); ?>?text=J'ai une question générale sur SMM Platform" 
                           target="_blank" 
                           class="btn btn-outline btn-sm">
                            <i class="fab fa-whatsapp"></i>
                            Poser ma question
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
/* Support Container */
.support-container {
    margin-bottom: 2rem;
}

.support-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

/* Support Section */
.support-section {
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

.support-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 15px;
    padding: 1.5rem;
}

/* Contact Methods */
.contact-methods {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.contact-method {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
    transition: all 0.3s ease;
}

.contact-method:hover {
    background: rgba(255, 255, 255, 0.08);
    transform: translateY(-2px);
}

.method-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #ffffff;
    flex-shrink: 0;
}

.method-icon.whatsapp {
    background: #25d366;
}

.method-icon.email {
    background: #ff7a00;
}

.method-icon.phone {
    background: #17a2b8;
}

.method-content h4 {
    color: #ffffff;
    margin-bottom: 0.25rem;
}

.method-content p {
    color: #cccccc;
    margin-bottom: 0.75rem;
    font-size: 0.875rem;
}

/* FAQ */
.faq-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.faq-item {
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.faq-item:hover {
    border-color: rgba(255, 122, 0, 0.3);
}

.faq-question {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    background: rgba(255, 255, 255, 0.05);
    cursor: pointer;
    transition: all 0.3s ease;
}

.faq-question:hover {
    background: rgba(255, 255, 255, 0.08);
}

.faq-question span {
    color: #ffffff;
    font-weight: 600;
}

.faq-question i {
    color: #ff7a00;
    transition: transform 0.3s ease;
}

.faq-question.active i {
    transform: rotate(180deg);
}

.faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
    background: rgba(255, 255, 255, 0.03);
}

.faq-answer.active {
    max-height: 200px;
}

.faq-answer p {
    color: #cccccc;
    margin: 0;
    padding: 1rem 1.5rem;
    line-height: 1.6;
}

/* Contact Info */
.contact-info {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
}

.info-icon {
    width: 40px;
    height: 40px;
    background: rgba(255, 122, 0, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ff7a00;
    font-size: 1rem;
    flex-shrink: 0;
}

.info-content h4 {
    color: #ffffff;
    margin-bottom: 0.25rem;
    font-size: 0.875rem;
}

.info-content p {
    color: #cccccc;
    margin: 0;
    font-size: 0.875rem;
}

/* Quick Help Section */
.quick-help-section {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 2rem;
}

.help-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.help-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 15px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
}

.help-card:hover {
    border-color: rgba(255, 122, 0, 0.3);
    transform: translateY(-2px);
}

.help-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 122, 0, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    color: #ff7a00;
    font-size: 1.5rem;
}

.help-card h4 {
    color: #ffffff;
    margin-bottom: 0.75rem;
}

.help-card p {
    color: #cccccc;
    margin-bottom: 1rem;
    font-size: 0.875rem;
    line-height: 1.5;
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

.btn-success {
    background: #25d366;
    color: #ffffff;
    border-color: #25d366;
}

.btn-success:hover {
    background: #20c997;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(37, 211, 102, 0.3);
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

.btn-full {
    width: 100%;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .support-grid {
        grid-template-columns: 1fr;
    }
    
    .support-section {
        padding: 1rem;
    }
    
    .support-card {
        padding: 1rem;
    }
    
    .contact-method {
        flex-direction: column;
        text-align: center;
    }
    
    .help-grid {
        grid-template-columns: 1fr;
    }
    
    .faq-question {
        padding: 0.75rem 1rem;
    }
    
    .faq-answer p {
        padding: 0.75rem 1rem;
    }
}
</style>

<script>
// Toggle FAQ answers
function toggleFAQ(element) {
    const faqItem = element.parentElement;
    const answer = faqItem.querySelector('.faq-answer');
    const isActive = element.classList.contains('active');
    
    // Close all other FAQ items
    document.querySelectorAll('.faq-question').forEach(question => {
        question.classList.remove('active');
        question.nextElementSibling.classList.remove('active');
    });
    
    // Toggle current FAQ item
    if (!isActive) {
        element.classList.add('active');
        answer.classList.add('active');
    }
}

// Auto-open first FAQ item
document.addEventListener('DOMContentLoaded', function() {
    const firstFaq = document.querySelector('.faq-question');
    if (firstFaq) {
        firstFaq.click();
    }
});

// Smooth scroll to help cards
document.querySelectorAll('.help-card a').forEach(link => {
    link.addEventListener('click', function(e) {
        // Add a small delay to show the click effect
        setTimeout(() => {
            // The link will open WhatsApp automatically
        }, 100);
    });
});
</script>

<?php include '../templates/footer.php'; ?>