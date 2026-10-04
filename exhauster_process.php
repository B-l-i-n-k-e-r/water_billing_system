<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'db.php';
include_once 'functions.php';

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: exhauster.php");
    exit();
}

$user_id = intval($_SESSION['id']);
$permit_num = generatePermitNumber($conn);

// Collect POSTed fields (with fallbacks)
$id_number  = trim($_POST['id_number'] ?? '');
$id_type    = trim($_POST['id_type'] ?? 'National ID');
$full_name  = trim($_POST['full_name'] ?? '');
$dob        = $_POST['date_of_birth'] ?? null;
$email      = trim($_POST['email'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$alt_name   = trim($_POST['alt_name'] ?? '');
$alt_phone  = trim($_POST['alt_phone'] ?? '');
$vehicle    = trim($_POST['vehicle_registration'] ?? '');
$capacity   = intval($_POST['vehicle_capacity'] ?? 0);

// Handle file uploads
$doc_kra     = handlePdfUpload('doc_kra_pin');
$doc_nema    = handlePdfUpload('doc_nema');
$doc_logbook = handlePdfUpload('doc_vehicle_logbook');
$doc_photo   = handlePdfUpload('doc_vehicle_photo');

$stmt = mysqli_prepare($conn,
    "INSERT INTO exhauster_permits (
        permit_number, user_id,
        doc_kra_pin, doc_nema, doc_vehicle_logbook, doc_vehicle_photo,
        id_number, id_type, full_name, date_of_birth,
        email, phone, alt_name, alt_phone,
        vehicle_registration, vehicle_capacity
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

mysqli_stmt_bind_param($stmt, "sisssssssssssssi",
    $permit_num, $user_id,
    $doc_kra, $doc_nema, $doc_logbook, $doc_photo,
    $id_number, $id_type, $full_name, $dob,
    $email, $phone, $alt_name, $alt_phone,
    $vehicle, $capacity
);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: exhauster.php?step=7&success=1");
    exit();
} else {
    error_log("Exhauster INSERT failed: " . mysqli_error($conn));
    header("Location: exhauster.php?step=7&err=1");
    exit();
}