<?php
    session_start();  // 1. Inizia la sessione
    session_unset();    // 2. Svuota tutte le variabili di sessione
    session_destroy();  // 3. Distrugge la sessione
    header("Location: ../pages/index.php");  // 4. Dopo il logout, rimanda alla home
    exit();
?>
