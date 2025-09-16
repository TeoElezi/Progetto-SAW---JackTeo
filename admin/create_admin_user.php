<?php
require_once '../config/config.php';

$clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
if ($clientIp !== '127.0.0.1' && $clientIp !== '::1') {
    http_response_code(403);
    echo "<h2>Accesso negato</h2><p>Questo script è disponibile solo in locale.</p>";
    exit;
}

echo "<h2>Creazione Utente Amministratore</h2>";

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE is_admin = 1");
$stmt->execute();
$result = $stmt->get_result();
$admin_count = $result->fetch_assoc()['count'];
$stmt->close();

if ($admin_count > 0) {
    echo "<p>Esiste già un utente amministratore nel sistema.</p>";
    echo "<p>Per accedere come amministratore, usa le credenziali di un utente con is_admin = 1.</p>";
} else {

    $admin_email = 'admin@fanhub.com';
    $admin_password = 'admin123';
    $admin_name = 'Admin';
    $admin_surname = 'User';

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $admin_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $stmt = $conn->prepare("UPDATE users SET is_admin = 1 WHERE email = ?");
        $stmt->bind_param("s", $admin_email);
        if ($stmt->execute()) {
            echo "<p>Utente esistente aggiornato come amministratore.</p>";
        } else {
            echo "<p>Errore nell'aggiornamento dell'utente: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {

        $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (name, surname, email, password_hash, newsletter, is_admin) VALUES (?, ?, ?, ?, 0, 1)");
        $stmt->bind_param("ssss", $admin_name, $admin_surname, $admin_email, $password_hash);

        if ($stmt->execute()) {
            echo "<p>Utente amministratore creato con successo!</p>";
            echo "<p><strong>Email:</strong> " . htmlspecialchars($admin_email) . "</p>";
            echo "<p><strong>Password:</strong> " . htmlspecialchars($admin_password) . "</p>";
            echo "<p><strong>ATTENZIONE:</strong> Cambia questa password dopo il primo accesso!</p>";
        } else {
            echo "<p>Errore nella creazione dell'utente: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}

echo "<hr>";
echo "<p><a href='../user/login.php'>Vai al Login</a></p>";
echo "<p><a href='index.php'>Vai al Pannello Amministrativo</a></p>";

$conn->close();
?>
