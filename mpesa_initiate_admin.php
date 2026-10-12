<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';
include_once 'mpesa_helper.php';

header('Content-Type: application/json');

$bill_id = intval($_POST['bill_id'] ?? 0);
$amount  = (float) ($_POST['amount'] ?? 0);
$phone   = trim($_POST['phone'] ?? '');

if ($bill_id <= 0 || $amount <= 0 || $phone === '') {
    echo json_encode(['ok' => false, 'error' => 'Invalid input']);
    exit();
}

$config = require __DIR__ . '/config.php';
$mpesa  = $config['mpesa'] ?? [];

if (empty($mpesa['consumer_key']) || empty($mpesa['callback_url'])) {
    echo json_encode(['ok' => false, 'error' => 'M-Pesa not configured']);
    exit();
}

// Fetch the bill (any owner — admin context)
$stmt = mysqli_prepare($conn, "SELECT * FROM bill WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $bill_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$bill = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$bill) {
    echo json_encode(['ok' => false, 'error' => 'Bill not found']);
    exit();
}

$balance = (float)$bill['amount'] - (float)$bill['amount_paid'];
if ($amount > $balance) $amount = $balance;

$accountRef = 'BILL-' . $bill_id;
$result = mpesaStkPush($mpesa, $phone, $amount, $accountRef, 'Bill payment');

if (!$result['ok']) {
    echo json_encode(['ok' => false, 'error' => 'STK Push failed', 'raw' => $result]);
    exit();
}

// Save pending row so callback can match it
$account = 'OWNER-' . intval($bill['owners_id']);
$desc    = 'M-Pesa pending (cashier) — Bill #' . $bill_id;
$ref     = $result['CheckoutRequestID'];

$pending = mysqli_prepare($conn,
    "INSERT INTO transactions
     (account_number, transaction_date, description, payment_reference, payment_provider, amount, balance_after)
     VALUES (?, CURDATE(), ?, ?, 'mpesa_pending', 0, ?)"
);
mysqli_stmt_bind_param($pending, "sssd", $account, $desc, $ref, $balance);
mysqli_stmt_execute($pending);
mysqli_stmt_close($pending);

echo json_encode(['ok' => true, 'CheckoutRequestID' => $ref]);