<?php
/**
 * Unified notification email body.
 *
 * @param string $name         Recipient name
 * @param string $heading      Email heading
 * @param string $reference    Reference number (application no, permit no, etc.)
 * @param string $detail       Short detail line
 * @param string $status       received | in_review | approved | rejected | completed
 * @param string $customUrl    Optional action URL (falls back to base_url default)
 */
function notificationEmail($name, $heading, $reference, $detail, $status = 'received', $customUrl = '') {

    $config = require __DIR__ . '/../config.php';
    $base   = rtrim($config['app']['base_url'], '/');

    // Status-specific messages and colors
    $meta = [
        'received'  => ['#0066CC', 'Received',        'We have received your submission and our team will review it shortly.'],
        'in_review' => ['#0066CC', 'In Review',       'Your submission is now being reviewed by our staff.'],
        'approved'  => ['#28a745', 'Approved',        'We are pleased to inform you that your submission has been approved.'],
        'rejected'  => ['#dc3545', 'Rejected',        'We regret to inform you that your submission could not be approved at this time.'],
        'completed' => ['#10b981', 'Completed',       'Your submission has been fully processed and completed.'],
    ];
    [$color, $statusLabel, $statusMessage] = $meta[$status] ?? $meta['received'];

    // Default action link
    $defaultUrl  = $base . '/index.php';
    $actionUrl   = $customUrl ?: $defaultUrl;
    $actionLabel = 'View on Portal';

    $safe = fn($v) => htmlspecialchars((string) $v);

    return emailTemplate($heading, "
        <p>Hello <strong>{$safe($name)}</strong>,</p>
        <p>{$statusMessage}</p>

        <div style=\"background:#f5f6f8;border-left:4px solid {$color};border-radius:6px;padding:16px;margin:20px 0;\">
            <p style=\"margin:0 0 6px;font-size:12px;color:#666;text-transform:uppercase;letter-spacing:0.5px;\">Status</p>
            <p style=\"margin:0 0 12px;font-weight:bold;color:{$color};\">{$statusLabel}</p>

            <p style=\"margin:0 0 6px;font-size:12px;color:#666;text-transform:uppercase;letter-spacing:0.5px;\">Reference</p>
            <p style=\"margin:0 0 12px;font-family:monospace;font-weight:bold;color:#1a1a1a;\">{$safe($reference)}</p>

            <p style=\"margin:0 0 6px;font-size:12px;color:#666;text-transform:uppercase;letter-spacing:0.5px;\">Details</p>
            <p style=\"margin:0;color:#333;\">{$safe($detail)}</p>
        </div>

        <p style=\"margin-top:24px;\">
            <a href=\"{$actionUrl}\"
               style=\"background:#0066CC;color:#fff;text-decoration:none;padding:12px 24px;border-radius:6px;display:inline-block;\">
               {$actionLabel}
            </a>
        </p>

        <p style=\"color:#666;font-size:13px;margin-top:24px;\">
            You can always check the current status of your submissions by logging into your NCWSC account.
        </p>
    ");
}