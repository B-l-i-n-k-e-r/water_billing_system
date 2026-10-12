<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'db.php';

// Load config ONCE — outside any conditional, at the top
$config = require __DIR__ . '/config.php';

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

        // DEBUG: log what we found (remove this once things work)
        error_log("PWD RESET DEBUG: identity='{$identity}' user=" . (
            $user ? "found id={$user['id']} email={$user['email']}" : "NOT FOUND"
        ));

        // Always show the same message (don't leak whether the account exists)
        $success = 'If that account exists, a password reset link has been sent to the registered email. Please check your inbox (and spam folder).';

        if ($user && !empty($user['email'])) {
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
                       style=\"background:#0d7d8c;color:#fff;text-decoration:none;padding:12px 24px;border-radius:8px;display:inline-block;font-weight:600;\">
                       Reset My Password
                    </a>
                </p>
                <p style=\"color:#666;font-size:13px;margin-top:24px;\">
                    If the button doesn't work, paste this URL into your browser:<br>
                    <span style=\"word-break:break-all;color:#0d7d8c;\">{$resetUrl}</span>
                </p>
                <p style=\"color:#666;font-size:13px;margin-top:16px;\">
                    If you did not request this, you can safely ignore this email. Your password will not change.
                </p>
            ");

            $mail = sendEmail($user['email'], 'Reset Your NCWSC Password', $body, $name);

            // DEBUG: log the send result (remove this once things work)
            error_log("PWD RESET DEBUG: sendEmail to={$user['email']} ok=" . (!empty($mail['ok']) ? '1' : '0') . " err=" . ($mail['error'] ?? 'none'));

            if (!$mail['ok']) {
                error_log("Password reset email failed for {$user['email']}: " . ($mail['error'] ?? 'unknown'));
            }
        }
    }
}

$pageTitle = 'Forgot Password';
include 'header.php';
?>

<style>
  @keyframes riseIn {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  .rise-in { animation: riseIn .5s cubic-bezier(.2,.8,.2,1) both; }

  .glass {
    background: rgba(255,255,255,0.85);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
  }

  .auth-bg {
    background:
      radial-gradient(900px 450px at 85% -10%, rgba(13,125,140,0.12), transparent 60%),
      radial-gradient(700px 350px at -10% 20%, rgba(15,39,68,0.10), transparent 60%),
      linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
  }

  .focus-teal:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(13,125,140,0.18);
    border-color: #0d7d8c;
  }
</style>

<!-- Background -->
<section class="auth-bg relative flex items-center justify-center
                min-h-[calc(100vh-4rem)] px-4 py-12 overflow-hidden">

  <!-- decorative blobs -->
  <div class="pointer-events-none absolute -top-32 -left-32 w-96 h-96 rounded-full
              bg-teal-500/10 blur-3xl"></div>
  <div class="pointer-events-none absolute -bottom-40 -right-24 w-[28rem] h-[28rem] rounded-full
              bg-slate-800/10 blur-3xl"></div>

  <!-- Card -->
  <div class="rise-in relative w-full max-w-md">
    <div class="glass rounded-2xl shadow-xl shadow-slate-900/5
                ring-1 ring-slate-900/5 p-8 sm:p-10">

      <!-- Header -->
      <div class="text-center mb-8">
        <div class="mx-auto w-12 h-12 rounded-xl
                    bg-gradient-to-br from-slate-800 to-teal-700
                    flex items-center justify-center shadow-lg shadow-slate-900/20 mb-4">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
          </svg>
        </div>
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Forgot password?</h1>
        <p class="text-sm text-slate-500 mt-1">
          Enter your email or username and we'll send you a reset link.
        </p>
      </div>

      <!-- Alerts -->
      <?php if ($error): ?>
        <div class="mb-5 flex items-start gap-3 p-3.5 rounded-xl bg-rose-50 ring-1 ring-rose-200/70 text-rose-800 text-sm">
          <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/>
          </svg>
          <span><?php echo htmlspecialchars($error); ?></span>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="mb-5 flex items-start gap-3 p-3.5 rounded-xl bg-emerald-50 ring-1 ring-emerald-200/70 text-emerald-800 text-sm">
          <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
          </svg>
          <span><?php echo htmlspecialchars($success); ?></span>
        </div>
      <?php endif; ?>

      <!-- Form -->
      <form action="forgot_password.php" method="POST" class="space-y-4">
        <div>
          <label for="identity" class="block text-sm font-medium text-slate-700 mb-1.5">Email or Username</label>
          <input id="identity" type="text" name="identity" required
                 placeholder="you@example.com"
                 autocomplete="username"
                 class="focus-teal w-full px-4 py-2.5 rounded-xl bg-white/70 text-slate-900 placeholder-slate-400
                        border border-slate-200 outline-none transition hover:border-slate-300 focus:bg-white">
        </div>

        <button type="submit"
                class="w-full py-2.5 rounded-xl font-semibold text-white
                       bg-gradient-to-b from-slate-800 to-slate-900
                       shadow-lg shadow-slate-900/20
                       hover:shadow-xl hover:shadow-slate-900/25 hover:-translate-y-0.5
                       active:translate-y-0
                       focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-600
                       transition-all duration-200">
          Send reset link
        </button>
      </form>

      <!-- Divider -->
      <div class="my-6 flex items-center gap-3 text-xs uppercase tracking-wider text-slate-400">
        <span class="h-px flex-1 bg-slate-200"></span>
        <span>Remembered it?</span>
        <span class="h-px flex-1 bg-slate-200"></span>
      </div>

      <!-- Back to login -->
      <a href="login.php"
         class="block w-full text-center py-2.5 rounded-xl font-medium text-slate-700
                border border-slate-200 hover:border-slate-300 hover:bg-white/60
                transition">
        Back to sign in
      </a>
    </div>

    <!-- back to home -->
    <p class="text-center text-xs text-slate-500 mt-6">
      <a href="index.php" class="hover:text-slate-700 transition">&larr; Back to home</a>
    </p>
  </div>
</section>

<?php include 'footer.php'; ?>