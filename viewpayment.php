<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';
include_once 'tariff.php';   // ← ADDED: gives us kes() helper
include 'admin_header.php';

$search = trim($_GET['q'] ?? '');

$sql = "SELECT t.*, o.fname, o.lname, o.contact
        FROM transactions t
        LEFT JOIN owners o ON CONCAT('OWNER-', o.id) = t.account_number
        WHERE t.amount < 0";  // payments are negative

$params = [];
$types = '';
if ($search !== '') {
    $sql .= " AND (o.fname LIKE ? OR o.lname LIKE ? OR o.contact LIKE ? OR t.description LIKE ?)";
    $like = "%$search%";
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= 'ssss';
}
$sql .= " ORDER BY t.transaction_date DESC, t.id DESC LIMIT 200";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    die("SQL error: " . mysqli_error($conn));
}
if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$payments = [];
while ($row = mysqli_fetch_assoc($res)) $payments[] = $row;
mysqli_stmt_close($stmt);

$totalToday = 0;
$today = date('Y-m-d');
foreach ($payments as $p) {
    if ($p['transaction_date'] === $today) {
        $totalToday += abs((float) $p['amount']);
    }
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Payments</h1>
            <p class="text-sm text-gray-500 mt-1">
                Received today: <strong class="text-gray-800"><?php echo kes($totalToday); ?></strong>
                &nbsp;·&nbsp; Total shown: <strong class="text-gray-800"><?php echo count($payments); ?></strong>
            </p>
        </div>
        <a href="paybill.php" class="btn-green text-white font-semibold py-2 px-6 rounded-md inline-flex items-center gap-2 w-max">
            <i data-lucide="plus" class="w-4 h-4"></i> Record Payment
        </a>
    </div>

    <form method="GET" class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 mb-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
        <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>"
               placeholder="Search by name, phone, or description"
               class="px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none sm:col-span-2">
        <button type="submit" class="btn-blue text-white font-semibold py-2 px-4 rounded-md">Search</button>
    </form>

    <div class="bg-white rounded-lg shadow-md border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-600 text-white">
                    <tr>
                        <th class="p-3">TXN #</th>
                        <th class="p-3">DATE</th>
                        <th class="p-3">CUSTOMER</th>
                        <th class="p-3">DESCRIPTION</th>
                        <th class="p-3 text-right">AMOUNT</th>
                        <th class="p-3 text-right">BALANCE AFTER</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr><td colspan="6" class="p-8 text-center text-gray-500">
                            No payments recorded yet.
                            <a href="paybill.php" class="text-ncwsc-blue underline ml-1">Record the first one</a>.
                        </td></tr>
                    <?php else: foreach ($payments as $p): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="p-3 font-semibold text-gray-800">#<?php echo intval($p['id']); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($p['transaction_date']); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars(trim(($p['fname'] ?? '') . ' ' . ($p['lname'] ?? '')) ?: '—'); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($p['description']); ?></td>
                            <td class="p-3 text-right font-semibold text-green-700"><?php echo number_format(abs((float)$p['amount']), 2); ?></td>
                            <td class="p-3 text-right"><?php echo number_format((float)($p['balance_after'] ?? 0), 2); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>