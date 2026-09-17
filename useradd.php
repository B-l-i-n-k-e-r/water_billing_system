<?php
session_start();

include_once 'auth.php';
checkLevel([1]); // Restricted to Admin
include_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {

    // Collect and sanitize inputs
    $id       = isset($_POST['id']) ? intval($_POST['id']) : null;
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $name     = trim($_POST['name']);

    if (!empty($username) && !empty($password) && !empty($name)) {

        // Hash password securely
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare insert query
        if ($id && $id > 0) {
            $stmt = mysqli_prepare($conn, "INSERT INTO user (id, username, password, name) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "isss", $id, $username, $hashed_password, $name);
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO user (username, password, name) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sss", $username, $hashed_password, $name);
        }

        // Execute and set session status messages
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_msg'] = "User added successfully!";
        } else {
            $_SESSION['error_msg'] = "Error adding user: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);

    } else {
        $_SESSION['error_msg'] = "Please fill in all required fields.";
    }

    header("Location: user.php");
    exit();
} else {
    header("Location: user.php");
    exit();
}
?>