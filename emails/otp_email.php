<?php
/**
 * Build the HTML body for an OTP email.
 * Expects $userName and $code in scope.
 */
function otpEmailBody($userName, $code, $purpose = 'verify') {
    $title = $purpose === 'reset' ? 'Password Reset Verification' : 'Verify Your Account';
    $actionText = $purpose === 'reset'
        ? 'use this code to reset your password'
        : 'enter this code to verify your account';

    return emailTemplate($title, "
        <p>Hello <strong>" . htmlspecialchars($userName) . "</strong>,</p>
        <p>Please {$actionText}:</p>
        <div style=\"text-align:center;margin:24px 0;\">
            <div style=\"display:inline-block;background:#f0f7ff;border:2px dashed #0066CC;border-radius:8px;padding:16px 32px;\">
                <span style=\"font-size:32px;font-weight:bold;letter-spacing:8px;color:#0066CC;\">{$code}</span>
            </div>
        </div>
        <p style=\"color:#666;font-size:13px;\">This code will expire in <strong>10 minutes</strong>. If you didn't request this, you can safely ignore this email.</p>
    ");
}