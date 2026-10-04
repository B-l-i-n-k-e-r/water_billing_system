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

$user_id = intval($_SESSION['id']);
$step = isset($_POST['step']) ? intval($_POST['step']) : 1;

if (!isset($_SESSION['exhauster_wizard'])) {
    $_SESSION['exhauster_wizard'] = [];
}

// Save POST data into session (skip 'step')
foreach ($_POST as $key => $value) {
    if ($key !== 'step') {
        $_SESSION['exhauster_wizard'][$key] = is_array($value) ? $value : trim($value);
    }
}

// Handle file uploads on step 1
if ($step === 1) {
    foreach (['doc_kra_pin', 'doc_nema', 'doc_vehicle_logbook', 'doc_vehicle_photo'] as $key) {
        $path = handlePdfUpload($key);
        if ($path) {
            $_SESSION['exhauster_wizard'][$key] = $path;
        }
    }
}

// Final insert on step 7
if ($step === 7) {
    $d = $_SESSION['exhauster_wizard'];

    if (empty($d['confirm'])) {
        header("Location: exhauster.php?step=7&err=confirm");
        exit();
    }

    $permit_number = generatePermitNumber($conn);

    $stmt = mysqli_prepare($conn,
        "INSERT INTO exhauster_permits (
            permit_number, user_id,
            doc_kra_pin, doc_nema, doc_vehicle_logbook, doc_vehicle_photo,
            id_number, id_type, full_name, date_of_birth,
            email, phone, alt_name, alt_phone,
            vehicle_registration, vehicle_capacity
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($conn));
    }

    $doc_kra     = $d['doc_kra_pin'] ?? null;
    $doc_nema    = $d['doc_nema'] ?? null;
    $doc_logbook = $d['doc_vehicle_logbook'] ?? null;
    $doc_photo   = $d['doc_vehicle_photo'] ?? null;
    $id_number   = $d['id_number'] ?? '';
    $id_type     = $d['id_type'] ?? 'National ID';
    $full_name   = $d['full_name'] ?? '';
    $dob         = !empty($d['date_of_birth']) ? $d['date_of_birth'] : null;
    $email       = $d['email'] ?? '';
    $phone       = $d['phone'] ?? '';
    $alt_name    = $d['alt_name'] ?? '';
    $alt_phone   = $d['alt_phone'] ?? '';
    $vehicle     = $d['vehicle_registration'] ?? '';
    $capacity    = intval($d['vehicle_capacity'] ?? 0);

    mysqli_stmt_bind_param($stmt, "sisssssssssssssi",
        $permit_number, $user_id,
        $doc_kra, $doc_nema, $doc_logbook, $doc_photo,
        $id_number, $id_type, $full_name, $dob,
        $email, $phone, $alt_name, $alt_phone,
        $vehicle, $capacity
    );

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        unset($_SESSION['exhauster_wizard']);
        header("Location: exhauster.php?step=7&success=1");
        exit();
    } else {
        $err = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        die("Insert failed: " . htmlspecialchars($err));
    }
}

// Otherwise go to next step
header("Location: exhauster.php?step=" . ($step + 1));
exit();