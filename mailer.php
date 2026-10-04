<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Send an email using PHPMailer + SMTP.
 *
 * @param string $to       Recipient email
 * @param string $subject  Subject line
 * @param string $htmlBody HTML content
 * @param string $toName   Optional recipient name
 * @return array{ok:bool, error?:string}
 */
function sendEmail($to, $subject, $htmlBody, $toName = '') {
    $config = require __DIR__ . '/config.php';
    $smtp   = $config['smtp'];
    $app    = $config['app'];

    $mail = new PHPMailer(true);

    try {
        // SMTP transport
        $mail->isSMTP();
        $mail->Host       = $smtp['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp['username'];
        $mail->Password   = str_replace(' ', '', $smtp['password']); // strip spaces
        $mail->SMTPSecure = $smtp['encryption'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int) $smtp['port'];
        $mail->CharSet    = 'UTF-8';

        // Debug (only if enabled)
        if (!empty($app['debug'])) {
            $mail->SMTPDebug = 0; // Set to SMTP::DEBUG_SERVER for verbose logging
        }

        // From / To
        $mail->setFrom($smtp['from_email'], $smtp['from_name']);
        $mail->addAddress($to, $toName ?: $to);
        $mail->addReplyTo($smtp['from_email'], $smtp['from_name']);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);

        $mail->send();
        return ['ok' => true];

    } catch (Exception $e) {
        error_log("Mail error to {$to}: " . $mail->ErrorInfo);
        return ['ok' => false, 'error' => $mail->ErrorInfo];
    }
}

/**
 * Wrap an email body in a consistent NCWSC template.
 */
function emailTemplate($title, $bodyHtml) {
    $year = date('Y');
    return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f5f6f8;font-family:Arial,Helvetica,sans-serif;color:#333;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f6f8;padding:24px 0;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.06);">
        <tr>
          <td style="background:#0066CC;padding:20px 28px;color:#fff;">
            <h1 style="margin:0;font-size:20px;">NCWSC Online Services</h1>
            <p style="margin:4px 0 0;font-size:12px;color:#cce0f5;">Nairobi City Water and Sewerage Company</p>
          </td>
        </tr>
        <tr>
          <td style="padding:28px;">
            <h2 style="margin:0 0 16px;font-size:18px;color:#1a1a1a;">{$title}</h2>
            {$bodyHtml}
          </td>
        </tr>
        <tr>
          <td style="padding:16px 28px;background:#f5f6f8;font-size:11px;color:#777;text-align:center;">
            &copy; {$year} Nairobi City Water and Sewerage Company.
            <br>This is an automated message — please do not reply directly.
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
}