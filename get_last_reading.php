<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';

header('Content-Type: application/json');

$owners_id = intval($_GET['owners_id'] ?? 0);
if ($owners_id <= 0) {
    echo json_encode(['prev' => null, 'error' => 'invalid_id']);
    exit();
}

$stmt = mysqli_prepare($conn,
    "SELECT pres FROM bill WHERE owners_id = ? ORDER BY id DESC LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "i", $owners_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

echo json_encode([
    'prev' => $row ? (float) $row['pres'] : null,
]);