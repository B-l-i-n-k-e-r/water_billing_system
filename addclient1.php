<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: clients.php");
    exit();
}

$id      = intval($_POST['id'] ?? 0);
$fname   = trim($_POST['fname'] ?? '');
$mi      = trim($_POST['mi'] ?? '');
$lname   = trim($_POST['lname'] ?? '');
$address = trim($_POST['address'] ?? '');
$contact = trim($_POST['contact'] ?? '');
$email   = trim($_POST['email'] ?? '');

if ($fname === '' || $lname === '' || $address === '' || $contact === '') {
    header("Location: addclient.php?err=empty" . ($id > 0 ? "&edit=$id" : ""));
    exit();
}

if ($id > 0) {
    // Update
    $stmt = mysqli_prepare($conn,
        "UPDATE owners SET fname = ?, mi = ?, lname = ?, address = ?, contact = ?, email = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "ssssssi", $fname, $mi, $lname, $address, $contact, $email, $id);
} else {
    // Insert
    $stmt = mysqli_prepare($conn,
        "INSERT INTO owners (fname, mi, lname, address, contact, email) VALUES (?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "ssssss", $fname, $mi, $lname, $address, $contact, $email);
}

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: clients.php?success=1");
    exit();
} else {
    $err = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    die("Save failed: " . htmlspecialchars($err));
}