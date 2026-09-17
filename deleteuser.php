<?php 
include 'auth.php';
checkLevel([1]);
session_start();

// Corrected session check and redirect syntax
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

include 'db.php';

$session = intval($_SESSION['id']);
$sessionname = "";

if ($session > 0) {
    // 1. Fetch current logged-in user's name
    $stmt_user = mysqli_prepare($conn, "SELECT name FROM user WHERE id = ?");
    mysqli_stmt_bind_param($stmt_user, "i", $session);
    mysqli_stmt_execute($stmt_user);
    $result = mysqli_stmt_get_result($stmt_user);

    if ($row = mysqli_fetch_assoc($result)) {
        $sessionname = $row['name'];
    }
    mysqli_stmt_close($stmt_user);

    // 2. Delete all users EXCEPT the currently logged-in user
    $stmt_del = mysqli_prepare($conn, "DELETE FROM user WHERE id != ?");
    mysqli_stmt_bind_param($stmt_del, "i", $session);
    $delete_success = mysqli_stmt_execute($stmt_del);
    mysqli_stmt_close($stmt_del);

    if ($delete_success) {
        header("Location: user.php");
    } else {
        echo "Error deleting users: " . mysqli_error($conn);
    }
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>