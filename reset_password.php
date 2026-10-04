<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'db.php';

$token = trim($_GET['token'] ?? '');
$error = '';
$valid = false;
$user  = null;

if ($token === '') {
    $error = 'Missing reset token.';
} else {
    // Look up the token
    $stmt = mysqli_prepare($conn,
        "SELECT pr.id AS reset_id, pr.user_id, pr.expires_at, pr.used,
                u.name, u.email
         FROM password_resets pr
         LEFT JOIN user u ON u.id = pr.user_id
         WHERE pr.token = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if (!$row) {
        $error = 'Invalid reset link.';
    } elseif (intval($row['used']) === 1) {
        $error = 'This reset link has already been used.';
    } elseif (strtotime($row['expires_at']) < time()) {
        $error = 'This reset link has expired. Please request a new one.';
    } else {
        $valid = true;
        $user  = $row;
    }
}

include 'header.php';
?>

<div class="flex items-center justify-center min-h-[70vh] px-4 py-10">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8 border border-gray-100">

        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Reset Password</h1>
            <p class="text-sm text-gray-500 mt-2">Choose a new password for your account.</p>
        </div>

        <?php if (!$valid): ?>
            <div class="mb-4 p-3 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <div class="text-center pt-2 text-sm">
                <a href="forgot_password.php" class="text-ncwsc-blue hover:underline">Request a new reset link</a>
            </div>
        <?php else: ?>

            <?php if (isset($_GET['err'])): ?>
                <div class="mb-4 p-3 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm">
                    <?php
                    echo $_GET['err'] === 'match'
                        ? 'Passwords do not match.'
                        : ($_GET['err'] === 'short'
                            ? 'Password must be at least 6 characters.'
                            : 'Please fill all fields.');
                    ?>
                </div>
            <?php endif; ?>

            <p class="text-sm text-gray-600 mb-4">
                Hi <strong><?php echo htmlspecialchars($user['name'] ?: 'User'); ?></strong>,
                enter your new password below.
            </p>

            <form action="reset_password_process.php" method="POST" class="space-y-4">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password:<span class="text-red-500">*</span></label>
                    <input type="password" name="password" required minlength="6"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password:<span class="text-red-500">*</span></label>
                    <input type="password" name="confirm" required minlength="6"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                </div>

                <button type="submit" class="w-full btn-green text-white font-semibold py-2 rounded-md transition">
                    Change Password
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>