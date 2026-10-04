<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: clients.php");
    exit();
}

// Block delete if client has unpaid bills
$check = mysqli_prepare($conn,
    "SELECT COUNT(*) AS c FROM bill WHERE owners_id = ? AND status != 'paid'"
);
mysqli_stmt_bind_param($check, "i", $id);
mysqli_stmt_execute($check);
$res = mysqli_stmt_get_result($check);
$count = intval(mysqli_fetch_assoc($res)['c']);
mysqli_stmt_close($check);

if ($count > 0) {
    header("Location: clients.php?err=has_unpaid");
    exit();
}

// Safe to delete — also delete their bills and transactions
mysqli_query($conn, "DELETE FROM bill WHERE owners_id = " . intval($id));
mysqli_query($conn, "DELETE FROM transactions WHERE account_number = 'OWNER-" . intval($id) . "'");

$stmt = mysqli_prepare($conn, "DELETE FROM owners WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: clients.php?success=deleted");
    exit();
} else {
    $err = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    die("Delete failed: " . htmlspecialchars($err));
}