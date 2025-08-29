<?php
require_once __DIR__ . '/../config/config.php';

// Imposta timeout ragionevole per evitare processi appesi
@set_time_limit(60);

// Chiave API
$apiKey = "c84c6aa628msh3c8f9ef0bdc9e70p19aed0jsn7a05ef46cabb";

// Richiesta cURL all'API
$curl = curl_init();
curl_setopt_array($curl, [
	CURLOPT_URL => 'https://f1-motorsport-data.p.rapidapi.com/news',
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => '',
	CURLOPT_MAXREDIRS => 5,
	CURLOPT_TIMEOUT => 25,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => 'GET',
	CURLOPT_HTTPHEADER => [
		'x-rapidapi-host: f1-motorsport-data.p.rapidapi.com',
		'x-rapidapi-key: ' . $apiKey,
		'Accept: application/json',
		'User-Agent: FanHub/1.0'
	],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($err) {
	http_response_code(502);
	echo 'Errore cURL: ' . $err;
	exit;
}

if ($httpCode < 200 || $httpCode >= 300) {
	http_response_code($httpCode ?: 502);
	echo 'Errore API: HTTP ' . $httpCode;
	exit;
}

// Decodifica la risposta
$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
	http_response_code(502);
	echo 'JSON non valido dalla API';
	exit;
}

if (!is_array($data) || !isset($data[0])) {
	echo 'Nessuna notizia disponibile.';
	$conn->close();
	exit;
}

// Prepara statement riutilizzabili
$selectStmt = $conn->prepare('SELECT id FROM news WHERE data_source_id = ? LIMIT 1');
$insertStmt = $conn->prepare('INSERT INTO news (title, content, link, image_url, image_caption, image_alt, image_height, image_width, posted_at, data_source_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

if (!$selectStmt || !$insertStmt) {
	http_response_code(500);
	echo 'Errore preparazione statement DB';
	$conn->close();
	exit;
}

$now = date('Y-m-d H:i:s');
$inserted = 0;
$skipped = 0;

// Transazione per performance
$conn->begin_transaction();

foreach ($data as $newsItem) {
	$dataSourceId = (string)($newsItem['dataSourceIdentifier'] ?? '');
	$headline = (string)($newsItem['headline'] ?? '');
	$description = (string)($newsItem['description'] ?? '');
	$link = (string)($newsItem['link'] ?? '');

	if ($dataSourceId === '' || $headline === '') {
		$skipped++;
		continue;
	}

	// Immagini
	$imageUrl = '';
	$imageCaption = '';
	$imageAlt = '';
	$imageHeight = 0;
	$imageWidth = 0;
	if (isset($newsItem['images'][0])) {
		$image = $newsItem['images'][0];
		$imageUrl = (string)($image['url'] ?? '');
		$imageCaption = (string)($image['caption'] ?? '');
		$imageAlt = (string)($image['alt'] ?? '');
		$imageHeight = (int)($image['height'] ?? 0);
		$imageWidth = (int)($image['width'] ?? 0);
	}

	// Evita duplicati
	$selectStmt->bind_param('s', $dataSourceId);
	$selectStmt->execute();
	$res = $selectStmt->get_result();
	if ($res && $res->num_rows > 0) {
		$skipped++;
		continue;
	}

	$insertStmt->bind_param(
		'sssssssiss',
		$headline,
		$description,
		$link,
		$imageUrl,
		$imageCaption,
		$imageAlt,
		$imageHeight,
		$imageWidth,
		$now,
		$dataSourceId
	);
	if ($insertStmt->execute()) {
		$inserted++;
	} else {
		// Non interrompere l'intero batch su singolo errore
	}
}

$conn->commit();

echo 'Ci sono: ' . $inserted . ' nuove news inserite ';

$selectStmt->close();
$insertStmt->close();
$conn->close();