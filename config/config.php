<?php
$host = 'localhost';
$user = 'root';
$password = ''; // oppure la tua password se l'hai impostata
$db = 'f1_fanhub';

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Imposta charset per evitare problemi con caratteri speciali
mysqli_set_charset($conn, "utf8");
?>
