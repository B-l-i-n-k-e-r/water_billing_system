<?php
include 'auth.php';
checkLevel([1]);
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {

    // Collect and sanitize inputs
    $id       = isset($_POST['id']) ? intval($_POST['id']) : null;
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $name     = trim($_POST['name']);

    if (!empty($username) && !empty($password) && !empty($name)) {

        // Hash the password securely for modern PHP authentication
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 1. If an ID was provided, insert with ID; otherwise let AUTO_INCREMENT handle it
        if ($id) {
            $stmt = mysqli_prepare($conn, "INSERT INTO user (id, username, password, name) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "isss", $id, $username, $hashed_password, $name);
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO user (username, password, name) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sss", $username, $hashed_password, $name);
        }

        // 2. Execute and check for success
        if (mysqli_stmt_execute($stmt)) {
            echo '<script>alert("User added successfully!"); window.location.href="user.php";</script>';
        } else {
            echo '<script>alert("Error adding user: ' . addslashes(mysqli_error($conn)) . '"); window.location.href="user.php";</script>';
        }
        mysqli_stmt_close($stmt);

    } else {
        echo '<script>alert("Please fill in all required fields."); window.location.href="user.php";</script>';
    }
    exit();
} else {
    header("Location: user.php");
    exit();
}
?>