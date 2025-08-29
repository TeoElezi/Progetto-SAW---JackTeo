<?php
require_once '../config/config.php';
require_once '../includes/session.php';

// Basic rate limiting keyed by client IP (stored in session)
$clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$now = time();
$windowSec = 900; // 15 minutes
$maxAttempts = 5;

if (!isset($_SESSION['login_rate_limit'][$clientIp])) {
    $_SESSION['login_rate_limit'][$clientIp] = [];
}

// Purge old attempts
$_SESSION['login_rate_limit'][$clientIp] = array_filter(
    $_SESSION['login_rate_limit'][$clientIp],
    function ($ts) use ($now, $windowSec) { return ($now - $ts) < $windowSec; }
);

if (count($_SESSION['login_rate_limit'][$clientIp]) >= $maxAttempts) {
    header("Location: ../user/login.php?error=too_many_attempts");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // CSRF validation
    $csrfForm = $_POST['csrf_token'] ?? '';
    $csrfSess = $_SESSION['csrf_token'] ?? '';
    if (!$csrfForm || !$csrfSess || !hash_equals($csrfSess, $csrfForm)) {
        header("Location: ../user/login.php?error=csrf");
        exit();
    }

    $email = strtolower(filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL));
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        header("Location: ../user/login.php?error=empty_fields");
        exit();
    }

    $stmt = $conn->prepare("SELECT id, name, surname, email, password_hash, newsletter, is_admin FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Timing-safe default path
    $valid = false;
    $user = null;
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $valid = password_verify($password, $user['password_hash']);
    } else {
        // Perform dummy verify to equalize timing
        password_verify($password, password_hash('dummy', PASSWORD_DEFAULT));
    }

    if ($valid) {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        $_SESSION['logged_in'] = true;
        $_SESSION['nome'] = $user['name'];
        $_SESSION['cognome'] = $user['surname'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['newsletter'] = $user['newsletter'];
        $_SESSION['is_admin'] = $user['is_admin'] ?? false;

        // Handle Remember Me
        if (isset($_POST['remember_me']) && $_POST['remember_me'] === 'on') {
            create_remember_cookie($user['id'], $user['email']);
        }

        // Clear login attempts for this IP/email on successful login
        clear_login_attempts($email);

        $stmt->close();
        $conn->close();
        header("Location: ../pages/index.php?success=login_success");
        exit();
    } else {
        // Record failed attempt
        $_SESSION['login_rate_limit'][$clientIp][] = $now;

        $stmt && $stmt->close();
        $conn->close();
        // Avoid disclosing whether user exists
        header("Location: ../user/login.php?error=invalid_credentials");
        exit();
    }
}
?>
