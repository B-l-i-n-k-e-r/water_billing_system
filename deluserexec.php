<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'auth.php';
checkLevel([1]); // Restricted to Admin

$logged_in_user_id = $_SESSION['id'] ?? $_SESSION['SESS_MEMBER_ID'] ?? null;

if (!$logged_in_user_id) {
    header("Location: index.php");
    exit();
}

include_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $logged_in_user_id = intval($logged_in_user_id);

    // Prevent user from deleting their own active session
    if ($id === $logged_in_user_id) {
        header("Location: user.php?status=self_delete_error");
        exit();
    }

    if ($id > 0) {
        // Delete record from user table
        $stmt = mysqli_prepare($conn, "DELETE FROM user WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($success) {
            header("Location: user.php?status=deleted");
        } else {
            header("Location: user.php?status=error");
        }
    } else {
        header("Location: user.php?status=invalid_id");
    }
    exit();
} else {
    header("Location: user.php");
    exit();
}