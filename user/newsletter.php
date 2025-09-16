<?php
require_once '../includes/session.php';
require_once '../includes/NewsletterManager.php';
include_once '../includes/header.php';

$newsletterManager = new NewsletterManager($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Token di sicurezza non valido.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'subscribe') {

            if ($newsletterManager->addSubscriber($_SESSION['email'])) {

                $stmt_update = $conn->prepare("UPDATE users SET newsletter = 1 WHERE email = ?");
                $stmt_update->bind_param("s", $_SESSION['email']);
                $stmt_update->execute();
                $stmt_update->close();

                $_SESSION['newsletter'] = 1;
                $success = 'Iscrizione alla newsletter completata con successo!';
            } else {
                $error = 'Errore durante l\'iscrizione. Riprova.';
            }
        } elseif ($action === 'unsubscribe') {

            if ($newsletterManager->removeSubscriber($_SESSION['email'])) {

                $stmt_update = $conn->prepare("UPDATE users SET newsletter = 0 WHERE email = ?");
                $stmt_update->bind_param("s", $_SESSION['email']);
                $stmt_update->execute();
                $stmt_update->close();

                $_SESSION['newsletter'] = 0;
                $success = 'Disiscrizione dalla newsletter completata.';
            } else {
                $error = 'Errore durante la disiscrizione. Riprova.';
            }
        }
    }
}

$stmt_status = $conn->prepare("SELECT newsletter FROM users WHERE email = ? LIMIT 1");
$stmt_status->bind_param("s", $_SESSION['email']);
$stmt_status->execute();
$res_status = $stmt_status->get_result();
$row_status = $res_status->fetch_assoc();
$stmt_status->close();
$newsletter_status = (int)($row_status['newsletter'] ?? ($_SESSION['newsletter'] ?? 0));
$_SESSION['newsletter'] = $newsletter_status;
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Gestione Newsletter</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Stato attuale</h5>
                            <p>
                                <strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?><br>
                                <strong>Newsletter:</strong>
                                <?php if ($newsletter_status): ?>
                                    <span class="badge bg-success">Iscritto</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Non iscritto</span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <h5>Azioni</h5>
                            <?php if ($newsletter_status): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <input type="hidden" name="action" value="unsubscribe">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Sei sicuro di voler disiscriverti dalla newsletter?')">
                                        Disiscriviti
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <input type="hidden" name="action" value="subscribe">
                                    <button type="submit" class="btn btn-success">
                                        Iscriviti
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr>

                    <div class="mt-4">
                        <h5>Informazioni sulla Newsletter</h5>
                        <ul>
                            <li>Riceverai aggiornamenti su notizie, eventi e contenuti esclusivi</li>
                            <li>Puoi disiscriverti in qualsiasi momento</li>
                            <li>La tua email è protetta e non sarà condivisa con terzi</li>
                            <li>Puoi modificare le tue preferenze dalla tua area personale</li>
                        </ul>
                    </div>

                    <div class="mt-3">
                        <a href="../index.php" class="btn btn-outline-primary">Torna alla Home</a>
                        <a href="profilePage.php" class="btn btn-outline-secondary">Profilo</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
