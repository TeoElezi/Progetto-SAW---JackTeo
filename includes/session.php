<?php
// Robust session bootstrap with secure cookie parameters

// Determine if HTTPS is being used
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
           (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

// Configure session cookie parameters before starting the session
$cookieParams = [
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
];

if (PHP_VERSION_ID >= 70300) {
    // session_set_cookie_params supports array signature
    session_set_cookie_params($cookieParams);
} else {
    // Fallback for older PHP versions (just in case)
    session_set_cookie_params(
        $cookieParams['lifetime'],
        $cookieParams['path'].'; SameSite='.$cookieParams['samesite'],
        $cookieParams['domain'],
        $cookieParams['secure'],
        $cookieParams['httponly']
    );
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Idle timeout enforcement (20 minutes)
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $now = time();
    $timeoutSeconds = 60 * 60 * 24 * 30; // 30 days
    if (isset($_SESSION['last_activity']) && ($now - (int)$_SESSION['last_activity']) > $timeoutSeconds) {
        // Session expired due to inactivity
        clear_remember_cookie();
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
        header('Location: ../user/login.php?error=session_timeout');
        exit();
    }
    $_SESSION['last_activity'] = $now;
}

// Ensure a CSRF token exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize container for rate limiting login attempts
if (!isset($_SESSION['login_rate_limit'])) {
    $_SESSION['login_rate_limit'] = [];
}

// Rate limiting functions
function check_rate_limit($identifier, $max_attempts = 5, $time_window = 900) {
    global $conn;
    
    $current_time = time();
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Clean old attempts
    $stmt = $conn->prepare("DELETE FROM login_attempts WHERE expires_at < ?");
    $stmt->bind_param("s", date('Y-m-d H:i:s', $current_time));
    $stmt->execute();
    $stmt->close();
    
    // Count recent attempts for this IP/email
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM login_attempts WHERE (ip_address = ? OR email = ?) AND expires_at > ?");
    $stmt->bind_param("sss", $ip_address, $identifier, date('Y-m-d H:i:s', $current_time));
    $stmt->execute();
    $result = $stmt->get_result();
    $attempts = $result->fetch_assoc()['count'];
    $stmt->close();
    
    if ($attempts >= $max_attempts) {
        return false; // Rate limit exceeded
    }
    
    return true; // Within rate limit
}

function record_login_attempt($identifier, $success = false) {
    global $conn;
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $expires_at = date('Y-m-d H:i:s', time() + 900); // 15 minutes
    
    $stmt = $conn->prepare("INSERT INTO login_attempts (ip_address, email, success, expires_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $ip_address, $identifier, $success ? '1' : '0', $expires_at);
    $stmt->execute();
    $stmt->close();
}

function clear_login_attempts($identifier) {
    global $conn;
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Clear attempts for this IP and email
    $stmt = $conn->prepare("DELETE FROM login_attempts WHERE ip_address = ? OR email = ?");
    $stmt->bind_param("ss", $ip_address, $identifier);
    $stmt->execute();
    $stmt->close();
}

// Remember Me functionality
function generate_remember_token() {
    return bin2hex(random_bytes(32));
}

function create_remember_cookie($user_id, $email) {
    global $conn;
    
    $token = generate_remember_token();
    $expires = time() + (30 * 24 * 60 * 60); // 30 days
    
    // Store token in database
    $stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $token, date('Y-m-d H:i:s', $expires));
    $stmt->execute();
    $stmt->close();
    
    // Set secure cookie
    $cookie_name = 'remember_token';
    $cookie_value = $user_id . ':' . $token;
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    
    setcookie($cookie_name, $cookie_value, $expires, '/', '', $secure, true);
    
    return $token;
}

function validate_remember_cookie() {
    global $conn;
    
    if (!isset($_COOKIE['remember_token'])) {
        return false;
    }
    
    $cookie_value = $_COOKIE['remember_token'];
    $parts = explode(':', $cookie_value);
    
    if (count($parts) !== 2) {
        return false;
    }
    
    list($user_id, $token) = $parts;
    
    // Validate token from database
    $stmt = $conn->prepare("SELECT u.id, u.name, u.surname, u.email, u.newsletter, u.is_admin FROM users u 
                           INNER JOIN remember_tokens rt ON u.id = rt.user_id 
                           WHERE rt.token = ? AND rt.expires_at > NOW() AND u.id = ?");
    $stmt->bind_param("si", $token, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Regenerate token for security
        $new_token = generate_remember_token();
        $new_expires = time() + (30 * 24 * 60 * 60);
        
        $stmt_update = $conn->prepare("UPDATE remember_tokens SET token = ?, expires_at = ? WHERE token = ?");
        $stmt_update->bind_param("sss", $new_token, date('Y-m-d H:i:s', $new_expires), $token);
        $stmt_update->execute();
        $stmt_update->close();
        
        // Update cookie
        $cookie_name = 'remember_token';
        $cookie_value = $user['id'] . ':' . $new_token;
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        
        setcookie($cookie_name, $cookie_value, $new_expires, '/', '', $secure, true);
        
        // Set session
        $_SESSION['logged_in'] = true;
        $_SESSION['nome'] = $user['name'];
        $_SESSION['cognome'] = $user['surname'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['newsletter'] = $user['newsletter'];
        $_SESSION['is_admin'] = $user['is_admin'];
        
        $stmt->close();
        return true;
    }
    
    $stmt->close();
    return false;
}

function clear_remember_cookie($user_id = null) {
    if (isset($_COOKIE['remember_token'])) {
        $cookie_value = $_COOKIE['remember_token'];
        $parts = explode(':', $cookie_value);
        
        if (count($parts) === 2) {
            list($user_id_cookie, $token) = $parts;
            
            // Remove token from database
            if ($user_id === null || $user_id == $user_id_cookie) {
                global $conn;
                $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE token = ?");
                $stmt->bind_param("s", $token);
                $stmt->execute();
                $stmt->close();
            }
        }
        
        // Remove cookie
        setcookie('remember_token', '', time() - 3600, '/');
    }
}

// Auto-login with remember me cookie
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    validate_remember_cookie();
}

?>