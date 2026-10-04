<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';
include 'admin_header.php';

$search = trim($_GET['q'] ?? '');
$status = trim($_GET['status'] ?? '');

$sql = "SELECT b.*, o.fname, o.lname, o.contact
        FROM bill b
        LEFT JOIN owners o ON o.id = b.owners_id
        WHERE 1=1";
$params = [];
$types  = '';

if ($search !== '') {
    $sql .= " AND (o.fname LIKE ? OR o.lname LIKE ? OR o.contact LIKE ? OR b.id = ?)";
    $like = "%$search%";
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $search;
    $types .= 'ssss';
}
if ($status !== '' && in_array($status, ['unpaid','partial','paid'])) {
    $sql .= " AND b.status = ?";
    $params[] = $status;
    $types .= 's';
}
$sql .= " ORDER BY b.id DESC LIMIT 200";

$stmt = mysqli_prepare($conn, $sql);
if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$bills = [];
while ($row = mysqli_fetch_assoc($res)) $bills[] = $row;
mysqli_stmt_close($stmt);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
        <h1 class="text-2xl font-bold text-gray-800">Billing</h1>
        <a href="addbill.php" class="btn-blue text-white font-semibold py-2 px-6 rounded-md inline-flex items-center gap-2 w-max">
            <i data-lucide="plus" class="w-4 h-4"></i> Create New Bill
        </a>
    </div>

    <form method="GET" class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 mb-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
        <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>"
               placeholder="Search by name, phone, or bill ID"
               class="px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none sm:col-span-2">
        <div class="flex gap-2">
            <select name="status" class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                <option value="">All Statuses</option>
                <option value="unpaid"  <?php echo $status==='unpaid'?'selected':''; ?>>Unpaid</option>
                <option value="partial" <?php echo $status==='partial'?'selected':''; ?>>Partial</option>
                <option value="paid"    <?php echo $status==='paid'?'selected':''; ?>>Paid</option>
            </select>
            <button type="submit" class="btn-blue text-white font-semibold py-2 px-4 rounded-md">Filter</button>
        </div>
    </form>

    <div class="bg-white rounded-lg shadow-md border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-600 text-white">
                    <tr>
                        <th class="p-3">#</th>
                        <th class="p-3">CUSTOMER</th>
                        <th class="p-3">CONTACT</th>
                        <th class="p-3">PREV</th>
                        <th class="p-3">PRES</th>
                        <th class="p-3">CONSUMPTION</th>
                        <th class="p-3">AMOUNT</th>
                        <th class="p-3">PAID</th>
                        <th class="p-3">STATUS</th>
                        <th class="p-3">DATE</th>
                        <th class="p-3">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bills)): ?>
                        <tr><td colspan="11" class="p-4 text-center text-gray-500">No bills found.</td></tr>
                    <?php else: foreach ($bills as $b):
                        $statusClass = [
                            'unpaid'  => 'bg-red-100 text-red-700',
                            'partial' => 'bg-yellow-100 text-yellow-800',
                            'paid'    => 'bg-green-100 text-green-700',
                        ][$b['status'] ?? 'unpaid'];
                    ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="p-3 font-semibold text-gray-800">#<?php echo intval($b['id']); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars(($b['fname'] ?? '') . ' ' . ($b['lname'] ?? '')); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($b['contact'] ?? '—'); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($b['prev']); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($b['pres']); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($b['consumption'] ?? '—'); ?></td>
                            <td class="p-3 font-semibold text-gray-800"><?php echo number_format((float)($b['amount'] ?? 0), 2); ?></td>
                            <td class="p-3"><?php echo number_format((float)($b['amount_paid'] ?? 0), 2); ?></td>
                            <td class="p-3"><span class="<?php echo $statusClass; ?> text-xs px-2 py-1 rounded uppercase"><?php echo $b['status'] ?? 'unpaid'; ?></span></td>
                            <td class="p-3"><?php echo htmlspecialchars($b['date'] ?? '—'); ?></td>
                            <td class="p-3">
                                <a href="viewbill.php?id=<?php echo intval($b['id']); ?>" class="text-ncwsc-blue hover:underline">View</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>