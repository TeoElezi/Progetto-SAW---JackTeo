<?php
require_once __DIR__ . '/../config/config.php';

// Richiesta cURL all'API
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://f1-motorsport-data.p.rapidapi.com/news",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => [
        "x-rapidapi-host: f1-motorsport-data.p.rapidapi.com",
        "x-rapidapi-key: " . "c84c6aa628msh3c8f9ef0bdc9e70p19aed0jsn7a05ef46cabb"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

// Gestione errori cURL
if ($err) {
    echo "Errore cURL: " . $err;
    exit;
}

// Decodifica la risposta
$data = json_decode($response, true);



// Verifica che ci siano notizie nell'array
if (isset($data[0])) {
    foreach ($data as $newsItem) {
        // Estrai i dati dalla risposta
        $dataSourceId = $conn->real_escape_string($newsItem['dataSourceIdentifier']);
        $headline = $conn->real_escape_string($newsItem['headline']);
        $description = $conn->real_escape_string($newsItem['description']);
        $link = $conn->real_escape_string($newsItem['link']);
        $postedAt = date('Y-m-d H:i:s'); // Usa la data attuale

        // Gestione immagini
        $image_url = '';
        $image_caption = '';
        $image_alt = '';
        $image_height = 0;
        $image_width = 0;

        // Verifica se ci sono immagini
        if (isset($newsItem['images'][0])) {
            $image = $newsItem['images'][0];
            $image_url = $conn->real_escape_string($image['url']);
            $image_caption = $conn->real_escape_string($image['caption']);
            $image_alt = $conn->real_escape_string($image['alt']);
            $image_height = (int) $image['height'];
            $image_width = (int) $image['width'];
        }

        // Controllo per evitare duplicati
        $checkQuery = "SELECT id FROM news WHERE data_source_id = '$dataSourceId'";
        $checkResult = $conn->query($checkQuery);

        if ($checkResult->num_rows == 0) {
            // Se non esiste, inserisco
            $insertQuery = "
                INSERT INTO news (title, content, link, image_url, image_caption, image_alt, image_height, image_width, posted_at, data_source_id)
                VALUES ('$headline', '$description', '$link', '$image_url', '$image_caption', '$image_alt', $image_height, $image_width, '$postedAt', '$dataSourceId')
            ";

            if ($conn->query($insertQuery) === TRUE) {
                echo "Notizia inserita: " . $headline . "<br>";
            } else {
                echo "Errore inserimento: " . $conn->error . "<br>";
            }
        } else {
            echo "Notizia già presente: " . $headline . "<br>";
        }
    }
} else {
    echo "Nessuna notizia disponibile.";
}

$conn->close();