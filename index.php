<?php
require_once 'config/config.php';
?>

<?php include 'includes/header.php' ?>

<div class="container my-5">

  <div class="row mb-5">
  <div class="col text-center">
    <h1 class="display-4">Benvenuti su F1 FanHub</h1>
    <p class="lead mt-3">
    F1 FanHub è la nuova startup dedicata agli appassionati di Formula 1. Raccogliamo le ultime notizie, offriamo aggiornamenti in tempo reale su gare e classifiche, e ti portiamo dentro il mondo della F1 come mai prima d’ora.
    </p>
    <p>
    Il nostro obiettivo è creare un punto di riferimento per i fan, con contenuti aggiornati, curiosità, statistiche e una community in continua crescita.
    </p>
    <a class="btn btn-outline-dark" href="<?php echo getBasePath(); ?>pages/404.php">Scopri chi siamo</a>
  </div>
  </div>

  <div class="row mb-5">
    <div class="col-md-10 mx-auto">
      <div class="p-4 p-md-5 bg-white border rounded-3 shadow-sm d-flex flex-column flex-md-row align-items-center gap-3">
        <div class="flex-grow-1">
          <h2 class="h3 mb-2">Supporta F1 FanHub</h2>
          <p class="mb-0 text-muted">Aiutaci a sviluppare nuove funzionalità e far crescere la community. Anche una piccola donazione può fare la differenza.</p>
        </div>
        <div>
          <a href="<?php echo getBasePath(); ?>payments/donazioni.php" class="btn btn-danger btn-lg">Dona ora</a>
        </div>
      </div>
    </div>
  </div>


<div class="row mb-5">
  <div class="col">
  <h2 class="mb-4">Ultime Notizie</h2>
  <div class="row">
    <?php

    $result = null;
    if ($stmt = $conn->prepare("SELECT id, title, content, link, image_url, image_alt, posted_at FROM news ORDER BY posted_at DESC LIMIT 5")) {
      $stmt->execute();
      $result = $stmt->get_result();
      $stmt->close();
    }
    $main_article = true;
    $article_count = 0;

    while ($result && ($row = mysqli_fetch_assoc($result))) {
      if ($main_article) {

        echo '<div class="col-lg-6">';
        echo '  <div class="card h-100">';
        if ($row['image_url']) {
          echo '    <img src="' . htmlspecialchars($row['image_url']) . '" class="card-img-top" alt="' . htmlspecialchars($row['image_alt']) . '">';
        }
        echo '    <div class="card-body">';
        echo '      <p class="text-red-500 text-sm">NEWS</p>';
        echo '      <h2 class="card-title">' . htmlspecialchars($row['title']) . '</h2>';
        $snippet = mb_substr(strip_tags($row['content']), 0, 120, 'UTF-8');
        echo '      <p class="card-text">' . htmlspecialchars($snippet) . '...</p>';

        if (!empty($row['link'])) {
          echo '      <a href="' . htmlspecialchars($row['link']) . '" class="btn btn-primary" target="_blank">Leggi su ESPN</a>';
        } else {
          echo '      <a href="' . getBasePath() . 'pages/news_detail.php?id=' . (int)$row['id'] . '" class="btn btn-primary">Leggi</a>';
        }

        echo '    </div>';
        echo '  </div>';
        echo '</div>';
        $main_article = false;
      } else {

        if ($article_count == 0) {
          echo '<div class="col-lg-6">';
          echo '  <div class="row row-cols-1 row-cols-md-2 g-4">';
        }

        echo '<div class="col">';
        echo '  <div class="card h-100">';
        if ($row['image_url']) {
          echo '    <img src="' . htmlspecialchars($row['image_url']) . '" class="card-img-top" alt="' . htmlspecialchars($row['image_alt']) . '">';
        }
        echo '    <div class="card-body">';
        echo '      <p class="text-red-500 text-sm">FEATURE</p>';
        echo '      <h5 class="card-title">' . htmlspecialchars($row['title']) . '</h5>';

        if (!empty($row['link'])) {
          echo '      <a href="' . htmlspecialchars($row['link']) . '" class="btn btn-primary" target="_blank">Leggi su ESPN</a>';
        } else {
          echo '      <a href="' . getBasePath() . 'pages/news_detail.php?id=' . (int)$row['id'] . '" class="btn btn-primary">Leggi</a>';
        }

        echo '    </div>';
        echo '  </div>';
        echo '</div>';

        $article_count++;

        if ($article_count == 4) {
          echo '  </div>';
          echo '</div>';
        }
      }
    }
    ?>
  </div>
  </div>
