<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'db.php';

// Log raw callback for debugging
$raw = file_get_contents('php://input');
error_log("MPESA CALLBACK: " . $raw);

$data = json_decode($raw, true);
$callback = $data['Body']['stkCallback'] ?? null;

if (!$callback) {
    http_response_code(400);
    echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback']);
    exit();
}

$checkoutId = $callback['CheckoutRequestID'] ?? '';
$resultCode = intval($callback['ResultCode'] ?? -1);

if ($resultCode !== 0) {
    // Payment failed or cancelled
    error_log("MPESA FAILED: {$checkoutId} — " . ($callback['ResultDesc'] ?? ''));
    echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    exit();
}

// Extract metadata
$items = $callback['CallbackMetadata']['Item'] ?? [];
$meta  = [];
foreach ($items as $item) {
    $meta[$item['Name']] = $item['Value'] ?? null;
}

$mpesaReceipt = $meta['MpesaReceiptNumber'] ?? '';
$paidAmount   = (float) ($meta['Amount'] ?? 0);
$phone        = $meta['PhoneNumber'] ?? '';

// Find the pending transaction
$stmt = mysqli_prepare($conn,
    "SELECT id, account_number, balance_after FROM transactions
     WHERE payment_reference = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "s", $checkoutId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$tx = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$tx) {
    error_log("MPESA: no pending txn for {$checkoutId}");
    echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    exit();
}

// Extract bill_id from account_number "OWNER-N"
$ownerId = intval(str_replace('OWNER-', '', $tx['account_number']));

// Find latest unpaid bill for this owner to apply the payment
$billStmt = mysqli_prepare($conn,
    "SELECT id, amount, amount_paid FROM bill
     WHERE owners_id = ? AND status != 'paid'
     ORDER BY id DESC LIMIT 1"
);
mysqli_stmt_bind_param($billStmt, "i", $ownerId);
mysqli_stmt_execute($billStmt);
$billRes = mysqli_stmt_get_result($billStmt);
$bill = mysqli_fetch_assoc($billRes);
mysqli_stmt_close($billStmt);

if (!$bill) {
    error_log("MPESA: no unpaid bill for owner {$ownerId}");
    echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    exit();
}

$existingPaid = (float)$bill['amount_paid'];
$billAmount   = (float)$bill['amount'];
$newPaid      = $existingPaid + $paidAmount;
$balance      = max(0, $billAmount - $newPaid);

if ($newPaid >= $billAmount) $newStatus = 'paid';
elseif ($newPaid > 0)        $newStatus = 'partial';
else                         $newStatus = 'unpaid';

// Update bill
$upd = mysqli_prepare($conn, "UPDATE bill SET amount_paid = ?, status = ? WHERE id = ?");
mysqli_stmt_bind_param($upd, "dsi", $newPaid, $newStatus, $bill['id']);
mysqli_stmt_execute($upd);
mysqli_stmt_close($upd);

// Update the pending transaction row → mark as mpesa completed
$ref = $mpesaReceipt ?: $checkoutId;
$desc = 'M-Pesa payment — Bill #' . $bill['id'] . ' (' . $phone . ')';
$negAmount = -1 * $paidAmount;

$final = mysqli_prepare($conn,
    "UPDATE transactions
     SET amount = ?, balance_after = ?, description = ?, payment_provider = 'mpesa', payment_reference = ?
     WHERE id = ?"
);
mysqli_stmt_bind_param($final, "ddssi", $negAmount, $balance, $desc, $ref, $tx['id']);
mysqli_stmt_execute($final);
mysqli_stmt_close($final);

error_log("MPESA SUCCESS: {$ref} — Bill #{$bill['id']} paid KES {$paidAmount}");

// Respond to Safaricom
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);