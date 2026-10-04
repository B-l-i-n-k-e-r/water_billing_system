<?php
function applicationEmailBody($userName, $type, $refNumber, $status = 'received') {
    $labels = [
        'received'   => 'Application Received',
        'in_review'  => 'Application In Review',
        'approved'   => 'Application Approved',
        'rejected'   => 'Application Update',
        'completed'  => 'Application Completed',
    ];
    $title = $labels[$status] ?? 'Application Update';

    $messages = [
        'received'  => 'We have received your application. Our team will review it shortly.',
        'in_review' => 'Your application is now being reviewed by our staff.',
        'approved'  => 'We are pleased to inform you that your application has been approved.',
        'rejected'  => 'We regret to inform you that your application could not be approved at this time.',
        'completed' => 'Your application has been fully processed and completed.',
    ];
    $message = $messages[$status] ?? 'Your application status has been updated.';

    $color = match($status) {
        'approved', 'completed' => '#28a745',
        'rejected'              => '#dc3545',
        'in_review'             => '#0066CC',
        default                 => '#666',
    };

    return emailTemplate($title, "
        <p>Hello <strong>" . htmlspecialchars($userName) . "</strong>,</p>
        <p>{$message}</p>
        <div style=\"background:#f5f6f8;border-left:4px solid {$color};border-radius:6px;padding:16px;margin:20px 0;\">
            <p style=\"margin:0 0 6px;font-size:13px;color:#666;\">Reference</p>
            <p style=\"margin:0;font-family:monospace;font-weight:bold;color:#1a1a1a;\">" . htmlspecialchars($refNumber) . "</p>
            <p style=\"margin:12px 0 0;font-size:13px;color:#666;\">Type</p>
            <p style=\"margin:0;font-weight:bold;\">" . htmlspecialchars($type) . "</p>
        </div>
        <p style=\"margin-top:24px;\">
            <a href=\"" . (require __DIR__ . '/../config.php')['app']['base_url'] . "/track_application.php\"
               style=\"background:#0066CC;color:#fff;text-decoration:none;padding:12px 24px;border-radius:6px;display:inline-block;\">
               View Details
            </a>
        </p>
    ");
}