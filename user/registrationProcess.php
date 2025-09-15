<?php
require_once '../config/config.php';
require_once '../includes/session.php';
require_once '../includes/NewsletterManager.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // CSRF validation
    $csrfForm = $_POST['csrf_token'] ?? '';
    $csrfSess = $_SESSION['csrf_token'] ?? '';
    if (!$csrfForm || !$csrfSess || !hash_equals($csrfSess, $csrfForm)) {
        header('Location: ../user/registration.php?error=csrf');
        exit();
    }

    // Basic rate limiting per IP
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $now = time();
    $windowSec = 900; // 15 min
    $maxAttempts = 5;
    if (!isset($_SESSION['register_rate_limit'])) {
        $_SESSION['register_rate_limit'] = [];
    }
    if (!isset($_SESSION['register_rate_limit'][$clientIp])) {
        $_SESSION['register_rate_limit'][$clientIp] = [];
    }
    $_SESSION['register_rate_limit'][$clientIp] = array_filter(
        $_SESSION['register_rate_limit'][$clientIp],
        function ($ts) use ($now, $windowSec) { return ($now - $ts) < $windowSec; }
    );
    if (count($_SESSION['register_rate_limit'][$clientIp]) >= $maxAttempts) {
        header('Location: ../user/registration.php?error=too_many_attempts');
        exit();
    }

    $nome = trim($_POST["name"] ?? '');
    $cognome = trim($_POST["surname"] ?? '');
    $email = strtolower(trim($_POST["email"] ?? ''));
    $password = trim($_POST["password"] ?? '');
    $confermaPW = trim($_POST["confirm"] ?? '');

    // Input validation
    if ($nome === '' || $cognome === '' || $email === '' || $password === '' || $confermaPW === '') {
        header('Location: ../user/registration.php?error=empty_fields');
        exit();
    }

    if (!preg_match("/^[a-zA-ZÀ-ÿ\s]{2,50}$/u", $nome)) {
        header("Location: ../user/registration.php?error=invalid_name");
        exit();
    }
    if (!preg_match("/^[a-zA-ZÀ-ÿ\s]{2,50}$/u", $cognome)) {
        header("Location: ../user/registration.php?error=invalid_surname");
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
        header('Location: ../user/registration.php?error=invalid_email');
        exit();
    }
    if ($password !== $confermaPW) {
        header("Location: ../user/registration.php?error=password_mismatch");
        exit();
    }
    // Password policy: min 8 chars, at least one letter and one digit
    if (!(strlen($password) >= 8 && preg_match('/[A-Za-z]/', $password) && preg_match('/\d/', $password))) {
        header('Location: ../user/registration.php?error=weak_password');
        exit();
    }

    $newsletter = isset($_POST['newsletter']) ? 1 : 0;

    // Check if email already exists
    $stmtCheckEmail = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmtCheckEmail->bind_param('s', $email);
    $stmtCheckEmail->execute();
    $resEmail = $stmtCheckEmail->get_result();
    if ($resEmail && $resEmail->num_rows > 0) {
        $stmtCheckEmail->close();
        header('Location: ../user/registration.php?error=email_exists');
        exit();
    }
    $stmtCheckEmail->close();

    $hashPassword = password_hash($password, PASSWORD_DEFAULT);

    // Determine if username column exists
    $hasUsername = false;
    if ($stmtCol = $conn->prepare("SHOW COLUMNS FROM users LIKE 'username'")) {
        $stmtCol->execute();
        $resCol = $stmtCol->get_result();
        $hasUsername = ($resCol && $resCol->num_rows === 1);
        $stmtCol->close();
    }

    if ($hasUsername) {
        $username = strstr($email, '@', true) ?: $email;
        $stmt = $conn->prepare("INSERT INTO users (username, name, surname, email, password_hash, newsletter) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $username, $nome, $cognome, $email, $hashPassword, $newsletter);
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, surname, email, password_hash, newsletter) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $nome, $cognome, $email, $hashPassword, $newsletter);
    }

    if ($stmt->execute()) {
        // If user subscribed to newsletter, add them to newsletter_subscribers table
        if ($newsletter) {
            $newsletterManager = new NewsletterManager($conn);
            $newsletterManager->addSubscriber($email);
        }
        
        // Prevent session fixation after privilege change
        session_regenerate_id(true);
        $_SESSION['logged_in'] = true;
        $_SESSION['email'] = $email;
        $_SESSION['nome'] = $nome;
        $_SESSION['cognome'] = $cognome;
        $_SESSION['newsletter'] = $newsletter;
        // Reset rate limit
        $_SESSION['register_rate_limit'][$clientIp] = [];
        $stmt->close();
        $conn->close();
        header("Location: ../index.php?success=registration_success");
        exit();
    } else {
        $stmt->close();
        $conn->close();
        header("Location: ../user/registration.php?error=registration_failed");
        exit();
    }
}
?>
