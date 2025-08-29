<?php
require_once '../includes/session.php';

// Check if user is admin
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || !($_SESSION['is_admin'] ?? false)) {
    header('Location: ../user/login.php?error=access_denied');
    exit();
}

include_once '../includes/header.php';

// Handle subscriber management
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Token di sicurezza non valido.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_status') {
            $subscriber_id = $_POST['subscriber_id'] ?? '';
            $status = $_POST['status'] ?? '';
            
            if ($status === 'unsubscribed') {
                $stmt = $conn->prepare("UPDATE newsletter_subscribers SET status = 'unsubscribed', unsubscribed_at = NOW() WHERE id = ?");
            } else {
                $stmt = $conn->prepare("UPDATE newsletter_subscribers SET status = 'active', unsubscribed_at = NULL WHERE id = ?");
            }
            
            $stmt->bind_param("i", $subscriber_id);
            
            if ($stmt->execute()) {
                // Mantieni allineata anche la tabella users se esiste un utente con stessa email
                $stmtEmail = $conn->prepare("SELECT email FROM newsletter_subscribers WHERE id = ?");
                $stmtEmail->bind_param("i", $subscriber_id);
                $stmtEmail->execute();
                $emailRes = $stmtEmail->get_result()->fetch_assoc();
                $stmtEmail->close();

                if ($emailRes && isset($emailRes['email'])) {
                    if ($status === 'unsubscribed') {
                        $stmtUser = $conn->prepare("UPDATE users SET newsletter = 0 WHERE email = ?");
                    } else {
                        $stmtUser = $conn->prepare("UPDATE users SET newsletter = 1 WHERE email = ?");
                    }
                    $stmtUser->bind_param("s", $emailRes['email']);
                    $stmtUser->execute();
                    $stmtUser->close();
                }
                $success = 'Stato iscritto aggiornato con successo!';
            } else {
                $error = 'Errore durante l\'aggiornamento dello stato.';
            }
            $stmt->close();
        } elseif ($action === 'delete_subscriber') {
            $subscriber_id = $_POST['subscriber_id'] ?? '';
            
            $stmt = $conn->prepare("DELETE FROM newsletter_subscribers WHERE id = ?");
            $stmt->bind_param("i", $subscriber_id);
            
            if ($stmt->execute()) {
                $success = 'Iscritto eliminato con successo!';
            } else {
                $error = 'Errore durante l\'eliminazione dell\'iscritto.';
            }
            $stmt->close();
        }
    }
}

// Get subscribers with pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total count
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM newsletter_subscribers");
$stmt->execute();
$total_subscribers = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$total_pages = ceil($total_subscribers / $per_page);

// Get subscribers for current page
$stmt = $conn->prepare("SELECT * FROM newsletter_subscribers ORDER BY subscribed_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $per_page, $offset);
$stmt->execute();
$subscribers = $stmt->get_result();
$stmt->close();

// Get status counts
$stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM newsletter_subscribers GROUP BY status");
$stmt->execute();
$status_counts = [];
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $status_counts[$row['status']] = $row['count'];
}
$stmt->close();
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
                    <a href="newsletter.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-envelope me-2"></i>Newsletter
                    </a>
                    <a href="subscribers.php" class="list-group-item list-group-item-action active">
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
                <h2>Gestione Iscritti Newsletter</h2>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <!-- Subscriber Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0"><?php echo $total_subscribers; ?></h4>
                                    <p class="mb-0">Totale Utenti</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0"><?php echo $status_counts['active'] ?? 0; ?></h4>
                                    <p class="mb-0">Attivi</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0"><?php echo $status_counts['unsubscribed'] ?? 0; ?></h4>
                                    <p class="mb-0">Disiscritti</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-times-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0"><?php echo $status_counts['bounced'] ?? 0; ?></h4>
                                    <p class="mb-0">Bounce</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscribers List -->
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">Lista Iscritti</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Email</th>
                                    <th>Stato</th>
                                    <th>Data Iscrizione</th>
                                    <th>Ultimo Invio</th>
                                    <th>Data Disiscrizione</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($subscriber = $subscribers->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($subscriber['email']); ?></td>
                                    <td>
                                        <?php if ($subscriber['status'] === 'active'): ?>
                                            <span class="badge bg-success">Attivo</span>
                                        <?php elseif ($subscriber['status'] === 'unsubscribed'): ?>
                                            <span class="badge bg-warning">Disiscritto</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Bounce</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($subscriber['subscribed_at'])); ?></td>
                                    <td>
                                        <?php echo $subscriber['last_sent_at'] ? date('d/m/Y H:i', strtotime($subscriber['last_sent_at'])) : '-'; ?>
                                    </td>
                                    <td>
                                        <?php echo $subscriber['unsubscribed_at'] ? date('d/m/Y H:i', strtotime($subscriber['unsubscribed_at'])) : '-'; ?>
                                    </td>
                                    <td>
                                        <?php if ($subscriber['status'] === 'active'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="subscriber_id" value="<?php echo $subscriber['id']; ?>">
                                                <input type="hidden" name="status" value="unsubscribed">
                                                <button type="submit" class="btn btn-sm btn-outline-warning" 
                                                        onclick="return confirm('Disiscrivere questo utente?')">
                                                    <i class="fas fa-user-times"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="subscriber_id" value="<?php echo $subscriber['id']; ?>">
                                                <input type="hidden" name="status" value="active">
                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-user-check"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            <input type="hidden" name="action" value="delete_subscriber">
                                            <input type="hidden" name="subscriber_id" value="<?php echo $subscriber['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                    onclick="return confirm('Eliminare definitivamente questo iscritto?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php if ($subscribers->num_rows === 0): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Nessun iscritto trovato</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Paginazione iscritti">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>">Precedente</a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Successiva</a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
