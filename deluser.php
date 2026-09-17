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

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$username = "";

if ($id > 0) {
    // 1. Fetch user information safely
    $stmt = mysqli_prepare($conn, "SELECT id, username FROM user WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($test = mysqli_fetch_assoc($result)) {
        $id = $test['id'];
        $username = $test['username'];
    } else {
        die("Error: User not found.");
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
    <title>Delete User</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap/dist/css/bootstrap.min.css" />
</head>
<body>

<div class="container" style="max-width: 400px; margin-top: 50px;">
    <div class="panel panel-danger">
        <div class="panel-heading">
            <h3 class="panel-title">Confirm Delete</h3>
        </div>
        <div class="panel-body text-center">
            <p>Are you sure you want to delete the user <strong><?php echo htmlspecialchars($username); ?></strong>?</p>
            
            <form action="deluserexec.php" method="post">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>" />
                <input type="submit" name="ok" value="Delete" class="btn btn-danger" />
                <a href="user.php" class="btn btn-default">Cancel</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>