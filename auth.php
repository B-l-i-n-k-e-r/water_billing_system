<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

/**
 * Enforce access permissions based on numeric userlevel.
 * Levels: 1 = Admin, 2 = Cashier, 3 = Staff
 */
if (!function_exists('checkLevel')) {
    function checkLevel($allowed_levels = []) {
        $current_level = isset($_SESSION['userlevel']) ? intval($_SESSION['userlevel']) : 3;

        if (!in_array($current_level, $allowed_levels)) {
            echo '<script>alert("Access Denied: You do not have permission to perform this action."); window.history.back();</script>';
            exit();
        }
    }
}