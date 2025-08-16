    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <!-- Informations de l'entreprise -->
                <div class="footer-section">
                    <div class="footer-logo">
                        <img src="/assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" class="footer-logo-img">
                        <span class="footer-logo-text"><?php echo SITE_NAME; ?></span>
                    </div>
                    <p class="footer-description">
                        Plateforme de services SMM premium pour Instagram, TikTok et YouTube. 
                        Boostez vos réseaux sociaux avec nos services de qualité.
                    </p>
                    <div class="footer-social">
                        <a href="#" class="social-link" aria-label="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Liens rapides -->
                <div class="footer-section">
                    <h3 class="footer-title">Liens rapides</h3>
                    <ul class="footer-links">
                        <li class="footer-link-item">
                            <a href="/#services" class="footer-link">Services</a>
                        </li>
                        <li class="footer-link-item">
                            <a href="/#how-it-works" class="footer-link">Comment ça marche</a>
                        </li>
                        <li class="footer-link-item">
                            <a href="/#testimonials" class="footer-link">Témoignages</a>
                        </li>
                        <li class="footer-link-item">
                            <a href="/#faq" class="footer-link">FAQ</a>
                        </li>
                        <li class="footer-link-item">
                            <a href="/#contact" class="footer-link">Contact</a>
                        </li>
                    </ul>
                </div>
                
                <!-- Services -->
                <div class="footer-section">
                    <h3 class="footer-title">Nos services</h3>
                    <ul class="footer-links">
                        <li class="footer-link-item">
                            <a href="/#instagram" class="footer-link">Instagram</a>
                        </li>
                        <li class="footer-link-item">
                            <a href="/#tiktok" class="footer-link">TikTok</a>
                        </li>
                        <li class="footer-link-item">
                            <a href="/#youtube" class="footer-link">YouTube</a>
                        </li>
                        <li class="footer-link-item">
                            <a href="/#facebook" class="footer-link">Facebook</a>
                        </li>
                        <li class="footer-link-item">
                            <a href="/#twitter" class="footer-link">Twitter</a>
                        </li>
                    </ul>
                </div>
                
                <!-- Support et contact -->
                <div class="footer-section">
                    <h3 class="footer-title">Support & Contact</h3>
                    <div class="footer-contact">
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>support@smmplatform.com</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>+225 0700000000</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <span>24/7 Support</span>
                        </div>
                    </div>
                    <div class="footer-cta">
                        <a href="/register.php" class="btn btn-primary btn-sm">Commencer maintenant</a>
                    </div>
                </div>
            </div>
            
            <!-- Barre de copyright -->
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p class="copyright">
                        &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Tous droits réservés.
                    </p>
                    <div class="footer-legal">
                        <a href="/terms.php" class="legal-link">Conditions d'utilisation</a>
                        <a href="/privacy.php" class="legal-link">Politique de confidentialité</a>
                        <a href="/refund.php" class="legal-link">Politique de remboursement</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script src="/assets/js/main.js"></script>
    
    <!-- Scripts spécifiques à la page -->
    <?php if (isset($page_scripts)): ?>
        <?php foreach ($page_scripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Scripts inline spécifiques à la page -->
    <?php if (isset($page_scripts_inline)): ?>
        <script>
            <?php echo $page_scripts_inline; ?>
        </script>
    <?php endif; ?>
</body>
</html>