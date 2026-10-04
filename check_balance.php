<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once 'auth.php';
include_once 'db.php';
include 'header.php';

$user_id = intval($_SESSION['id']);
$account = trim($_GET['account'] ?? '');

$stmt = mysqli_prepare($conn, "SELECT * FROM customer_accounts WHERE user_id = ? AND account_number = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "is", $user_id, $account);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);
?>
<div class="max-w-2xl mx-auto px-4 py-16 text-center">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Account Balance</h1>
    <?php if (!$row): ?>
        <p class="text-red-600">Account not found.</p>
    <?php else: ?>
        <div class="bg-white p-8 rounded-lg shadow-md border border-gray-100">
            <p class="text-gray-500 mb-2">Account Number</p>
            <p class="text-2xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($row['account_number']); ?></p>
            <p class="text-gray-500 mb-2">Current Balance</p>
            <p class="text-3xl font-bold text-ncwsc-blue">KES <?php echo number_format((float)$row['balance'], 2); ?></p>
        </div>
    <?php endif; ?>
    <a href="selfcare.php" class="inline-block mt-6 text-ncwsc-blue hover:underline">&larr; Back</a>
</div>
<?php include 'footer.php'; ?>