<?php
require_once '../config/config.php';
require_once '../includes/session.php';
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../user/login.php?error=access_denied');
    exit();
}
?>

<?php include '../includes/header.php' ?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow-lg border-0">
        <div class="card-header bg-dark text-white text-center py-4">
          <h2 class="mb-0">
            <i class="fas fa-user-circle me-2"></i>Profilo Utente
          </h2>
        </div>
        <div class="card-body p-4">

          <div class="row">
            <div class="col-md-6 mb-4">
              <div class="profile-info-card h-100">
                <div class="d-flex align-items-center mb-3">
                  <div class="profile-avatar me-3">
                    <img src="../assets/images/user.png" alt="Avatar" class="rounded-circle" style="width: 60px; height: 60px;">
                  </div>
                  <div>
                    <h5 class="mb-1"><?php echo htmlspecialchars($_SESSION['nome'] . ' ' . $_SESSION['cognome']); ?></h5>
                    <small class="text-muted">Membro FanHub</small>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-6 mb-4">
              <div class="profile-stats-card h-100 text-center">
                <h6 class="text-muted mb-2">Stato Account</h6>
                <div class="badge bg-success fs-6 px-3 py-2">Attivo</div>
                <div class="mt-2">
                  <small class="text-muted">
                    Membro dal <?php echo date('M Y', strtotime($_SESSION['created_at'] ?? 'now')); ?>
                  </small>
                </div>
              </div>
            </div>
          </div>

          <hr class="my-4">

          <div class="profile-details">
            <h4 class="mb-4 text-center">Informazioni Personali</h4>

            <div class="row g-3">
              <div class="col-md-6">
                <div class="info-item">
                  <label class="form-label fw-bold text-muted">Nome</label>
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="fs-5"><?php echo htmlspecialchars($_SESSION['nome']); ?></span>
                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modificaNomeModal">
                      <i class="fas fa-edit me-1"></i>Modifica
                    </button>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="info-item">
                  <label class="form-label fw-bold text-muted">Cognome</label>
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="fs-5"><?php echo htmlspecialchars($_SESSION['cognome']); ?></span>
                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modificaCognomeModal">
                      <i class="fas fa-edit me-1"></i>Modifica
                    </button>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="info-item">
                  <label class="form-label fw-bold text-muted">Email</label>
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="fs-5"><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modificaEmailModal">
                      <i class="fas fa-edit me-1"></i>Modifica
                    </button>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="info-item">
                  <label class="form-label fw-bold text-muted">Newsletter</label>
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="fs-5">
                      <?php if($_SESSION['newsletter'] == 1): ?>
                        <span class="badge bg-success">Attiva</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Non attiva</span>
                      <?php endif; ?>
                    </span>
                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modificaNewsletterModal">
                      <i class="fas fa-edit me-1"></i>Modifica
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="text-center mt-5">
            <a href="../index.php" class="btn btn-outline-dark me-3">
              <i class="fas fa-home me-1"></i>Torna alla Home
            </a>
            <a href="../user/logout.php" class="btn btn-danger">
              <i class="fas fa-sign-out-alt me-1"></i>Logout
            </a>
            <button type="button" class="btn btn-outline-danger ms-3" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
              <i class="fas fa-user-slash me-1"></i>Elimina account
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="profilePageProcess.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <input type="hidden" name="action" value="delete_account">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="deleteAccountLabel">
            <i class="fas fa-triangle-exclamation me-2"></i>Conferma eliminazione account
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Chiudi"></button>
        </div>
        <div class="modal-body">
          <p class="mb-3">Questa azione è permanente e non può essere annullata.</p>
          <div class="mb-3">
            <label for="confirmPassword" class="form-label fw-bold">Inserisci la tua password per confermare</label>
            <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required minlength="8" autocomplete="current-password">
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="confirmDelete" required>
            <label class="form-check-label" for="confirmDelete">
              Ho compreso che il mio account e i dati associati verranno eliminati.
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>Annulla
          </button>
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-user-slash me-1"></i>Elimina definitivamente
          </button>
        </div>
      </form>
    </div>
  </div>

</div>

<div class="modal fade" id="modificaNomeModal" tabindex="-1" aria-labelledby="modificaNomeLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="profilePageProcess.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <div class="modal-header bg-dark text-white">
          <h5 class="modal-title" id="modificaNomeLabel">
            <i class="fas fa-user-edit me-2"></i>Modifica Nome
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Chiudi"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="nome" class="form-label fw-bold">Nuovo nome:</label>
            <input type="text" class="form-control" name="nome" id="nome" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>Annulla
          </button>
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-save me-1"></i>Salva
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modificaCognomeModal" tabindex="-1" aria-labelledby="modificaCognomeLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="profilePageProcess.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <div class="modal-header bg-dark text-white">
          <h5 class="modal-title" id="modificaCognomeLabel">
            <i class="fas fa-user-edit me-2"></i>Modifica Cognome
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Chiudi"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="cognome" class="form-label fw-bold">Nuovo cognome:</label>
            <input type="text" class="form-control" name="cognome" id="cognome" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>Annulla
          </button>
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-save me-1"></i>Salva
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modificaEmailModal" tabindex="-1" aria-labelledby="modificaEmailLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="profilePageProcess.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <div class="modal-header bg-dark text-white">
          <h5 class="modal-title" id="modificaEmailLabel">
            <i class="fas fa-envelope me-2"></i>Modifica Email
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Chiudi"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="email" class="form-label fw-bold">Nuova email:</label>
            <input type="email" class="form-control" name="email" id="email" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>Annulla
          </button>
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-save me-1"></i>Salva
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modificaNewsletterModal" tabindex="-1" aria-labelledby="modificaNewsletterLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="profilePageProcess.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <div class="modal-header bg-dark text-white">
          <h5 class="modal-title" id="modificaNewsletterLabel">
            <i class="fas fa-newspaper me-2"></i>Gestione Newsletter
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Chiudi"></button>
        </div>
        <div class="modal-body">
          <div class="form-check form-switch">
            <input type="checkbox" class="form-check-input" id="newsletter" name="newsletter" <?php echo ($_SESSION['newsletter'] == 1) ? 'checked' : ''; ?>>
            <label class="form-check-label fw-bold" for="newsletter">
              Ricevi aggiornamenti via email
            </label>
          </div>
          <small class="text-muted">Riceverai notizie sui GP, aggiornamenti sui piloti e contenuti esclusivi</small>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>Annulla
          </button>
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-save me-1"></i>Salva
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include '../includes/footer.php' ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
.profile-info-card, .profile-stats-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px;
    padding: 1.5rem;
    border: 1px solid #dee2e6;
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.profile-info-card:hover, .profile-stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.info-item {
    background: #fff;
    padding: 1.5rem;
    border-radius: 10px;
    border: 1px solid #dee2e6;
    transition: all 0.2s ease-in-out;
}

.info-item:hover {
    border-color: var(--brand-red);
    box-shadow: 0 2px 10px rgba(225, 6, 0, 0.1);
}

.profile-avatar img {
    border: 3px solid var(--brand-red);
    transition: transform 0.2s ease-in-out;
}

.profile-avatar img:hover {
    transform: scale(1.05);
}

.btn-outline-danger:hover {
    background-color: var(--brand-red);
    border-color: var(--brand-red);
    color: white;
}

.modal-header {
    border-bottom: 2px solid var(--brand-red);
}

.modal-footer {
    border-top: 1px solid #dee2e6;
}

.form-check-input:checked {
    background-color: var(--brand-red);
    border-color: var(--brand-red);
}
</style>
