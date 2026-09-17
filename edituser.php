<?php
include 'auth.php';
checkLevel([1]);
session_start();

// Authentication check
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

include 'db.php';

$user_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

$id = $username = $name = "";

if ($user_id > 0) {
    // Fetch user details safely without retrieving plain-text passwords
    $stmt = mysqli_prepare($conn, "SELECT id, username, name FROM user WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($test = mysqli_fetch_assoc($result)) {
        $id       = $test['id'];
        $username = $test['username'];
        $name     = $test['name'];
    } else {
        die("Error: Data not found..");
    }
    mysqli_stmt_close($stmt);
} else {
    die("Invalid user ID.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Users Update</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap/dist/css/bootstrap.min.css" />
</head>
<body>

<div class="container" style="max-width: 500px; margin-top: 30px;">
    <h1 class="text-center">Users Update</h1>

    <form method="post" action="edituserecex.php">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>" />

        <div class="form-group">
            <label>Username:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" class="form-control" required />
        </div>

        <div class="form-group">
            <label>Name:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" class="form-control" required />
        </div>

        <div class="form-group">
            <label>New Password (leave blank to keep current password):</label>
            <input type="password" name="password" value="" class="form-control" placeholder="••••••••" />
        </div>

        <br />
        <input type="submit" name="save" value="Edit User" class="btn btn-primary form-control" />
    </form>
</div>

</body>
</html>