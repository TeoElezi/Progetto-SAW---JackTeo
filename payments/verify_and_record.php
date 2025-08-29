<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$orderId = $input['orderId'] ?? '';
$name = trim($input['name'] ?? '');
$amountClient = isset($input['amount']) ? number_format((float)$input['amount'], 2, '.', '') : null;

if ($orderId === '' || $name === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Missing fields']);
    exit;
}

if ($paypalClientId === '' || $paypalClientSecret === '') {
    http_response_code(500);
    echo json_encode(['error' => 'Missing PayPal credentials']);
    exit;
}

$base = $paypalEnvironment === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

// Get access token
$ch = curl_init("$base/v1/oauth2/token");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD => $paypalClientId . ':' . $paypalClientSecret,
    CURLOPT_HTTPHEADER => ['Accept: application/json', 'Accept-Language: en_US'],
    CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
]);
$res = curl_exec($ch);
if ($res === false) {
    http_response_code(502);
    echo json_encode(['error' => 'Token request failed']);
    exit;
}
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($status >= 400) {
    http_response_code($status);
    echo $res;
    exit;
}
$token = json_decode($res, true)['access_token'] ?? null;
if (!$token) {
    http_response_code(502);
    echo json_encode(['error' => 'Token missing']);
    exit;
}

// Fetch order details to verify status/amount
$ch = curl_init("$base/v2/checkout/orders/" . urlencode($orderId));
curl_setopt_array($ch, [
    CURLOPT_HTTPGET => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    ],
]);
$orderRes = curl_exec($ch);
$orderStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($orderStatus >= 400) {
    http_response_code($orderStatus);
    echo $orderRes;
    exit;
}
$order = json_decode($orderRes, true);

$status = $order['status'] ?? '';
$amountServer = $order['purchase_units'][0]['amount']['value'] ?? null;
$currencyServer = $order['purchase_units'][0]['amount']['currency_code'] ?? null;

if ($status !== 'COMPLETED' && $status !== 'APPROVED' && $status !== 'CAPTURED') {
    http_response_code(409);
    echo json_encode(['error' => 'Order not completed', 'status' => $status]);
    exit;
}

if ($amountClient !== null && $amountServer !== null && $amountClient !== $amountServer) {
    http_response_code(409);
    echo json_encode(['error' => 'Amount mismatch', 'client' => $amountClient, 'server' => $amountServer]);
    exit;
}

// Idempotent insert: ignore duplicate order ids
$ok = false;
$stmt = $conn->prepare("INSERT IGNORE INTO donations (name, amount, created_at, paypal_order_id) VALUES (?, ?, NOW(), ?)");
if ($stmt) {
    $amount = (float)($amountServer ?: $amountClient ?: 0);
    $stmt->bind_param('sds', $name, $amount, $orderId);
    if ($stmt->execute()) {
        $ok = true;
    }
    $stmt->close();
}

if (!$ok) {
    http_response_code(200);
    echo json_encode(['success' => true, 'note' => 'Already recorded or no changes']);
    exit;
}

echo json_encode(['success' => true]);
?>


