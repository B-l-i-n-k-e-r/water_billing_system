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

$pageTitle = 'Reset Password';
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

  <div class="pointer-events-none absolute -top-32 -left-32 w-96 h-96 rounded-full
              bg-teal-500/10 blur-3xl"></div>
  <div class="pointer-events-none absolute -bottom-40 -right-24 w-[28rem] h-[28rem] rounded-full
              bg-slate-800/10 blur-3xl"></div>

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
                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
          </svg>
        </div>
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">
          <?= $valid ? 'Set a new password' : 'Reset link problem' ?>
        </h1>
        <p class="text-sm text-slate-500 mt-1">
          <?= $valid
              ? 'Choose a strong password for your account.'
              : 'We couldn\'t use this reset link.' ?>
        </p>
      </div>

      <?php if (!$valid): ?>
        <!-- Invalid token state -->
        <div class="mb-5 flex items-start gap-3 p-3.5 rounded-xl bg-rose-50 ring-1 ring-rose-200/70 text-rose-800 text-sm">
          <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/>
          </svg>
          <span><?= htmlspecialchars($error) ?></span>
        </div>

        <a href="forgot_password.php"
           class="block w-full text-center py-2.5 rounded-xl font-semibold text-white
                  bg-gradient-to-b from-slate-800 to-slate-900
                  hover:shadow-xl hover:-translate-y-0.5
                  transition-all duration-200">
          Request a new link
        </a>

      <?php else: ?>
        <!-- Valid token — password form -->
        <?php if (isset($_GET['err'])): ?>
          <div class="mb-5 flex items-start gap-3 p-3.5 rounded-xl bg-rose-50 ring-1 ring-rose-200/70 text-rose-800 text-sm">
            <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/>
            </svg>
            <span><?php
              echo htmlspecialchars(
                $_GET['err'] === 'match'
                  ? 'Passwords do not match.'
                  : ($_GET['err'] === 'short'
                      ? 'Password must be at least 6 characters.'
                      : 'Please fill all fields.')
              );
            ?></span>
          </div>
        <?php endif; ?>

        <p class="text-sm text-slate-600 mb-5">
          Hi <strong class="text-slate-800"><?= htmlspecialchars($user['name'] ?: 'User') ?></strong>,
          enter your new password below.
        </p>

        <form action="reset_password_process.php" method="POST" class="space-y-4">
          <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

          <div>
            <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">New password</label>
            <div class="relative">
              <input id="password" type="password" name="password" required minlength="6"
                     autocomplete="new-password" placeholder="At least 6 characters"
                     class="focus-teal w-full px-4 py-2.5 pr-11 rounded-xl bg-white/70 text-slate-900 placeholder-slate-400
                            border border-slate-200 outline-none transition hover:border-slate-300 focus:bg-white">
              <button type="button" onclick="togglePw(this)"
                      class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-700 transition"
                      aria-label="Show password">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
            </div>
          </div>

          <div>
            <label for="confirm" class="block text-sm font-medium text-slate-700 mb-1.5">Confirm new password</label>
            <div class="relative">
              <input id="confirm" type="password" name="confirm" required minlength="6"
                     autocomplete="new-password" placeholder="Re-enter password"
                     class="focus-teal w-full px-4 py-2.5 pr-11 rounded-xl bg-white/70 text-slate-900 placeholder-slate-400
                            border border-slate-200 outline-none transition hover:border-slate-300 focus:bg-white">
              <button type="button" onclick="togglePw(this)"
                      class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-700 transition"
                      aria-label="Show password">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
            </div>
          </div>

          <button type="submit"
                  class="w-full py-2.5 mt-2 rounded-xl font-semibold text-white
                         bg-gradient-to-b from-slate-800 to-slate-900
                         shadow-lg shadow-slate-900/20
                         hover:shadow-xl hover:shadow-slate-900/25 hover:-translate-y-0.5
                         active:translate-y-0
                         focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-600
                         transition-all duration-200">
            Change password
          </button>
        </form>
      <?php endif; ?>
    </div>

    <p class="text-center text-xs text-slate-500 mt-6">
      <a href="login.php" class="hover:text-slate-700 transition">Back to sign in</a>
    </p>
  </div>
</section>

<script>
function togglePw(btn) {
  const input = btn.parentElement.querySelector('input');
  const isPw = input.type === 'password';
  input.type = isPw ? 'text' : 'password';
  btn.setAttribute('aria-label', isPw ? 'Hide password' : 'Show password');
}
</script>

<?php include 'footer.php'; ?>