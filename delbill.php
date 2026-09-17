<?php
include 'auth.php';
checkLevel([1, 2]);
session_start();
include 'db.php';

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$lname = "";

if ($id > 0) {
    $stmt = mysqli_prepare($conn, "SELECT id, lname FROM owners WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($test = mysqli_fetch_assoc($result)) {
        $id = $test['id'];
        $lname = $test['lname'];
    } else {
        die("Error: Data not found.");
    }
    mysqli_stmt_close($stmt);
} else {
    die("Invalid record ID.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Bill</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap/dist/css/bootstrap.min.css" />
</head>
<body>

<form action="delbillexec.php" method="post" style="padding: 15px;">
    <h3>Are you sure you want to delete this record for <b><?php echo htmlspecialchars($lname); ?></b>?</h3>
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>" />
    <br />
    <input type="submit" name="ok" value="Delete" class="btn btn-danger" />
</form>

</body>
</html>