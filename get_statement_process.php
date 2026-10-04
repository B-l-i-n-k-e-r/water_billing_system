<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'db.php';

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

$account   = trim($_POST['account_number'] ?? '');
$date_from = $_POST['date_from'] ?? '';
$date_to   = $_POST['date_to'] ?? '';
$mobile    = trim($_POST['mobile_number'] ?? '');

if (empty($account) || empty($date_from) || empty($date_to) || empty($mobile)) {
    header("Location: selfcare.php?tab=get_statement&err=empty");
    exit();
}

// Fetch transactions for the period
$stmt = mysqli_prepare($conn,
    "SELECT transaction_date, description, amount, balance_after 
     FROM transactions 
     WHERE account_number = ? AND transaction_date BETWEEN ? AND ?
     ORDER BY transaction_date ASC"
);
mysqli_stmt_bind_param($stmt, "sss", $account, $date_from, $date_to);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

// In a real app: generate PDF / email statement to $mobile
// For now: store in session and redirect to a printable view
$_SESSION['statement'] = [
    'account' => $account,
    'from'    => $date_from,
    'to'      => $date_to,
    'rows'    => []
];
while ($row = mysqli_fetch_assoc($res)) {
    $_SESSION['statement']['rows'][] = $row;
}
mysqli_stmt_close($stmt);

header("Location: selfcare.php?tab=get_statement&success=1");
exit();