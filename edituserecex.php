<?php
include 'auth.php';
checkLevel([1]); // Restricted to Admin

include 'db.php';

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
        mysqli_stmt_close($stmt);

        if ($success) {
            echo '<script>alert("User details updated successfully!"); window.location.href="user.php";</script>';
        } else {
            echo '<script>alert("Error updating user details: ' . addslashes(mysqli_error($conn)) . '"); window.location.href="user.php";</script>';
        }
    } else {
        echo '<script>alert("Invalid User ID."); window.location.href="user.php";</script>';
    }
    exit();
} else {
    header("Location: user.php");
    exit();
}
?>