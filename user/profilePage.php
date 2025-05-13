<?php
session_start();
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profilo Utente</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
  <div class="card shadow">
    <div class="card-body">
      <h3 class="card-title mb-4">Profilo Utente</h3>

      <ul class="list-group">
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <strong>Nome:</strong> <span id="nomeUtente"><?php echo htmlspecialchars($_SESSION['nome']); ?></span>
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modificaNomeModal">Modifica</button>

        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <strong>Cognome:</strong> <span id="cognomeUtente"><?php echo htmlspecialchars($_SESSION['cognome']); ?></span>
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modificaCognomeModal">Modifica</button>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <strong>Email:</strong> <span id="emailUtente"><?php echo htmlspecialchars($_SESSION['email']); ?></span>
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modificaEmailModal">Modifica</button>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <strong>Newsletter:</strong> <span id="emailUtente"><?php if($_SESSION['newsletter'] == 1) echo "Sì";
                                                                    else echo "No"; ?></span>
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modificaNewsletterModal">Modifica</button>
        </li>
      </ul>

    </div>
  </div>
</div>

<!-- Modale per modifica del nome -->
<div class="modal fade" id="modificaNomeModal" tabindex="-1" aria-labelledby="modificaNomeLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="profilePageProcess.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="modificaNomeLabel">Modifica nome</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
        </div>
        <div class="modal-body">
          <label for="nome" class="form-label">Nuovo nome:</label>
          <input type="text" class="form-control" name="nome" id="nome" required>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Salva</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modale per modifica del nome -->

<div class="modal fade" id="modificaCognomeModal" tabindex="-1" aria-labelledby="modificaCognomeLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="profilePageProcess.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="modificaNomeLabel">Modifica cognome</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
        </div>
        <div class="modal-body">
          <label for="cognome" class="form-label">Nuovo cognome:</label>
          <input type="text" class="form-control" name="cognome" id="cognome" required>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Salva</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modale per modifica del nome -->

<div class="modal fade" id="modificaEmailModal" tabindex="-1" aria-labelledby="modificaEmailLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="profilePageProcess.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="modificaEmailLabel">Modifica email</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
        </div>
        <div class="modal-body">
          <label for="email" class="form-label">Nuovo email:</label>
          <input type="text" class="form-control" name="email" id="email" required>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Salva</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modificaNewsletterModal" tabindex="-1" aria-labelledby="modificaNewsletterLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="profilePageProcess.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="modificaNewsletterLabel">Modifica newsletter</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
        </div>
        <div class="mb-3 form-check">
          <input type="checkbox" class="form-check-input" id="newsletter" name="newsletter">
          <label class="form-check-label" for="newsletter">Newsletter</label>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Salva</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
