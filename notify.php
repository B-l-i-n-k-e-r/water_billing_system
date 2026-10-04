<?php
/**
 * Notification helpers — send emails when key events happen.
 * Each function is safe to call; failures are logged but never fatal.
 */

require_once __DIR__ . '/mailer.php';

/**
 * Get user info by ID.
 */
function getUserInfo($conn, $user_id) {
    $stmt = mysqli_prepare($conn, "SELECT id, name, email FROM user WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row ?: null;
}

/**
 * Fetch an application with owner info.
 */
function getApplication($conn, $app_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT a.*, u.name AS user_name, u.email AS user_email
         FROM applications a LEFT JOIN user u ON u.id = a.user_id
         WHERE a.id = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "i", $app_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row ?: null;
}

/**
 * Fetch a sewer request with owner info.
 */
function getSewerRequest($conn, $id) {
    $stmt = mysqli_prepare($conn,
        "SELECT sr.*, u.name AS user_name, u.email AS user_email
         FROM sewer_requests sr LEFT JOIN user u ON u.id = sr.user_id
         WHERE sr.id = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row ?: null;
}

/**
 * Fetch an exhauster permit with owner info.
 */
function getExhausterPermit($conn, $id) {
    $stmt = mysqli_prepare($conn,
        "SELECT ep.*, u.name AS user_name, u.email AS user_email
         FROM exhauster_permits ep LEFT JOIN user u ON u.id = ep.user_id
         WHERE ep.id = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row ?: null;
}

/* ------------------------------------------------------------------
   APPLICATION NOTIFICATIONS
------------------------------------------------------------------ */

/**
 * Send "Application Received" email.
 */
function notifyApplicationReceived($conn, $app_id) {
    require_once __DIR__ . '/emails/notification_email.php';
    $a = getApplication($conn, $app_id);
    if (!$a || empty($a['email'])) return false;

    $body = notificationEmail(
        $a['full_name'] ?: ($a['user_name'] ?? 'Applicant'),
        'Water / Sewer Application Received',
        $a['application_number'],
        ucfirst($a['application_type']) . ' · ' . ucfirst($a['service_type']) . ' connection',
        'received',
        'https://' // will be replaced inside template using config base_url
    );
    $mail = sendEmail($a['email'], 'NCWSC: Application Received — ' . $a['application_number'], $body, $a['full_name'] ?? '');
    if (!$mail['ok']) error_log("notifyApplicationReceived failed: " . ($mail['error'] ?? ''));
    return $mail['ok'];
}

/**
 * Send "Application Status Update" email.
 */
function notifyApplicationStatus($conn, $app_id, $new_status) {
    require_once __DIR__ . '/emails/notification_email.php';
    $a = getApplication($conn, $app_id);
    if (!$a || empty($a['email'])) return false;

    $body = notificationEmail(
        $a['full_name'] ?: ($a['user_name'] ?? 'Applicant'),
        'Water / Sewer Application Update',
        $a['application_number'],
        ucfirst($a['application_type']) . ' · ' . ucfirst($a['service_type']) . ' connection',
        $new_status,
        ''  // link auto-filled by template
    );
    $mail = sendEmail($a['email'], 'NCWSC: Application ' . ucwords(str_replace('_',' ', $new_status)) . ' — ' . $a['application_number'], $body, $a['full_name'] ?? '');
    if (!$mail['ok']) error_log("notifyApplicationStatus failed: " . ($mail['error'] ?? ''));
    return $mail['ok'];
}

/* ------------------------------------------------------------------
   SEWER REQUEST NOTIFICATIONS
------------------------------------------------------------------ */

function notifySewerReceived($conn, $id) {
    require_once __DIR__ . '/emails/notification_email.php';
    $r = getSewerRequest($conn, $id);
    if (!$r) return false;

    // Look up email via user_id
    $u = getUserInfo($conn, $r['user_id']);
    if (!$u || empty($u['email'])) return false;

    $body = notificationEmail(
        $u['name'] ?? 'Customer',
        'Sewer Connection Request Received',
        $r['request_number'],
        'Sewer connection · Account ' . $r['account_number'],
        'received',
        ''
    );
    $mail = sendEmail($u['email'], 'NCWSC: Sewer Request Received — ' . $r['request_number'], $body, $u['name'] ?? '');
    if (!$mail['ok']) error_log("notifySewerReceived failed: " . ($mail['error'] ?? ''));
    return $mail['ok'];
}

function notifySewerStatus($conn, $id, $new_status) {
    require_once __DIR__ . '/emails/notification_email.php';
    $r = getSewerRequest($conn, $id);
    if (!$r) return false;

    $u = getUserInfo($conn, $r['user_id']);
    if (!$u || empty($u['email'])) return false;

    $body = notificationEmail(
        $u['name'] ?? 'Customer',
        'Sewer Connection Request Update',
        $r['request_number'],
        'Sewer connection · Account ' . $r['account_number'],
        $new_status,
        ''
    );
    $mail = sendEmail($u['email'], 'NCWSC: Sewer Request ' . ucwords(str_replace('_',' ', $new_status)) . ' — ' . $r['request_number'], $body, $u['name'] ?? '');
    if (!$mail['ok']) error_log("notifySewerStatus failed: " . ($mail['error'] ?? ''));
    return $mail['ok'];
}

/* ------------------------------------------------------------------
   EXHAUSTER PERMIT NOTIFICATIONS
------------------------------------------------------------------ */

function notifyExhausterReceived($conn, $id) {
    require_once __DIR__ . '/emails/notification_email.php';
    $p = getExhausterPermit($conn, $id);
    if (!$p || empty($p['email'])) return false;

    $body = notificationEmail(
        $p['full_name'] ?: ($p['user_name'] ?? 'Applicant'),
        'Exhauster Permit Application Received',
        $p['permit_number'],
        'Vehicle ' . ($p['vehicle_registration'] ?: '—') . ' · ' . ($p['vehicle_capacity'] ? $p['vehicle_capacity'] . ' L' : ''),
        'received',
        ''
    );
    $mail = sendEmail($p['email'], 'NCWSC: Permit Application Received — ' . $p['permit_number'], $body, $p['full_name'] ?? '');
    if (!$mail['ok']) error_log("notifyExhausterReceived failed: " . ($mail['error'] ?? ''));
    return $mail['ok'];
}

function notifyExhausterStatus($conn, $id, $new_status) {
    require_once __DIR__ . '/emails/notification_email.php';
    $p = getExhausterPermit($conn, $id);
    if (!$p || empty($p['email'])) return false;

    $body = notificationEmail(
        $p['full_name'] ?: ($p['user_name'] ?? 'Applicant'),
        'Exhauster Permit Update',
        $p['permit_number'],
        'Vehicle ' . ($p['vehicle_registration'] ?: '—') . ' · ' . ($p['vehicle_capacity'] ? $p['vehicle_capacity'] . ' L' : ''),
        $new_status,
        ''
    );
    $mail = sendEmail($p['email'], 'NCWSC: Permit ' . ucwords(str_replace('_',' ', $new_status)) . ' — ' . $p['permit_number'], $body, $p['full_name'] ?? '');
    if (!$mail['ok']) error_log("notifyExhausterStatus failed: " . ($mail['error'] ?? ''));
    return $mail['ok'];
}