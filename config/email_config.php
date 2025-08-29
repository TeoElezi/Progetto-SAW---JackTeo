<?php
// Configurazione Email per Newsletter
// Questo file contiene le impostazioni per l'invio delle email

// Configurazione mittente
define('NEWSLETTER_FROM_EMAIL', 'newsletter@f1fanhub.com');
define('NEWSLETTER_FROM_NAME', 'F1 FanHub Newsletter');

// Configurazione SMTP (Gmail)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587); // 587 per TLS, 465 per SSL
define('SMTP_USERNAME', 'matteoelezi02@gmail.com'); // es: tuonome@gmail.com
define('SMTP_PASSWORD', 'cevl wxmy sjqp lzks'); // usa una App Password di Google
define('SMTP_SECURE', 'tls'); // 'tls' oppure 'ssl'

// Configurazione email
define('NEWSLETTER_SUBJECT_PREFIX', '[F1 FanHub] ');
define('NEWSLETTER_REPLY_TO', 'noreply@f1fanhub.com');

// Configurazione template
define('NEWSLETTER_TEMPLATE_PATH', '../templates/newsletter/');
define('NEWSLETTER_LOGO_URL', 'https://f1fanhub.com/assets/images/f1_logo_white.png');

// Configurazione rate limiting
define('NEWSLETTER_MAX_EMAILS_PER_HOUR', 100);
define('NEWSLETTER_DELAY_BETWEEN_EMAILS', 1); // secondi

// Configurazione disiscrizione
define('NEWSLETTER_UNSUBSCRIBE_EXPIRY_DAYS', 7);

// Configurazione debug
define('NEWSLETTER_DEBUG_MODE', true); // In produzione, impostare a false
define('NEWSLETTER_LOG_FILE', '../logs/newsletter.log');

// Funzioni helper per la configurazione email
function getNewsletterConfig() {
    return [
        'from_email' => NEWSLETTER_FROM_EMAIL,
        'from_name' => NEWSLETTER_FROM_NAME,
        'smtp_host' => SMTP_HOST,
        'smtp_port' => SMTP_PORT,
        'smtp_username' => SMTP_USERNAME,
        'smtp_password' => SMTP_PASSWORD,
        'smtp_secure' => SMTP_SECURE,
        'subject_prefix' => NEWSLETTER_SUBJECT_PREFIX,
        'reply_to' => NEWSLETTER_REPLY_TO,
        'debug_mode' => NEWSLETTER_DEBUG_MODE
    ];
}

// Funzione per verificare se la configurazione email è valida
function validateEmailConfig() {
    $config = getNewsletterConfig();
    $errors = [];
    
    if (empty($config['from_email']) || !filter_var($config['from_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email mittente non valida';
    }
    
    if (empty($config['from_name'])) {
        $errors[] = 'Nome mittente mancante';
    }
    
    if (empty($config['smtp_host'])) {
        $errors[] = 'Host SMTP mancante';
    }
    
    if ($config['smtp_port'] <= 0 || $config['smtp_port'] > 65535) {
        $errors[] = 'Porta SMTP non valida';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

// Funzione per ottenere le impostazioni di debug
function getDebugSettings() {
    return [
        'debug_mode' => NEWSLETTER_DEBUG_MODE,
        'log_file' => NEWSLETTER_LOG_FILE,
        'max_emails_per_hour' => NEWSLETTER_MAX_EMAILS_PER_HOUR,
        'delay_between_emails' => NEWSLETTER_DELAY_BETWEEN_EMAILS
    ];
}
?>
