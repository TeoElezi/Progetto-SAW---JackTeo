<?php
require_once '../includes/session.php';
require_once '../includes/NewsletterManager.php';

$success = false;
$error = '';
$email = '';

// Verifica parametri
if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = $_GET['email'];
    $token = $_GET['token'];
    
    // Verifica token (semplificato per demo)
    $expected_token = hash('sha256', $email . 'unsubscribe' . date('Y-m-d'));
    
    if (hash_equals($expected_token, $token)) {
        // Disiscrivi dalla newsletter
        $newsletterManager = new NewsletterManager($conn);
        if ($newsletterManager->removeSubscriber($email)) {
            $success = true;
            
            // Aggiorna anche la tabella users se l'utente è loggato
            if (isset($_SESSION['logged_in']) && $_SESSION['email'] === $email) {
                $stmt = $conn->prepare("UPDATE users SET newsletter = 0 WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->close();
                
                $_SESSION['newsletter'] = 0;
            }
        } else {
            $error = 'Errore durante la disiscrizione. Riprova.';
        }
    } else {
        $error = 'Link di disiscrizione non valido o scaduto.';
    }
} else {
    $error = 'Parametri mancanti per la disiscrizione.';
}

include_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">
                        <i class="fas fa-envelope me-2"></i>
                        Disiscrizione Newsletter
                    </h3>
                </div>
                <div class="card-body text-center">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h4>Disiscrizione Completata</h4>
                            <p class="mb-0">
                                L'email <strong><?php echo htmlspecialchars($email); ?></strong> è stata rimossa dalla nostra newsletter.
                            </p>
                        </div>
                        
                        <p class="text-muted">
                            Non riceverai più email dalla nostra newsletter. 
                            Se cambierai idea, potrai sempre iscriverti di nuovo dalla tua area personale.
                        </p>
                        
                        <div class="mt-4">
                            <a href="../pages/index.php" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i>Torna alla Home
                            </a>
                            <?php if (isset($_SESSION['logged_in'])): ?>
                                <a href="profilePage.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-user me-2"></i>Area Personale
                                </a>
                            <?php endif; ?>
                        </div>
                        
                    <?php elseif ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                            <h4>Errore</h4>
                            <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                        </div>
                        
                        <div class="mt-4">
                            <a href="../pages/index.php" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i>Torna alla Home
                            </a>
                            <a href="newsletter.php" class="btn btn-outline-secondary">
                                <i class="fas fa-envelope me-2"></i>Gestione Newsletter
                            </a>
                        </div>
                        
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle fa-3x text-info mb-3"></i>
                            <h4>Disiscrizione Newsletter</h4>
                            <p class="mb-0">
                                Per disiscriverti dalla newsletter, clicca sul link che hai ricevuto via email.
                            </p>
                        </div>
                        
                        <div class="mt-4">
                            <a href="../pages/index.php" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i>Torna alla Home
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
