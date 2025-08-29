<?php
  require_once '../includes/session.php';
  if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: ../pages/index.php');
    exit();
  }
?>
<?php include_once '../includes/header.php'; ?>

<div class="col-md-6 justify-content-center mx-auto">
  <div class="p-4 shadow bg-white rounded">
    <h3 class="mb-4 text-center">Accedi</h3>

    <?php
      if (isset($_GET['error'])) {
        $error = $_GET['error'];
        $errorMessage = '';

        switch ($error) {
          case 'invalid_credentials':
            $errorMessage = 'Credenziali non valide.';
            break;
          case 'user_not_found':
            $errorMessage = 'Utente non trovato. Controlla l\'email inserita.';
            break;
          case 'wrong_password':
            $errorMessage = 'Password errata. Riprova.';
            break;
          case 'empty_fields':
            $errorMessage = 'Per favore, compila tutti i campi.';
            break;
          case 'csrf':
            $errorMessage = 'Sessione non valida. Riprova.';
            break;
          case 'too_many_attempts':
            $errorMessage = 'Troppi tentativi. Riprova tra qualche minuto.';
            break;
          default:
            $errorMessage = 'Si è verificato un errore. Riprova.';
        }

        echo "<div class='alert alert-danger'>$errorMessage</div>";
      }
    ?>

    <form action="loginProcess.php" method="POST">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
      </div>

      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
        <label class="form-check-label" for="remember_me">Ricordami</label>
      </div>

      <div class="mb-3">
        <p class="mb-2">Non sei registrato?</p>
        <a href="registration.php" class="btn btn-outline-primary w-100" aria-label="Vai alla pagina di registrazione">Registrati</a>
      </div>

      <button type="submit" class="btn btn-dark w-100">Accedi</button>
    </form>
  </div>
</div>

<?php include_once '../includes/footer.php'; ?>
