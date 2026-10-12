<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['id'])) {
    header("Location: dashboard.php");
    exit();
}
$pageTitle = 'Sign In';
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

  <!-- decorative blobs (muted, matching landing) -->
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
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2"
               viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 3l8 4v5c0 4.5-3.4 8.5-8 9-4.6-.5-8-4.5-8-9V7l8-4z"/>
          </svg>
        </div>
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Welcome back</h1>
        <p class="text-sm text-slate-500 mt-1">
          Sign in to <span class="font-medium text-slate-700">NCWSC Online Services</span>
        </p>
      </div>

      <!-- Alerts -->
      <?php if (isset($_GET['verified'])): ?>
        <div class="mb-5 flex items-start gap-3 p-3.5 rounded-xl bg-emerald-50 ring-1 ring-emerald-200/70 text-emerald-800 text-sm">
          <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
          <span><strong class="font-semibold">Account verified.</strong> You can now log in.</span>
        </div>
      <?php endif; ?>

      <?php if (isset($_GET['reset'])): ?>
        <div class="mb-5 flex items-start gap-3 p-3.5 rounded-xl bg-emerald-50 ring-1 ring-emerald-200/70 text-emerald-800 text-sm">
          <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
          <span><strong class="font-semibold">Password changed.</strong> Log in with your new password.</span>
        </div>
      <?php endif; ?>

      <?php if (isset($_GET['timeout'])): ?>
        <div class="mb-5 flex items-start gap-3 p-3.5 rounded-xl bg-amber-50 ring-1 ring-amber-200/70 text-amber-800 text-sm">
          <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2M12 3a9 9 0 100 18 9 9 0 000-18z"/></svg>
          <span>Session expired due to inactivity. Please log in again.</span>
        </div>
      <?php endif; ?>

      <?php if (isset($_GET['err'])): ?>
        <div class="mb-5 flex items-start gap-3 p-3.5 rounded-xl bg-rose-50 ring-1 ring-rose-200/70 text-rose-800 text-sm">
          <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg>
          <span>Invalid email or password. Please try again.</span>
        </div>
      <?php endif; ?>

      <!-- Form -->
      <form action="process.php" method="post" class="space-y-5">

        <!-- Email / Username -->
        <div>
          <label for="username" class="block text-sm font-medium text-slate-700 mb-1.5">
            Email or Username
          </label>
          <input id="username" type="text" name="username" required autocomplete="username"
                 placeholder="you@example.com"
                 class="focus-teal w-full px-4 py-2.5 rounded-xl bg-white/70 text-slate-900 placeholder-slate-400
                        border border-slate-200 outline-none transition
                        hover:border-slate-300 focus:bg-white">
        </div>

        <!-- Password -->
        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
            <a href="forgot_password.php"
               class="text-xs font-medium text-teal-700 hover:text-teal-800 transition">
              Forgot password?
            </a>
          </div>
          <div class="relative">
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   placeholder="••••••••"
                   class="focus-teal w-full px-4 py-2.5 pr-11 rounded-xl bg-white/70 text-slate-900 placeholder-slate-400
                          border border-slate-200 outline-none transition
                          hover:border-slate-300 focus:bg-white">
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
                class="w-full py-2.5 rounded-xl font-semibold text-white
                       bg-gradient-to-b from-slate-800 to-slate-900
                       shadow-lg shadow-slate-900/20
                       hover:shadow-xl hover:shadow-slate-900/25 hover:-translate-y-0.5
                       active:translate-y-0
                       focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-600
                       transition-all duration-200">
          Sign in
        </button>
      </form>

      <!-- Divider -->
      <div class="my-6 flex items-center gap-3 text-xs uppercase tracking-wider text-slate-400">
        <span class="h-px flex-1 bg-slate-200"></span>
        <span>New here?</span>
        <span class="h-px flex-1 bg-slate-200"></span>
      </div>

      <!-- Register -->
      <a href="register.php"
         class="block w-full text-center py-2.5 rounded-xl font-medium text-slate-700
                border border-slate-200 hover:border-slate-300 hover:bg-white/60
                transition">
        Create an account
      </a>
    </div>

    <!-- back to home link -->
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