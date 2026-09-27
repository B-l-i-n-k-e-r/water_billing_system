```php
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
    $email     = trim($_POST['email']);
    $userlevel = isset($_POST['userlevel']) ? intval($_POST['userlevel']) : 3;

    // Validate required fields
    if (!empty($username) && !empty($password) && !empty($name) && !empty($email)) {

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_msg'] = "Please enter a valid email address.";
            mysqli_close($conn);
            header("Location: user.php");
            exit();
        }

        // Hash password securely
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user including email
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO user (username, password, name, email, userlevel)
             VALUES (?, ?, ?, ?, ?)"
        );

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "ssssi",
                $username,
                $hashed_password,
                $name,
                $email,
                $userlevel
            );

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_msg'] = "User added successfully!";
            } else {
                $_SESSION['error_msg'] = "Error adding user: " . mysqli_stmt_error($stmt);
            }

            mysqli_stmt_close($stmt);

        } else {
            $_SESSION['error_msg'] = "Database error: " . mysqli_error($conn);
        }

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
```
