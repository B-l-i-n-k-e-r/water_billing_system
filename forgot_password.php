<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'db.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = trim($_POST['identity'] ?? '');

    if ($identity === '') {
        $error = 'Please enter your email or username.';
    } else {
        // Look up user by username OR email
        $stmt = mysqli_prepare($conn,
            "SELECT id, name, email FROM user WHERE username = ? OR email = ? LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "ss", $identity, $identity);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        // Always show the same message (don't leak whether the account exists)
        $success = 'If that account exists, a password reset link has been sent to the registered email. Please check your inbox (and spam folder).';

        if ($user && !empty($user['email'])) {
            require_once 'config.php';
            $config = require 'config.php';

            // Generate a secure random token
            $token  = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour

            // Invalidate any existing unused tokens for this user
            $clear = mysqli_prepare($conn,
                "UPDATE password_resets SET used = 1 WHERE user_id = ? AND used = 0"
            );
            mysqli_stmt_bind_param($clear, "i", $user['id']);
            mysqli_stmt_execute($clear);
            mysqli_stmt_close($clear);

            // Insert new token
            $ins = mysqli_prepare($conn,
                "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)"
            );
            mysqli_stmt_bind_param($ins, "iss", $user['id'], $token, $expiry);
            mysqli_stmt_execute($ins);
            mysqli_stmt_close($ins);

            // Send email
            require_once 'mailer.php';

            $resetUrl = rtrim($config['app']['base_url'], '/') . '/reset_password.php?token=' . urlencode($token);
            $name     = $user['name'] ?: 'User';

            $body = emailTemplate('Reset Your NCWSC Password', "
                <p>Hello <strong>" . htmlspecialchars($name) . "</strong>,</p>
                <p>We received a request to reset your NCWSC Online Services password.</p>
                <p>Click the button below to choose a new password. This link is valid for <strong>1 hour</strong> and can only be used once.</p>
                <p style=\"margin-top:24px;\">
                    <a href=\"{$resetUrl}\"
                       style=\"background:#0066CC;color:#fff;text-decoration:none;padding:12px 24px;border-radius:6px;display:inline-block;\">
                       Reset My Password
                    </a>
                </p>
                <p style=\"color:#666;font-size:13px;margin-top:24px;\">
                    If the button doesn't work, paste this URL into your browser:<br>
                    <span style=\"word-break:break-all;color:#0066CC;\">{$resetUrl}</span>
                </p>
                <p style=\"color:#666;font-size:13px;margin-top:16px;\">
                    If you did not request this, you can safely ignore this email. Your password will not change.
                </p>
            ");

            $mail = sendEmail($user['email'], 'Reset Your NCWSC Password', $body, $name);
            if (!$mail['ok']) {
                error_log("Password reset email failed for {$user['email']}: " . ($mail['error'] ?? 'unknown'));
            }
        }
    }
}

include 'header.php';
?>

<div class="flex items-center justify-center min-h-[70vh] px-4 py-10">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8 border border-gray-100">

        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Forgot Password</h1>
            <p class="text-sm text-gray-500 mt-2">Enter your email or username to receive a reset link.</p>
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

        <form action="forgot_password.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email or Username:<span class="text-red-500">*</span></label>
                <input type="text" name="identity" required placeholder="Enter Email or Username"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
            </div>

            <button type="submit" class="w-full btn-blue text-white font-semibold py-2 rounded-md transition">
                Send Reset Link
            </button>

            <div class="text-center pt-2 text-sm text-gray-500">
                <a href="index.php" class="text-ncwsc-blue hover:underline">&larr; Back to Login</a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>