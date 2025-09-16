<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$name = isset($data['name']) ? trim($data['name']) : '';
$amount = isset($data['amount']) ? (float)$data['amount'] : 0;
$paypalOrderId = isset($data['orderId']) ? trim($data['orderId']) : null;

if ($amount <= 0 || $name === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Missing or invalid fields']);
    exit;
}

$ok = false;
$stmt = $conn->prepare("INSERT INTO donations (name, amount, created_at, paypal_order_id) VALUES (?, ?, NOW(), ?)");
if ($stmt) {
    $stmt->bind_param('sds', $name, $amount, $paypalOrderId);
    if ($stmt->execute()) {
        $ok = true;
    }
    $stmt->close();
}

if (!$ok) {
    $stmt2 = $conn->prepare("INSERT INTO donations (name, amount, created_at) VALUES (?, ?, NOW())");
    if ($stmt2) {
        $stmt2->bind_param('sd', $name, $amount);
        $ok = $stmt2->execute();
        $stmt2->close();
    }
}

if (!$ok) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    exit;
}

echo json_encode(['success' => true]);
?>

