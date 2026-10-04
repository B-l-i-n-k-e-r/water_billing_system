<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'db.php';

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

$user_id = intval($_SESSION['id']);
$account = trim($_POST['account_number'] ?? '');

if (empty($account)) {
    header("Location: selfcare.php?tab=add_account&err=empty");
    exit();
}

$stmt = mysqli_prepare($conn,
    "INSERT IGNORE INTO customer_accounts (user_id, account_number) VALUES (?, ?)"
);
mysqli_stmt_bind_param($stmt, "is", $user_id, $account);

if (mysqli_stmt_execute($stmt)) {
    header("Location: selfcare.php?tab=my_accounts&success=1");
} else {
    header("Location: selfcare.php?tab=add_account&err=db");
}
mysqli_stmt_close($stmt);
exit();