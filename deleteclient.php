<?php
include 'auth.php';
checkLevel([1, 2, 3]); 
session_start();
if (!isset($_SESSION['id'])) {
    echo '<script>window.location.href="index.php";</script>';
    exit();
}

include 'db.php';

// Truncate the owners table
$q = "TRUNCATE TABLE owners";
$result = mysqli_query($conn, $q);

if ($result) {
    // Optionally truncate tempo_bill as well to prevent orphaned data
    mysqli_query($conn, "TRUNCATE TABLE tempo_bill");
    
    header("Location: clients.php");
} else {
    echo "Error truncating table: " . mysqli_error($conn);
}
exit();
?>