<?php
/**
 * OTP generation and verification helpers.
 * All time comparisons are done inside MySQL to avoid PHP/DB timezone drift.
 */

/**
 * Generate a random 6-digit OTP code.
 */
function generateOTP() {
    return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Create and store a new OTP for a user.
 *
 * @param mysqli $conn
 * @param int    $user_id
 * @param string $purpose 'verify' or 'reset'
 * @param int    $ttlMinutes How long until it expires
 * @return string The generated code
 */
function createOTP($conn, $user_id, $purpose = 'verify', $ttlMinutes = 10) {
    // Invalidate any existing unused OTPs for this user + purpose
    $clear = mysqli_prepare($conn,
        "UPDATE otp_tokens SET used = 1 WHERE user_id = ? AND purpose = ? AND used = 0"
    );
    if ($clear) {
        mysqli_stmt_bind_param($clear, "is", $user_id, $purpose);
        mysqli_stmt_execute($clear);
        mysqli_stmt_close($clear);
    }

    $code    = generateOTP();
    $expires = date('Y-m-d H:i:s', time() + ($ttlMinutes * 60));

    $stmt = mysqli_prepare($conn,
        "INSERT INTO otp_tokens (user_id, code, purpose, expires_at) VALUES (?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "isss", $user_id, $code, $purpose, $expires);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $code;
}

/**
 * Verify an OTP code. Marks it as used on success.
 *
 * The expiry check is done inside MySQL (`expires_at > NOW()`) so both
 * timestamps use the same clock — no PHP/DB timezone drift can cause a
 * valid OTP to look expired.
 *
 * @return bool
 */
function verifyOTP($conn, $user_id, $code, $purpose = 'verify') {
    $stmt = mysqli_prepare($conn,
        "SELECT id FROM otp_tokens
         WHERE user_id = ? AND code = ? AND purpose = ? AND used = 0
           AND expires_at > NOW()
         ORDER BY id DESC LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "iss", $user_id, $code, $purpose);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if (!$row) return false;

    // Mark as used
    $upd = mysqli_prepare($conn, "UPDATE otp_tokens SET used = 1 WHERE id = ?");
    mysqli_stmt_bind_param($upd, "i", $row['id']);
    mysqli_stmt_execute($upd);
    mysqli_stmt_close($upd);

    return true;
}

/**
 * Check whether a user has requested too many OTPs recently.
 * Returns [allowed: bool, wait_seconds: int].
 *
 * All time math is done inside MySQL so it's immune to PHP vs DB timezone drift.
 */
function otpRateLimit($conn, $user_id, $purpose = 'verify', $maxPerHour = 3) {
    // TIMESTAMPDIFF + NOW() run on the same clock — no cross-system drift
    $stmt = mysqli_prepare($conn,
        "SELECT
            COUNT(*) AS c,
            COALESCE(TIMESTAMPDIFF(SECOND, MAX(created_at), NOW()), 999999) AS seconds_since_last
         FROM otp_tokens
         WHERE user_id = ? AND purpose = ?
           AND created_at > (NOW() - INTERVAL 1 HOUR)"
    );
    mysqli_stmt_bind_param($stmt, "is", $user_id, $purpose);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    $count      = intval($row['c'] ?? 0);
    $secondsAgo = intval($row['seconds_since_last'] ?? 999999);

    // Cooldown: minimum 30 seconds between requests
    $cooldown = 30;
    if ($secondsAgo < $cooldown) {
        return ['allowed' => false, 'wait_seconds' => $cooldown - $secondsAgo];
    }

    // Max per hour
    if ($count >= $maxPerHour) {
        $wait = max(0, 3600 - $secondsAgo);
        return ['allowed' => false, 'wait_seconds' => $wait];
    }

    return ['allowed' => true, 'wait_seconds' => 0];
}