<?php
include 'auth.php';
checkLevel([1, 2, 3]);
session_start();

// Authentication check
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

include 'db.php';

$owner_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

$id = $lname = $fname = $mi = $address = $contact = "";

if ($owner_id > 0) {
    // Fetch current owner details using prepared statement
    $stmt = mysqli_prepare($conn, "SELECT * FROM owners WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $owner_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($test = mysqli_fetch_assoc($result)) {
        $id      = $test['id'];
        $lname   = $test['lname'];
        $fname   = $test['fname'];
        $mi      = $test['mi'];
        $address = $test['address'];
        $contact = $test['contact'];
    } else {
        die("Error: Data not found.");
    }
    mysqli_stmt_close($stmt);
} else {
    die("Invalid owner ID.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Owners Update</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap/dist/css/bootstrap.min.css" />
</head>
<body>

<div class="container" style="max-width: 500px; margin-top: 30px;">
    <h1 class="text-center">Owners Update</h1>

    <form method="post" action="editecex.php">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>" />

        <div class="form-group">
            <label>Last Name:</label>
            <input type="text" name="lname" value="<?php echo htmlspecialchars($lname); ?>" class="form-control" required />
        </div>

        <div class="form-group">
            <label>First Name:</label>
            <input type="text" name="fname" value="<?php echo htmlspecialchars($fname); ?>" class="form-control" required />
        </div>

        <div class="form-group">
            <label>Meter Number / MI:</label>
            <input type="text" name="mi" value="<?php echo htmlspecialchars($mi); ?>" class="form-control" required />
        </div>

        <div class="form-group">
            <label>Address:</label>
            <input type="text" name="address" value="<?php echo htmlspecialchars($address); ?>" class="form-control" required />
        </div>

        <div class="form-group">
            <label>Contact:</label>
            <input type="text" name="contact" value="<?php echo htmlspecialchars($contact); ?>" class="form-control" required />
        </div>

        <br />
        <input type="submit" name="save" value="Save Changes" class="btn btn-primary form-control" />
    </form>
</div>

</body>
</html>