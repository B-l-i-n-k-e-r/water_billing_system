<?php
include 'auth.php';
checkLevel([1, 2]); // Cashier and Admin
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cashier Dashboard - Water Billing System</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap/dist/css/bootstrap.min.css" />
</head>
<body>
<div class="container" style="margin-top: 30px;">
    <div class="well">
        <h2>Cashier Station</h2>
        <p>Process customer billing transactions and account updates.</p>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-info text-center">
                <div class="panel-heading"><h4>Billing & Payments</h4></div>
                <div class="panel-body">
                    <p>Record meter readings, compute totals, and process payments.</p>
                    <a href="billing.php" class="btn btn-info btn-block">Billing Terminal</a>
                    <a href="viewpayment.php" class="btn btn-default btn-block">View Payment History</a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="panel panel-success text-center">
                <div class="panel-heading"><h4>Client Directory</h4></div>
                <div class="panel-body">
                    <p>Lookup client meter details and account contacts.</p>
                    <a href="clients.php" class="btn btn-success btn-block">View Clients</a>
                </div>
            </div>
        </div>
    </div>

    <a href="logout.php" class="btn btn-danger">Logout</a>
</div>
</body>
</html>