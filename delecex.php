<?php
include 'auth.php';
checkLevel([1, 2, 3]);
session_start();
include 'db.php';

// Ensure request method is POST and ID is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    
    // Sanitize input as an integer
    $id = intval($_POST['id']);

    if ($id > 0) {
        // 1. Delete owner record using prepared statements
        $stmt1 = mysqli_prepare($conn, "DELETE FROM owners WHERE id = ?");
        mysqli_stmt_bind_param($stmt1, "i", $id);
        $success = mysqli_stmt_execute($stmt1);
        mysqli_stmt_close($stmt1);

        // 2. Clean up associated temporary bill record if present
        $stmt2 = mysqli_prepare($conn, "DELETE FROM tempo_bill WHERE id = ?");
        mysqli_stmt_bind_param($stmt2, "i", $id);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);

        if ($success) {
            echo '<script>alert("Client deleted successfully!"); window.location.href="clients.php";</script>';
        } else {
            echo '<script>alert("Error deleting client: ' . addslashes(mysqli_error($conn)) . '"); window.location.href="clients.php";</script>';
        }
    } else {
        echo '<script>alert("Invalid ID specified."); window.location.href="clients.php";</script>';
    }
    exit();
} else {
    header("Location: clients.php");
    exit();
}
?>