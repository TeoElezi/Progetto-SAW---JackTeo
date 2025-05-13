<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registrazione</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow">
          <div class="card-body">
            <h3 class="card-title mb-4 text-center">Registrazione</h3>

            <form action="registrationProcess.php" method="POST">
              <div class="mb-3">
                <label for="username" class="form-label">Nome</label>
                <input type="text" class="form-control" id="name" name="name" required>
              </div>

              <div class="mb-3">
                <label for="username" class="form-label">Cognome</label>
                <input type="text" class="form-control" id="surname" name="surname" required>
              </div>

              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
              </div>

              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required minlength="6">
              </div>

              <div class="mb-3">
                <label for="confirm" class="form-label">Conferma Password</label>
                <input type="password" class="form-control" id="confirm" name="confirm" required>
              </div>

              <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="newsletter" name="newsletter">
                <label class="form-check-label" for="newsletter">Newsletter</label>
              </div>

              <button type="submit" class="btn btn-primary w-100">Registrati</button>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
