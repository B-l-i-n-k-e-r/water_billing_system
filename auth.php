<?php
if (session_status() === PHP_SESSION_NONE) {
    // Harden session cookie settings
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (($_SERVER['SERVER_PORT'] ?? 80) == 443);

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isSecure,      // only over HTTPS in prod
        'httponly' => true,           // JS can't read the cookie
        'samesite' => 'Lax',          // CSRF mitigation
    ]);

    session_start();
}

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Auto-logout after 60 minutes of inactivity
$timeout = 60 * 60;
if (isset($_SESSION['_last_activity']) && (time() - $_SESSION['_last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: index.php?timeout=1");
    exit();
}
$_SESSION['_last_activity'] = time();

/**
 * Enforce access permissions based on numeric userlevel.
 * Levels: 1 = Admin, 2 = Cashier, 3 = Staff/Client
 */
if (!function_exists('checkLevel')) {
    function checkLevel($allowed_levels = []) {
        $current_level = isset($_SESSION['userlevel']) ? intval($_SESSION['userlevel']) : 3;

        if (!in_array($current_level, $allowed_levels)) {
            http_response_code(403);
            echo '<div style="font-family:sans-serif;padding:40px;text-align:center;">'
               . '<h2>Access Denied</h2>'
               . '<p>You do not have permission to view this page.</p>'
               . '<a href="dashboard.php">Return to Dashboard</a>'
               . '</div>';
            exit();
        }
    }
}