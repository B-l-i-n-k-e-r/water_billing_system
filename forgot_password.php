<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'db.php';

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = trim($_POST['identity'] ?? '');

    if (empty($identity)) {
        $err = 'Please enter your email or username.';
    } else {
        // Check if user exists
        $stmt = mysqli_prepare($conn, "SELECT id FROM user WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $identity);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            // In real app: generate token, save to DB, email reset link
            $msg = 'If that account exists, a reset link has been sent to the registered email.';
        } else {
            // Don't reveal whether account exists (security)
            $msg = 'If that account exists, a reset link has been sent to the registered email.';
        }
        mysqli_stmt_close($stmt);
    }
}

include 'header.php';
?>

<div class="flex items-center justify-center min-h-[70vh] px-4 py-10">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8 border border-gray-100">

        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Forgot Password</h1>
            <p class="text-sm text-gray-500 mt-2">Enter your email or username to reset your password.</p>
        </div>

        <?php if ($err): ?>
            <div class="mb-4 p-3 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm"><?php echo htmlspecialchars($err); ?></div>
        <?php endif; ?>

        <?php if ($msg): ?>
            <div class="mb-4 p-3 rounded-md bg-green-50 border border-green-200 text-green-700 text-sm"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <form action="forgot_password.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email or Username:<span class="text-red-500">*</span></label>
                <input type="text" name="identity" required placeholder="Enter Email or Username"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
            </div>

            <button type="submit" class="w-full btn-blue text-white font-semibold py-2 rounded-md transition">
                Send Reset Link
            </button>

            <div class="text-center pt-2">
                <a href="index.php" class="text-sm text-ncwsc-blue hover:underline">Back to Login</a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>