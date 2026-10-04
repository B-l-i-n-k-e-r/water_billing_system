<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
include_once 'db.php';
require_once 'notify.php';

$user_id = intval($_SESSION['id']);
$account = trim($_POST['account_number'] ?? '');

if (empty($account)) {
    header("Location: addsewer_form.php?err=empty");
    exit();
}

$request_number = 'SEW-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

$stmt = mysqli_prepare($conn,
    "INSERT INTO sewer_requests (request_number, user_id, account_number) VALUES (?, ?, ?)"
);
mysqli_stmt_bind_param($stmt, "sis", $request_number, $user_id, $account);

if (mysqli_stmt_execute($stmt)) {
    $sewer_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    // Send confirmation email
    notifySewerReceived($conn, $sewer_id);

    $_SESSION['sewer_request_success'] = $request_number;
    header("Location: addsewer_form.php?success=1");
} else {
    mysqli_stmt_close($stmt);
    header("Location: addsewer_form.php?err=db");
}
exit();