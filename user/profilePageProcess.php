<?php
    require_once '../config/config.php';
    $email = $_SESSION['email'];
    // Esegui query per cercare l'utente
    $query = "SELECT * FROM users WHERE Email = '$email'";
    $result = $conn->query($query);
 
    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $idUtente = $user["Id"];
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        if (isset($_POST['nome']) && !empty($_POST['nome'])) {
            $nome = $conn->real_escape_string(trim($_POST['nome']));
            if (!preg_match("/^[a-zA-ZÀ-ÿ\s]+$/u", $nome)) {
                echo "Il nome può contenere solo lettere.";
                exit;
            }
            $sql = "UPDATE utenti SET Nome = '$nome' WHERE Id = $idUtente";
            $conn->query($sql);
            $_SESSION['nome'] = $nome;
        }
    
        if (isset($_POST['cognome']) && !empty($_POST['cognome'])) {
            $cognome = $conn->real_escape_string(trim($_POST['cognome']));
            if (!preg_match("/^[a-zA-ZÀ-ÿ\s]+$/u", $cognome)) {
                echo "Il cognome può contenere solo lettere.";
                exit;
            }
            $sql = "UPDATE utenti SET Cognome = '$cognome' WHERE Id = $idUtente";
            $conn->query($sql);
            $_SESSION['cognome'] = $cognome;
        }
    
        if (isset($_POST['email']) && !empty($_POST['email'])) {
            $email = $conn->real_escape_string(trim($_POST['email']));
            $sql = "UPDATE utenti SET Email = '$email' WHERE Id = $idUtente";
            $conn->query($sql);
            $_SESSION['email'] = $email;
        }
    
            $newsletter = isset($_POST["newsletter"]) ? 1 : 0;
            $sql = "UPDATE utenti SET Newsletter = '$newsletter' WHERE Id = $idUtente";
            $conn->query($sql);
            $_SESSION['newsletter'] = $newsletter;

        if ($conn -> query($sql) === true){
             echo "Record aggiornato con successo";  
            $_SESSION['logged_in'] = true;
            header("Location: ../index.php");
        }
        else echo "Errore durante l'inserimento del record: " . mysqli_error($conn);


        mysqli_close($conn);
    }



?>
