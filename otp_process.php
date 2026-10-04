<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Placeholder: In real implementation, compare $_POST['otp'] against DB-stored OTP
$otp = trim($_POST['otp'] ?? '');

if ($otp === '123456' || !empty($otp)) {
    // On success, redirect to login
    header("Location: index.php?otp=success");
    exit();
} else {
    header("Location: otp.php?err=1");
    exit();
}