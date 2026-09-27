<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'auth.php';
checkLevel([1]); // Restricted to Admin
include_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {

    $username  = trim($_POST['username']);
    $password  = trim($_POST['password']);
    $name      = trim($_POST['name']);
    $userlevel = isset($_POST['userlevel']) ? intval($_POST['userlevel']) : 3;

    if (!empty($username) && !empty($password) && !empty($name)) {

        // Hash password securely
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert into database with userlevel
        $stmt = mysqli_prepare($conn, "INSERT INTO user (username, password, name, userlevel) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssi", $username, $hashed_password, $name, $userlevel);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_msg'] = "User added successfully!";
        } else {
            $_SESSION['error_msg'] = "Error adding user: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);

    } else {
        $_SESSION['error_msg'] = "Please fill in all required fields.";
    }

    mysqli_close($conn);
    header("Location: user.php");
    exit();
} else {
    if (isset($conn)) {
        mysqli_close($conn);
    }
    header("Location: user.php");
    exit();
}
?>