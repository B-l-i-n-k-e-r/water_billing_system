<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $username   = trim($_POST['username'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $mobile     = trim($_POST['mobile'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($mobile) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $error = 'Username must be 3–30 characters (letters, numbers, underscore only).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!preg_match('/^(07\d{8}|01\d{8})$/', $mobile)) {
        $error = 'Mobile must be a valid Kenyan number (e.g. 0712345678 or 0112345678).';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Duplicate check on username OR email
        $check = mysqli_prepare($conn, "SELECT id FROM user WHERE username = ? OR email = ? LIMIT 1");
        mysqli_stmt_bind_param($check, "ss", $username, $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = 'An account with that username or email already exists.';
        } else {
            mysqli_stmt_close($check);

            $hashed    = password_hash($password, PASSWORD_DEFAULT);
            $level     = 3; // default: client
            $full_name = trim($first_name . ' ' . $last_name);

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO user (name, email, username, mobile, password, userlevel, verified) 
                 VALUES (?, ?, ?, ?, ?, ?, 0)"
            );
            mysqli_stmt_bind_param($stmt, "sssssi", $full_name, $email, $username, $mobile, $hashed, $level);

            if (mysqli_stmt_execute($stmt)) {
                $new_user_id = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt);

                // Generate OTP and send email
                require_once 'mailer.php';
                require_once 'otp_helper.php';
                require_once 'emails/otp_email.php';

                $rl = otpRateLimit($conn, $new_user_id, 'verify');
                if (!$rl['allowed']) {
                    error_log("OTP rate limited for user {$new_user_id}");
                    session_regenerate_id(true);
                    $_SESSION['pending_user_id']    = $new_user_id;
                    $_SESSION['pending_user_email'] = $email;
                    $_SESSION['pending_user_name']  = $full_name;
                    header("Location: otp.php");
                    exit();
                }

                $code = createOTP($conn, $new_user_id, 'verify', 10);
                $body = otpEmailBody($full_name, $code, 'verify');
                $mail = sendEmail($email, 'Verify Your NCWSC Account', $body, $full_name);

                if (!$mail['ok']) {
                    error_log("OTP email failed for {$email}: " . ($mail['error'] ?? 'unknown'));
                }

                session_regenerate_id(true);
                $_SESSION['pending_user_id']    = $new_user_id;
                $_SESSION['pending_user_email'] = $email;
                $_SESSION['pending_user_name']  = $full_name;

                header("Location: otp.php");
                exit();
            } else {
                $error = 'Registration failed: ' . mysqli_error($conn);
                mysqli_stmt_close($stmt);
            }
        }
    }
}

