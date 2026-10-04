<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';
require_once 'notify.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_exhauster.php");
    exit();
}

$id     = intval($_POST['id'] ?? 0);
$status = trim($_POST['status'] ?? '');

if ($id <= 0 || !in_array($status, ['pending','in_review','approved','rejected'])) {
    header("Location: admin_exhauster.php");
    exit();
}

$stmt = mysqli_prepare($conn, "UPDATE exhauster_permits SET status = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, "si", $status, $id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);

    // Notify client
    notifyExhausterStatus($conn, $id, $status);

    header("Location: admin_exhauster.php?success=updated");
    exit();
} else {
    $err = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    die("Update failed: " . htmlspecialchars($err));
}