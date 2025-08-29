<?php
require_once '../includes/session.php';

// Check if user is admin
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || !($_SESSION['is_admin'] ?? false)) {
    header('Location: ../user/login.php?error=access_denied');
    exit();
}

include_once '../includes/header.php';
require_once '../includes/NewsletterManager.php';

// Initialize NewsletterManager
$newsletterManager = new NewsletterManager($conn);

// Handle newsletter sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Token di sicurezza non valido.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'send_newsletter') {
            $selected_news = $_POST['selected_news'] ?? [];
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');
            
            if (empty($selected_news) || empty($subject)) {
                $error = 'Seleziona almeno una news e inserisci un oggetto.';
            } else {
                // Send newsletter using selected news
                $result = $newsletterManager->sendNewsletterWithNews($selected_news, $subject, $message);
                
                if ($result['success']) {
                    $success = "Newsletter inviata con successo! Inviata a {$result['sent_count']} iscritti.";
                    if ($result['failed_count'] > 0) {
                        $success .= " {$result['failed_count']} invii falliti.";
                    }
                    if (!empty($result['errors'])) {
                        $success .= "<br><small>Errori: " . implode(', ', $result['errors']) . "</small>";
                    }
                } else {
                    $error = 'Errore durante l\'invio: ' . $result['error'];
                }
            }
        }
    }
}

// Get latest news for selection
$stmt = $conn->prepare("SELECT id, title, posted_at FROM news ORDER BY posted_at DESC LIMIT 100");
$stmt->execute();
$available_news = $stmt->get_result();
$stmt->close();

// Get newsletter statistics
$stats = $newsletterManager->getNewsletterStats();
$subscriber_count = $stats['active_subscribers'];
?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Amministrazione</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="index.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    
                    <a href="users.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-users me-2"></i>Gestione Utenti
                    </a>
                    <a href="newsletter.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-envelope me-2"></i>Newsletter
                    </a>
                    <a href="subscribers.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-address-book me-2"></i>Iscritti
                    </a>
                    <a href="../pages/index.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-home me-2"></i>Torna al Sito
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Gestione Newsletter</h2>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <!-- Newsletter Statistics -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0"><?php echo $subscriber_count; ?></h4>
                                    <p class="mb-0">Iscritti Attivi</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                
                
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">Newsletter</h4>
                                    <p class="mb-0">Gestione</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-envelope fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Send Newsletter Form -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Invia Newsletter</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="action" value="send_newsletter">
                        
                        <div class="mb-3">
                            <label class="form-label">Seleziona News da inviare *</label>
                            <div class="border rounded p-3" style="max-height: 320px; overflow-y: auto;">
                                <?php if ($available_news->num_rows > 0): ?>
                                    <?php while ($n = $available_news->fetch_assoc()): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="<?php echo (int)$n['id']; ?>" id="news_<?php echo (int)$n['id']; ?>" name="selected_news[]">
                                            <label class="form-check-label" for="news_<?php echo (int)$n['id']; ?>">
                                                <?php echo htmlspecialchars($n['title']); ?> (<?php echo date('d/m/Y', strtotime($n['posted_at'])); ?>)
                                            </label>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-muted">Nessuna news disponibile. Usa "Fetch News Manuale" dalla Dashboard.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label">Oggetto Email *</label>
                            <input type="text" class="form-control" id="subject" name="subject" 
                                   placeholder="Oggetto della newsletter..." required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Messaggio Personalizzato (opzionale)</label>
                            <textarea class="form-control" id="message" name="message" rows="4" 
                                      placeholder="Aggiungi un messaggio introduttivo prima dell'elenco news..."></textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Nota:</strong> La newsletter verrà inviata a tutti gli iscritti attivi. 
                            L'email includerà le news selezionate con titolo e link.
                        </div>
                        
                        <button type="submit" class="btn btn-primary" 
                                onclick="return confirm('Sei sicuro di voler inviare la newsletter a <?php echo $subscriber_count; ?> iscritti?')">
                            <i class="fas fa-paper-plane me-2"></i>Invia Newsletter
                        </button>
                    </form>
                </div>
            </div>

            
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
