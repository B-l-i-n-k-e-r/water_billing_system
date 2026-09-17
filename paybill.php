<?php
include 'auth.php';
checkLevel([1, 2]);
session_start();

include 'db.php';

$owner_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

$id = $lname = $fname = $mi = $address = $contact = "";
$previous = 0;

if ($owner_id > 0) {
    // 1. Fetch Owner Information
    $stmt_owner = mysqli_prepare($conn, "SELECT * FROM owners WHERE id = ?");
    mysqli_stmt_bind_param($stmt_owner, "i", $owner_id);
    mysqli_stmt_execute($stmt_owner);
    $result_owner = mysqli_stmt_get_result($stmt_owner);

    if ($test = mysqli_fetch_assoc($result_owner)) {
        $id      = $test['id'];
        $lname   = $test['lname'];
        $fname   = $test['fname'];
        $mi      = $test['mi'];
        $address = $test['address'];
        $contact = $test['contact'];
    } else {
        die("Error: Data not found.");
    }
    mysqli_stmt_close($stmt_owner);

    // 2. Fetch Previous Reading from tempo_bill using client name
    $stmt_tempo = mysqli_prepare($conn, "SELECT Prev FROM tempo_bill WHERE Client = ?");
    mysqli_stmt_bind_param($stmt_tempo, "s", $fname);
    mysqli_stmt_execute($stmt_tempo);
    $result_tempo = mysqli_stmt_get_result($stmt_tempo);

    if ($results = mysqli_fetch_assoc($result_tempo)) {
        $previous = $results['Prev'];
    }
    mysqli_stmt_close($stmt_tempo);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Bill</title>
</head>
<body>

<h1>Client Bill</h1>
<h2>Name: <?php echo htmlspecialchars($lname . ' ' . $fname . ' ' . $mi); ?></h2>
<p><?php $date = date('Y/m/d H:i:s'); echo htmlspecialchars($date); ?></p>

<form method="post" action="addbill.php">
    <input type="hidden" name="owners_id" value="<?php echo htmlspecialchars($id); ?>" />
    <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>" />
    
    <table width="346" border="1">
        <tr>
            <td width="118">Previous Reading:</td>
            <td width="66">
                <input type="text" name="prev" value="<?php echo htmlspecialchars($previous); ?>" />
            </td>
            <td>ml</td>
        </tr>
        <tr>
            <td>Present Reading:</td>
            <td><input type="text" name="pres" required /></td>
            <td>ml</td>
        </tr>
        <tr>
            <td>Price/ml</td>
            <td><input type="text" name="price" value="10" /></td>
            <td>Tshs</td>
        </tr>
        <tr>
            <td colspan="3">
                <input type="submit" name="total" value="Add" />
            </td>
        </tr>
    </table>
</form>

</body>
</html>