<?php
session_start();

include_once 'auth.php';
checkLevel([1, 2, 3]);

// Authentication check
if (!isset($_SESSION['SESS_MEMBER_ID']) && !isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

include_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {

    // Sanitize and collect inputs
    $id      = intval($_POST['id']);
    $lname   = trim($_POST['lname']);
    $fname   = trim($_POST['fname']);
    $mi      = trim($_POST['mi']);
    $address = trim($_POST['address']);
    $contact = trim($_POST['contact']);

    if ($id > 0) {
        // 1. Update owner record using prepared statements
        $stmt1 = mysqli_prepare($conn, "UPDATE owners SET lname = ?, fname = ?, mi = ?, address = ?, contact = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt1, "sssssi", $lname, $fname, $mi, $address, $contact, $id);
        $success = mysqli_stmt_execute($stmt1);
        mysqli_stmt_close($stmt1);

        // 2. Keep client name synchronized in tempo_bill table
        if ($success) {
            $stmt2 = mysqli_prepare($conn, "UPDATE tempo_bill SET Client = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt2, "si", $fname, $id);
            mysqli_stmt_execute($stmt2);
            mysqli_stmt_close($stmt2);

            $_SESSION['success_msg'] = "Client details updated successfully!";
        } else {
            $_SESSION['error_msg'] = "Error updating owner: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error_msg'] = "Invalid Client ID.";
    }

    header("Location: clients.php");
    exit();
} else {
    header("Location: clients.php");
    exit();
}
?>