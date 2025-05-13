<?php

    require_once '../config/config.php';
    


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Prendi i dati dal form
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        $email = $conn->real_escape_string($email);
    
        // Esegui query per cercare l'utente
        $query = "SELECT * FROM users WHERE Email = '$email'";
        $result = $conn->query($query);
    
        if ($result && $result->num_rows == 1) {
            $user = $result->fetch_assoc();
    
            // Verifica la password (se è salvata criptata usa password_verify)
            if (password_verify($password, $user['password_hash'])) {
                // Login corretto: imposta le variabili di sessione
                session_start(); 
                $_SESSION['logged_in'] = true; 
                $_SESSION['nome'] = $user['Nome'];
                $_SESSION['cognome'] = $user['Cognome'];
                $_SESSION['email'] = $user['Email'];
                $_SESSION['newsletter'] = $user['Newsletter'];
    
                header("Location: ../pages/index.php"); // Vai alla home
                exit();
            } else {
                echo "Password errata.";
            }
        } else {
            echo "Utente non trovato.";
        }
    }

?>