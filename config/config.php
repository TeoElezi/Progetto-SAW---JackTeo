<?php
function getBasePath() {
    $dirName = basename(dirname(__DIR__));
    return '/' . rawurlencode($dirName) . '/';
}
$paypalClientId = 'Ad0E-Fmama54pAeeymJwsOewpCUYTaAL05bjbd_s_Xn8-Tomq6J5xTbGXS27HWhdmtfasDSKgzCSKxF_';
$paypalCurrency = 'EUR';
$paypalEnvironment = 'sandbox';
$paypalClientSecret = getenv('PAYPAL_CLIENT_SECRET') ?: '';
$host = 'localhost';
$user = 'root';
$password = ''; // oppure la tua password se l'hai impostata
$db = 'f1_fanhub';

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Imposta charset per evitare problemi con caratteri speciali
mysqli_set_charset($conn, "utf8mb4");
?>
