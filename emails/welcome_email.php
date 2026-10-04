<?php
function welcomeEmailBody($userName) {
    return emailTemplate('Welcome to NCWSC Online Services', "
        <p>Hello <strong>" . htmlspecialchars($userName) . "</strong>,</p>
        <p>Your account has been created successfully. You can now log in to:</p>
        <ul style=\"padding-left:20px;line-height:1.8;\">
            <li>Apply for new water or sewer connections</li>
            <li>Track your submitted applications</li>
            <li>Link your water accounts and check bills</li>
            <li>Apply for private exhauster permits</li>
        </ul>
        <p style=\"margin-top:24px;\">
            <a href=\"" . (require __DIR__ . '/../config.php')['app']['base_url'] . "/index.php\"
               style=\"background:#0066CC;color:#fff;text-decoration:none;padding:12px 24px;border-radius:6px;display:inline-block;\">
               Login Now
            </a>
        </p>
        <p style=\"color:#666;font-size:13px;margin-top:24px;\">For any enquiries, contact us at info@nairobiwater.co.ke or call +254703080000.</p>
    ");
}