<?php
function getBasePath() {
    $dirName = basename(dirname(__DIR__));
    return '/' . rawurlencode($dirName) . '/';
}

function appIsProduction() {
    $env = getenv('APP_ENV') ?: 'local';
    return strtolower($env) === 'production';
}

if (appIsProduction()) {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
} else {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set(getenv('APP_TZ') ?: 'Europe/Rome');
}
ini_set('default_charset', 'UTF-8');

$paypalClientId = getenv('PAYPAL_CLIENT_ID') ?: '';
$paypalCurrency = getenv('PAYPAL_CURRENCY') ?: 'EUR';
$paypalEnvironment = getenv('PAYPAL_ENV') ?: 'sandbox';
$paypalClientSecret = getenv('PAYPAL_CLIENT_SECRET') ?: '';

if (!appIsProduction()) {
    if ($paypalClientId === '' && $paypalEnvironment !== 'live') {
        $paypalClientId = 'Ad0E-Fmama54pAeeymJwsOewpCUYTaAL05bjbd_s_Xn8-Tomq6J5xTbGXS27HWhdmtfasDSKgzCSKxF_';
    }
    if ($paypalClientSecret === '' && $paypalEnvironment !== 'live') {
        $paypalClientSecret = 'EHf6I_EQSx3B3bYjfBIq7Wy_jLXZoywBgej64-RefDtqDhLmkqB2Gyl5JfveSI9-e-kGy3Sw2ufmjqbM';
    }
}

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$db = getenv('DB_DATABASE') ?: 'f1_fanhub';

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($host, $user, $password, $db);

if ($conn->connect_errno) {
    error_log('DB connection failed (' . $conn->connect_errno . '): ' . $conn->connect_error);
    http_response_code(500);
    exit('Si è verificato un errore di sistema. Riprova più tardi.');
}

if (!mysqli_set_charset($conn, 'utf8mb4')) {
    error_log('Unable to set utf8mb4 charset: ' . $conn->error);
}
?>
