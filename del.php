<?php
include 'auth.php';
checkLevel([1, 2, 3]);
session_start();
include 'db.php';

$owner_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

$id = $lname = "";

if ($owner_id > 0) {
    $stmt = mysqli_prepare($conn, "SELECT id, lname FROM owners WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $owner_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($test = mysqli_fetch_assoc($result)) {
        $id    = $test['id'];
        $lname = $test['lname'];
    } else {
        die("Error: Data not found.");
    }
    mysqli_stmt_close($stmt);
} else {
    die("Invalid owner ID.");
}
?>

<form action="delecex.php" method="post">
    <h4>Are you sure you want to delete <br /></h4>
    <h5><?php echo htmlspecialchars($lname); ?></h5>
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>" />
    <input type="submit" name="ok" value="Delete" class="btn btn-danger" />
</form>