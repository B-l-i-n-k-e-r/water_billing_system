<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
include_once 'db.php';

header('Content-Type: application/json');

$user_id = intval($_SESSION['id']);
$bill_id = intval($_GET['bill_id'] ?? 0);

if ($bill_id <= 0) {
    echo json_encode(['status' => 'unknown']);
    exit();
}

$stmt = mysqli_prepare($conn,
    "SELECT b.status FROM bill b
     INNER JOIN owners o ON o.id = b.owners_id
     WHERE b.id = ? AND o.user_id = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "ii", $bill_id, $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

echo json_encode(['status' => $row['status'] ?? 'unknown']);