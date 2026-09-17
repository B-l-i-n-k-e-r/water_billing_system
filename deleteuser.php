<?php
// Always initialize the session first before accessing $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'auth.php';
checkLevel([1]); // Restricted to Admin

// Verify active session membership
$session_id = $_SESSION['id'] ?? $_SESSION['SESS_MEMBER_ID'] ?? null;

if (!$session_id) {
    header("Location: index.php");
    exit();
}

include_once 'db.php';

$session = intval($session_id);

if ($session > 0) {
    // Delete all users EXCEPT the currently logged-in user
    $stmt_del = mysqli_prepare($conn, "DELETE FROM user WHERE id != ?");
    mysqli_stmt_bind_param($stmt_del, "i", $session);
    
    if (mysqli_stmt_execute($stmt_del)) {
        mysqli_stmt_close($stmt_del);
        header("Location: user.php?status=deleted");
    } else {
        $error = mysqli_error($conn);
        mysqli_stmt_close($stmt_del);
        die("Error deleting users: " . htmlspecialchars($error));
    }
    exit();
} else {
    header("Location: index.php");
    exit();
}