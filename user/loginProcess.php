<?php
require_once '../config/config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);

    // Prepared statement
    $stmt = $conn->prepare("SELECT name, surname, email, password_hash, newsletter FROM users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['nome'] = $user['name'];
            $_SESSION['cognome'] = $user['surname'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['newsletter'] = $user['newsletter'];

            header("Location: ../pages/index.php?success=login_success");
            $stmt->close();
            $conn->close();
            exit();
        } else {
            header("Location: ../pages/login.php?error=wrong_password");
            $stmt->close();
            $conn->close();
            exit();
        }
    } else {
        header("Location: ../pages/login.php?error=user_not_found");
        $stmt->close();
        $conn->close();
        exit();
    }
}
