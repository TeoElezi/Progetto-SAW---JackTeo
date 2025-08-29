<?php
  require_once '../includes/session.php';
  if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: ../pages/index.php');
    exit();
  }
  include_once '../includes/header.php';
?>
    <div class="row justify-content-center">
  <div class="col-md-6">
    <div class="p-4 shadow bg-white rounded">
      <h3 class="mb-4 text-center">Registrazione</h3>

      <form action="registrationProcess.php" method="POST" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <div class="mb-3">
          <label for="name" class="form-label">Nome</label>
          <input type="text" class="form-control" id="name" name="name" required minlength="2" maxlength="50" pattern="[A-Za-zÀ-ÿ\s]+">
        </div>

        <div class="mb-3">
          <label for="surname" class="form-label">Cognome</label>
          <input type="text" class="form-control" id="surname" name="surname" required minlength="2" maxlength="50" pattern="[A-Za-zÀ-ÿ\s]+">
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" name="email" required maxlength="255" autocomplete="email">
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" required minlength="8" autocomplete="new-password">
          <div class="form-text">Minimo 8 caratteri, almeno una lettera e un numero.</div>
        </div>

        <div class="mb-3">
          <label for="confirm" class="form-label">Conferma Password</label>
          <input type="password" class="form-control" id="confirm" name="confirm" required minlength="8" autocomplete="new-password">
        </div>

        <div class="mb-3 form-check">
          <input type="checkbox" class="form-check-input" id="newsletter" name="newsletter">
          <label class="form-check-label" for="newsletter">Newsletter</label>
        </div>

        <button type="submit" class="btn btn-dark w-100">Registrati</button>
      </form>
    </div>
  </div>
</div>

<?php include_once '../includes/footer.php'; ?>