$pageTitle = 'Create Account';
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
  <div class="rise-in relative w-full max-w-lg">
    <div class="glass rounded-2xl shadow-xl shadow-slate-900/5
                ring-1 ring-slate-900/5 p-8 sm:p-10">

      <!-- Header -->
      <div class="text-center mb-8">
        <div class="mx-auto w-12 h-12 rounded-xl
                    bg-gradient-to-br from-slate-800 to-teal-700
                    flex items-center justify-center shadow-lg shadow-slate-900/20 mb-4">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M18 9a6 6 0 10-12 0c0 4 3 6 6 12 3-6 6-8 6-12z"/>
            <circle cx="12" cy="9" r="2"/>
          </svg>
        </div>
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Create your account</h1>
        <p class="text-sm text-slate-500 mt-1">Join NCWSC Online Services in a few steps</p>
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

      <form action="register.php" method="POST" class="space-y-4">

        <!-- Name row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="first_name" class="block text-sm font-medium text-slate-700 mb-1.5">First name</label>
            <input id="first_name" type="text" name="first_name" required placeholder="John"
                   value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
                   class="focus-teal w-full px-4 py-2.5 rounded-xl bg-white/70 text-slate-900 placeholder-slate-400
                          border border-slate-200 outline-none transition hover:border-slate-300 focus:bg-white">
          </div>
          <div>
            <label for="last_name" class="block text-sm font-medium text-slate-700 mb-1.5">Last name</label>
            <input id="last_name" type="text" name="last_name" required placeholder="Doe"
                   value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                   class="focus-teal w-full px-4 py-2.5 rounded-xl bg-white/70 text-slate-900 placeholder-slate-400
                          border border-slate-200 outline-none transition hover:border-slate-300 focus:bg-white">
          </div>
        </div>

        <!-- Username -->
        <div>
          <label for="username" class="block text-sm font-medium text-slate-700 mb-1.5">Username</label>
          <input id="username" type="text" name="username" required placeholder="johndoe"
                 pattern="[A-Za-z0-9_]{3,30}" maxlength="30"
                 autocomplete="username"
                 value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                 class="focus-teal w-full px-4 py-2.5 rounded-xl bg-white/70 text-slate-900 placeholder-slate-400
                        border border-slate-200 outline-none transition hover:border-slate-300 focus:bg-white">
          <p class="text-xs text-slate-500 mt-1.5">3–30 characters. Letters, numbers, underscore only.</p>
        </div>

        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email address</label>
          <input id="email" type="email" name="email" required placeholder="you@example.com"
                 autocomplete="email"
                 value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                 class="focus-teal w-full px-4 py-2.5 rounded-xl bg-white/70 text-slate-900 placeholder-slate-400
                        border border-slate-200 outline-none transition hover:border-slate-300 focus:bg-white">
        </div>

        <!-- Mobile -->
        <div>
          <label for="mobile" class="block text-sm font-medium text-slate-700 mb-1.5">Mobile number</label>
          <input id="mobile" type="text" name="mobile" required placeholder="0712345678"
                 pattern="07[0-9]{8}|01[0-9]{8}" maxlength="10"
                 autocomplete="tel" inputmode="numeric"
                 value="<?php echo htmlspecialchars($_POST['mobile'] ?? ''); ?>"
                 class="focus-teal w-full px-4 py-2.5 rounded-xl bg-white/70 text-slate-900 placeholder-slate-400
                        border border-slate-200 outline-none transition hover:border-slate-300 focus:bg-white">
          <p class="text-xs text-slate-500 mt-1.5">Format: 0712345678 or 0112345678</p>
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
          <div class="relative">
            <input id="password" type="password" name="password" required placeholder="At least 6 characters"
                   autocomplete="new-password"
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

        <!-- Confirm Password -->
        <div>
          <label for="confirm_password" class="block text-sm font-medium text-slate-700 mb-1.5">Confirm password</label>
          <div class="relative">
            <input id="confirm_password" type="password" name="confirm_password" required placeholder="Re-enter password"
                   autocomplete="new-password"
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

        <!-- Submit -->
        <button type="submit"
                class="w-full py-2.5 mt-2 rounded-xl font-semibold text-white
                       bg-gradient-to-b from-slate-800 to-slate-900
                       shadow-lg shadow-slate-900/20
                       hover:shadow-xl hover:shadow-slate-900/25 hover:-translate-y-0.5
                       active:translate-y-0
                       focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-600
                       transition-all duration-200">
          Create account
        </button>
      </form>

      <!-- Divider -->
      <div class="my-6 flex items-center gap-3 text-xs uppercase tracking-wider text-slate-400">
        <span class="h-px flex-1 bg-slate-200"></span>
        <span>Already registered?</span>
        <span class="h-px flex-1 bg-slate-200"></span>
      </div>

      <!-- Login link -->
      <a href="login.php"
         class="block w-full text-center py-2.5 rounded-xl font-medium text-slate-700
                border border-slate-200 hover:border-slate-300 hover:bg-white/60
                transition">
        Sign in instead
      </a>
    </div>

    <!-- back to home -->
    <p class="text-center text-xs text-slate-500 mt-6">
      <a href="index.php" class="hover:text-slate-700 transition">&larr; Back to home</a>
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