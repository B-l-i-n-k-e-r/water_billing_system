<?php
/**
 * Generate a unique application number like NCWSC-2026-000123
 */
function generateApplicationNumber($conn) {
    $year = date('Y');
    $prefix = "NCWSC-{$year}-";

    $stmt = mysqli_prepare($conn,
        "SELECT application_number FROM applications 
         WHERE application_number LIKE ? 
         ORDER BY id DESC LIMIT 1"
    );
    $like = $prefix . '%';
    mysqli_stmt_bind_param($stmt, "s", $like);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $next = 1;
    if ($row = mysqli_fetch_assoc($res)) {
        $parts = explode('-', $row['application_number']);
        $next = intval(end($parts)) + 1;
    }
    mysqli_stmt_close($stmt);

    return $prefix . str_pad($next, 6, '0', STR_PAD_LEFT);
}

/**
 * Generate a unique exhauster permit number
 */
function generatePermitNumber($conn) {
    $year = date('Y');
    $prefix = "EXH-{$year}-";

    $stmt = mysqli_prepare($conn,
        "SELECT permit_number FROM exhauster_permits 
         WHERE permit_number LIKE ? 
         ORDER BY id DESC LIMIT 1"
    );
    $like = $prefix . '%';
    mysqli_stmt_bind_param($stmt, "s", $like);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $next = 1;
    if ($row = mysqli_fetch_assoc($res)) {
        $parts = explode('-', $row['permit_number']);
        $next = intval(end($parts)) + 1;
    }
    mysqli_stmt_close($stmt);

    return $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);
}

/**
 * Safe file upload handler (PDF only, max 2MB)
 */
function handlePdfUpload($fileKey, $subdir = 'uploads') {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $file = $_FILES[$fileKey];

    // Size check
    if ($file['size'] > 2 * 1024 * 1024) return null;

    // Extension check
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') return null;

    // MIME type check
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, ['application/pdf', 'application/x-pdf'])) return null;

    // Magic byte check — first 5 bytes must be "%PDF-"
    $fh = fopen($file['tmp_name'], 'rb');
    if (!$fh) return null;
    $header = fread($fh, 5);
    fclose($fh);
    if ($header !== '%PDF-') return null;

    // Safe filename
    $dir = __DIR__ . '/' . $subdir;
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
    $newName  = $safeName . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';
    $target   = $dir . '/' . $newName;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        chmod($target, 0644);
        return $subdir . '/' . $newName;
    }
    return null;
}