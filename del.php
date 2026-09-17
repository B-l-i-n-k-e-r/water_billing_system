<?php
// Start session BEFORE running authentication checks
session_start();

include 'auth.php';
checkLevel([1, 2, 3]);

include 'db.php';

$owner_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

$id = "";
$lname = "";

if ($owner_id > 0) {
    $stmt = mysqli_prepare($conn, "SELECT id, lname FROM owners WHERE id = ?");
    
    if ($stmt) {
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
        die("Error: Failed to prepare statement.");
    }
} else {
    die("Invalid owner ID.");
}
?>

<form action="delecex.php" method="post">
    <h4>Are you sure you want to delete <br /></h4>
    <h5><?php echo htmlspecialchars($lname, ENT_QUOTES, 'UTF-8'); ?></h5>
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>" />
    <input type="submit" name="ok" value="Delete" class="btn btn-danger" />
</form>