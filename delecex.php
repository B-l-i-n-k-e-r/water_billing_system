<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'auth.php';
checkLevel([1, 2, 3]);

include_once 'db.php';

// Ensure request method is POST and ID is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    
    $id = intval($_POST['id']);

    if ($id <= 0) {
        mysqli_close($conn);
        echo '<script>alert("Invalid client ID specified."); window.location.href="clients.php";</script>';
        exit();
    }

    // Begin atomic transaction
    mysqli_begin_transaction($conn);

    try {
        // 1. Delete associated temporary bill record first
        $stmt_tempo = mysqli_prepare($conn, "DELETE FROM tempo_bill WHERE id = ?");
        if (!$stmt_tempo) {
            throw new Exception("Prepare failed (tempo_bill): " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt_tempo, "i", $id);
        
        if (!mysqli_stmt_execute($stmt_tempo)) {
            throw new Exception("Execute failed (tempo_bill): " . mysqli_stmt_error($stmt_tempo));
        }
        mysqli_stmt_close($stmt_tempo);

        // 2. Delete owner record
        $stmt_owner = mysqli_prepare($conn, "DELETE FROM owners WHERE id = ?");
        if (!$stmt_owner) {
            throw new Exception("Prepare failed (owners): " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt_owner, "i", $id);
        
        if (!mysqli_stmt_execute($stmt_owner)) {
            throw new Exception("Execute failed (owners): " . mysqli_stmt_error($stmt_owner));
        }
        mysqli_stmt_close($stmt_owner);

        // Commit transaction if both delete operations succeed
        mysqli_commit($conn);
        mysqli_close($conn);

        echo '<script>alert("Client deleted successfully!"); window.location.href="clients.php";</script>';
        exit();

    } catch (Exception $e) {
        // Rollback on database failure
        mysqli_rollback($conn);
        mysqli_close($conn);

        echo '<script>alert("Error deleting client: ' . addslashes($e->getMessage()) . '"); window.location.href="clients.php";</script>';
        exit();
    }
} else {
    if (isset($conn)) {
        mysqli_close($conn);
    }
    header("Location: clients.php");
    exit();
}
?>