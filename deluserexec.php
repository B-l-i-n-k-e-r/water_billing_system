<?php
include 'auth.php';
checkLevel([1]);
session_start();

// Authentication check
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $logged_in_user_id = intval($_SESSION['id']);

    // Prevent user from deleting their own active session
    if ($id === $logged_in_user_id) {
        echo '<script>alert("You cannot delete your own logged-in account!"); window.location.href="user.php";</script>';
        exit();
    }

    if ($id > 0) {
        // Delete record from user table
        $stmt = mysqli_prepare($conn, "DELETE FROM user WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($success) {
            echo '<script>alert("User deleted successfully."); window.location.href="user.php";</script>';
        } else {
            echo '<script>alert("Error deleting user: ' . addslashes(mysqli_error($conn)) . '"); window.location.href="user.php";</script>';
        }
    } else {
        echo '<script>alert("Invalid user ID."); window.location.href="user.php";</script>';
    }
    exit();
} else {
    header("Location: user.php");
    exit();
}
?>