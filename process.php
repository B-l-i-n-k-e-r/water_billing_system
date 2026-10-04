<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['username'], $_POST['password'])) {
    header("Location: index.php");
    exit();
}

$username = trim($_POST['username']);
$password = trim($_POST['password']);

if (empty($username) || empty($password)) {
    header("Location: index.php?err=1");
    exit();
}

// Fetch user with all fields we need
$stmt = mysqli_prepare($conn,
    "SELECT id, name, email, password, userlevel, verified 
     FROM user WHERE username = ? OR email = ? LIMIT 1"
);

if (!$stmt) {
    error_log("process.php prepare failed: " . mysqli_error($conn));
    header("Location: index.php?err=1");
    exit();
}

mysqli_stmt_bind_param($stmt, "ss", $username, $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$row = mysqli_fetch_assoc($result)) {
    // No such user
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    header("Location: index.php?err=1");
    exit();
}
mysqli_stmt_close($stmt);

$hashed_password = $row['password'];
$password_ok = password_verify($password, $hashed_password) || $password === $hashed_password;

if (!$password_ok) {
    mysqli_close($conn);
    header("Location: index.php?err=1");
    exit();
}

// ---------- Login successful ----------

// Re-hash plain-text legacy passwords
if ($password === $hashed_password) {
    $new_hash = password_hash($password, PASSWORD_DEFAULT);
    $update_stmt = mysqli_prepare($conn, "UPDATE user SET password = ? WHERE id = ?");
    if ($update_stmt) {
        mysqli_stmt_bind_param($update_stmt, "si", $new_hash, $row['id']);
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);
    }
}

// ---------- Check account verification ----------
$verified = isset($row['verified']) ? intval($row['verified']) : 1;

if ($verified === 0) {
    require_once 'otp_helper.php';
$rl = otpRateLimit($conn, $row['id'], 'verify');
if ($rl['allowed']) {
    $code = createOTP($conn, $row['id'], 'verify', 10);
    $body = otpEmailBody($row['name'] ?? 'User', $code, 'verify');
    @sendEmail($row['email'] ?? '', 'Verify Your NCWSC Account', $body, $row['name'] ?? '');
} else {
    error_log("OTP rate limited on login for user {$row['id']}");
}
    // Not verified — generate a fresh OTP and send them to otp.php
    require_once 'mailer.php';
    require_once 'otp_helper.php';
    require_once 'emails/otp_email.php';

    $code = createOTP($conn, $row['id'], 'verify', 10);
    $body = otpEmailBody($row['name'] ?? 'User', $code, 'verify');
    @sendEmail($row['email'] ?? '', 'Verify Your NCWSC Account', $body, $row['name'] ?? '');

    session_regenerate_id(true);
    $_SESSION['pending_user_id']    = $row['id'];
    $_SESSION['pending_user_email'] = $row['email'] ?? '';
    $_SESSION['pending_user_name']  = $row['name'] ?? 'User';

    mysqli_close($conn);
    header("Location: otp.php?resent=1");
    exit();
}

// ---------- Fully verified — log in ----------
session_regenerate_id(true);

$_SESSION['id']        = $row['id'];
$_SESSION['userlevel'] = intval($row['userlevel'] ?? 3);
$_SESSION['name']      = $row['name'] ?? '';
$_SESSION['email']     = $row['email'] ?? '';

mysqli_close($conn);

header("Location: dashboard.php");
exit();