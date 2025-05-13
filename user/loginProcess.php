<?php

    $DBHost = "127.0.0.1";
    $DBusername = "Amministratore";
    $DBpassword = "Giacomo.3544";
    $DBdatabase = "f1database";

        
    // Connessione al database
    $connect = mysqli_connect($DBHost, $DBusername, $DBpassword, $DBdatabase);

    // Verifica della connessione
    if ($connect === false) {
        die("Connessione al database fallita: " . mysqli_connect_error());
    }
    
    echo "Connessione al database riuscita";


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Prendi i dati dal form
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        $email = $connect->real_escape_string($email);
    
        // Esegui query per cercare l'utente
        $query = "SELECT * FROM utenti WHERE Email = '$email'";
        $result = $connect->query($query);
    
        if ($result && $result->num_rows == 1) {
            $user = $result->fetch_assoc();
    
            // Verifica la password (se è salvata criptata usa password_verify)
            if (password_verify($password, $user['Pw'])) {
                // Login corretto: imposta le variabili di sessione
                session_start(); 
                $_SESSION['logged_in'] = true; 
                $_SESSION['nome'] = $user['Nome'];
                $_SESSION['cognome'] = $user['Cognome'];
                $_SESSION['email'] = $user['Email'];
                $_SESSION['newsletter'] = $user['Newsletter'];
    
                header("Location: ../index.php"); // Vai alla home
                exit();
            } else {
                echo "Password errata.";
            }
        } else {
            echo "Utente non trovato.";
        }
    }

?>
