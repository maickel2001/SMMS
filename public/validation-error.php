<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$page_title = 'Erreur de validation';
$page_description = 'Erreur de validation des données soumises';

// Récupérer les erreurs de validation
$errors = $_SESSION['validation_errors'] ?? [];
$form_data = $_SESSION['form_data'] ?? [];

// Nettoyer les erreurs après les avoir récupérées
unset($_SESSION['validation_errors'], $_SESSION['form_data']);

// Rediriger si pas d'erreurs
if (empty($errors)) {
    redirect('/');
}

include '../templates/header.php';
?>

<div class="error-page">
    <div class="container">
        <div class="error-content">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            
            <h1 class="error-title">Erreur de validation</h1>
            <p class="error-description">
                Certaines informations saisies ne sont pas valides. Veuillez corriger les erreurs ci-dessous.
            </p>

            <div class="validation-errors">
                <h3>Erreurs détectées :</h3>
                <ul class="error-list">
                    <?php foreach ($errors as $field => $error): ?>
                        <li class="error-item">
                            <span class="error-field"><?php echo htmlspecialchars($field); ?> :</span>
                            <span class="error-message"><?php echo htmlspecialchars($error); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="error-actions">
                <a href="javascript:history.back()" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Retour
                </a>
                
                <a href="/" class="btn btn-primary">
                    <i class="fas fa-home"></i>
                    Accueil
                </a>
            </div>

            <?php if (!empty($form_data)): ?>
                <div class="form-data-preview">
                    <h4>Données saisies :</h4>
                    <div class="data-grid">
                        <?php foreach ($form_data as $field => $value): ?>
                            <?php if (is_string($value) && !empty($value)): ?>
                                <div class="data-item">
                                    <span class="data-label"><?php echo htmlspecialchars($field); ?> :</span>
                                    <span class="data-value"><?php echo htmlspecialchars($value); ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.error-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    padding: 2rem 0;
}

.error-content {
    text-align: center;
    max-width: 600px;
    margin: 0 auto;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 20px;
    padding: 3rem 2rem;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.error-icon {
    font-size: 4rem;
    color: #ff7a00;
    margin-bottom: 1.5rem;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.error-title {
    font-size: 2.5rem;
    color: #ffffff;
    margin-bottom: 1rem;
    font-weight: 700;
}

.error-description {
    font-size: 1.1rem;
    color: #cccccc;
    margin-bottom: 2rem;
    line-height: 1.6;
}

.validation-errors {
    background: rgba(255, 122, 0, 0.1);
    border: 1px solid rgba(255, 122, 0, 0.3);
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    text-align: left;
}

.validation-errors h3 {
    color: #ff7a00;
    margin-bottom: 1rem;
    font-size: 1.2rem;
}

.error-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.error-item {
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(255, 122, 0, 0.2);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.error-item:last-child {
    border-bottom: none;
}

.error-field {
    font-weight: 600;
    color: #ff7a00;
    text-transform: capitalize;
}

.error-message {
    color: #ffffff;
    font-style: italic;
}

.error-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-bottom: 2rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.btn-outline {
    background: transparent;
    color: #ff7a00;
    border-color: #ff7a00;
}

.btn-outline:hover {
    background: #ff7a00;
    color: #ffffff;
    transform: translateY(-2px);
}

.btn-primary {
    background: #ff7a00;
    color: #ffffff;
}

.btn-primary:hover {
    background: #e66a00;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 122, 0, 0.3);
}

.form-data-preview {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    padding: 1.5rem;
    text-align: left;
}

.form-data-preview h4 {
    color: #ffffff;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.data-grid {
    display: grid;
    gap: 0.75rem;
}

.data-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
}

.data-label {
    font-weight: 600;
    color: #ff7a00;
    text-transform: capitalize;
}

.data-value {
    color: #ffffff;
    font-family: monospace;
    background: rgba(0, 0, 0, 0.3);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

@media (max-width: 768px) {
    .error-content {
        padding: 2rem 1rem;
        margin: 1rem;
    }
    
    .error-title {
        font-size: 2rem;
    }
    
    .error-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
    
    .error-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
    
    .data-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
}
</style>

<?php include '../templates/footer.php'; ?>