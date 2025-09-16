<?php
require_once '../includes/session.php';
require_once '../includes/header.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || !($_SESSION['is_admin'] ?? false)) {
    header('Location: ../user/login.php?error=access_denied');
    exit();
}

$action = $_GET['action'] ?? 'list';
$user_id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Token di sicurezza non valido.';
    } else {
        $action_type = $_POST['action_type'] ?? '';

        if ($action_type === 'update_status') {
            $user_id = $_POST['user_id'] ?? '';
            $is_admin = $_POST['is_admin'] ?? 0;
            $newsletter = $_POST['newsletter'] ?? 0;

            $stmt = $conn->prepare("UPDATE users SET is_admin = ?, newsletter = ? WHERE id = ?");
            $stmt->bind_param("iii", $is_admin, $newsletter, $user_id);

            if ($stmt->execute()) {

                if ((int)$newsletter === 1) {
                    $stmtUserEmail = $conn->prepare("SELECT email FROM users WHERE id = ?");
                    $stmtUserEmail->bind_param("i", $user_id);
                    $stmtUserEmail->execute();
                    $emailRes = $stmtUserEmail->get_result()->fetch_assoc();
                    $stmtUserEmail->close();

                    if ($emailRes && isset($emailRes['email'])) {
                        $emailVal = $emailRes['email'];
                        $stmtSub = $conn->prepare("INSERT INTO newsletter_subscribers (email, status, subscribed_at) VALUES (?, 'active', NOW()) ON DUPLICATE KEY UPDATE status='active', unsubscribed_at=NULL");
                        $stmtSub->bind_param("s", $emailVal);
                        $stmtSub->execute();
                        $stmtSub->close();
                    }
                } else {
                    $stmtUserEmail = $conn->prepare("SELECT email FROM users WHERE id = ?");
                    $stmtUserEmail->bind_param("i", $user_id);
                    $stmtUserEmail->execute();
                    $emailRes = $stmtUserEmail->get_result()->fetch_assoc();
                    $stmtUserEmail->close();

                    if ($emailRes && isset($emailRes['email'])) {
                        $emailVal = $emailRes['email'];
                        $stmtSub = $conn->prepare("UPDATE newsletter_subscribers SET status='unsubscribed', unsubscribed_at=NOW() WHERE email = ?");
                        $stmtSub->bind_param("s", $emailVal);
                        $stmtSub->execute();
                        $stmtSub->close();
                    }
                }

                $success = 'Utente aggiornato con successo!';
            } else {
                $error = 'Errore durante l\'aggiornamento dell\'utente.';
            }
            $stmt->close();
        } elseif ($action_type === 'update_profile_fields') {
            $user_id = intval($_POST['user_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $surname = trim($_POST['surname'] ?? '');
            $email = strtolower(trim($_POST['email'] ?? ''));
            if ($name === '' || $surname === '' || $email === '') {
                $error = 'Tutti i campi sono obbligatori.';
            } elseif (!preg_match("/^[a-zA-ZÀ-ÿ\s]{2,50}$/u", $name) || !preg_match("/^[a-zA-ZÀ-ÿ\s]{2,50}$/u", $surname)) {
                $error = 'Nome o cognome non validi.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
                $error = 'Email non valida.';
            } else {

                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1");
                $stmt->bind_param('si', $email, $user_id);
                $stmt->execute();
                $exists = ($stmt->get_result()->num_rows > 0);
                $stmt->close();
                if ($exists) {
                    $error = 'Email già in uso da un altro utente.';
                } else {
                    $stmt = $conn->prepare("UPDATE users SET name = ?, surname = ?, email = ? WHERE id = ?");
                    $stmt->bind_param('sssi', $name, $surname, $email, $user_id);
                    if ($stmt->execute()) {
                        $success = 'Dati utente aggiornati.';
                    } else {
                        $error = 'Errore durante l\'aggiornamento dei dati utente.';
                    }
                    $stmt->close();
                }
            }
        } elseif ($action_type === 'delete_user') {
            $user_id_to_delete = $_POST['user_id'] ?? '';

            if ($user_id_to_delete == ($_SESSION['user_id'] ?? null)) {
                $error = 'Non puoi eliminare il tuo account.';
            } else {
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id_to_delete);

                if ($stmt->execute()) {
                    $success = 'Utente eliminato con successo!';
                } else {
                    $error = 'Errore durante l\'eliminazione dell\'utente.';
                }
                $stmt->close();
            }
        }
    }
}

