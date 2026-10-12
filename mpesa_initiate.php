<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
include_once 'db.php';
include_once 'mpesa_helper.php';

header('Content-Type: application/json');

$user_id = intval($_SESSION['id']);
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

// Verify ownership
$stmt = mysqli_prepare($conn,
    "SELECT b.* FROM bill b
     INNER JOIN owners o ON o.id = b.owners_id
     WHERE b.id = ? AND o.user_id = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "ii", $bill_id, $user_id);
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

// Save pending state so mpesa_callback can find it
$pending = mysqli_prepare($conn,
    "INSERT INTO transactions
     (account_number, transaction_date, description, payment_reference, payment_provider, amount, balance_after)
     VALUES (?, CURDATE(), ?, ?, 'mpesa_pending', 0, ?)"
);
$account = 'OWNER-' . intval($bill['owners_id']);
$desc    = 'M-Pesa pending — Bill #' . $bill_id;
$ref     = $result['CheckoutRequestID'];
mysqli_stmt_bind_param($pending, "sssd", $account, $desc, $ref, $balance);
mysqli_stmt_execute($pending);
$pending_id = mysqli_insert_id($pending);
mysqli_stmt_close($pending);

// Store the bill_id ↔ CheckoutRequestID mapping in session
$_SESSION['mpesa_pending'][$ref] = [
    'bill_id'    => $bill_id,
    'pending_id' => $pending_id,
    'amount'     => $amount,
];

echo json_encode(['ok' => true, 'CheckoutRequestID' => $ref]);