<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Force PHP timezone to Nairobi (EAT, UTC+3)
date_default_timezone_set('Africa/Nairobi');

$conn = mysqli_connect('127.0.0.1', 'root', '', 'waterbilling');

if (!$conn) {
    die('Could not connect: ' . mysqli_connect_error());
}

// Force MySQL session timezone to match PHP.
// This is critical for the OTP system: createOTP() writes expires_at using
// PHP's clock, and verifyOTP() compares with MySQL's NOW(). Without this,
// a 3-hour timezone gap makes fresh OTPs look already-expired.
mysqli_query($conn, "SET time_zone = '+03:00'");