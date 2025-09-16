<?php
require_once '../config/config.php';
require_once '../includes/session.php';
require_once '../includes/NewsletterManager.php';
require_once '../config/email_config.php';

$success = false;
$error = '';
$email = '';

if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = $_GET['email'];
    $token = $_GET['token'];

    $decoded = base64_decode($token, true);
    if ($decoded !== false) {
        $parts = explode('|', $decoded);
        if (count($parts) === 3) {
            list($emailInToken, $expiresAt, $signature) = $parts;
            $payload = $emailInToken . '|' . $expiresAt;
            $expectedSig = hash_hmac('sha256', $payload, defined('NEWSLETTER_UNSUBSCRIBE_SECRET') ? NEWSLETTER_UNSUBSCRIBE_SECRET : '');
            if (hash_equals($expectedSig, $signature) && hash_equals($emailInToken, $email) && (time() <= (int)$expiresAt)) {

        $newsletterManager = new NewsletterManager($conn);
        if ($newsletterManager->removeSubscriber($email)) {
            $success = true;

            $stmt = $conn->prepare("UPDATE users SET newsletter = 0 WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->close();

            if (isset($_SESSION['logged_in']) && isset($_SESSION['email']) && $_SESSION['email'] === $email) {
                $_SESSION['newsletter'] = 0;
            }
        } else {
            $error = 'Errore durante la disiscrizione. Riprova.';
        }
            } else {
                $error = 'Link di disiscrizione non valido o scaduto.';
            }
        } else {
            $error = 'Link di disiscrizione non valido.';
        }
    } else {
        $error = 'Link di disiscrizione non valido.';
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
                            <a href="../index.php" class="btn btn-primary">
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
                            <a href="../index.php" class="btn btn-primary">
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
                            <a href="../index.php" class="btn btn-primary">
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
