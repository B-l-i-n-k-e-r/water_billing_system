<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1]);
include_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: adduser.php");
    exit();
}

$name       = trim($_POST['name'] ?? '');
$username   = trim($_POST['username'] ?? '');
$email      = trim($_POST['email'] ?? '');
$mobile     = trim($_POST['mobile'] ?? '');
$password   = $_POST['password'] ?? '';
$confirm    = $_POST['confirm'] ?? '';
$userlevel  = intval($_POST['userlevel'] ?? 3);

if ($name === '' || $username === '' || $password === '') {
    header("Location: adduser.php?err=empty");
    exit();
}

if ($password !== $confirm) {
    header("Location: adduser.php?err=match");
    exit();
}

if (!in_array($userlevel, [1, 2, 3])) {
    $userlevel = 3;
}

// Duplicate check
$check = mysqli_prepare($conn, "SELECT id FROM user WHERE username = ? OR (email != '' AND email = ?) LIMIT 1");
mysqli_stmt_bind_param($check, "ss", $username, $email);
mysqli_stmt_execute($check);
mysqli_stmt_store_result($check);
if (mysqli_stmt_num_rows($check) > 0) {
    mysqli_stmt_close($check);
    header("Location: adduser.php?err=exists");
    exit();
}
mysqli_stmt_close($check);

$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = mysqli_prepare($conn,
    "INSERT INTO user (name, username, email, mobile, password, userlevel) VALUES (?, ?, ?, ?, ?, ?)"
);
mysqli_stmt_bind_param($stmt, "sssssi", $name, $username, $email, $mobile, $hashed, $userlevel);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: user.php?success=1");
    exit();
} else {
    $err = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    error_log("adduserexec failed: " . $err);
    header("Location: adduser.php?err=db");
    exit();
}