<?php
include 'auth.php';
checkLevel([1, 2]);
session_start();
include 'db.php';

// Ensure request method is POST and ID is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    
    // Sanitize input as an integer
    $id = intval($_POST['id']);

    if ($id > 0) {
        // Use prepared statements to prevent SQL Injection
        $stmt = mysqli_prepare($conn, "DELETE FROM bill WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($success) {
            echo '<script>alert("Bill deleted successfully!"); window.location.href="bill.php";</script>';
        } else {
            echo '<script>alert("Error deleting record: ' . addslashes(mysqli_error($conn)) . '"); window.location.href="bill.php";</script>';
        }
    } else {
        echo '<script>alert("Invalid ID specified."); window.location.href="bill.php";</script>';
    }
    exit();
} else {
    header("Location: bill.php");
    exit();
}
?>