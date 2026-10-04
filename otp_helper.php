<?php
/**
 * OTP generation and verification helpers.
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
 * @return bool
 */
function verifyOTP($conn, $user_id, $code, $purpose = 'verify') {
    $stmt = mysqli_prepare($conn,
        "SELECT id, expires_at FROM otp_tokens
         WHERE user_id = ? AND code = ? AND purpose = ? AND used = 0
         ORDER BY id DESC LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "iss", $user_id, $code, $purpose);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if (!$row) return false;

    // Check expiry
    if (strtotime($row['expires_at']) < time()) return false;

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
 */
function otpRateLimit($conn, $user_id, $purpose = 'verify', $maxPerHour = 3) {
    // Count OTPs issued in the last hour
    $stmt = mysqli_prepare($conn,
        "SELECT COUNT(*) AS c, MAX(created_at) AS last_at 
         FROM otp_tokens 
         WHERE user_id = ? AND purpose = ? 
           AND created_at > (NOW() - INTERVAL 1 HOUR)"
    );
    mysqli_stmt_bind_param($stmt, "is", $user_id, $purpose);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    $count  = intval($row['c'] ?? 0);
    $lastAt = $row['last_at'] ?? null;

    // Cooldown: minimum 30 seconds between requests
    $cooldown = 30;
    if ($lastAt) {
        $secondsSince = time() - strtotime($lastAt);
        if ($secondsSince < $cooldown) {
            return ['allowed' => false, 'wait_seconds' => $cooldown - $secondsSince];
        }
    }

    // Max per hour
    if ($count >= $maxPerHour) {
        return ['allowed' => false, 'wait_seconds' => 3600 - (time() - strtotime($lastAt))];
    }

    return ['allowed' => true, 'wait_seconds' => 0];
}