<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1]);
include_once 'db.php';

$id = intval($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0 || $id === intval($_SESSION['id'])) {
    header("Location: user.php?err=self");
    exit();
}

$stmt = mysqli_prepare($conn, "DELETE FROM user WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: user.php?success=deleted");
    exit();
} else {
    $err = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    die("Delete failed: " . htmlspecialchars($err));
}