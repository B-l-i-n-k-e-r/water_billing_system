<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1]);
include_once 'db.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: user.php");
    exit();
}

if ($id === intval($_SESSION['id'])) {
    header("Location: user.php?err=self");
    exit();
}

// Check the user exists
$stmt = mysqli_prepare($conn, "SELECT id FROM user WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$exists = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$exists) {
    header("Location: user.php?err=notfound");
    exit();
}

// Perform delete
$del = mysqli_prepare($conn, "DELETE FROM user WHERE id = ?");
mysqli_stmt_bind_param($del, "i", $id);

if (mysqli_stmt_execute($del)) {
    mysqli_stmt_close($del);
    header("Location: user.php?success=deleted");
    exit();
} else {
    $err = mysqli_stmt_error($del);
    mysqli_stmt_close($del);
    die("Delete failed: " . htmlspecialchars($err));
}