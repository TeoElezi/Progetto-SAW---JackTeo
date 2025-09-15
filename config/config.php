<?php
function getBasePath() {
    $dirName = basename(dirname(__DIR__));
    return '/' . rawurlencode($dirName) . '/';
}
$paypalClientId = getenv('PAYPAL_CLIENT_ID') ?: 'Ad0E-Fmama54pAeeymJwsOewpCUYTaAL05bjbd_s_Xn8-Tomq6J5xTbGXS27HWhdmtfasDSKgzCSKxF_';
$paypalCurrency = getenv('PAYPAL_CURRENCY') ?: 'EUR';
$paypalEnvironment = getenv('PAYPAL_ENV') ?: 'sandbox';
$paypalClientSecret = getenv('PAYPAL_CLIENT_SECRET') ?: 'EHf6I_EQSx3B3bYjfBIq7Wy_jLXZoywBgej64-RefDtqDhLmkqB2Gyl5JfveSI9-e-kGy3Sw2ufmjqbM';
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
