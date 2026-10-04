<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once 'auth.php';
include_once 'db.php';

$user_id = intval($_SESSION['id']);
$id = intval($_GET['id'] ?? 0);

$stmt = mysqli_prepare($conn, "DELETE FROM customer_accounts WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header("Location: selfcare.php?tab=my_accounts&removed=1");
exit();