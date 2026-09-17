<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Details</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="css/bootstrap/dist/css/bootstrap-theme.min.css" />
</head>
<body>

<h4>Note: Bill Amount = Total Consumption * Price/unit<br />&copy; 2016</h4>

<?php
include 'auth.php';
checkLevel([1, 2]);
include 'db.php';

// Sanitize inputs and prevent SQL injection
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

if ($id > 0) {
    // Use prepared statements for security
    $stmt = mysqli_prepare($conn, "SELECT * FROM bill WHERE owners_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    echo "<table class=\"table table-striped table-hover table-bordered\">
    <tr>
        <th>Id</th>
        <th>Previous Reading</th>
        <th>Present Reading</th>
        <th>Consumption</th>
        <th>Price</th>
        <th>Date</th>
        <th>Bill Amount</th>
        <th>Action</th>
    </tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        $prev = $row['prev'];
        $pres = $row['pres'];
        $price = $row['price'];
        $totalcons = $pres - $prev;
        $bill = $totalcons * $price;

        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($prev) . "</td>";
        echo "<td>" . htmlspecialchars($pres) . "</td>";
        echo "<td>" . htmlspecialchars($totalcons) . "</td>";
        echo "<td>" . htmlspecialchars($price) . "</td>";
        echo "<td>" . htmlspecialchars($row['date']) . "</td>";
        echo "<td>" . htmlspecialchars($bill) . "</td>";
        echo "<td>";
        echo "<a rel='facebox' href='viewpayment.php?id=" . urlencode($row['id']) . "'><span class=\"glyphicon glyphicon-eye-open\"></span> View</a> | ";
        echo "<a rel='facebox' href='delbill.php?id=" . urlencode($row['id']) . "'>Del</a>";
        echo "</td>";
        echo "</tr>";
    }

    echo "</table>";
    mysqli_stmt_close($stmt);
} else {
    echo "<div class='alert alert-warning'>Invalid Owner ID provided.</div>";
}
?>

</body>
</html>