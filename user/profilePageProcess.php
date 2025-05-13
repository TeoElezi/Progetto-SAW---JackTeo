<?php

    session_start();

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

    $email = $_SESSION['email'];
    // Esegui query per cercare l'utente
    $query = "SELECT * FROM utenti WHERE Email = '$email'";
    $result = $connect->query($query);
 
    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $idUtente = $user["Id"];
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        if (isset($_POST['nome']) && !empty($_POST['nome'])) {
            $nome = $connect->real_escape_string(trim($_POST['nome']));
            if (!preg_match("/^[a-zA-ZÀ-ÿ\s]+$/u", $nome)) {
                echo "Il nome può contenere solo lettere.";
                exit;
            }
            $sql = "UPDATE utenti SET Nome = '$nome' WHERE Id = $idUtente";
            $connect->query($sql);
            $_SESSION['nome'] = $nome;
        }
    
        if (isset($_POST['cognome']) && !empty($_POST['cognome'])) {
            $cognome = $connect->real_escape_string(trim($_POST['cognome']));
            if (!preg_match("/^[a-zA-ZÀ-ÿ\s]+$/u", $cognome)) {
                echo "Il cognome può contenere solo lettere.";
                exit;
            }
            $sql = "UPDATE utenti SET Cognome = '$cognome' WHERE Id = $idUtente";
            $connect->query($sql);
            $_SESSION['cognome'] = $cognome;
        }
    
        if (isset($_POST['email']) && !empty($_POST['email'])) {
            $email = $connect->real_escape_string(trim($_POST['email']));
            $sql = "UPDATE utenti SET Email = '$email' WHERE Id = $idUtente";
            $connect->query($sql);
            $_SESSION['email'] = $email;
        }
    
            $newsletter = isset($_POST["newsletter"]) ? 1 : 0;
            $sql = "UPDATE utenti SET Newsletter = '$newsletter' WHERE Id = $idUtente";
            $connect->query($sql);
            $_SESSION['newsletter'] = $newsletter;

        if ($connect -> query($sql) === true){
             echo "Record aggiornato con successo";  
            $_SESSION['logged_in'] = true;
            header("Location: ../index.php");
        }
        else echo "Errore durante l'inserimento del record: " . mysqli_error($connect);


        mysqli_close($connect);
    }



?>
