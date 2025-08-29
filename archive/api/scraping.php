<?php
    require_once __DIR__ . '/../config/config.php'; // Connessione al DB
    //SEZIONE DEDICATA ALL'AGGIORNAMENTO DEI PUNTI DEI PILOTI
    $html = file_get_contents('https://racingnews365.com/formula-1-drivers');
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    // Trova tutte le card dei piloti
    $cards = $xpath->query('//div[contains(@class, "card") and contains(@class, "card--driver")]');
    foreach ($cards as $card) {
        $nomeNode = $xpath->query('.//span[contains(@class, "card__title")]', $card);
        $puntiNode = $xpath->query('.//div[contains(@class, "card__image__stat__main")]/span', $card);
        $imageurlNode = $xpath->query('.//img[contains(@class, "card__driver-image")]', $card);
        $imageurl = $imageurlNode->length > 0 ? trim($imageurlNode[0]->getAttribute('src')) : "N/D";
        $nome = $nomeNode->length > 0 ? trim($nomeNode[0]->nodeValue) : "N/D";
        $punti = $puntiNode->length > 0 ? trim($puntiNode[0]->nodeValue) : "N/D";
        echo "Nome: $nome<br>Punti: $punti<br>Immagine: <a href='$imageurl' target='_blank'>link</a><br><br>";
        // Verifica se il pilota esiste già nel database
        $stmt_check = $conn->prepare("SELECT id FROM drivers WHERE name = ?");
        $stmt_check->bind_param("s", $nome);
        $stmt_check->execute();
        $stmt_check->store_result();
        // Se il pilota esiste, aggiorna i punti
        if ($stmt_check->num_rows > 0) {
            // Aggiorna i punti
            $stmt_update = $conn->prepare("UPDATE drivers SET points = ?, image_url = ? WHERE name = ?");
            $puntiInt = (int)$punti;
            $stmt_update->bind_param("iss", $puntiInt, $imageurl, $nome);
            if ($stmt_update->execute()) {
                echo "Punti aggiornati per " . $nome . "<br>";
            } else {
                echo "Errore nell'aggiornamento dei punti: " . $stmt_update->error . "<br>";
            }
            $stmt_update->close();
        } else {
            // Se il pilota non esiste, inserisci i dati
            $stmt_insert = $conn->prepare("INSERT INTO drivers (name, points, image_url) VALUES (?, ?, ?)");
            $puntiInt = (int)$punti;
            $stmt_insert->bind_param("sis", $nome, $puntiInt, $imageurl);
            if ($stmt_insert->execute()) {
                echo "Dati inseriti per " . $nome . "<br>";
            } else {
                echo "Errore nell'inserimento dei dati: " . $stmt_insert->error . "<br>";
            }
            $stmt_insert->close();
        }
        // Chiudi la dichiarazione
        $stmt_check->close();
    }
    //SEZIONE DEDICATA ALL'AGGIORNAMENTO DEI PUNTI DEI TEAM
    $html = file_get_contents('https://racingnews365.com/formula-1-teams');
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    // Trova tutte le card dei team
    $cards = $xpath->query('//div[contains(@class, "card") and contains(@class, "card--race")]');
    foreach ($cards as $card) {
        $nomeNode = $xpath->query('.//span[contains(@class, "card__title")]', $card);
        $puntiNode = $xpath->query('.//div[contains(@class, "card__image__stat__main")]/span', $card);
        $nome = $nomeNode->length > 0 ? trim($nomeNode[0]->nodeValue) : "N/D";
        $punti = $puntiNode->length > 0 ? trim($puntiNode[0]->nodeValue) : "N/D";
        echo"". $nome ." ". $punti ." ".$imageurl.";" . "<br>";
        // Verifica se il team esiste già nel database
        $stmt_check = $conn->prepare("SELECT id FROM teams WHERE name = ?");
        $stmt_check->bind_param("s", $nome);
        $stmt_check->execute();
        $stmt_check->store_result();
        // Se il team esiste, aggiorna i punti
        if ($stmt_check->num_rows > 0) {
            // Aggiorna i punti
            $stmt_update = $conn->prepare("UPDATE teams SET points = ? WHERE name = ?");
            $puntiInt = (int)$punti;
            $stmt_update->bind_param("is", $puntiInt, $nome);
            if ($stmt_update->execute()) {
                echo "Punti aggiornati per " . $nome . "<br>";
            } else {
                echo "Errore nell'aggiornamento dei punti: " . $stmt_update->error . "<br>";
            }
            $stmt_update->close();
        } else {
            // Se il team non esiste, inserisci i dati
            $stmt_insert = $conn->prepare("INSERT INTO teams (name, points) VALUES (?, ?)");
            $puntiInt = (int)$punti;
            $stmt_insert->bind_param("si", $nome, $puntiInt);
            if ($stmt_insert->execute()) {
                echo "Dati inseriti per " . $nome . "<br>";
            } else {
                echo "Errore nell'inserimento dei dati: " . $stmt_insert->error . "<br>";
            }
            $stmt_insert->close();
        }
        // Chiudi la dichiarazione
        $stmt_check->close();
    }
