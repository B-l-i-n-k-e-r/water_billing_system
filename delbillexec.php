<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'auth.php';
checkLevel([1, 2]); // Restricted to Admin & Staff

include_once 'db.php';

// Ensure request method is POST and ID is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    
    // Sanitize input as an integer
    $id = intval($_POST['id']);

    if ($id > 0) {
        // Use prepared statements to prevent SQL Injection
        $stmt = mysqli_prepare($conn, "DELETE FROM bill WHERE id = ?");
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            $success = mysqli_stmt_execute($stmt);
            
            if ($success) {
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                echo '<script>alert("Bill deleted successfully!"); window.location.href="bill.php";</script>';
                exit();
            } else {
                $error = mysqli_stmt_error($stmt);
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                echo '<script>alert("Error deleting record: ' . addslashes($error) . '"); window.location.href="bill.php";</script>';
                exit();
            }
        } else {
            $error = mysqli_error($conn);
            mysqli_close($conn);
            echo '<script>alert("Database error: ' . addslashes($error) . '"); window.location.href="bill.php";</script>';
            exit();
        }
    } else {
        mysqli_close($conn);
        echo '<script>alert("Invalid ID specified."); window.location.href="bill.php";</script>';
        exit();
    }
} else {
    if (isset($conn)) {
        mysqli_close($conn);
    }
    header("Location: bill.php");
    exit();
}
?>