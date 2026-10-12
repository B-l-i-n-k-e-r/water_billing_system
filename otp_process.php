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

$otp = trim($_POST['otp'] ?? '');
$uid = intval($_SESSION['pending_user_id']);

if ($otp === '' || !preg_match('/^\d{6}$/', $otp)) {
    header("Location: otp.php?err=empty");
    exit();
}

if (!verifyOTP($conn, $uid, $otp, 'verify')) {
    header("Location: otp.php?err=code");
    exit();
}

// ---- Mark user verified ----
$upd = mysqli_prepare($conn, "UPDATE user SET verified = 1, verified_at = NOW() WHERE id = ?");
mysqli_stmt_bind_param($upd, "i", $uid);
mysqli_stmt_execute($upd);
mysqli_stmt_close($upd);

// ---- Fetch the user's details ----
$usrStmt = mysqli_prepare($conn, "SELECT name, email, mobile FROM user WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($usrStmt, "i", $uid);
mysqli_stmt_execute($usrStmt);
$usrRes = mysqli_stmt_get_result($usrStmt);
$user_row = mysqli_fetch_assoc($usrRes) ?: [];
mysqli_stmt_close($usrStmt);

// ---- Auto-create owners record if missing ----
$ownerCheck = mysqli_prepare($conn, "SELECT id FROM owners WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($ownerCheck, "i", $uid);
mysqli_stmt_execute($ownerCheck);
mysqli_stmt_store_result($ownerCheck);

$newOwnerId = 0;

if (mysqli_stmt_num_rows($ownerCheck) === 0) {
    mysqli_stmt_close($ownerCheck);

    // Build owner fields from user data
    $fullName  = trim($user_row['name'] ?? '');
    $nameParts = preg_split('/\s+/', $fullName, 2);

    $owner_fname   = $nameParts[0] ?: 'Client';
    $owner_lname   = $nameParts[1] ?? '';
    $owner_address = 'Nairobi';
    $owner_contact = !empty($user_row['mobile']) ? $user_row['mobile'] : '0000000000';
    $owner_email   = $user_row['email'] ?? '';

    $insOwner = mysqli_prepare($conn,
        "INSERT INTO owners (user_id, fname, lname, mi, address, contact, email)
         VALUES (?, ?, ?, '', ?, ?, ?)"
    );
    mysqli_stmt_bind_param($insOwner, "isssss",
        $uid,
        $owner_fname,
        $owner_lname,
        $owner_address,
        $owner_contact,
        $owner_email
    );
    mysqli_stmt_execute($insOwner);
    $newOwnerId = mysqli_insert_id($conn);   // ← capture immediately after execute
    mysqli_stmt_close($insOwner);
} else {
    // Owner already exists — grab its ID for account-number generation
    mysqli_stmt_close($ownerCheck);

    $ownerIdStmt = mysqli_prepare($conn, "SELECT id FROM owners WHERE user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($ownerIdStmt, "i", $uid);
    mysqli_stmt_execute($ownerIdStmt);
    $ownerIdRes = mysqli_stmt_get_result($ownerIdStmt);
    $ownerIdRow = mysqli_fetch_assoc($ownerIdRes);
    $newOwnerId = intval($ownerIdRow['id'] ?? 0);
    mysqli_stmt_close($ownerIdStmt);
}

// ---- Auto-create the first customer_accounts row if missing ----
if ($newOwnerId > 0) {
    $acctCheck = mysqli_prepare($conn,
        "SELECT id FROM customer_accounts WHERE user_id = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($acctCheck, "i", $uid);
    mysqli_stmt_execute($acctCheck);
    mysqli_stmt_store_result($acctCheck);

    if (mysqli_stmt_num_rows($acctCheck) === 0) {
        mysqli_stmt_close($acctCheck);

        // Account number based on owners.id, padded to 7 digits (0000001, ...)
        $accountNumber = str_pad((string) $newOwnerId, 7, '0', STR_PAD_LEFT);

        $accStmt = mysqli_prepare($conn,
            "INSERT INTO customer_accounts
             (user_id, account_number, balance, latest_ebill)
             VALUES (?, ?, 0.00, 0.00)"
        );
        mysqli_stmt_bind_param($accStmt, "is", $uid, $accountNumber);
        mysqli_stmt_execute($accStmt);
        mysqli_stmt_close($accStmt);
    } else {
        mysqli_stmt_close($acctCheck);
    }
}

// ---- Welcome email ----
require_once 'mailer.php';
require_once 'emails/welcome_email.php';
$name  = $_SESSION['pending_user_name'] ?? 'User';
$email = $_SESSION['pending_user_email'] ?? '';
if ($email) {
    $body = welcomeEmailBody($name);
    sendEmail($email, 'Welcome to NCWSC Online Services', $body, $name);
}

// ---- Clear session + redirect ----
unset($_SESSION['pending_user_id'], $_SESSION['pending_user_email'], $_SESSION['pending_user_name']);

header("Location: login.php?verified=1");
exit();