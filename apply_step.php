<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'db.php';
include_once 'functions.php';
require_once 'notify.php';

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

$user_id = intval($_SESSION['id']);
$step = isset($_POST['step']) ? intval($_POST['step']) : 1;
$next = $step + 1;

// Ensure wizard_data container exists
if (!isset($_SESSION['wizard_data'])) {
    $_SESSION['wizard_data'] = [];
}

// Copy all POSTed fields (except 'step') into session
foreach ($_POST as $key => $value) {
    if ($key !== 'step') {
        $_SESSION['wizard_data'][$key] = is_array($value) ? $value : trim($value);
    }
}

// Handle file uploads on step 3
if ($step === 3) {
    foreach (['doc_kra_pin', 'doc_nema', 'doc_vehicle_logbook', 'doc_vehicle_photo'] as $key) {
        $path = handlePdfUpload($key);
        if ($path) {
            $_SESSION['wizard_data'][$key] = $path;
        }
    }
}

// On final step (11) → insert into DB
if ($step === 11) {
    $d = $_SESSION['wizard_data'];

    $app_num = generateApplicationNumber($conn);

    $stmt = mysqli_prepare($conn,
        "INSERT INTO applications (
            application_number, user_id, application_type, service_type,
            id_number, id_type, kra_pin, full_name, date_of_birth, gender,
            email, phone, postal_address,
            alt_name, alt_phone, alt_relationship,
            premises_type, units, daily_demand, meter_size, supply_category,
            county, estate, plot_number, street,
            doc_kra_pin, doc_nema, doc_vehicle_logbook, doc_vehicle_photo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($conn));
    }

    $app_type     = $d['app_type'] ?? 'individual';
    $service_type = $d['service_type'] ?? 'water';
    $id_number    = $d['id_number'] ?? '';
    $id_type      = $d['id_type'] ?? 'National ID';
    $kra_pin      = $d['kra_pin'] ?? '';
    $full_name    = $d['full_name'] ?? '';
    $dob          = !empty($d['date_of_birth']) ? $d['date_of_birth'] : null;
    $gender       = $d['gender'] ?? '';
    $email        = $d['email'] ?? '';
    $phone        = $d['phone'] ?? '';
    $postal       = $d['postal_address'] ?? '';
    $alt_name     = $d['alt_name'] ?? '';
    $alt_phone    = $d['alt_phone'] ?? '';
    $alt_rel      = $d['alt_relationship'] ?? '';
    $premises     = $d['premises_type'] ?? '';
    $units        = intval($d['units'] ?? 0);
    $daily_demand = floatval($d['daily_demand'] ?? 0);
    $meter_size   = $d['meter_size'] ?? '';
    $supply_cat   = $d['supply_category'] ?? '';
    $county       = $d['county'] ?? 'Nairobi';
    $estate       = $d['estate'] ?? '';
    $plot         = $d['plot_number'] ?? '';
    $street       = $d['street'] ?? '';
    $doc_kra      = $d['doc_kra_pin'] ?? null;
    $doc_nema     = $d['doc_nema'] ?? null;
    $doc_logbook  = $d['doc_vehicle_logbook'] ?? null;
    $doc_photo    = $d['doc_vehicle_photo'] ?? null;

    mysqli_stmt_bind_param($stmt, "sisssssssssssssssisssssssssss",
        $app_num, $user_id, $app_type, $service_type,
        $id_number, $id_type, $kra_pin, $full_name, $dob, $gender,
        $email, $phone, $postal,
        $alt_name, $alt_phone, $alt_rel,
        $premises, $units, $daily_demand, $meter_size, $supply_cat,
        $county, $estate, $plot, $street,
        $doc_kra, $doc_nema, $doc_logbook, $doc_photo
    );

    if (mysqli_stmt_execute($stmt)) {
    $app_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    unset($_SESSION['wizard_data']);

    // Send confirmation email
    notifyApplicationReceived($conn, $app_id);

    header("Location: track_application.php?success=1");
    exit();
    } else {
        $err = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        die("Insert failed: " . htmlspecialchars($err));
    }
}

// Otherwise redirect to the next step
header("Location: apply.php?step=" . $next);
exit();
?>