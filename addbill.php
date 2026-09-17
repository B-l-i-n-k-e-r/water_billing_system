<?php
include 'auth.php';
checkLevel([1, 2]); // Restricted to Admin & Cashier

include 'db.php';

// Verify POST request and required inputs exist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['owners_id'])) {
    
    // Sanitize and validate inputs
    $owners_id = intval($_POST['owners_id']);
    $prev      = floatval($_POST['prev'] ?? 0);
    $pres      = floatval($_POST['pres'] ?? 0);
    $price     = floatval($_POST['price'] ?? 0);
    $date      = trim($_POST['date'] ?? date('Y-m-d'));

    // Calculate billing amounts
    $totalcun   = $pres - $prev;
    $pricetotal = $totalcun * $price;

    // Begin database transaction for atomic execution
    mysqli_begin_transaction($conn);

    try {
        // 1. Insert new bill record
        $stmt1 = mysqli_prepare($conn, "INSERT INTO bill (owners_id, prev, pres, price, date) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt1, "iddds", $owners_id, $prev, $pres, $pricetotal, $date);
        
        if (!mysqli_stmt_execute($stmt1)) {
            throw new Exception("Insert failed: " . mysqli_stmt_error($stmt1));
        }
        mysqli_stmt_close($stmt1);

        // 2. Update meter reading in tempo_bill
        $stmt2 = mysqli_prepare($conn, "UPDATE tempo_bill SET Prev = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt2, "di", $pres, $owners_id);
        
        if (!mysqli_stmt_execute($stmt2)) {
            throw new Exception("Update failed: " . mysqli_stmt_error($stmt2));
        }
        mysqli_stmt_close($stmt2);

        // Commit transaction if both operations succeeded
        mysqli_commit($conn);

        echo '<script>alert("Bill successfully added!"); window.location.href="bill.php";</script>';
        exit();

    } catch (Exception $e) {
        // Rollback any database changes if an error occurred
        mysqli_rollback($conn);

        echo '<script>alert("Error processing bill: ' . addslashes($e->getMessage()) . '"); window.location.href="bill.php";</script>';
        exit();
    }
} else {
    // Redirect if accessed directly without POST data
    header("Location: bill.php");
    exit();
}
?>