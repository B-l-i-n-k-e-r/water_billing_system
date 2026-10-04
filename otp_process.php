<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'db.php';
include_once 'otp_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: otp.php");
    exit();
}

if (empty($_SESSION['pending_user_id'])) {
    header("Location: index.php");
    exit();
}

$otp  = trim($_POST['otp'] ?? '');
$uid  = intval($_SESSION['pending_user_id']);

if ($otp === '' || !preg_match('/^\d{6}$/', $otp)) {
    header("Location: otp.php?err=empty");
    exit();
}

if (verifyOTP($conn, $uid, $otp, 'verify')) {
    // Mark user verified
    $upd = mysqli_prepare($conn, "UPDATE user SET verified = 1, verified_at = NOW() WHERE id = ?");
    mysqli_stmt_bind_param($upd, "i", $uid);
    mysqli_stmt_execute($upd);
    mysqli_stmt_close($upd);

    // Send welcome email
    require_once 'mailer.php';
    require_once 'emails/welcome_email.php';
    $name  = $_SESSION['pending_user_name'] ?? 'User';
    $email = $_SESSION['pending_user_email'] ?? '';
    if ($email) {
        $body = welcomeEmailBody($name);
        sendEmail($email, 'Welcome to NCWSC Online Services', $body, $name);
    }

    // Clear pending session
    unset($_SESSION['pending_user_id'], $_SESSION['pending_user_email'], $_SESSION['pending_user_name']);

    header("Location: index.php?verified=1");
    exit();
} else {
    header("Location: otp.php?err=code");
    exit();
}