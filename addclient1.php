
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'auth.php';
checkLevel([1, 2, 3]); // Access restricted to Admin, Cashier, and Manager

include_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {

    // Sanitize and collect form inputs safely
    $lname       = trim($_POST['lname'] ?? '');
    $fname       = trim($_POST['fname'] ?? '');
    $mi          = trim($_POST['mi'] ?? '');
    $address     = trim($_POST['address'] ?? '');
    $contact     = trim($_POST['contact'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $meterReader = floatval($_POST['meterReader'] ?? 0);

    // Basic validation
    if (empty($fname) || empty($lname)) {
        mysqli_close($conn);
        echo '<script>alert("Error: First and Last name are required."); window.location.href="clients.php";</script>';
        exit();
    }

    // Begin atomic transaction
    mysqli_begin_transaction($conn);

    try {
        // 1. Insert new client into 'owners' table
        $stmt1 = mysqli_prepare($conn, "INSERT INTO owners (lname, fname, mi, address, contact, email) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt1) {
            throw new Exception("Prepare failed (owners): " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt1, "ssssss", $lname, $fname, $mi, $address, $contact, $email);
        
        if (!mysqli_stmt_execute($stmt1)) {
            throw new Exception("Execute failed (owners): " . mysqli_stmt_error($stmt1));
        }

        // Retrieve auto-incremented primary key
        $new_owner_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt1);

        // 2. Insert initial meter reading record into 'tempo_bill'
        $stmt2 = mysqli_prepare($conn, "INSERT INTO tempo_bill (id, Client, Prev) VALUES (?, ?, ?)");
        if (!$stmt2) {
            throw new Exception("Prepare failed (tempo_bill): " . mysqli_error($conn));
        }

        $full_client_name = trim($fname . ' ' . $lname);
        mysqli_stmt_bind_param($stmt2, "isd", $new_owner_id, $full_client_name, $meterReader);

        if (!mysqli_stmt_execute($stmt2)) {
            throw new Exception("Execute failed (tempo_bill): " . mysqli_stmt_error($stmt2));
        }
        mysqli_stmt_close($stmt2);

        // Commit transaction if both statements succeeded
        mysqli_commit($conn);
        mysqli_close($conn);

        // Redirect on success
        header("Location: clients.php");
        exit();

    } catch (Exception $e) {
        // Rollback database changes on failure
        mysqli_rollback($conn);
        mysqli_close($conn);

        echo '<script>alert("Error processing client registration: ' . addslashes($e->getMessage()) . '"); window.location.href="clients.php";</script>';
        exit();
    }
} else {
    if (isset($conn)) {
        mysqli_close($conn);
    }
    header("Location: clients.php");
    exit();
}
?>
```
