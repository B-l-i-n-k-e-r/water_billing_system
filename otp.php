<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'db.php';
include_once 'mailer.php';
include_once 'otp_helper.php';
include_once 'emails/otp_email.php';

// Handle resend action
if (isset($_GET['resend']) && isset($_SESSION['pending_user_id'])) {
    $uid  = intval($_SESSION['pending_user_id']);
    $name = $_SESSION['pending_user_name'] ?? 'User';
    $mail = $_SESSION['pending_user_email'] ?? '';

    if ($uid > 0 && $mail) {
        require_once 'otp_helper.php';
        $rl = otpRateLimit($conn, $uid, 'verify');
        if (!$rl['allowed']) {
            header("Location: otp.php?cooldown=" . intval($rl['wait_seconds']));
            exit();
        }
        $code = createOTP($conn, $uid, 'verify', 10);
        $body = otpEmailBody($name, $code, 'verify');
        sendEmail($mail, 'Verify Your NCWSC Account', $body, $name);
        header("Location: otp.php?resent=1");
        exit();
    }
}
// Must have pending user in session
if (empty($_SESSION['pending_user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
if (isset($_GET['err'])) {
    $error = $_GET['err'] === 'code'
        ? 'Invalid or expired code. Please try again.'
        : ($_GET['err'] === 'empty' ? 'Please enter the code.' : 'Something went wrong.');
}

$pageTitle = 'Verify Account';
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

  /* OTP digit boxes */
  .otp-input {
    width: 100%;
    aspect-ratio: 1 / 1;
    text-align: center;
    font-size: 1.5rem;
    font-weight: 600;
    color: #0f172a;
    background: rgba(255,255,255,0.85);
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    outline: none;
    transition: all .15s ease;
  }
  .otp-input:hover   { border-color: #cbd5e1; }
  .otp-input:focus   { border-color:#0d7d8c; box-shadow: 0 0 0 3px rgba(13,125,140,0.18); background:#fff; }
  .otp-input.filled  { border-color:#0d7d8c; background:#fff; }
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
                  d="M3 8l9 6 9-6M21 8v10a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h14a2 2 0 012 2z"/>
          </svg>
        </div>
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Check your email</h1>
        <p class="text-sm text-slate-500 mt-1">
          We sent a 6-digit code to
          <span class="font-medium text-slate-700">
            <?= htmlspecialchars($_SESSION['pending_user_email'] ?? 'your email') ?>
          </span>
        </p>
      </div>

      <!-- Info notice -->
      <div class="mb-6 flex items-start gap-3 p-3.5 rounded-xl bg-teal-50/70 ring-1 ring-teal-200/60 text-teal-900 text-sm">
        <svg class="w-5 h-5 mt-0.5 shrink-0 text-teal-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>
          The code expires in <strong>10 minutes</strong>. If you don't see the email,
          check your <strong>spam folder</strong> and mark it as "not spam".
        </span>
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

      <?php if (isset($_GET['resent'])): ?>
        <div class="mb-5 flex items-start gap-3 p-3.5 rounded-xl bg-emerald-50 ring-1 ring-emerald-200/70 text-emerald-800 text-sm">
          <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
          </svg>
          <span>A new code has been sent to your email.</span>
        </div>
      <?php endif; ?>

      <?php if (isset($_GET['cooldown'])): ?>
        <div class="mb-5 flex items-start gap-3 p-3.5 rounded-xl bg-amber-50 ring-1 ring-amber-200/70 text-amber-800 text-sm">
          <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2M12 3a9 9 0 100 18 9 9 0 000-18z"/>
          </svg>
          <span>Please wait <strong><?= intval($_GET['cooldown']) ?>s</strong> before requesting another code.</span>
        </div>
      <?php endif; ?>

      <!-- Form -->
      <form action="otp_process.php" method="POST" class="space-y-5" id="otp-form">

        <!-- Hidden real input that submits the combined value -->
        <input type="hidden" name="otp" id="otp-value" value="">

        <!-- 6 digit boxes -->
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2 text-center">Enter 6-digit code</label>
          <div class="flex justify-between gap-2 sm:gap-3" id="otp-boxes">
            <?php for ($i = 0; $i < 6; $i++): ?>
              <input type="text"
                     inputmode="numeric"
                     autocomplete="<?= $i === 0 ? 'one-time-code' : 'off' ?>"
                     maxlength="1"
                     pattern="\d"
                     data-index="<?= $i ?>"
                     class="otp-input">
            <?php endfor; ?>
          </div>
        </div>

        <!-- Buttons -->
        <button type="submit"
                class="w-full py-2.5 rounded-xl font-semibold text-white
                       bg-gradient-to-b from-slate-800 to-slate-900
                       shadow-lg shadow-slate-900/20
                       hover:shadow-xl hover:shadow-slate-900/25 hover:-translate-y-0.5
                       active:translate-y-0
                       focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-600
                       transition-all duration-200">
          Verify &amp; continue
        </button>

        <a href="otp.php?resend=1"
           class="block w-full text-center py-2.5 rounded-xl font-medium text-slate-700
                  border border-slate-200 hover:border-slate-300 hover:bg-white/60
                  transition">
          Resend code
        </a>
      </form>

      <!-- Wrong email -->
      <p class="mt-6 text-center text-xs text-slate-500">
        Wrong email? <a href="register.php" class="lp-teal font-medium hover:underline" style="color:#0d7d8c;">Start over</a>
      </p>
    </div>

    <!-- back to home -->
    <p class="text-center text-xs text-slate-500 mt-6">
      <a href="index.php" class="hover:text-slate-700 transition">&larr; Back to home</a>
    </p>
  </div>
</section>

<script>
(function() {
  const boxes    = Array.from(document.querySelectorAll('#otp-boxes .otp-input'));
  const hidden   = document.getElementById('otp-value');
  const form     = document.getElementById('otp-form');

  function sync() {
    hidden.value = boxes.map(b => b.value).join('');
    boxes.forEach(b => b.classList.toggle('filled', b.value !== ''));
  }

  boxes.forEach((box, i) => {
    box.addEventListener('input', (e) => {
      // only digits
      box.value = box.value.replace(/\D/g, '').slice(0, 1);
      sync();
      if (box.value && i < boxes.length - 1) boxes[i + 1].focus();
    });

    box.addEventListener('keydown', (e) => {
      if (e.key === 'Backspace' && !box.value && i > 0) {
        boxes[i - 1].focus();
        boxes[i - 1].value = '';
        sync();
        e.preventDefault();
      }
      if (e.key === 'ArrowLeft'  && i > 0) boxes[i - 1].focus();
      if (e.key === 'ArrowRight' && i < boxes.length - 1) boxes[i + 1].focus();
    });

    box.addEventListener('paste', (e) => {
      e.preventDefault();
      const text = (e.clipboardData || window.clipboardData).getData('text');
      const digits = text.replace(/\D/g, '').slice(0, 6).split('');
      digits.forEach((d, idx) => {
        if (boxes[idx]) boxes[idx].value = d;
      });
      sync();
      const nextEmpty = boxes.findIndex(b => !b.value);
      (boxes[nextEmpty === -1 ? boxes.length - 1 : nextEmpty]).focus();
    });

    box.addEventListener('focus', () => box.select());
  });

  // Submit guard
  form.addEventListener('submit', (e) => {
    sync();
    if (hidden.value.length !== 6) {
      e.preventDefault();
      boxes[0].focus();
    }
  });

  // Autofocus first box
  boxes[0].focus();
})();
</script>

<?php include 'footer.php'; ?>