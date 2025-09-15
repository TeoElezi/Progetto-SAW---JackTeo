# F1 FanHub (Progetto SAW)

Progetto universitario dimostrativo con funzionalità principali: autenticazione, ricerca news, newsletter con invio via SMTP, area admin per utenti e iscritti, donazioni PayPal.

## Requisiti
- PHP 8.x, MySQL (XAMPP consigliato)
- Composer per le dipendenze (PHPMailer)

## Setup rapido
1. Clona il progetto sotto la root HTTP (es. `htdocs`).
2. Crea il database `f1_fanhub` e importa lo schema minimo:
   - Vedi file `database/schema_min.sql` (include tabelle `users`, `newsletter_subscribers`, `news`, `donations`, `remember_tokens`, `login_attempts`).
3. Copia `.env.example` in `.env` e imposta le variabili richieste (SMTP, PayPal):
   - `SMTP_HOST`, `SMTP_PORT`, `SMTP_USERNAME`, `SMTP_PASSWORD`
   - `NEWSLETTER_FROM_EMAIL`, `NEWSLETTER_FROM_NAME`
   - `NEWSLETTER_UNSUBSCRIBE_SECRET`
   - `PAYPAL_CLIENT_SECRET`
4. Esegui `composer install` nella root per installare PHPMailer.
5. Configura `config/config.php` con le credenziali MySQL locali.

## Funzionalità
- Registrazione e login con hashing sicuro delle password (`password_hash`).
- Protezione CSRF nei form sensibili; rate limiting per login/registrazione.
- Newsletter: gestione iscritti, invio email con PHPMailer, link di disiscrizione firmati HMAC con scadenza.
- Area admin: gestione utenti, sincronizzazione stato newsletter.
- Ricerca news con paginazione e query parametrizzate.
- Donazioni PayPal con verifica server-side e inserimento idempotente.

## Demo admin
- Script `admin/create_admin_user.php` crea un admin locale; accesso limitato a `localhost`.

## Sicurezza e note
- Nessuna credenziale sensibile è committata: usare `.env`/variabili d'ambiente.
- I link di disiscrizione scadono dopo `NEWSLETTER_UNSUBSCRIBE_EXPIRY_DAYS` (default 7).

## Limitazioni note
- Progetto dimostrativo: non copre logging avanzato, ruoli granulari, test automatici.

## Licenza
Uso accademico/didattico.
