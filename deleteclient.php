<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'auth.php';

// Restricted strictly to Admin (Level 1) for destructive database actions
checkLevel([1]);

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

include_once 'db.php';

// Process truncation using error handling
try {
    // Disable foreign key checks temporarily if tables are referenced
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");

    // Truncate owners table
    $truncate_owners = mysqli_query($conn, "TRUNCATE TABLE owners");
    
    // Truncate tempo_bill table to keep tables synchronized
    $truncate_tempo = mysqli_query($conn, "TRUNCATE TABLE tempo_bill");

    // Re-enable foreign key checks
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

    if ($truncate_owners && $truncate_tempo) {
        mysqli_close($conn);
        echo '<script>alert("All client records wiped successfully!"); window.location.href="clients.php";</script>';
        exit();
    } else {
        throw new Exception(mysqli_error($conn));
    }

} catch (Exception $e) {
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");
    mysqli_close($conn);
    
    echo '<script>alert("Error wiping clients: ' . addslashes($e->getMessage()) . '"); window.location.href="clients.php";</script>';
    exit();
}
?>