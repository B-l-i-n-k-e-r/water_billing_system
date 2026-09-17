<?php
include 'auth.php';
// Allows all authenticated users
checkLevel([1, 2, 3]); 

$level = isset($_SESSION['userlevel']) ? intval($_SESSION['userlevel']) : 3;

switch ($level) {
    case 1:
        header("Location: admin_dashboard.php");
        break;
    case 2:
        header("Location: cashier_dashboard.php");
        break;
    case 3:
    default:
        header("Location: staff_dashboard.php");
        break;
}
exit();
?>