$user = null;
if ($action === 'edit' && $user_id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        header('Location: users.php?error=user_not_found');
        exit();
    }
}
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-3 col-lg-2">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Amministrazione</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="index.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>

                    <a href="users.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-users me-2"></i>Gestione Utenti
                    </a>
                    <a href="newsletter.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-envelope me-2"></i>Newsletter
                    </a>
                    <a href="subscribers.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-address-book me-2"></i>Iscritti
                    </a>
                    <a href="../index.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-home me-2"></i>Torna al Sito
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-9 col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <?php if ($action === 'edit'): ?>
                        Modifica Utente
                    <?php else: ?>
                        Gestione Utenti
                    <?php endif; ?>
                </h2>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">Lista Utenti</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Cognome</th>
                                        <th>Email</th>
                                        <th>Newsletter</th>
                                        <th>Admin</th>
                                        <th>Data Registrazione</th>
                                        <th>Azioni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $conn->prepare("SELECT * FROM users ORDER BY created_at DESC");
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    while ($row = $result->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['surname']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td>
                                            <?php if ($row['newsletter']): ?>
                                                <span class="badge bg-success">Iscritto</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Non iscritto</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($row['is_admin']): ?>
                                                <span class="badge bg-danger">Admin</span>
                                            <?php else: ?>
                                                <span class="badge bg-info">Utente</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'] ?? 'now')); ?></td>
                                        <td>
                                            <a href="?action=edit&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($row['id'] != ($_SESSION['user_id'] ?? null)): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteUser(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name'] . ' ' . $row['surname']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    <?php if ($result->num_rows === 0): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Nessun utente trovato</td>
                                    </tr>
                                    <?php endif;
                                    $stmt->close();
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">Modifica Utente: <?php echo htmlspecialchars($user['name'] . ' ' . $user['surname']); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <input type="hidden" name="action_type" value="update_profile_fields">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Nome</label>
                                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required minlength="2" maxlength="50" pattern="[A-Za-zÀ-ÿ\s]+">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Cognome</label>
                                        <input type="text" name="surname" class="form-control" value="<?php echo htmlspecialchars($user['surname']); ?>" required minlength="2" maxlength="50" pattern="[A-Za-zÀ-ÿ\s]+">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required maxlength="255">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Salva Dati</button>
                                </form>
                            </div>
                            <div class="col-lg-6">
                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <input type="hidden" name="action_type" value="update_status">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <div class="mb-3">
                                        <label for="newsletter" class="form-label">Newsletter</label>
                                        <select class="form-select" id="newsletter" name="newsletter">
                                            <option value="0" <?php echo !$user['newsletter'] ? 'selected' : ''; ?>>Non iscritto</option>
                                            <option value="1" <?php echo $user['newsletter'] ? 'selected' : ''; ?>>Iscritto</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="is_admin" class="form-label">Ruolo</label>
                                        <select class="form-select" id="is_admin" name="is_admin">
                                            <option value="0" <?php echo !$user['is_admin'] ? 'selected' : ''; ?>>Utente</option>
                                            <option value="1" <?php echo $user['is_admin'] ? 'selected' : ''; ?>>Amministratore</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-secondary">Aggiorna Stato</button>
                                </form>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="users.php" class="btn btn-outline-secondary">Torna alla lista</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Conferma Eliminazione</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Sei sicuro di voler eliminare l'utente <strong id="userName"></strong>?</p>
                <p class="text-danger">Questa azione non può essere annullata.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="action_type" value="delete_user">
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <button type="submit" class="btn btn-danger">Elimina</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function deleteUser(userId, userName) {
    document.getElementById('deleteUserId').value = userId;
    document.getElementById('userName').textContent = userName;
    new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
}
</script>

<?php include_once '../includes/footer.php'; ?>