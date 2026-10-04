<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1]);
include_once 'db.php';
include 'admin_header.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: user.php");
    exit();
}

$stmt = mysqli_prepare($conn, "SELECT id, name, username, email, mobile, userlevel FROM user WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$user) {
    header("Location: user.php?err=notfound");
    exit();
}

$err = $_GET['err'] ?? null;
?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit User</h1>
        <a href="user.php" class="text-sm text-gray-500 hover:text-ncwsc-blue">&larr; Back to Users</a>
    </div>

    <?php if ($err === 'empty'): ?>
        <div class="mb-4 p-3 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm">Required fields are missing.</div>
    <?php elseif ($err === 'match'): ?>
        <div class="mb-4 p-3 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm">Passwords do not match.</div>
    <?php elseif ($err === 'exists'): ?>
        <div class="mb-4 p-3 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm">Username or email already used by another account.</div>
    <?php elseif ($err === 'db'): ?>
        <div class="mb-4 p-3 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm">Database error. Please try again.</div>
    <?php endif; ?>

    <form action="edituserecex.php" method="POST" class="bg-white p-8 rounded-lg shadow-md border border-gray-100 space-y-5">

        <input type="hidden" name="id" value="<?php echo intval($user['id']); ?>">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name:<span class="text-red-500">*</span></label>
                <input type="text" name="name" required value="<?php echo htmlspecialchars($user['name']); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username:<span class="text-red-500">*</span></label>
                <input type="text" name="username" required value="<?php echo htmlspecialchars($user['username']); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mobile:</label>
                <input type="text" name="mobile" maxlength="10" value="<?php echo htmlspecialchars($user['mobile'] ?? ''); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">New Password:</label>
                <input type="password" name="password"
                       placeholder="Leave blank to keep current"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password:</label>
                <input type="password" name="confirm"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">User Level:<span class="text-red-500">*</span></label>
            <select name="userlevel" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                <option value="1" <?php echo (intval($user['userlevel'])===1)?'selected':''; ?>>Admin (Level 1)</option>
                <option value="2" <?php echo (intval($user['userlevel'])===2)?'selected':''; ?>>Cashier (Level 2)</option>
                <option value="3" <?php echo (intval($user['userlevel'])===3)?'selected':''; ?>>Client (Level 3)</option>
            </select>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <a href="user.php" class="text-gray-500 hover:text-gray-700 font-semibold py-2 px-4">Cancel</a>
            <button type="submit" class="btn-green text-white font-semibold py-2 px-6 rounded-md transition">
                Update User
            </button>
        </div>

    </form>
</div>

<?php include 'footer.php'; ?>