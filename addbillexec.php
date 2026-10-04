<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';
include_once 'tariff.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: addbill.php");
    exit();
}

$owners_id  = intval($_POST['owners_id'] ?? 0);
$prev       = (float) ($_POST['prev'] ?? 0);
$pres       = (float) ($_POST['pres'] ?? 0);
$bill_month = trim($_POST['bill_month'] ?? currentBillMonth());

if ($owners_id <= 0 || $pres < $prev) {
    header("Location: addbill.php?err=invalid");
    exit();
}

$consumption = $pres - $prev;
$calc        = calculateBill($consumption, $conn);
$today       = date('Y-m-d');

$stmt = mysqli_prepare($conn,
    "INSERT INTO bill 
        (owners_id, prev, pres, consumption, price, amount, bill_month, date)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    die("Prepare failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "idddddss",
    $owners_id,
    $prev,
    $pres,
    $consumption,
    $calc['total'],
    $calc['total'],
    $bill_month,
    $today
);

if (mysqli_stmt_execute($stmt)) {
    $bill_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    // Also log to transactions (for Self Care view)
    $txn = mysqli_prepare($conn,
        "INSERT INTO transactions (account_number, transaction_date, description, amount, balance_after)
         VALUES (?, ?, ?, ?, ?)"
    );
    if ($txn) {
        $account = 'OWNER-' . $owners_id;
        $desc = 'Water bill ' . $bill_month;
        $bal  = $calc['total'];
        mysqli_stmt_bind_param($txn, "sssdd", $account, $today, $desc, $calc['total'], $bal);
        mysqli_stmt_execute($txn);
        mysqli_stmt_close($txn);
    }

    header("Location: viewbill.php?id=" . $bill_id . "&created=1");
    exit();
} else {
    $err = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    die("Insert failed: " . htmlspecialchars($err));
}