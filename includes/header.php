<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/auto_fetch_news.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>FanHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo getBasePath(); ?>assets/css/style.css">

    <!-- Icona F1 -->
    <link rel="icon" href="<?php echo getBasePath(); ?>assets/images/fanhub.jpeg" type="image/x-icon">
</head>
<body class="bg-light text-dark">

<!-- Header principale -->
<header class="bg-dark shadow-sm">
    <div class="header-container container py-3">
        <nav class="navbar navbar-expand-lg navbar-dark w-100">
            <!-- Logo e titolo -->
            <a class="navbar-brand" href="<?php echo getBasePath(); ?>index.php">
                <img src="<?php echo getBasePath(); ?>assets/images/f1_logo_white.png" alt="F1 FanHub" width="150" class="me-2">
                <span class="fw-bold">FanHub - StartSaw</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
            aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            

            <!-- Contenuto navbar -->
            <div class="collapse navbar-collapse" id="navbarContent">                <!-- Link -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="<?php echo getBasePath(); ?>pages/404.php">Notizie</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo getBasePath(); ?>pages/races.php">Gare</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo getBasePath(); ?>pages/drivers.php">Piloti</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo getBasePath(); ?>pages/404.php">Team</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo getBasePath(); ?>pages/404.php">Classifiche</a></li>
                </ul>

                <!-- Search -->
                <form class="d-flex ms-lg-auto mt-3 mt-lg-0" role="search" method="GET" action="<?php echo getBasePath(); ?>pages/search.php">
                    <input class="form-control me-2" type="search" name="q" placeholder="Cerca..." aria-label="Cerca" required>
                    <button class="btn btn-outline-light" type="submit">Cerca</button>
                </form>
                <!-- Accesso / Profilo -->
                <?php if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true): ?>
                    <a href="<?php echo getBasePath(); ?>user/login.php" class="text-decoration-none text-center d-block ms-3">
                        <img src="<?php echo getBasePath(); ?>assets/images/user.png" alt="Accedi" class="rounded-circle" style="width: 70px; height: 70px;">
                        <small class="d-block text-light">Accedi</small>
                    </a>
                <?php else: ?>
                    <div class="dropdown ms-3">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($_SESSION['nome']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?php echo getBasePath(); ?>user/profilePage.php"><i class="fas fa-user-circle me-2"></i>Profilo</a></li>
                            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                                <li><a class="dropdown-item" href="<?php echo getBasePath(); ?>admin/index.php"><i class="fas fa-cog me-2"></i>Area Amministrativa</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo getBasePath(); ?>user/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                <?php endif; ?>

            </div>
        </nav>
    </div>


    <!-- Barra rossa dinamica tipo Ticker -->
    <div class="bg-danger">
        <div class="container py-1 d-flex justify-content-center align-items-center">
            <?php
            // Esegui la query per ottenere il prossimo GP
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
<main class="container py-5 flex-fill d-flex align-items-center justify-content-center">

    <div class="container-fluid bg-light rounded py-4">
        

