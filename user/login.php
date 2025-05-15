<?php
  include '../includes/header.php';
?>
    <div class="col-md-6 justify-content-center mx-auto">
      <div class="p-4 shadow bg-white rounded">
        <h3 class="mb-4 text-center">Accedi</h3>

        <form action="loginProcess.php" method="POST">
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>

          <div class="mb-3">
            <label class="form-label">non sei registrato?</label>
            <a href="registration.php" class="btn btn-outline-primary w-100">Registrati</a>
          </div>

          <button type="submit" class="btn btn-dark w-100">Accedi</button>
        </form>
      </div>
    </div>

<?php
  include '../includes/footer.php';
?>