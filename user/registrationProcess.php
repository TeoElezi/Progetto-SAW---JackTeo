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
        // 1. Prendo i dati GREZZI
        $nome = trim($_POST["name"]);
        $cognome = trim($_POST["surname"]);
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);
        $confermaPW = trim($_POST["confirm"]);
    
         // DEBUG
        echo "Password: " . $password . "<br>";
        echo "ConfermaPW: " . $confermaPW . "<br>";

        if ($password !== $confermaPW) {
            echo "<script>alert('Le password non coincidono'); window.history.back();</script>";
            exit;
        } else {
            echo "Password ok! $newsletter";
            // Ora puoi procedere al salvataggio
        }
    
        // 4. SOLO ORA proteggi per il database
        $nome = $connect->real_escape_string($nome);
        $cognome = $connect->real_escape_string($cognome);
        $email = $connect->real_escape_string($email);
        $password = $connect->real_escape_string($password);

        if (!preg_match("/^[a-zA-ZÀ-ÿ\s]+$/u", $nome)) {
            echo "Il nome può contenere solo lettere.";
            exit;
        }
        
        if (!preg_match("/^[a-zA-ZÀ-ÿ\s]+$/u", $cognome)) {
            echo "Il cognome può contenere solo lettere.";
            exit;
        }
        if (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)(?=.*[.!?_@#])[A-Za-z\d.!?_@#]{8,}$/", $password)) {
            echo "La password deve contenere almeno una lettera, un numero, un simbolo (.!?_@#) ed essere lunga almeno 8 caratteri.";
            exit;
        }
        

        // Gestione delle password 
        $hashPassword = password_hash($password, PASSWORD_DEFAULT);
    
        $newsletter = isset($_POST["newsletter"]) ? 1 : 0;


        $sql = "INSERT INTO utenti (Nome, Cognome, Email, Pw, Newsletter) VALUES ('$nome', '$cognome', '$email', '$hashPassword', '$newsletter')";
    
        if ($connect -> query($sql) === true){
             echo "Record inserito con successo";
            session_start();  
            $_SESSION['logged_in'] = true;
            $_SESSION['email'] = $email;
            $_SESSION['nome'] = $nome;
            $_SESSION['cognome'] = $cognome;
            $_SESSION['newsletter'] = $newsletter;
            header("Location: ../index.php");
        }
        else echo "Errore durante l'inserimento del record: " . mysqli_error($connect);


        mysqli_close($connect);

    }



?>
