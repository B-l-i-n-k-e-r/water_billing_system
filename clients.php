<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';
include_once 'tariff.php';
include 'admin_header.php';

$search = trim($_GET['q'] ?? '');

$sql = "SELECT o.*,
          (SELECT COUNT(*) FROM bill b WHERE b.owners_id = o.id) AS bill_count,
          (SELECT COALESCE(SUM(b.amount - b.amount_paid), 0) FROM bill b 
             WHERE b.owners_id = o.id AND b.status != 'paid') AS outstanding
        FROM owners o
        WHERE 1=1";
$params = [];
$types = '';
if ($search !== '') {
    $sql .= " AND (o.fname LIKE ? OR o.lname LIKE ? OR o.contact LIKE ? OR o.email LIKE ? OR o.address LIKE ?)";
    $like = "%$search%";
    for ($i = 0; $i < 5; $i++) $params[] = $like;
    $types .= 'sssss';
}
$sql .= " ORDER BY o.id DESC LIMIT 200";

$stmt = mysqli_prepare($conn, $sql);
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$clients = [];
while ($row = mysqli_fetch_assoc($res)) $clients[] = $row;
mysqli_stmt_close($stmt);

$flash = $_GET['success'] ?? null;
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
        <h1 class="text-2xl font-bold text-gray-800">Clients</h1>
        <a href="addclient.php" class="btn-blue text-white font-semibold py-2 px-6 rounded-md inline-flex items-center gap-2 w-max">
            <i data-lucide="user-plus" class="w-4 h-4"></i> Add New Client
        </a>
    </div>

    <?php if ($flash === '1'): ?>
        <div class="mb-4 p-3 rounded-md bg-green-50 border border-green-200 text-green-700 text-sm">
            Client saved successfully.
        </div>
    <?php elseif ($flash === 'deleted'): ?>
        <div class="mb-4 p-3 rounded-md bg-green-50 border border-green-200 text-green-700 text-sm">
            Client deleted.
        </div>
    <?php endif; ?>

    <form method="GET" class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 mb-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
        <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>"
               placeholder="Search by name, phone, email, or address"
               class="px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none sm:col-span-2">
        <button type="submit" class="btn-blue text-white font-semibold py-2 px-4 rounded-md">Search</button>
    </form>

    <div class="bg-white rounded-lg shadow-md border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-600 text-white">
                    <tr>
                        <th class="p-3">#</th>
                        <th class="p-3">NAME</th>
                        <th class="p-3">CONTACT</th>
                        <th class="p-3">EMAIL</th>
                        <th class="p-3">ADDRESS</th>
                        <th class="p-3 text-center">BILLS</th>
                        <th class="p-3 text-right">OUTSTANDING</th>
                        <th class="p-3">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clients)): ?>
                        <tr><td colspan="8" class="p-0">
    <?php emptyState(
        'users',
        'No clients yet',
        'Get started by adding your first customer. You can create bills, record payments, and track outstanding balances for each one.',
        'Add First Client',
        'addclient.php'
    ); ?>
</td></tr>
                    <?php else: foreach ($clients as $c): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="p-3 font-semibold text-gray-800">#<?php echo intval($c['id']); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($c['fname'] . ' ' . $c['mi'] . ' ' . $c['lname']); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($c['contact']); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($c['email'] ?: '—'); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($c['address']); ?></td>
                            <td class="p-3 text-center"><?php echo intval($c['bill_count']); ?></td>
                            <td class="p-3 text-right font-semibold <?php echo ((float)$c['outstanding'] > 0) ? 'text-red-600' : 'text-gray-800'; ?>">
                                <?php echo number_format((float) $c['outstanding'], 2); ?>
                            </td>
                            <td class="p-3">
                                <a href="addbill.php?owners_id=<?php echo intval($c['id']); ?>" class="text-ncwsc-blue hover:underline mr-2">Bill</a>
                               <a href="deleteclient.php?id=<?php echo intval($c['id']); ?>"                                    class="text-red-500 hover:underline"
                                   onclick="return confirm('Delete this client and all their bills?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>