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

    $id        = intval($_POST['id']);
    $username  = trim($_POST['username']);
    $name      = trim($_POST['name']);
    $password  = trim($_POST['password']);
    $userlevel = isset($_POST['userlevel']) ? intval($_POST['userlevel']) : 3;

    if ($id > 0) {
        if (!empty($password)) {
            // Update username, name, password, and userlevel
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "UPDATE user SET username = ?, name = ?, password = ?, userlevel = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "sssii", $username, $name, $hashed_password, $userlevel, $id);
        } else {
            // Update username, name, and userlevel only
            $stmt = mysqli_prepare($conn, "UPDATE user SET username = ?, name = ?, userlevel = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "ssii", $username, $name, $userlevel, $id);
        }

        $success = mysqli_stmt_execute($stmt);
        
        if ($success) {
            $_SESSION['success_msg'] = "User details updated successfully!";
        } else {
            $_SESSION['error_msg'] = "Error updating user details: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);

    } else {
        $_SESSION['error_msg'] = "Invalid User ID.";
    }

    header("Location: user.php");
    exit();
} else {
    header("Location: user.php");
    exit();
}
?>