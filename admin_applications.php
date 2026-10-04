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
$status = trim($_GET['status'] ?? '');

$sql = "SELECT a.*, u.name AS user_name, u.email AS user_email
        FROM applications a
        LEFT JOIN user u ON u.id = a.user_id
        WHERE 1=1";
$params = [];
$types  = '';

if ($search !== '') {
    $sql .= " AND (a.application_number LIKE ? OR a.full_name LIKE ? OR a.email LIKE ? OR a.phone LIKE ?)";
    $like = "%$search%";
    for ($i = 0; $i < 4; $i++) $params[] = $like;
    $types .= 'ssss';
}
if ($status !== '' && in_array($status, ['pending','in_review','approved','rejected','completed'])) {
    $sql .= " AND a.status = ?";
    $params[] = $status;
    $types .= 's';
}
$sql .= " ORDER BY a.created_at DESC LIMIT 200";

$stmt = mysqli_prepare($conn, $sql);
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$apps = [];
while ($row = mysqli_fetch_assoc($res)) $apps[] = $row;
mysqli_stmt_close($stmt);

$flash = $_GET['success'] ?? null;

$statusColors = [
    'pending'    => 'bg-yellow-100 text-yellow-800',
    'in_review'  => 'bg-blue-100 text-blue-800',
    'approved'   => 'bg-green-100 text-green-800',
    'rejected'   => 'bg-red-100 text-red-800',
    'completed'  => 'bg-emerald-100 text-emerald-800',
];
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Water / Sewer Applications</h1>
            <p class="text-sm text-gray-500 mt-1">Review and process new connection applications</p>
        </div>
    </div>

    <?php if ($flash === 'updated'): ?>
        <div class="mb-4 p-3 rounded-md bg-green-50 border border-green-200 text-green-700 text-sm">
            Application status updated.
        </div>
    <?php endif; ?>

    <form method="GET" class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 mb-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
        <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>"
               placeholder="Search by application no, name, email, or phone"
               class="px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none sm:col-span-2">
        <div class="flex gap-2">
            <select name="status" class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                <option value="">All Statuses</option>
                <?php foreach (['pending','in_review','approved','rejected','completed'] as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo $status===$s?'selected':''; ?>>
                        <?php echo ucwords(str_replace('_',' ', $s)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-blue text-white font-semibold py-2 px-4 rounded-md">Filter</button>
        </div>
    </form>

    <div class="bg-white rounded-lg shadow-md border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-600 text-white">
                    <tr>
                        <th class="p-3">APP #</th>
                        <th class="p-3">APPLICANT</th>
                        <th class="p-3">TYPE</th>
                        <th class="p-3">SERVICE</th>
                        <th class="p-3">STATUS</th>
                        <th class="p-3">DATE</th>
                        <th class="p-3">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($apps)): ?>
                        <tr><td colspan="7" class="p-8 text-center text-gray-500">No applications found.</td></tr>
                    <?php else: foreach ($apps as $a): 
                        $cls = $statusColors[$a['status']] ?? 'bg-gray-100 text-gray-800';
                    ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="p-3 font-semibold text-gray-800"><?php echo htmlspecialchars($a['application_number']); ?></td>
                            <td class="p-3">
                                <div class="font-medium text-gray-800"><?php echo htmlspecialchars($a['full_name']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($a['email']); ?></div>
                            </td>
                            <td class="p-3 capitalize"><?php echo htmlspecialchars($a['application_type']); ?></td>
                            <td class="p-3 capitalize"><?php echo htmlspecialchars($a['service_type']); ?></td>
                            <td class="p-3">
                                <span class="<?php echo $cls; ?> text-xs px-2 py-1 rounded uppercase font-semibold">
                                    <?php echo str_replace('_',' ', $a['status']); ?>
                                </span>
                            </td>
                            <td class="p-3"><?php echo htmlspecialchars(date('d M Y', strtotime($a['created_at']))); ?></td>
                            <td class="p-3">
                                <a href="admin_application_view.php?id=<?php echo intval($a['id']); ?>" 
                                   class="text-ncwsc-blue hover:underline">Review</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>