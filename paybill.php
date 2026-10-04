<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';
include_once 'tariff.php';
include 'admin_header.php';

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bill_id     = intval($_POST['bill_id'] ?? 0);
    $amount      = (float) ($_POST['amount'] ?? 0);
    $method      = trim($_POST['method'] ?? 'Cash');
    $reference   = trim($_POST['reference'] ?? '');
    $payment_date = trim($_POST['payment_date'] ?? date('Y-m-d'));

    if ($bill_id <= 0 || $amount <= 0) {
        $error = 'Invalid bill or amount.';
    } else {
        // Fetch bill
        $stmt = mysqli_prepare($conn, "SELECT * FROM bill WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $bill_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $bill = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        if (!$bill) {
            $error = 'Bill not found.';
        } else {
            $existingPaid = (float) ($bill['amount_paid'] ?? 0);
            $billAmount   = (float) ($bill['amount'] ?? $bill['price'] ?? 0);
            $newPaid      = $existingPaid + $amount;
            $balance      = max(0, $billAmount - $newPaid);

            if ($newPaid >= $billAmount) {
                $newStatus = 'paid';
            } elseif ($newPaid > 0) {
                $newStatus = 'partial';
            } else {
                $newStatus = 'unpaid';
            }

            // Update the bill
            $upd = mysqli_prepare($conn,
                "UPDATE bill SET amount_paid = ?, status = ? WHERE id = ?"
            );
            mysqli_stmt_bind_param($upd, "dsi", $newPaid, $newStatus, $bill_id);

            if (mysqli_stmt_execute($upd)) {
                mysqli_stmt_close($upd);

                // Log to transactions (payment is a negative amount)
                $txn = mysqli_prepare($conn,
                    "INSERT INTO transactions (account_number, transaction_date, description, amount, balance_after)
                     VALUES (?, ?, ?, ?, ?)"
                );
                if ($txn) {
                    $account = 'OWNER-' . intval($bill['owners_id']);
                    $desc = 'Payment via ' . $method . ($reference ? " ({$reference})" : '') . ' — Bill #' . $bill_id;
                    $negAmount = -1 * $amount;
                    mysqli_stmt_bind_param($txn, "sssdd", $account, $payment_date, $desc, $negAmount, $balance);
                    mysqli_stmt_execute($txn);
                    mysqli_stmt_close($txn);
                }

                $success = "Payment of KES " . number_format($amount, 2) . " recorded. New balance: KES " . number_format($balance, 2);

                // Reload the bill
                $stmt = mysqli_prepare($conn, "SELECT * FROM bill WHERE id = ? LIMIT 1");
                mysqli_stmt_bind_param($stmt, "i", $bill_id);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                $bill = mysqli_fetch_assoc($res);
                mysqli_stmt_close($stmt);
            } else {
                $error = 'Failed to record payment: ' . mysqli_error($conn);
                mysqli_stmt_close($upd);
            }
        }
    }
} else {
    // GET — preload bill from ?bill_id=N
    $preload_id = intval($_GET['bill_id'] ?? 0);
}

// Data for dropdowns
$bills = [];
$res = mysqli_query($conn,
    "SELECT b.id, b.amount, b.amount_paid, b.status, b.bill_month, o.fname, o.lname, o.contact
     FROM bill b
     LEFT JOIN owners o ON o.id = b.owners_id
     WHERE b.status != 'paid' OR b.status IS NULL
     ORDER BY b.id DESC
     LIMIT 500"
);
while ($row = mysqli_fetch_assoc($res)) $bills[] = $row;

// Preloaded bill (if any)
$preload = null;
if (isset($preload_id) && $preload_id > 0) {
    foreach ($bills as $b) {
        if (intval($b['id']) === $preload_id) { $preload = $b; break; }
    }
}
?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Record Payment</h1>
        <a href="viewpayment.php" class="text-sm text-gray-500 hover:text-ncwsc-blue">View All Payments &rarr;</a>
    </div>

    <?php if ($error): ?>
        <div class="mb-4 p-3 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="mb-4 p-3 rounded-md bg-green-50 border border-green-200 text-green-700 text-sm">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="paybill.php" class="bg-white p-8 rounded-lg shadow-md border border-gray-100 space-y-5">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Select Bill:<span class="text-red-500">*</span></label>
            <select name="bill_id" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                <option value="">-- Choose Unpaid Bill --</option>
                <?php foreach ($bills as $b):
                    $bal = (float)$b['amount'] - (float)$b['amount_paid'];
                ?>
                    <option value="<?php echo intval($b['id']); ?>"
                        <?php echo ($preload && intval($preload['id']) === intval($b['id'])) ? 'selected' : ''; ?>>
                        #<?php echo intval($b['id']); ?> —
                        <?php echo htmlspecialchars($b['fname'] . ' ' . $b['lname']); ?>
                        (<?php echo htmlspecialchars($b['contact']); ?>) —
                        Balance: <?php echo number_format($bal, 2); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Amount (KES):<span class="text-red-500">*</span></label>
                <input type="number" name="amount" step="0.01" min="0.01" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date:</label>
                <input type="date" name="payment_date" value="<?php echo date('Y-m-d'); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method:</label>
                <select name="method"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                    <option>Cash</option>
                    <option>M-Pesa</option>
                    <option>Bank Transfer</option>
                    <option>Cheque</option>
                    <option>Card</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reference / Receipt No:</label>
                <input type="text" name="reference" placeholder="e.g. QK123XYZ"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <a href="billing.php" class="text-gray-500 hover:text-gray-700 font-semibold py-2 px-4">Cancel</a>
            <button type="submit" class="btn-green text-white font-semibold py-2 px-6 rounded-md transition">
                Record Payment
            </button>
        </div>

    </form>
</div>

<?php include 'footer.php'; ?>