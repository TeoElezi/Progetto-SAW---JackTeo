<?php
require_once __DIR__ . '/../config/config.php';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>FanHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <!-- Icona F1 -->
    <link rel="icon" href="../assets/images/fanhub.jpeg" type="image/x-icon">
</head>
<body class="bg-light text-dark">

<!-- Header principale -->
<header class="bg-black shadow-sm">
    <div class="header-container container py-3">
        <nav class="navbar navbar-expand-lg navbar-dark w-100">
            <!-- Logo e titolo -->
            <a class="navbar-brand d-flex align-items-center" href="../pages/index.php">
                <img src="../assets/images/f1_logo_white.png" alt="F1 FanHub" width="150" class="me-2">
                <span class="fw-bold">FanHub - StartSaw</span>
            </a>

            <!-- Pulsante Hamburger -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
                aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Contenuto navbar -->
            <div class="collapse navbar-collapse justify-content-between align-items-center" id="navbarContent">
                <!-- Link -->
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 d-flex gap-lg-3 text-center">
                    <li class="nav-item"><a class="nav-link" href="../pages/news.php">Notizie</a></li>
                    <li class="nav-item"><a class="nav-link" href="../pages/races.php">Gare</a></li>
                    <li class="nav-item"><a class="nav-link" href="../pages/drivers.php">Piloti</a></li>
                    <li class="nav-item"><a class="nav-link" href="../pages/teams.php">Team</a></li>
                    <li class="nav-item"><a class="nav-link" href="../pages/standings.php">Classifiche</a></li>
                </ul>

                <!-- Search -->
                <form class="d-flex ms-lg-auto mt-3 mt-lg-0" role="search" method="GET" action="../pages/search.php">
                    <input class="form-control me-2" type="search" name="q" placeholder="Cerca..." aria-label="Cerca" required>
                    <button class="btn btn-outline-light" type="submit">Cerca</button>
                </form>
            </div>
        </nav>
    </div>


    <!-- Barra rossa dinamica tipo Ticker -->
    <div class="bg-danger">
        <div class="container py-1 d-flex justify-content-center align-items-center">
            <?php
            // Esegui la query per ottenere il prossimo GP
            require_once __DIR__ .'/../config/config.php';
            $query = "SELECT name, date, location FROM races WHERE date > NOW() ORDER BY date ASC LIMIT 1";
            $result = mysqli_query($conn, $query);
            while ($row = mysqli_fetch_assoc($result)) {
            ?>
                <span class="small fw-bold">LIVE UPDATES: Prossimo GP ➔ <?php echo $row['name'] . ' - ' . date('d M Y', strtotime($row['date'])); ?></span>
            <?php
            }
            ?>
        </div>
    </div>
</header>

<!-- Contenitore principale -->
<main class="container py-5">
    <div class="container-fluid bg-light rounded py-4">

