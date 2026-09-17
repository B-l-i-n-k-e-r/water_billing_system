<?php
include 'auth.php';
checkLevel([1, 2]);
session_start();

// Fixed JavaScript redirect syntax
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

include 'db.php';

// Sanitize inputs
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

$prev = $owners_id = $pres = $price = $totalcons = $bill = $date = "";
$lname = $fname = $mi = $address = $contact = "";
$sessionname = "";

if ($id > 0) {
    // 1. Fetch Bill Information
    $stmt = mysqli_prepare($conn, "SELECT * FROM bill WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $prev = $row['prev'];
        $owners_id = $row['owners_id'];
        $pres = $row['pres'];
        $price = $row['price'];
        $totalcons = $pres - $prev;
        $bill = $totalcons * $price;
        $date = $row['date'];
    }
    mysqli_stmt_close($stmt);

    // 2. Fetch Owner Information
    if (!empty($owners_id)) {
        $stmt_owner = mysqli_prepare($conn, "SELECT * FROM owners WHERE id = ?");
        mysqli_stmt_bind_param($stmt_owner, "i", $owners_id);
        mysqli_stmt_execute($stmt_owner);
        $result_owner = mysqli_stmt_get_result($stmt_owner);

        if ($test = mysqli_fetch_assoc($result_owner)) {
            $owner_id = $test['id'];
            $lname = $test['lname'];
            $fname = $test['fname'];
            $mi = $test['mi'];
            $address = $test['address'];
            $contact = $test['contact'];
        }
        mysqli_stmt_close($stmt_owner);
    }

    // 3. Fetch User / Cashier Session Name
    $session = intval($_SESSION['id']);
    $stmt_user = mysqli_prepare($conn, "SELECT * FROM user WHERE id = ?");
    mysqli_stmt_bind_param($stmt_user, "i", $session);
    mysqli_stmt_execute($stmt_user);
    $result_user = mysqli_stmt_get_result($stmt_user);

    if ($row_user = mysqli_fetch_assoc($result_user)) {
        $sessionname = $row_user['name'];
    }
    mysqli_stmt_close($stmt_user);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart Utilities</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="css/bootstrap/dist/css/bootstrap-theme.min.css" />
    <style type="text/css">
        #data {
            margin: 0 auto;
            width: 700px;
            padding: 20px;
            border: #066 thin ridge;
            height: 600px;
        }
    </style>
    <script>
        function printDiv() {
            var printContents = document.getElementById('data').innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            window.location.reload(); // Reload to restore event listeners safely
        }
    </script>
</head>
<body style="background-size:cover; font-family:'Courier New', Courier;">

<div id="data">
    <div class="text-center">
        <h4><b>Water Billing System</b></h4>
        <p>ESPSN - ESSP</p>
        <p><strong>Bill Invoice</strong></p>
        <p>Phone: +255 (0) 654 235</p>
        <i style="float: right; margin-right: 50px;">Date: <?php echo htmlspecialchars($date); ?></i>
    </div>
    <div style="clear: both;"></div>
    
    <div id="context" style="margin-top: 15px;">
        <table class="table table-striped table-bordered">
            <tr>
                <td>Last Name:</td>
                <td><b><i><?php echo htmlspecialchars($lname); ?></i></b></td>
                <td>Client ID:</td>
                <td><i>SMART/00<?php echo htmlspecialchars($id); ?></i></td>
            </tr>
            <tr>
                <td>First Name:</td>
                <td><b><i><?php echo htmlspecialchars($fname); ?></i></b></td>
                <td>Meter Number:</td>
                <td><?php echo htmlspecialchars($mi); ?></td>
            </tr>
            <tr>
                <td>Address:</td>
                <td colspan="3"><b><i><?php echo htmlspecialchars($address); ?></i></b></td>
            </tr>
            <tr>
                <td>Contact:</td>
                <td colspan="3"><b><i><?php echo htmlspecialchars($contact); ?></i></b></td>
            </tr>
            <tr>
                <td>Previous Reading:</td>
                <td><b><i><?php echo htmlspecialchars($prev); ?></i></b></td>
                <td>Present Reading:</td>
                <td><b><i><?php echo htmlspecialchars($pres); ?></i></b></td>
            </tr>
            <tr>
                <td>Consumption:</td>
                <td><b><i><?php echo htmlspecialchars($totalcons); ?></i></b></td>
                <td>Price / unit:</td>
                <td><b><i><?php echo htmlspecialchars($price); ?></i> Tshs</b></td>
            </tr>
            <tr>
                <td colspan="4" class="text-center">
                    <h2>Total Invoice: <b><i><?php echo htmlspecialchars($bill); ?></i> /= Tshs</b></h2>
                </td>
            </tr>
            <tr>
                <td colspan="2">Cashier: <b><?php echo htmlspecialchars($sessionname); ?></b></td>
                <td colspan="2">Signature: _____________</td>
            </tr>
        </table>
    </div>
</div>

<br />
<div class="text-center">
    <button type="button" class="btn btn-default" onclick="printDiv()">
        <span class="glyphicon glyphicon-print"></span>&nbsp;Print Bill
    </button>&nbsp;
    <a href="bill.php" class="btn btn-danger">
        <span class="glyphicon glyphicon-arrow-left"></span>&nbsp;Go back
    </a>
</div>

</body>
</html>