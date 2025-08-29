<?php
// Questo file implementa un semplice pseudo-cron basato sul traffico web.
// Se è passato più di $intervalSeconds dall'ultimo fetch, invia una richiesta
// non bloccante a api/fetch_news.php.

if (!isset($conn)) {
    require_once __DIR__ . '/../config/config.php';
}

$intervalSeconds = 3600; // 1 ora

// Leggi ultimo fetch
$lastFetchTs = 0;
$stmt = $conn->prepare("SELECT setting_value FROM app_settings WHERE setting_key = 'news_last_fetch'");
if ($stmt && $stmt->execute()) {
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $lastFetchTs = (int)($row['setting_value'] ?? 0);
    }
    $stmt->close();
}

$now = time();
if ($now - $lastFetchTs >= $intervalSeconds) {
    // Debounce immediatamente per evitare storm da richieste concorrenti
    $newVal = (string)$now;
    $stmt = $conn->prepare("INSERT INTO app_settings (setting_key, setting_value) VALUES ('news_last_fetch', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    if ($stmt) {
        $stmt->bind_param('s', $newVal);
        $stmt->execute();
        $stmt->close();
    }

    // Costruisci richiesta HTTP non bloccante
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == 443;
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $port = $isHttps ? 443 : 80;
    $schemePrefix = $isHttps ? 'ssl://' : '';
    $path = (function() {
        if (!function_exists('getBasePath')) {
            require_once __DIR__ . '/../config/config.php';
        }
        return getBasePath() . 'api/fetch_news.php';
    })();

    $errno = 0; $errstr = '';
    $fp = @fsockopen($schemePrefix . $host, $port, $errno, $errstr, 1);
    if ($fp) {
        $out = "GET $path HTTP/1.1\r\n" .
               "Host: $host\r\n" .
               "Connection: Close\r\n\r\n";
        // Imposta non bloccante e invia
        stream_set_blocking($fp, false);
        @fwrite($fp, $out);
        @fclose($fp);
    }
}
?>


