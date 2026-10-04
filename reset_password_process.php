<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$token    = trim($_POST['token'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm'] ?? '';

if ($token === '' || $password === '' || $confirm === '') {
    header("Location: reset_password.php?token=" . urlencode($token) . "&err=empty");
    exit();
}

if ($password !== $confirm) {
    header("Location: reset_password.php?token=" . urlencode($token) . "&err=match");
    exit();
}

if (strlen($password) < 6) {
    header("Location: reset_password.php?token=" . urlencode($token) . "&err=short");
    exit();
}

// Validate token
$stmt = mysqli_prepare($conn,
    "SELECT id, user_id, expires_at, used FROM password_resets WHERE token = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "s", $token);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$row || intval($row['used']) === 1 || strtotime($row['expires_at']) < time()) {
    header("Location: reset_password.php?token=" . urlencode($token));
    exit();
}

// Update password
$hashed = password_hash($password, PASSWORD_DEFAULT);
$upd = mysqli_prepare($conn, "UPDATE user SET password = ? WHERE id = ?");
mysqli_stmt_bind_param($upd, "si", $hashed, $row['user_id']);

if (!mysqli_stmt_execute($upd)) {
    $err = mysqli_stmt_error($upd);
    mysqli_stmt_close($upd);
    die("Password update failed: " . htmlspecialchars($err));
}
mysqli_stmt_close($upd);

// Mark token as used
$mark = mysqli_prepare($conn, "UPDATE password_resets SET used = 1 WHERE id = ?");
mysqli_stmt_bind_param($mark, "i", $row['id']);
mysqli_stmt_execute($mark);
mysqli_stmt_close($mark);

mysqli_close($conn);

header("Location: index.php?reset=1");
exit();