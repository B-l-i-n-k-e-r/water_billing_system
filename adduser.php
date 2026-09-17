<?php
include 'auth.php';
checkLevel([1]); // Restricted to Admin
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add User</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap/dist/css/bootstrap.min.css" />
</head>
<body>

<div class="container" style="max-width: 400px; margin-top: 40px;">
    <h1 class="text-center">Add User</h1>
    
    <form method="post" action="useradd.php">
        <div class="form-group">
            <label>Username:</label>
            <input type="text" name="username" class="form-control" required="required" />
        </div>

        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" class="form-control" required="required" />
        </div>

        <div class="form-group">
            <label>Name:</label>
            <input type="text" name="name" class="form-control" required="required" />
        </div>

        <br />
        <input type="submit" name="ok" value="Add" class="btn btn-primary form-control" />
    </form>
</div>

</body>
</html>