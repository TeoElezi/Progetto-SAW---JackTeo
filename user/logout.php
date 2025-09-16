<?php
    require_once '../config/config.php';
    require_once '../includes/session.php';
    clear_remember_cookie();
    session_unset();
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
    $conn->close();
    header("Location: ../index.php");
    exit();
?>