<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>F1 FanHub - Home</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">🏁 F1 FanHub</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navContent">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="news.php">Notizie</a></li>
        <li class="nav-item"><a class="nav-link" href="races.php">Gare</a></li>
        <li class="nav-item"><a class="nav-link" href="drivers.php">Piloti</a></li>
        <li class="nav-item"><a class="nav-link" href="teams.php">Team</a></li>
        <li class="nav-item"><a class="nav-link" href="standings.php">Classifica</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Contenuto principale -->
<div class="container my-5">

  <!-- Sezione Notizie -->
  <div class="row mb-5">
    <div class="col">
      <h2 class="mb-4">Ultime Notizie</h2>
      <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php
        $result = mysqli_query($conn, "SELECT * FROM news ORDER BY posted_at DESC LIMIT 3");
        while($row = mysqli_fetch_assoc($result)) {
            echo '<div class="col">';
            echo '  <div class="card h-100">';
            if ($row['image_url']) {
                echo '    <img src="' . $row['image_url'] . '" class="card-img-top" alt="immagine notizia">';
            }
            echo '    <div class="card-body">';
            echo '      <h5 class="card-title">' . htmlspecialchars($row['title']) . '</h5>';
            echo '      <p class="card-text">' . substr(strip_tags($row['content']), 0, 120) . '...</p>';
            echo '      <a href="news_detail.php?id=' . $row['id'] . '" class="btn btn-primary">Leggi</a>';
            echo '    </div>';
            echo '  </div>';
            echo '</div>';
        }
        ?>
      </div>
    </div>
  </div>

  <!-- Sezione Prossimo GP -->
  <div class="row">
    <div class="col-md-6">
      <h2>Prossimo Gran Premio</h2>
      <p><strong>Gran Premio di Imola – 19 Maggio 2025</strong></p>
      <div class="alert alert-info" id="countdown">Caricamento countdown...</div>
    </div>
    <div class="col-md-6">
      <h2>🏆 Classifica Piloti</h2>
      <ul class="list-group">
        <?php
        $piloti = mysqli_query($conn, "SELECT name, points FROM drivers ORDER BY points DESC LIMIT 5");
        while ($pilota = mysqli_fetch_assoc($piloti)) {
            echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
            echo htmlspecialchars($pilota['name']);
            echo '<span class="badge bg-danger rounded-pill">' . $pilota['points'] . ' pt</span>';
            echo '</li>';
        }
        ?>
      </ul>
    </div>
  </div>

</div>

<!-- Footer -->
<footer class="bg-dark text-white text-center py-4 mt-5">
    <p class="mb-0">&copy; 2025 F1 FanHub - Tutti i diritti riservati</p>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Script Countdown -->
<script>
    const targetDate = new Date("2025-05-19T15:00:00").getTime();
    const countdown = setInterval(() => {
        const now = new Date().getTime();
        const distance = targetDate - now;

        if (distance < 0) {
            document.getElementById("countdown").innerHTML = "Gara in corso o conclusa!";
            clearInterval(countdown);
        } else {
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            document.getElementById("countdown").innerHTML = `${days} giorni, ${hours} ore, ${minutes} minuti`;
        }
    }, 1000);
</script>

</body>
</html>
