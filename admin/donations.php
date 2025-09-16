<?php
require_once '../includes/session.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || !($_SESSION['is_admin'] ?? false)) {
    header('Location: ../user/login.php?error=access_denied');
    exit();
}

require_once '../config/config.php';

$orderId = isset($_GET['order']) ? trim($_GET['order']) : '';
$from = isset($_GET['from']) ? trim($_GET['from']) : '';
$to = isset($_GET['to']) ? trim($_GET['to']) : '';

$where = [];
$params = [];
$types = '';

if ($orderId !== '') { $where[] = 'paypal_order_id = ?'; $params[] = $orderId; $types .= 's'; }
if ($from !== '') { $where[] = 'created_at >= ?'; $params[] = $from . ' 00:00:00'; $types .= 's'; }
if ($to !== '') { $where[] = 'created_at <= ?'; $params[] = $to . ' 23:59:59'; $types .= 's'; }

$sql = 'SELECT name, amount, created_at, paypal_order_id FROM donations';
if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY created_at DESC LIMIT 200';

$stmt = $conn->prepare($sql);
if ($types !== '') { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$result = $stmt->get_result();

include_once '../includes/header.php';
?>

<div class="container py-4">
  <h2 class="mb-3">Donazioni</h2>

  <form class="row g-3 mb-4" method="get" action="donations.php">
    <div class="col-md-3">
      <label class="form-label">Order ID</label>
      <input type="text" name="order" value="<?php echo htmlspecialchars($orderId); ?>" class="form-control" placeholder="PayPal Order ID">
    </div>
    <div class="col-md-3">
      <label class="form-label">Dal</label>
      <input type="date" name="from" value="<?php echo htmlspecialchars($from); ?>" class="form-control">
    </div>
    <div class="col-md-3">
      <label class="form-label">Al</label>
      <input type="date" name="to" value="<?php echo htmlspecialchars($to); ?>" class="form-control">
    </div>
    <div class="col-md-3 d-flex align-items-end">
      <button class="btn btn-primary w-100" type="submit">Filtra</button>
    </div>
  </form>

  <div class="card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped align-middle">
          <thead>
            <tr>
              <th>Data</th>
              <th>Nome</th>
              <th>Importo (€)</th>
              <th>Order ID</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result->num_rows > 0): ?>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                  <td><?php echo htmlspecialchars($row['name']); ?></td>
                  <td><strong><?php echo number_format($row['amount'], 2, ',', '.'); ?></strong></td>
                  <td><code><?php echo htmlspecialchars($row['paypal_order_id'] ?? ''); ?></code></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="4" class="text-center text-muted">Nessuna donazione trovata.</td></tr>
            <?php endif; $stmt->close(); ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include_once '../includes/footer.php'; ?>

