<?php
    require_once '../config/config.php';
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
            echo "Password ok!";
        }
    
        // 4. SOLO ORA proteggi per il database
        $nome = $conn->real_escape_string($nome);
        $cognome = $conn->real_escape_string($cognome);
        $email = $conn->real_escape_string($email);
        $password = $conn->real_escape_string($password);

        if (!preg_match("/^[a-zA-ZÀ-ÿ\s]+$/u", $nome)) {
            echo "Il nome può contenere solo lettere.";
            exit;
        }
        
        // Gestione delle password 
        $hashPassword = password_hash($password, PASSWORD_DEFAULT);
    
        // Inizializza la variabile newsletter (0 se non selezionato, 1 se selezionato)
        $newsletter = isset($_POST['newsletter']) ? 1 : 0;

        // Correzione della sintassi SQL (rimuovere le virgolette attorno ai nomi delle colonne)
        $sql = "INSERT INTO users (name, surname, email, password_hash, newsletter) 
                VALUES ('$nome', '$cognome', '$email', '$hashPassword', '$newsletter')";
    
        if ($conn -> query($sql) === true){
             echo "Record inserito con successo";
            session_start();  
            $_SESSION['logged_in'] = true;
            $_SESSION['email'] = $email;
            $_SESSION['nome'] = $nome;
            $_SESSION['cognome'] = $cognome;
            $_SESSION['newsletter'] = $newsletter;
            header("Location: ../pages/index.php");
        }
        else echo "Errore durante l'inserimento del record: " . mysqli_error($conn);

        mysqli_close($conn);
    }
?>
