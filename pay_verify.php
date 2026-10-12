<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
include_once 'db.php';

header('Content-Type: application/json');

$user_id   = intval($_SESSION['id']);
$reference = trim($_POST['reference'] ?? '');
$bill_id   = intval($_POST['bill_id'] ?? 0);

if ($reference === '' || $bill_id <= 0) {
    echo json_encode(['ok' => false, 'error' => 'Missing reference or bill']);
    exit();
}

$config = require __DIR__ . '/config.php';
$secret = $config['paystack']['secret_key'] ?? '';
if (empty($secret)) {
    echo json_encode(['ok' => false, 'error' => 'Gateway not configured']);
    exit();
}

// Idempotency check
$chk = mysqli_prepare($conn, "SELECT id FROM transactions WHERE payment_reference = ? LIMIT 1");
mysqli_stmt_bind_param($chk, "s", $reference);
mysqli_stmt_execute($chk);
mysqli_stmt_store_result($chk);
if (mysqli_stmt_num_rows($chk) > 0) {
    mysqli_stmt_close($chk);
    echo json_encode(['ok' => true, 'reference' => $reference, 'note' => 'already recorded']);
    exit();
}
mysqli_stmt_close($chk);

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
    echo json_encode(['ok' => false, 'error' => 'Bill not found for your account']);
    exit();
}

// Verify with Paystack
$ch = curl_init('https://api.paystack.co/transaction/verify/' . rawurlencode($reference));
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $secret],
    CURLOPT_TIMEOUT        => 20,
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($code !== 200 || !$resp) {
    echo json_encode(['ok' => false, 'error' => 'Verification request failed']);
    exit();
}

$json = json_decode($resp, true);
if (empty($json['status']) || ($json['data']['status'] ?? '') !== 'success') {
    echo json_encode(['ok' => false, 'error' => 'Payment not successful at gateway']);
    exit();
}

$paidKes = intval($json['data']['amount']) / 100;

$existingPaid = (float) ($bill['amount_paid'] ?? 0);
$billAmount   = (float) ($bill['amount'] ?? 0);
$newPaid      = $existingPaid + $paidKes;
$balance      = max(0, $billAmount - $newPaid);

if ($newPaid >= $billAmount) $newStatus = 'paid';
elseif ($newPaid > 0)        $newStatus = 'partial';
else                         $newStatus = 'unpaid';

$upd = mysqli_prepare($conn, "UPDATE bill SET amount_paid = ?, status = ? WHERE id = ?");
mysqli_stmt_bind_param($upd, "dsi", $newPaid, $newStatus, $bill_id);
mysqli_stmt_execute($upd);
mysqli_stmt_close($upd);

$account   = 'OWNER-' . intval($bill['owners_id']);
$desc      = 'Online payment (' . ($json['data']['channel'] ?? 'card') . ') — Bill #' . $bill_id;
$negAmount = -1 * $paidKes;
$today     = date('Y-m-d');

$txn = mysqli_prepare($conn,
    "INSERT INTO transactions
     (account_number, transaction_date, description, payment_reference, payment_provider, amount, balance_after)
     VALUES (?, ?, ?, ?, 'paystack', ?, ?)"
);
mysqli_stmt_bind_param($txn, "ssssdd", $account, $today, $desc, $reference, $negAmount, $balance);
mysqli_stmt_execute($txn);
mysqli_stmt_close($txn);

echo json_encode(['ok' => true, 'reference' => $reference, 'amount' => $paidKes, 'balance' => $balance]);