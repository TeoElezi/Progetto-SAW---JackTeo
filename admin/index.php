<?php
require_once '../includes/session.php';

// Check if user is admin
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || !($_SESSION['is_admin'] ?? false)) {
    header('Location: ../user/login.php?error=access_denied');
    exit();
}

include_once '../includes/header.php';

// Get statistics
$stats = [];

// Total users
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
$stmt->execute();
$result = $stmt->get_result();
$stats['users'] = $result->fetch_assoc()['total'];
$stmt->close();

// Total newsletter subscribers
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM newsletter_subscribers WHERE status = 'active'");
$stmt->execute();
$result = $stmt->get_result();
$stats['subscribers'] = $result->fetch_assoc()['total'];
$stmt->close();

// Total posts
// Rimosso: gestione post non utilizzata
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
                    <a href="index.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a href="users.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-users me-2"></i>Gestione Utenti
                    </a>
                    <a href="donations.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-donate me-2"></i>Donazioni
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

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Dashboard Amministrativa</h2>
                <div>
                    <span class="text-muted">Benvenuto, <?php echo htmlspecialchars($_SESSION['nome'] . ' ' . $_SESSION['cognome']); ?></span>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Utenti Totali</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['users']; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Iscritti Newsletter</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['subscribers']; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-envelope fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header">
                            <h5 class="mb-0">Azioni Rapide</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <a href="newsletter.php" class="btn btn-success w-100">
                                        <i class="fas fa-paper-plane me-2"></i>Invia Newsletter
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="users.php" class="btn btn-info w-100">
                                        <i class="fas fa-user-cog me-2"></i>Gestisci Utenti
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="subscribers.php" class="btn btn-warning w-100">
                                        <i class="fas fa-list me-2"></i>Lista Iscritti
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button onclick="fetchNews()" class="btn btn-secondary w-100">
                                        <i class="fas fa-sync-alt me-2"></i>Fetch News Manuale
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>

<div id="overlay" class="overlay"></div>
<div id="customAlert" class="custom-alert">
    <div id="alertMessage"></div>
    <button onclick="closeAlert()">OK</button>
</div>

<script>
function showAlert(message, isError = false) {
    const alert = document.getElementById('customAlert');
    const overlay = document.getElementById('overlay');
    const messageDiv = document.getElementById('alertMessage');
    
    messageDiv.innerHTML = message;
    alert.className = 'custom-alert' + (isError ? ' error' : '');
    
    overlay.style.display = 'block';
    alert.style.display = 'block';
}

function closeAlert() {
    document.getElementById('overlay').style.display = 'none';
    document.getElementById('customAlert').style.display = 'none';
}

function fetchNews() {
    // Mostra messaggio di caricamento
    showAlert('Caricamento news in corso...');
    
    // Chiamata AJAX
    fetch('../api/fetch_news.php')
        .then(response => response.text())
        .then(data => {
            // Mostra il risultato
            showAlert('Successo! ' + data);
        })
        .catch(error => {
            // Mostra errore
            showAlert('Errore durante il caricamento delle news: ' + error.message, true);
        });
}

// Chiudi alert cliccando fuori
document.getElementById('overlay').addEventListener('click', closeAlert);
</script>
