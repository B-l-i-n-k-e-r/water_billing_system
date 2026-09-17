<?php
include 'auth.php';
checkLevel([1]); // Admin only
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Water Billing System</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap/dist/css/bootstrap.min.css" />
</head>
<body>
<div class="container" style="margin-top: 30px;">
    <div class="well">
        <h2>Administrator Control Panel</h2>
        <p>Welcome, Administrator. Full system privilege enabled.</p>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="panel panel-primary text-center">
                <div class="panel-heading"><h4>User Management</h4></div>
                <div class="panel-body">
                    <p>Add, edit, or remove system accounts and user levels.</p>
                    <a href="user.php" class="btn btn-primary btn-block">Manage Users</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="panel panel-info text-center">
                <div class="panel-heading"><h4>Billing & Payments</h4></div>
                <div class="panel-body">
                    <p>View active bills, enter meter readings, and process receipts.</p>
                    <a href="billing.php" class="btn btn-info btn-block">Go to Billing</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="panel panel-success text-center">
                <div class="panel-heading"><h4>Client Database</h4></div>
                <div class="panel-body">
                    <p>Manage registered water service owners and accounts.</p>
                    <a href="clients.php" class="btn btn-success btn-block">View Clients</a>
                </div>
            </div>
        </div>
    </div>
    
    <a href="logout.php" class="btn btn-danger">Logout</a>
</div>
</body>
</html>