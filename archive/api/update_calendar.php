<?php
require_once __DIR__ . '/../config/config.php';

// Chiamata all'API per ottenere i dati delle gare
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://f1-motorsport-data.p.rapidapi.com/schedule?year=2025",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => [
        "x-rapidapi-host: f1-motorsport-data.p.rapidapi.com",
        "x-rapidapi-key: c84c6aa628msh3c8f9ef0bdc9e70p19aed0jsn7a05ef46cabb"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    echo "cURL Error #:" . $err;
} else {
    // Decodifica la risposta JSON
    $data = json_decode($response, true);

    // Itera sulle date e inserisci i dati nel database
    foreach ($data as $dateKey => $races) {
        foreach ($races as $race) {
            $name = $race['gPrx'] ?? null;
            $date = isset($race['startDate']) ? date('Y-m-d', strtotime($race['startDate'])) : null;
            $location = $race['crct'] ?? null;
            $circuit_img = $race['evLink'] ?? null;

            // winner_id non disponibile: settiamo NULL
            $winner_id = null;

            $stmt = $conn->prepare("INSERT INTO races (name, date, location, circuit_img, winner_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $name, $date, $location, $circuit_img, $winner_id);
            if ($stmt->execute()) {
                echo "Record inserito con successo per la gara: $name\n";
            } else {
                echo "Errore nell'inserimento della gara: $name - " . $conn->error . "\n";
            }
            $stmt->close();
        }
    }
}

// Chiudi la connessione al database
$conn->close();
