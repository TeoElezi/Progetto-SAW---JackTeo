<?php
require_once '../config/config.php';
require_once '../includes/NewsletterManager.php';
session_start();

if (!isset($_SESSION['email'])) {
    header('Location: ../user/login.php');
    exit();
}

$emailSession = $_SESSION['email'];

// Recupera id utente
$stmtUser = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmtUser->bind_param("s", $emailSession);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
if (!$resultUser || $resultUser->num_rows !== 1) {
    $stmtUser->close();
    $conn->close();
    header('Location: ../user/login.php');
    exit();
}
$userRow = $resultUser->fetch_assoc();
$userId = (int)$userRow['id'];
$stmtUser->close();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Aggiorna nome
    if (!empty($_POST['nome'])) {
        $nome = trim($_POST['nome']);
        if (!preg_match("/^[a-zA-ZÀ-ÿ\s]+$/u", $nome)) {
            header('Location: ./profilePage.php?error=invalid_name');
            exit();
        }
        $stmt = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $nome, $userId);
        $stmt->execute();
        $stmt->close();
        $_SESSION['nome'] = $nome;
    }

    // Aggiorna cognome
    if (!empty($_POST['cognome'])) {
        $cognome = trim($_POST['cognome']);
        if (!preg_match("/^[a-zA-ZÀ-ÿ\s]+$/u", $cognome)) {
            header('Location: ./profilePage.php?error=invalid_surname');
            exit();
        }
        $stmt = $conn->prepare("UPDATE users SET surname = ? WHERE id = ?");
        $stmt->bind_param("si", $cognome, $userId);
        $stmt->execute();
        $stmt->close();
        $_SESSION['cognome'] = $cognome;
    }

    // Aggiorna email
    if (!empty($_POST['email'])) {
        $newEmail = trim($_POST['email']);
        $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->bind_param("si", $newEmail, $userId);
        $stmt->execute();
        $stmt->close();
        $_SESSION['email'] = $newEmail;
    }

    // Aggiorna newsletter
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;
    $stmt = $conn->prepare("UPDATE users SET newsletter = ? WHERE id = ?");
    $stmt->bind_param("ii", $newsletter, $userId);
    $stmt->execute();
    $stmt->close();
    $_SESSION['newsletter'] = $newsletter;
    
    // Sync newsletter subscription status
    $newsletterManager = new NewsletterManager($conn);
    if ($newsletter) {
        $newsletterManager->addSubscriber($_SESSION['email']);
    } else {
        $newsletterManager->removeSubscriber($_SESSION['email']);
    }

    $conn->close();
    header('Location: ./profilePage.php?success=profile_updated');
    exit();
}
?>
