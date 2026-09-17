<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'auth.php';

// Allows access for Admin (1), Cashier (2), and Staff/Manager (3)
checkLevel([1, 2, 3]);

// Retrieve user privilege level securely from session
$level = isset($_SESSION['userlevel']) ? intval($_SESSION['userlevel']) : 3;

// Route user to their corresponding dashboard
switch ($level) {
    case 1:
        header("Location: admin_dashboard.php");
        exit();
        
    case 2:
        header("Location: cashier_dashboard.php");
        exit();

    case 3:
    default:
        header("Location: staff_dashboard.php");
        exit();
}
?>