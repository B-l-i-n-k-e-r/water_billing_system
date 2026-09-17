<?php
include 'auth.php';
checkLevel([1, 2, 3]);
include 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Client</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap/dist/css/bootstrap.min.css" />
</head>
<body>

<div class="container" style="max-width: 500px; margin-top: 30px;">
    <h1 class="text-center">Add Client</h1>
    
    <form method="post" action="addclient1.php">
        <div class="form-group">
            <label>Last Name:</label>
            <input type="text" name="lname" class="form-control" required="required" />
        </div>

        <div class="form-group">
            <label>First Name:</label>
            <input type="text" name="fname" class="form-control" required="required" />
        </div>

        <div class="form-group">
            <label>Meter Number:</label>
            <input type="text" name="mi" class="form-control" required="required" />
        </div>

        <div class="form-group">
            <label>Address:</label>
            <input type="text" name="address" class="form-control" required="required" />
        </div>

        <div class="form-group">
            <label>Contact #:</label>
            <input type="text" name="contact" class="form-control" required="required" />
        </div>

        <div class="form-group">
            <label>First Meter Reading:</label>
            <input type="number" step="any" name="meterReader" class="form-control" required="required" />
        </div>

        <br />
        <input type="submit" name="add" value="ADD" class="btn btn-success form-control" />
    </form>
</div>

</body>
</html>