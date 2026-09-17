<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        
        // Retrieve id, password, and userlevel using a prepared statement
        $stmt = mysqli_prepare($conn, "SELECT id, password, userlevel FROM user WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            $hashed_password = $row['password'];

            // Check password using password_verify() with fallback for legacy plain-text passwords
            if (password_verify($password, $hashed_password) || $password === $hashed_password) {
                
                // Re-hash plain-text legacy passwords automatically on login
                if ($password === $hashed_password) {
                    $new_hash = password_hash($password, PASSWORD_DEFAULT);
                    $update_stmt = mysqli_prepare($conn, "UPDATE user SET password = ? WHERE id = ?");
                    mysqli_stmt_bind_param($update_stmt, "si", $new_hash, $row['id']);
                    mysqli_stmt_execute($update_stmt);
                    mysqli_stmt_close($update_stmt);
                }

                // Store user session variables
                $_SESSION['id']        = $row['id'];
                $_SESSION['userlevel'] = isset($row['userlevel']) ? intval($row['userlevel']) : 3;
                
                mysqli_stmt_close($stmt);

                // Unified redirect to the dashboard router
                echo '<script>window.location.href="dashboard.php";</script>';
                exit();
            }
        }
        mysqli_stmt_close($stmt);
    }

    // Invalid credentials or missing fields
    header("Location: index.php?err");
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>