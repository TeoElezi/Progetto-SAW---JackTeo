<?php
require_once '../config/config.php';
require_once '../includes/session.php';
require_once '../includes/NewsletterManager.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['email'])) {
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
    // CSRF check
    $csrfForm = $_POST['csrf_token'] ?? '';
    $csrfSess = $_SESSION['csrf_token'] ?? '';
    if (!$csrfForm || !$csrfSess || !hash_equals($csrfSess, $csrfForm)) {
        header('Location: ./profilePage.php?error=csrf');
        exit();
    }
    // Eliminazione account
    if (isset($_POST['action']) && $_POST['action'] === 'delete_account') {
        $confirmPassword = trim($_POST['confirm_password'] ?? '');
        if ($confirmPassword === '') {
            header('Location: ./profilePage.php?error=missing_password');
            exit();
        }
        // Recupera hash
        $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        if (!$row || !password_verify($confirmPassword, $row['password_hash'])) {
            header('Location: ./profilePage.php?error=invalid_password');
            exit();
        }

        // Se admin, impedisci auto-eliminazione dell'ultimo admin
        $isAdmin = ($_SESSION['is_admin'] ?? false) ? 1 : 0;
        if ($isAdmin) {
            $stmt = $conn->prepare("SELECT COUNT(*) as c FROM users WHERE is_admin = 1");
            $stmt->execute();
            $c = $stmt->get_result()->fetch_assoc()['c'] ?? 1;
            $stmt->close();
            if ((int)$c <= 1) {
                header('Location: ./profilePage.php?error=last_admin_cannot_delete');
                exit();
            }
        }

        // Invalida remember token e sessione, elimina record correlati minimi
        clear_remember_cookie($userId);
        $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

        // Se iscritto newsletter, marca come unsubscribed
        $stmtEmail = $conn->prepare("SELECT email FROM users WHERE id = ?");
        $stmtEmail->bind_param("i", $userId);
        $stmtEmail->execute();
        $emailRow = $stmtEmail->get_result()->fetch_assoc();
        $stmtEmail->close();
        if ($emailRow && isset($emailRow['email'])) {
            $em = $emailRow['email'];
            $stmtNs = $conn->prepare("UPDATE newsletter_subscribers SET status='unsubscribed', unsubscribed_at=NOW() WHERE email = ?");
            $stmtNs->bind_param("s", $em);
            $stmtNs->execute();
            $stmtNs->close();
        }

        // Elimina l'utente
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

        // Chiudi sessione in modo sicuro
        session_regenerate_id(true);
        clear_remember_cookie($userId);
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
        header('Location: ../index.php?success=account_deleted');
        exit();
    }

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
        $newEmail = strtolower(trim($_POST['email']));
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL) || strlen($newEmail) > 255) {
            header('Location: ./profilePage.php?error=invalid_email');
            exit();
        }
        // Check uniqueness (exclude current user)
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1");
        $stmt->bind_param("si", $newEmail, $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $exists = $res && $res->num_rows > 0;
        $stmt->close();
        if ($exists) {
            header('Location: ./profilePage.php?error=email_exists');
            exit();
        }
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
