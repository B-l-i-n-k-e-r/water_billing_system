<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1]); // Admin only
include_once 'db.php';
include 'admin_header.php';

$search = trim($_GET['q'] ?? '');

$sql = "SELECT id, name, username, email, mobile, userlevel FROM user WHERE 1=1";
$params = [];
$types = '';
if ($search !== '') {
    $sql .= " AND (name LIKE ? OR username LIKE ? OR email LIKE ? OR mobile LIKE ?)";
    $like = "%$search%";
    for ($i = 0; $i < 4; $i++) $params[] = $like;
    $types .= 'ssss';
}
$sql .= " ORDER BY userlevel ASC, id ASC";

$stmt = mysqli_prepare($conn, $sql);
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$users = [];
while ($row = mysqli_fetch_assoc($res)) $users[] = $row;
mysqli_stmt_close($stmt);

$flash = $_GET['success'] ?? null;
$err   = $_GET['err'] ?? null;

$levelLabel = [1 => 'Admin', 2 => 'Cashier', 3 => 'Client'];
$levelColor = [
    1 => 'bg-purple-100 text-purple-700',
    2 => 'bg-blue-100 text-blue-700',
    3 => 'bg-gray-100 text-gray-700',
];
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
        <h1 class="text-2xl font-bold text-gray-800">User Management</h1>
        <a href="adduser.php" class="btn-blue text-white font-semibold py-2 px-6 rounded-md inline-flex items-center gap-2 w-max">
            <i data-lucide="user-plus" class="w-4 h-4"></i> Add New User
        </a>
    </div>

    <?php if ($flash === '1'): ?>
        <div class="mb-4 p-3 rounded-md bg-green-50 border border-green-200 text-green-700 text-sm">User saved successfully.</div>
    <?php elseif ($flash === 'deleted'): ?>
        <div class="mb-4 p-3 rounded-md bg-green-50 border border-green-200 text-green-700 text-sm">User deleted.</div>
    <?php endif; ?>

    <?php if ($err === 'self'): ?>
        <div class="mb-4 p-3 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm">You cannot delete your own account.</div>
    <?php elseif ($err === 'notfound'): ?>
        <div class="mb-4 p-3 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm">User not found.</div>
    <?php endif; ?>

    <form method="GET" class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 mb-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
        <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>"
               placeholder="Search by name, username, email, or mobile"
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
                        <th class="p-3">USERNAME</th>
                        <th class="p-3">EMAIL</th>
                        <th class="p-3">MOBILE</th>
                        <th class="p-3">LEVEL</th>
                        <th class="p-3">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="7" class="p-8 text-center text-gray-500">No users found.</td></tr>
                    <?php else: foreach ($users as $u):
                        $lvl = intval($u['userlevel']);
                        $lbl = $levelLabel[$lvl] ?? 'Unknown';
                        $cls = $levelColor[$lvl] ?? 'bg-gray-100 text-gray-700';
                    ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="p-3 font-semibold text-gray-800">#<?php echo intval($u['id']); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($u['name'] ?: '—'); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($u['username']); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($u['email'] ?: '—'); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($u['mobile'] ?: '—'); ?></td>
                            <td class="p-3">
                                <span class="<?php echo $cls; ?> text-xs px-2 py-1 rounded uppercase font-semibold"><?php echo $lbl; ?></span>
                            </td>
                            <td class="p-3">
                                <a href="edituser.php?id=<?php echo intval($u['id']); ?>" class="text-ncwsc-blue hover:underline mr-2">Edit</a>
                                <?php if (intval($u['id']) !== intval($_SESSION['id'])): ?>
                                    <a href="deluser.php?id=<?php echo intval($u['id']); ?>"
                                       class="text-red-500 hover:underline"
                                       onclick="return confirm('Delete this user?');">Delete</a>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs italic">(you)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>