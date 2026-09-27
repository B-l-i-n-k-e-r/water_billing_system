```php
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
    $email     = trim($_POST['email']);
    $password  = trim($_POST['password']);
    $userlevel = isset($_POST['userlevel']) ? intval($_POST['userlevel']) : 3;

    // Check required fields
    if ($id <= 0 || empty($username) || empty($name) || empty($email)) {
        $_SESSION['error_msg'] = "Please fill in all required fields.";
        header("Location: user.php");
        exit();
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_msg'] = "Please enter a valid email address.";
        header("Location: user.php");
        exit();
    }

    /*
     * If a new password was provided,
     * update username, name, email, password and userlevel.
     */
    if (!empty($password)) {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE user
             SET username = ?, name = ?, email = ?, password = ?, userlevel = ?
             WHERE id = ?"
        );

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "ssssii",
                $username,
                $name,
                $email,
                $hashed_password,
                $userlevel,
                $id
            );

        }

    } else {

        /*
         * No new password provided.
         * Keep the existing password unchanged.
         */
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE user
             SET username = ?, name = ?, email = ?, userlevel = ?
             WHERE id = ?"
        );

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "sssii",
                $username,
                $name,
                $email,
                $userlevel,
                $id
            );

        }
    }

    // Check if statement was prepared successfully
    if (!$stmt) {

        $_SESSION['error_msg'] =
            "Database error: " . mysqli_error($conn);

    } else {

        // Execute update
        $success = mysqli_stmt_execute($stmt);

        if ($success) {

            $_SESSION['success_msg'] =
                "User details updated successfully!";

        } else {

            $_SESSION['error_msg'] =
                "Error updating user details: " .
                mysqli_stmt_error($stmt);
        }

        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);

    header("Location: user.php");
    exit();

} else {

    header("Location: user.php");
    exit();
}
?>
```
