<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1]);
include_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: user.php");
    exit();
}

$id         = intval($_POST['id'] ?? 0);
$name       = trim($_POST['name'] ?? '');
$username   = trim($_POST['username'] ?? '');
$email      = trim($_POST['email'] ?? '');
$mobile     = trim($_POST['mobile'] ?? '');
$password   = $_POST['password'] ?? '';
$confirm    = $_POST['confirm'] ?? '';
$userlevel  = intval($_POST['userlevel'] ?? 3);

if ($id <= 0 || $name === '' || $username === '') {
    header("Location: edituser.php?id=$id&err=empty");
    exit();
}

if ($password !== '' && $password !== $confirm) {
    header("Location: edituser.php?id=$id&err=match");
    exit();
}

if (!in_array($userlevel, [1, 2, 3])) {
    $userlevel = 3;
}

// Duplicate check excluding self
$check = mysqli_prepare($conn,
    "SELECT id FROM user WHERE (username = ? OR (email != '' AND email = ?)) AND id != ? LIMIT 1"
);
mysqli_stmt_bind_param($check, "ssi", $username, $email, $id);
mysqli_stmt_execute($check);
mysqli_stmt_store_result($check);
if (mysqli_stmt_num_rows($check) > 0) {
    mysqli_stmt_close($check);
    header("Location: edituser.php?id=$id&err=exists");
    exit();
}
mysqli_stmt_close($check);

if ($password !== '') {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn,
        "UPDATE user SET name = ?, username = ?, email = ?, mobile = ?, userlevel = ?, password = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "ssssisi", $name, $username, $email, $mobile, $userlevel, $hashed, $id);
} else {
    $stmt = mysqli_prepare($conn,
        "UPDATE user SET name = ?, username = ?, email = ?, mobile = ?, userlevel = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "ssssii", $name, $username, $email, $mobile, $userlevel, $id);
}

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: user.php?success=1");
    exit();
} else {
    $err = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    error_log("edituserexec failed: " . $err);
    header("Location: edituser.php?id=$id&err=db");
    exit();
}