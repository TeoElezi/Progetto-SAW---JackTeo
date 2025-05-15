<?php
require_once '../config/config.php';
?>
<!-- Navbar -->
<?php include '../includes/header.php' ?>
  
<!-- Contenuto principale -->
<div class="container my-5">

  <!-- Sezione Introduzione obbligatoria (Presentazione di startSAW) -->
  <div class="row mb-5">
  <div class="col text-center">
    <h1 class="display-4">Benvenuti su F1 FanHub</h1>
    <p class="lead mt-3">
    F1 FanHub è la nuova startup dedicata agli appassionati di Formula 1. Raccogliamo le ultime notizie, offriamo aggiornamenti in tempo reale su gare e classifiche, e ti portiamo dentro il mondo della F1 come mai prima d’ora.
    </p>
    <p>
    Il nostro obiettivo è creare un punto di riferimento per i fan, con contenuti aggiornati, curiosità, statistiche e una community in continua crescita.  
    </p>
    <button class="btn btn-outline-dark" href="404.php">Scopri chi siamo</button>
  </div>
  </div>

<!-- Sezione Notizie -->
<div class="row mb-5">
  <div class="col">
  <h2 class="mb-4">Ultime Notizie</h2>
  <div class="row">
    <?php
    // Execute the query to get the latest 5 news articles
    $result = mysqli_query($conn, "SELECT * FROM news ORDER BY posted_at DESC LIMIT 5");
    $main_article = true;
    $article_count = 0;

    // Loop through the results and display each article
    while ($row = mysqli_fetch_assoc($result)) {
      if ($main_article) {
        // Display the main article in a larger card
        echo '<div class="col-lg-6">';
        echo '  <div class="card h-100">';
        if ($row['image_url']) {
          echo '    <img src="' . htmlspecialchars($row['image_url']) . '" class="card-img-top" alt="' . htmlspecialchars($row['image_alt']) . '">';
        }
        echo '    <div class="card-body">';
        echo '      <p class="text-red-500 text-sm">NEWS</p>';
        echo '      <h2 class="card-title">' . htmlspecialchars($row['title']) . '</h2>';
        echo '      <p class="card-text">' . substr(strip_tags($row['content']), 0, 120) . '...</p>';

        if (!empty($row['link'])) {
          echo '      <a href="' . htmlspecialchars($row['link']) . '" class="btn btn-primary" target="_blank">Leggi su ESPN</a>';
        } else {
          echo '      <a href="news_detail.php?id=' . $row['id'] . '" class="btn btn-primary">Leggi</a>';
        }

        echo '    </div>';
        echo '  </div>';
        echo '</div>';
        $main_article = false;
      } else {
        // Start a new row for additional articles if it's the first additional article
        if ($article_count == 0) {
          echo '<div class="col-lg-6">';
          echo '  <div class="row row-cols-1 row-cols-md-2 g-4">';
        }

        // Display additional articles in smaller cards
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
          echo '      <a href="news_detail.php?id=' . $row['id'] . '" class="btn btn-primary">Leggi</a>';
        }

        echo '    </div>';
        echo '  </div>';
        echo '</div>';

        // Increment the article count
        $article_count++;

        // Close the row and column if it's the last additional article
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




  <!-- Sezione Prossimo GP -->
  <div class="race-box text-white bg-dark p-3" style="border-radius: 15px; margin-left: auto; margin-right: auto; margin-bottom: 40px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); position: relative;">
  <?php
  $query = "SELECT name, date, location, circuit_img FROM races WHERE date > NOW() ORDER BY date ASC LIMIT 1";
  $result = mysqli_query($conn, $query);
  while ($row = mysqli_fetch_assoc($result)) {
  ?>
    
  <h2 class="mb-4">Prossimo gran premio</h2>
    <!-- Data in alto a destra -->
    <div style="position: absolute; top: 10px; right: 10px; background-color: #444; padding: 5px 15px; border-radius: 20px; font-weight: bold; font-size: 1.2rem;">
      <?php echo date('d M', strtotime($row['date'])); ?>
    </div>

    <!-- Immagine del circuito centrata -->
    <div class="text-center">
      <img src="<?php echo $row['circuit_img']; ?>" alt="Circuit" class="img-fluid" style="max-height: 300px; object-fit: contain;">
    </div>

    <!-- Info GP -->
    <div class="p-2 px-0">
      <p class="mb-2" style="font-weight: bold; font-size: 2.6rem;">
        <span style="color: #ccc;"><?php echo $row['name']; ?></span>
      </p>
      <p class="mb-1" style="color: red; font-weight: bold; font-size: 1.6rem">
        <?php echo $row['location']; ?>
      </p>
      <div style="font-size: 1.2rem" id="countdown">Caricamento countdown...</div>
    </div>

  <?php } ?>
</div>





  <div class="row">
    <!-- Classifica Piloti -->
    <div class="col-md-6 mb-4">
    <h2>🏆 Classifica Piloti</h2>
    <ul class="list-group">
      <?php
      $posizionePiloti = 1;
      $piloti = mysqli_query($conn, "SELECT name, points FROM drivers ORDER BY points DESC ");
      while ($pilota = mysqli_fetch_assoc($piloti)) {
        echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
        echo $posizionePiloti . '. ' . htmlspecialchars($pilota['name']);
        echo '<span class="badge bg-danger rounded-pill">' . $pilota['points'] . ' pt</span>';
        echo '</li>';
        $posizionePiloti++;
      }
      ?>
    </ul>
    </div>

    <!-- Classifica Costruttori -->
    <div class="col-md-6 mb-4">
    <h2>🏎️ Classifica Costruttori</h2>
    <ul class="list-group">
      <?php
      $posizioneTeam = 1;
      $teams = mysqli_query($conn, "SELECT name, points FROM teams ORDER BY points DESC ");
      while ($team = mysqli_fetch_assoc($teams)) {
        echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
        echo $posizioneTeam . '. ' . htmlspecialchars($team['name']);
        echo '<span class="badge bg-primary rounded-pill">' . $team['points'] . ' pt</span>';
        echo '</li>';
        $posizioneTeam++;
      }
      ?>
    </ul>
    </div>
  </div>
  </div>
</div>
<!-- Footer -->
<?php include '../includes/footer.php' ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<!-- Script Countdown -->
<?php
  $raceDateQuery = "SELECT date FROM races WHERE date > NOW() ORDER BY date ASC LIMIT 1";
  $raceDateResult = mysqli_query($conn, $raceDateQuery);
  $raceDateRow = mysqli_fetch_assoc($raceDateResult);
  $raceDate = $raceDateRow ? $raceDateRow['date'] : null;
?>
<!-- Pass the race date to JavaScript -->
<script>
  const raceDate = "<?php echo $raceDate ? date('Y-m-d\TH:i:s', strtotime($raceDate)) : '1970-01-01T00:00:00'; ?>";
</script>

<!-- Include the countdown.js file -->
<script src="../assets/js/countdown.js"></script>