</div>

  <div class="race-box text-white bg-dark p-3" style="border-radius: 15px; margin-left: auto; margin-right: auto; margin-bottom: 40px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); position: relative;">
  <?php
  $row = null;
  if ($stmt = $conn->prepare("SELECT name, date, location, circuit_img FROM races WHERE date > NOW() ORDER BY date ASC LIMIT 1")) {
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
  }
  if ($row) {
  ?>

  <h2 class="mb-4">Prossimo gran premio</h2>

    <div style="position: absolute; top: 10px; right: 10px; background-color: #444; padding: 5px 15px; border-radius: 20px; font-weight: bold; font-size: 1.2rem;">
      <?php echo date('d M', strtotime($row['date'])); ?>
    </div>

    <div class="text-center">
      <img src="<?php echo htmlspecialchars($row['circuit_img']); ?>" alt="Circuit" class="img-fluid" style="max-height: 300px; object-fit: contain;">
    </div>

    <div class="p-2 px-0">
      <p class="mb-2" style="font-weight: bold; font-size: 2.6rem;">
        <span style="color: #ccc;"><?php echo htmlspecialchars($row['name']); ?></span>
      </p>
      <p class="mb-1" style="color: red; font-weight: bold; font-size: 1.6rem">
        <?php echo htmlspecialchars($row['location']); ?>
      </p>
      <div style="font-size: 1.2rem" id="countdown">Caricamento countdown...</div>
    </div>

  <?php } ?>
</div>

  <div class="row">

    <div class="col-md-6 mb-4">
    <h2>🏆 Classifica Piloti</h2>
    <ul class="list-group">
      <?php
      $posizionePiloti = 1;
      $piloti = null;
      if ($stmt = $conn->prepare("SELECT name, points FROM drivers ORDER BY points DESC")) {
        $stmt->execute();
        $piloti = $stmt->get_result();
        $stmt->close();
      }
      while ($piloti && ($pilota = mysqli_fetch_assoc($piloti))) {
        echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
        echo $posizionePiloti . '. ' . htmlspecialchars($pilota['name']);
        echo '<span class="badge bg-danger rounded-pill">' . (int)$pilota['points'] . ' pt</span>';
        echo '</li>';
        $posizionePiloti++;
      }
      ?>
    </ul>
    </div>

    <div class="col-md-6 mb-4">
    <h2>🏎️ Classifica Costruttori</h2>
    <ul class="list-group">
      <?php
      $posizioneTeam = 1;
      $teams = null;
      if ($stmt = $conn->prepare("SELECT name, points FROM teams ORDER BY points DESC")) {
        $stmt->execute();
        $teams = $stmt->get_result();
        $stmt->close();
      }
      while ($teams && ($team = mysqli_fetch_assoc($teams))) {
        echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
        echo $posizioneTeam . '. ' . htmlspecialchars($team['name']);
        echo '<span class="badge bg-primary rounded-pill">' . (int)$team['points'] . ' pt</span>';
        echo '</li>';
        $posizioneTeam++;
      }
      ?>
    </ul>
    </div>
  </div>
  </div>
</div>

<?php include 'includes/footer.php' ?>

<?php
  $raceDateRow = null;
  if ($stmt = $conn->prepare("SELECT date FROM races WHERE date > NOW() ORDER BY date ASC LIMIT 1")) {
    $stmt->execute();
    $res = $stmt->get_result();
    $raceDateRow = $res ? $res->fetch_assoc() : null;
    $stmt->close();
  }
  $raceDate = $raceDateRow ? $raceDateRow['date'] : null;
?>

<script>
  const raceDate = "<?php echo $raceDate ? date('Y-m-d\TH:i:s', strtotime($raceDate)) : '1970-01-01T00:00:00'; ?>";
  </script>

<script src="<?php echo getBasePath(); ?>assets/js/countdown.js"></script>

