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
$app_num = trim($_POST['application_number'] ?? '');

if (empty($app_num)) {
    header("Location: upload_application.php?err=noapp");
    exit();
}

// Find application by number and owner
$stmt = mysqli_prepare($conn,
    "SELECT id FROM applications WHERE application_number = ? AND user_id = ?"
);
mysqli_stmt_bind_param($stmt, "si", $app_num, $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if (!$row = mysqli_fetch_assoc($res)) {
    mysqli_stmt_close($stmt);
    header("Location: upload_application.php?err=notfound");
    exit();
}
$app_id = intval($row['id']);
mysqli_stmt_close($stmt);

// Handle any uploaded files
$docTypes = [
    'doc_kra_pin'         => 'KRA PIN',
    'doc_nema'            => 'NEMA',
    'doc_vehicle_logbook' => 'Vehicle Logbook',
    'doc_vehicle_photo'   => 'Vehicle Photo',
    'doc_passport_photo'  => 'Passport Photo',
    'doc_id_copy'         => 'ID Copy',
    'doc_lease'           => 'Lease/Tenancy',
    'doc_sketch_map'      => 'Sketch Map',
];

$saved = 0;
foreach ($docTypes as $key => $label) {
    $path = handlePdfUpload($key);
    if ($path) {
        $ins = mysqli_prepare($conn,
            "INSERT INTO uploaded_documents (application_id, document_type, file_name, file_path)
             VALUES (?, ?, ?, ?)"
        );
        $basename = basename($path);
        mysqli_stmt_bind_param($ins, "isss", $app_id, $label, $basename, $path);
        mysqli_stmt_execute($ins);
        mysqli_stmt_close($ins);
        $saved++;
    }
}

header("Location: upload_application.php?success=1&saved=" . $saved);
exit();