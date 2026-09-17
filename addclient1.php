<?php
include 'auth.php';
checkLevel([1, 2, 3]);
include 'db.php';
if (isset($_POST['add'])) {
    // Include database connection ($conn)
    include 'db.php';

    // Sanitize and collect form inputs
    $lname       = trim($_POST['lname']);
    $fname       = trim($_POST['fname']);
    $mi          = trim($_POST['mi']);
    $address     = trim($_POST['address']);
    $contact     = trim($_POST['contact']);
    $meterReader = floatval($_POST['meterReader']);

    // 1. Insert new owner into 'owners' table
    $stmt1 = mysqli_prepare($conn, "INSERT INTO owners (lname, fname, mi, address, contact) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt1, "sssss", $lname, $fname, $mi, $address, $contact);
    $success1 = mysqli_stmt_execute($stmt1);

    // Get auto-incremented ID of the newly inserted client
    $new_owner_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt1);

    // 2. Insert initial meter reading into 'tempo_bill'
    if ($success1) {
        $stmt2 = mysqli_prepare($conn, "INSERT INTO tempo_bill (id, Client, Prev) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt2, "isd", $new_owner_id, $fname, $meterReader);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);
    }

    // Redirect back to client list
    header("Location: clients.php");
    exit();
}
?>