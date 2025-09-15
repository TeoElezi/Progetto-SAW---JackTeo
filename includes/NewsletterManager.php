<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class NewsletterManager {
    private $conn;
    private $from_email;
    private $from_name;
    private $smtp_host;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;
    private $smtp_secure;
    
    public function __construct($conn) {
        $this->conn = $conn;
        
        // Carica configurazione email
        require_once __DIR__ . '/../config/email_config.php';
        // Carica PHPMailer (richiede `composer require phpmailer/phpmailer`)
        $autoloadPath = __DIR__ . '/../vendor/autoload.php';
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
        }
        
        $config = getNewsletterConfig();
        $this->from_email = $config['from_email'];
        $this->from_name = $config['from_name'];
        $this->smtp_host = $config['smtp_host'];
        $this->smtp_port = $config['smtp_port'];
        $this->smtp_username = $config['smtp_username'];
        $this->smtp_password = $config['smtp_password'];
        $this->smtp_secure = $config['smtp_secure'];
    }
    
    /**
     * Invia newsletter a tutti gli iscritti attivi
     */
    public function sendNewsletterWithNews($news_ids, $subject, $custom_message = '') {
        try {
            if (!is_array($news_ids) || count($news_ids) === 0) {
                throw new Exception('Nessuna news selezionata');
            }
            $news_items = $this->getNewsItemsByIds($news_ids);
            if (empty($news_items)) {
                throw new Exception('News non trovate');
            }
            
            // Ottieni iscritti attivi
            $subscribers = $this->getActiveSubscribers();
            if (empty($subscribers)) {
                throw new Exception('Nessun iscritto attivo');
            }
            
            $sent_count = 0;
            $failed_count = 0;
            $errors = [];
            
            foreach ($subscribers as $subscriber) {
                try {
                    $email_sent = $this->sendEmailToSubscriberWithNews($subscriber, $news_items, $subject, $custom_message);
                    
                    if ($email_sent) {
                        $this->updateSubscriberLastSent($subscriber['id']);
                        $sent_count++;
                    } else {
                        $failed_count++;
                        $errors[] = "Fallito invio a: " . $subscriber['email'];
                    }
                } catch (Exception $e) {
                    $failed_count++;
                    $errors[] = "Errore per " . $subscriber['email'] . ": " . $e->getMessage();
                }
            }
            
            return [
                'success' => true,
                'sent_count' => $sent_count,
                'failed_count' => $failed_count,
                'errors' => $errors
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Ottiene i dettagli di un post
     */
    private function getNewsItemsByIds($news_ids) {
        $placeholders = implode(',', array_fill(0, count($news_ids), '?'));
        $types = str_repeat('i', count($news_ids));
        $stmt = $this->conn->prepare("SELECT id, title, content, link, posted_at FROM news WHERE id IN ($placeholders) ORDER BY posted_at DESC");
        $stmt->bind_param($types, ...array_map('intval', $news_ids));
        $stmt->execute();
        $result = $stmt->get_result();
        $news_items = [];
        while ($row = $result->fetch_assoc()) {
            $news_items[] = $row;
        }
        $stmt->close();
        return $news_items;
    }
    
    /**
     * Ottiene tutti gli iscritti attivi
     */
    private function getActiveSubscribers() {
        $stmt = $this->conn->prepare("SELECT * FROM newsletter_subscribers WHERE status = 'active'");
        $stmt->execute();
        $result = $stmt->get_result();
        $subscribers = [];
        
        while ($row = $result->fetch_assoc()) {
            $subscribers[] = $row;
        }
        
        $stmt->close();
        return $subscribers;
    }
    
    /**
     * Invia email a un singolo iscritto
     */
    private function sendEmailToSubscriberWithNews($subscriber, $news_items, $subject, $custom_message) {
        $to = $subscriber['email'];

        if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            // PHPMailer non installato
            throw new Exception('PHPMailer non è installato. Esegui: composer require phpmailer/phpmailer');
        }

        $mail = new PHPMailer(true);
        try {
            // Usa sempre SMTP (Gmail)
            $mail->isSMTP();
            $mail->Host = $this->smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtp_username;
            $mail->Password = $this->smtp_password;
            if ($this->smtp_secure === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = $this->smtp_port ?: 465;
            } else { // default tls
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $this->smtp_port ?: 587;
            }

            // Mittente e destinatario
            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($to);
            $mail->addReplyTo($this->from_email, $this->from_name);

            // Contenuto
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->Subject = $subject;
            // Logo inline (CID), fallback a URL
            $logoFsPath = realpath(__DIR__ . '/../assets/images/f1_logo_white.png');
            $logoCid = '';
            $logoUrl = '';
            if ($logoFsPath && file_exists($logoFsPath)) {
                $logoCid = 'brandlogo';
                $mail->addEmbeddedImage($logoFsPath, $logoCid, 'logo.png');
            } else {
                $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
                $logoUrl = (defined('NEWSLETTER_LOGO_URL') && NEWSLETTER_LOGO_URL) ? NEWSLETTER_LOGO_URL : ($baseUrl . '/assets/images/f1_logo_white.png');
            }

            $htmlBody = $this->buildEmailMessageWithNews($subscriber, $news_items, $custom_message, $logoCid, $logoUrl);
            $mail->Body = $htmlBody;
            $mail->AltBody = $this->buildAltBodyFromNews($news_items, $custom_message);

            // Opzionale: debug
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;

            return $mail->send();
        } catch (Exception $e) {
            // Rilancia per contare come fallito e accumulare errori
            throw new Exception('Mailer Error: ' . $mail->ErrorInfo);
        }
    }
    
    /**
     * Prepara gli headers dell'email
     */
    private function getEmailHeaders() {
        // Non più usato con PHPMailer
        return '';
    }
    
    /**
     * Costruisce il messaggio email
     */
    private function buildEmailMessageWithNews($subscriber, $news_items, $custom_message, $logoCid = '', $logoUrl = '') {
        $unsubscribe_link = $this->generateUnsubscribeLink($subscriber['email']);
        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://'. $_SERVER['HTTP_HOST'];
        $logo_url = $logoUrl ?: (defined('NEWSLETTER_LOGO_URL') && NEWSLETTER_LOGO_URL ? NEWSLETTER_LOGO_URL : ($base_url . '/assets/images/f1_logo_white.png'));
        $font_url = $base_url . '/assets/fonts/Formula1-Bold.otf';
        $fontFaceCss = "@font-face { font-family: 'Formula1'; src: url('{$font_url}') format('opentype'); font-weight: 700; font-style: normal; }";
        
        $message = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
            <title>F1 FanHub Newsletter</title>
            <style>
                body { margin:0; padding:0; background:#f0f2f5; }
                table { border-collapse: collapse; }
                img { border:0; outline:none; text-decoration:none; }
                .wrapper { width:100%; background:#f0f2f5; padding: 24px 0; }
                ' . $fontFaceCss . '
                .container { width: 600px; margin:0 auto; background:#ffffff; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Arial, Helvetica, sans-serif; color:#222; }
                .brand-title { font-family: \"Formula1\", -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Arial, Helvetica, sans-serif; font-size:22px; color:#ffffff; padding-left:12px; white-space:nowrap; }
                .banner { background:#1f2937; color:#ffffff; text-align:left; padding:22px 24px; }
                .content { padding:24px; }
                .title { font-size:26px; line-height:1.25; margin:0 0 16px 0; font-weight:800; }
                .intro { color:#6b7280; font-size:14px; margin:0 0 20px 0; }
                .news-item { border-top:1px solid #eee; padding:14px 0; }
                .news-item:first-child { border-top:none; }
                .news-title { color:#e10600; font-size:18px; font-weight:700; margin:0 0 6px 0; }
                .news-meta { color:#6b7280; font-size:12px; margin:0 0 8px 0; }
                .btn { display:inline-block; background:#e10600; color:#ffffff !important; text-decoration:none; padding:10px 14px; border-radius:6px; font-size:14px; }
                .footer { text-align:center; padding:18px 24px 28px 24px; font-size:12px; color:#6b7280; }
                .unsubscribe { color:#e10600; text-decoration:none; }
                .brand img { height:40px; }
            </style>
        </head>
        <body>
            <div class="wrapper">
                <table role="presentation" class="container">
                    <tr>
                        <td class="banner">
                            <table role="presentation" width="100%">
                                <tr>
                                    <td class="brand" style="vertical-align:middle; width:56px;"><img src="' . ($logoCid ? 'cid:' . $logoCid : htmlspecialchars($logo_url)) . '" alt="F1 FanHub" /></td>
                                    <td class="brand-title" style="vertical-align:middle;">FanHub - StartSaw</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="content">
                            <div class="title">Ultime news selezionate</div>';
        
        if (!empty($custom_message)) {
            $message .= '<p class="intro"><em>' . nl2br(htmlspecialchars($custom_message)) . '</em></p>';
        }
        
        $message .= '<div>';
        foreach ($news_items as $news) {
            $message .= '<div class="news-item">'
                . '<div class="news-title">' . htmlspecialchars($news['title']) . '</div>'
                . '<div class="news-meta">' . date('d/m/Y H:i', strtotime($news['posted_at'])) . '</div>'
                . '<div><a class="btn" href="' . htmlspecialchars($news['link']) . '" target="_blank" rel="noopener noreferrer">Leggi la news</a></div>'
                . '</div>';
        }
        $message .= '</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="footer">
                            <div>Questa email è stata inviata a: ' . htmlspecialchars($subscriber['email']) . '</div>
                            <div><a href="' . $unsubscribe_link . '" class="unsubscribe">Disiscriviti dalla newsletter</a></div>
                            <div>&copy; ' . date('Y') . ' F1 FanHub. Tutti i diritti riservati.</div>
                        </td>
                    </tr>
                </table>
            </div>
        </body>
        </html>';
        
        return $message;
    }

    private function buildAltBodyFromNews($news_items, $custom_message) {
        $lines = [];
        if (!empty($custom_message)) {
            $lines[] = strip_tags($custom_message);
            $lines[] = '';
        }
        foreach ($news_items as $news) {
            $lines[] = '- ' . $news['title'] . ' (' . date('d/m/Y H:i', strtotime($news['posted_at'])) . ')';
            $lines[] = '  ' . $news['link'];
            $lines[] = '';
        }
        $lines[] = 'Grazie per essere iscritto alla nostra newsletter!';
        return implode("\r\n", $lines);
    }

    // Rimosso: invio SMTP manuale; ora gestito da PHPMailer
    
    /**
     * Genera link per disiscrizione
     */
    private function generateUnsubscribeLink($email) {
        // Token con HMAC e scadenza
        $expiresAt = time() + (NEWSLETTER_UNSUBSCRIBE_EXPIRY_DAYS * 86400);
        $payload = $email . '|' . $expiresAt;
        $signature = hash_hmac('sha256', $payload, defined('NEWSLETTER_UNSUBSCRIBE_SECRET') ? NEWSLETTER_UNSUBSCRIBE_SECRET : '');
        $token = base64_encode($payload . '|' . $signature);

        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base_url = $scheme . '://' . $host;

        // Determina il base path dell'app (gestisce sottocartelle come /progetto%20saw%20jackteo/)
        $basePath = '/';
        $configPath = __DIR__ . '/../config/config.php';
        if (file_exists($configPath)) {
            require_once $configPath;
            if (function_exists('getBasePath')) {
                $basePath = getBasePath(); // es. '/progetto%20saw%20jackteo/'
            }
        }
        $pathPrefix = rtrim($basePath, '/'); // '' oppure '/progetto%20saw%20jackteo'

        return $base_url . $pathPrefix . '/user/unsubscribe.php?email=' . urlencode($email) . '&token=' . urlencode($token);
    }
    
    /**
     * Registra l'invio della newsletter
     */
    // Rimosso tracciamento invii per post
    
    /**
     * Aggiorna la data dell'ultimo invio per l'iscritto
     */
    private function updateSubscriberLastSent($subscriber_id) {
        $stmt = $this->conn->prepare("UPDATE newsletter_subscribers SET last_sent_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $subscriber_id);
        $stmt->execute();
        $stmt->close();
    }
    
    /**
     * Ottiene statistiche della newsletter
     */
    public function getNewsletterStats() {
        $stats = [];
        
        // Conteggio iscritti attivi
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM newsletter_subscribers WHERE status = 'active'");
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['active_subscribers'] = $result->fetch_assoc()['count'];
        $stmt->close();
        
        // Conteggio totale iscritti (inclusi disiscritti)
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM newsletter_subscribers");
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['total_subscribers'] = $result->fetch_assoc()['count'];
        $stmt->close();
        
        // Conteggio utenti con newsletter = 1 nella tabella users
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM users WHERE newsletter = 1");
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['users_with_newsletter'] = $result->fetch_assoc()['count'];
        $stmt->close();
        
        // Rimosso conteggio post pubblicati
        
        // Rimosse statistiche invii (newsletter_sends)
        
        return $stats;
    }
    
    /**
     * Aggiunge un nuovo iscritto
     */
    public function addSubscriber($email) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?) ON DUPLICATE KEY UPDATE status = 'active', unsubscribed_at = NULL");
            $stmt->bind_param("s", $email);
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Rimuove un iscritto
     */
    public function removeSubscriber($email) {
        try {
            $stmt = $this->conn->prepare("UPDATE newsletter_subscribers SET status = 'unsubscribed', unsubscribed_at = NOW() WHERE email = ?");
            $stmt->bind_param("s", $email);
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
