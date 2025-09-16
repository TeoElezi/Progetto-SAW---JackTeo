## F1 FanHub

Applicazione PHP/MySQL per fan F1 con: news automatiche, classifiche, autenticazione e profilo utente, newsletter SMTP, donazioni PayPal e una semplice area admin.

### Stack e prerequisiti
- PHP 8.1+ con estensioni: mysqli, curl, json, mbstring, openssl
- MySQL/MariaDB 10.4+
- Composer (per `phpmailer/phpmailer`)
- Server web (Apache/Nginx). In locale va bene XAMPP

### Struttura progetto (principale)
- `index.php`: homepage, ultime news, prossimo GP, classifiche
- `includes/header.php`, `includes/footer.php`: layout, nav, banner live GP
- `includes/session.php`: sessione, CSRF, remember-me, rate-limit
- `config/config.php`: connessione DB, ambiente, PayPal
- `config/email_config.php`: SMTP/newsletter
- `includes/NewsletterManager.php`: invio newsletter con PHPMailer
- `api/fetch_news.php`: import notizie via RapidAPI (ESPN)
- `includes/auto_fetch_news.php`: trigger fetch notizie ogni ora
- `pages/*.php`: ricerche e viste secondarie
- `user/*.php`: login/registrazione/profile, unsubscribe
- `admin/*.php`: utenti, iscritti newsletter, invii
- `payments/*`: donazioni e verifica PayPal
- `assets/*`: CSS, JS, immagini, font
- `DBTEO.sql`: schema/dati di esempio completi

### Setup passo-passo
1) Posiziona la cartella sotto la root del server (`htdocs` su XAMPP). Il percorso base è calcolato da `getBasePath()` in `config/config.php`.

2) Database
- Crea un DB MySQL, es. `f1_fanhub`
- Importa `DBTEO.sql` che contiene tabelle: `users`, `newsletter_subscribers`, `news`, `donations`, `remember_tokens`, `drivers`, `teams`, `races`, `votes`, `app_settings` e dati demo

3) Variabili di ambiente (consigliato)
- `APP_ENV` = `local` o `production`
- `APP_TZ` = es. `Europe/Rome`
- DB: `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD`, `DB_DATABASE`
- PayPal: `PAYPAL_CLIENT_ID`, `PAYPAL_CLIENT_SECRET`, `PAYPAL_ENV`=`sandbox|live`, `PAYPAL_CURRENCY` (es. `EUR`)
- SMTP/Newsletter: `SMTP_HOST`, `SMTP_PORT`, `SMTP_USERNAME`, `SMTP_PASSWORD`, `SMTP_SECURE`=`tls|ssl`, `NEWSLETTER_FROM_EMAIL`, `NEWSLETTER_FROM_NAME`, `NEWSLETTER_UNSUBSCRIBE_SECRET`
- Opzionali: `NEWSLETTER_LOGO_URL`, `NEWSLETTER_DEBUG_MODE`, `NEWSLETTER_LOG_FILE`, `NEWSLETTER_UNSUBSCRIBE_EXPIRY_DAYS`

4) Configurazione applicazione
- `config/config.php` legge le variabili e crea `$conn` (mysqli, charset `utf8mb4`)
- `config/email_config.php` compone la config newsletter e definisce costanti per PHPMailer
- Aggiorna le variabili ambiente o, se in locale, modifica i default dei file di config

5) Dipendenze
```bash
composer install
```
Installa PHPMailer in `vendor/`.

### Funzionalità principali
- Autenticazione: `user/login.php`, `user/registration.php` + process in `user/loginProcess.php`, `user/registrationProcess.php`
  - Password con `password_hash`
  - CSRF token in sessione
  - Rate-limiting login/registrazione su sessione (finestra 15 minuti)
  - Remember-me con tabella `remember_tokens`

- News automatiche: `api/fetch_news.php`
  - Usa RapidAPI `f1-motorsport-data` (imposta header `x-rapidapi-key`)
  - Importa campi principali in tabella `news` con idempotenza su `data_source_id`
  - `includes/auto_fetch_news.php` esegue a orologeria (minimo 1/h) via chiamata HTTP asincrona

- Homepage `index.php`
  - Ultime 5 news (card principale + card minori)
  - Prossimo GP da tabella `races` con countdown client-side (`assets/js/countdown.js`)
  - Classifica piloti e costruttori da `drivers` e `teams`

- Newsletter: `includes/NewsletterManager.php`
  - Invio HTML con PHPMailer via SMTP
  - Embedding logo via CID o URL
  - Link disiscrizione firmato HMAC con scadenza (`NEWSLETTER_UNSUBSCRIBE_SECRET`, `NEWSLETTER_UNSUBSCRIBE_EXPIRY_DAYS`)
  - Tabelle: `newsletter_subscribers` (stato, last_sent_at)

- Pagamenti/Donazioni `payments/donazioni.php`
  - Bottone PayPal JS SDK con `client-id` e `currency`
  - Verifica server-side su `payments/verify_and_record.php` (OAuth2 a PayPal, GET ordine, confronto importi, insert idempotente su `donations`)
  - Endpoint alternativo `payments/record_donation.php` per inserimenti senza PayPal

- Area Admin `admin/*`
  - Gestione utenti, iscritti newsletter, invio newsletter (interfacce basilari)

### Configurazioni chiave
- Percorso base: `getBasePath()` in `config/config.php` calcola il path in base al nome della cartella progetto. Se pubblichi in sottocartelle, i link rimangono coerenti.
- Header sicuri: `includes/header.php` invia header di sicurezza base e include asset da CDN (Bootstrap, Font Awesome) e `assets/css/style.css`.
- Sessione: `includes/session.php` applica SameSite=Lax, Secure se HTTPS, rigenera ID a login, aggiorna `last_activity`, crea CSRF e gestisce remember-me.

### Come eseguire in locale
1. Avvia MySQL e Apache (XAMPP)
2. Importa `DBTEO.sql`
3. Configura variabili ambiente o modifica `config/config.php` e `config/email_config.php`
4. `composer install`
5. Apri `http://localhost/<nome-cartella>/index.php`

### Popolamento e test
- News: chiama manualmente `http://localhost/<cartella>/api/fetch_news.php` (serve una chiave RapidAPI valida)
- Donazioni: su `payments/donazioni.php` testa sia form base sia PayPal (sandbox)
- Newsletter: costruisci un elenco in `newsletter_subscribers` e invia da `admin/newsletter.php`
- Admin: crea un amministratore con `admin/create_admin_user.php` e accedi a `admin/index.php`

### Sicurezza
- Prepared statements MySQL in tutte le query dinamiche
- CSRF per form sensibili, rate limit basato su sessione
- Nessuna credenziale in repo: usa variabili d’ambiente
- PayPal: verifica lato server dell’ordine prima dell’inserimento

### Personalizzazione UI/UX
- Stili in `assets/css/style.css`
- Immagini in `assets/images`
- Font Formula1 inclusi in `assets/fonts`

### Troubleshooting
- Errore DB: verifica variabili `DB_*` e che `mysqli_set_charset` non fallisca
- PayPal 401/403: conferma `PAYPAL_CLIENT_ID`/`PAYPAL_CLIENT_SECRET` e `PAYPAL_ENV`
- RapidAPI errore/timeout: chiave scaduta o rate limit; controlla `x-rapidapi-key`
- Percorsi rotti: verifica `getBasePath()` e il nome cartella in cui è pubblicato il progetto

### Dipendenze
```json
{
  "require": { "phpmailer/phpmailer": "^6.10" }
}
```

### Licenza
Uso didattico.
