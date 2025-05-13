<?php
  include '../includes/header.php';
?>
    <div class="row justify-content-center">
  <div class="col-md-6">
    <div class="p-4 shadow bg-white rounded">
      <h3 class="mb-4 text-center">Registrazione</h3>

      <form action="registrationProcess.php" method="POST">
        <div class="mb-3">
          <label for="name" class="form-label">Nome</label>
          <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <div class="mb-3">
          <label for="surname" class="form-label">Cognome</label>
          <input type="text" class="form-control" id="surname" name="surname" required>
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" name="email" required>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <div class="mb-3">
          <label for="confirm" class="form-label">Conferma Password</label>
          <input type="password" class="form-control" id="confirm" name="confirm" required>
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

<?php
  include '../includes/footer.php';
?>
