<?php
require_once '../config/config.php';
?>
<!-- Navbar -->
<?php include '../includes/header.php' ?>

<div class="container my-5">
  <div class="row mb-5">
    <div class="col text-center">
      <h1 class="display-4">Supporta F1 FunHub 🚀</h1>
      <p class="lead mt-3">
        FunHub è una startup nelle sue prime fasi, il tuo sostegno può fare la differenza!  
        Abbiamo lanciato una campagna di crowdfunding per realizzare nuove funzionalità e una community sempre più grande.  
      </p>
      <p>
        Se raggiungeremo l’obiettivo entro la data stabilita, potremo avviare la fase successiva del progetto.  
        Altrimenti, i fondi non verranno utilizzati.
      </p>
    </div>
  </div>

  <?php
  // Parametri della campagna
  $goal_amount = 10000; // Obiettivo in €
  $deadline = "2025-12-31 23:59:59";

  // Totale donazioni
  $res = mysqli_query($conn, "SELECT SUM(amount) as total FROM donations");
  $row = mysqli_fetch_assoc($res);
  $total_donations = $row['total'] ?? 0;

  $percent = min(100, ($total_donations / $goal_amount) * 100);
  ?>

  <!-- Barra di progresso -->
  <div class="mb-5">
    <h3>Obiettivo: <?php echo $goal_amount; ?> €</h3>
    <div class="progress" style="height: 30px;">
      <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $percent; ?>%;">
        <?php echo number_format($percent, 0); ?>%
      </div>
    </div>
    <p class="mt-2">
      Raccolti: <strong><?php echo $total_donations; ?> €</strong> su <?php echo $goal_amount; ?> €  
      <br>
      Scadenza: <span id="deadline"></span>
    </p>
  </div>

  <!-- Form Donazione -->
<div class="card shadow-lg p-4 mb-5">
  <h4>Fai una donazione ❤️</h4>
  <form method="POST" action="">
    <div class="mb-3">
      <label for="name" class="form-label">Il tuo nome/nickname</label>
      <input type="text" class="form-control" name="name" id="name" required>
    </div>
    <div class="mb-3">
      <label for="amount" class="form-label">Importo (€)</label>
      <input type="number" class="form-control" name="amount" id="amount" min="1" required>
    </div>
    <div class="d-flex justify-content-center">
      <div id="paypal-button-container" class="d-inline-block" style="min-width:280px; max-width:380px;"></div>
    </div>
  </form>
</div>

  <!-- Lista Donatori -->
  <div>
    <h4>Ultimi sostenitori 🏁</h4>
    <ul class="list-group">
      <?php
      $donors = mysqli_query($conn, "SELECT name, amount, created_at FROM donations ORDER BY created_at DESC LIMIT 10");
      while ($donor = mysqli_fetch_assoc($donors)) {
        echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
        echo htmlspecialchars($donor['name']);
        echo '<span class="badge bg-danger rounded-pill">+' . $donor['amount'] . ' €</span>';
        echo '</li>';
      }
      ?>
    </ul>
  </div>
</div>

<?php
// Salvataggio donazione (fallback manuale) con prepared statements
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['amount'])) {
  $name = trim($_POST['name']);
  $amount = (float) $_POST['amount'];
  if ($name !== '' && $amount > 0) {
    if ($stmt = $conn->prepare("INSERT INTO donations (name, amount, created_at) VALUES (?, ?, NOW())")) {
      $stmt->bind_param('sd', $name, $amount);
      $stmt->execute();
      $stmt->close();
      echo "<script>window.location.href = 'donazioni.php';</script>";
    }
  }
}
?>

<!-- Footer -->
<?php include '../includes/footer.php' ?>

<script>
  // Countdown alla scadenza
  const deadline = new Date("<?php echo $deadline; ?>").getTime();
  const x = setInterval(() => {
    const now = new Date().getTime();
    const distance = deadline - now;

    if (distance < 0) {
      document.getElementById("deadline").innerHTML = "Campagna conclusa ❌";
      clearInterval(x);
    } else {
      const days = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      document.getElementById("deadline").innerHTML = days + "g " + hours + "h rimanenti";
    }
  }, 1000);
</script>

<?php if (!empty($paypalClientId)) : ?>
<!-- PayPal SDK -->
<script src="https://www.paypal.com/sdk/js?client-id=<?php echo urlencode($paypalClientId); ?>&currency=<?php echo urlencode($paypalCurrency ?? 'EUR'); ?>&components=buttons&intent=capture"></script>
<script>
  const ppNameInput = document.getElementById('name');
  const ppAmountInput = document.getElementById('amount');

  paypal.Buttons({
    style: { layout: 'vertical', color: 'gold', shape: 'rect', label: 'paypal', height: 48 },
    onInit: function(data, actions) {
      actions.disable();
      const validate = () => {
        const val = parseFloat(ppAmountInput.value);
        if (!isNaN(val) && val > 0 && ppNameInput.value.trim().length > 0) {
          actions.enable();
        } else {
          actions.disable();
        }
      };
      ppAmountInput.addEventListener('input', validate);
      ppNameInput.addEventListener('input', validate);
      validate();
    },
    createOrder: function(data, actions) {
      const value = parseFloat(ppAmountInput.value || '0').toFixed(2);
      return actions.order.create({
        intent: 'CAPTURE',
        purchase_units: [{ amount: { currency_code: '<?php echo $paypalCurrency ?? 'EUR'; ?>', value } }]
      });
    },
    onApprove: function(data, actions) {
      return actions.order.capture().then(function(details) {
        const payload = {
          name: ppNameInput.value.trim(),
          amount: parseFloat(ppAmountInput.value || '0'),
          orderId: data.orderID
        };
        return fetch('verify_and_record.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        }).then(async (res) => {
          if (!res.ok) {
            const msg = await res.text().catch(() => '');
            throw new Error(msg || 'Verifica pagamento fallita');
          }
          return res.json().catch(() => ({}));
        }).then(() => {
          window.location.href = 'donazioni.php';
        }).catch((err) => {
          console.error('Verify failed', err);
          alert('Pagamento effettuato su PayPal ma non registrato sul sito. Contattaci con l\'ID ordine: ' + data.orderID);
        });
      });
    },
    onError: function(err) {
      console.error(err);
      alert('Si è verificato un errore con PayPal. Riprova.');
    }
  }).render('#paypal-button-container');
</script>
<?php endif; ?>


