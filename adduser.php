<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1]);
include_once 'db.php';
$pageTitle = 'Add User';
include 'admin_header.php';

$err = $_GET['err'] ?? null;

$errorMessages = [
    'empty'  => 'All fields are required.',
    'match'  => 'Passwords do not match.',
    'exists' => 'Username or email already exists.',
    'db'     => 'Database error. Please try again.',
];
?>

<style>
  .wiz-input {
    width: 100%;
    padding: 0.625rem 1rem;
    border-radius: 0.75rem;
    background: #fff;
    border: 1px solid #e2e8f0;
    color: #0f172a;
    outline: none;
    transition: all .15s ease;
  }
  .wiz-input::placeholder { color: #94a3b8; }
  .wiz-input:hover { border-color: #cbd5e1; }
  .wiz-input:focus {
    border-color: #0d7d8c;
    box-shadow: 0 0 0 3px rgba(13,125,140,0.18);
  }
  .wiz-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #334155;
    margin-bottom: 0.375rem;
  }
</style>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <a href="user.php"
     class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 transition mb-6">
    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
    Back to users
  </a>

  <div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Add new user</h1>
    <p class="text-sm text-slate-500 mt-1">Create a staff or customer account.</p>
  </div>

  <?php if ($err && isset($errorMessages[$err])): ?>
    <div class="mb-6 flex items-start gap-3 p-3.5 rounded-xl bg-rose-50 ring-1 ring-rose-200/70 text-rose-800 text-sm">
      <i data-lucide="alert-triangle" class="w-5 h-5 mt-0.5 shrink-0"></i>
      <span><?= htmlspecialchars($errorMessages[$err]) ?></span>
    </div>
  <?php endif; ?>

  <form action="adduserexec.php" method="POST"
        class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8 space-y-5">

    <!-- Name + Username -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label for="name" class="wiz-label">Full name</label>
        <input id="name" type="text" name="name" required placeholder="John Doe" class="wiz-input">
      </div>
      <div>
        <label for="username" class="wiz-label">Username</label>
        <input id="username" type="text" name="username" required placeholder="johndoe"
               autocomplete="off" class="wiz-input">
      </div>
    </div>

    <!-- Email + Mobile -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label for="email" class="wiz-label">Email <span class="text-slate-400 font-normal">(optional)</span></label>
        <input id="email" type="email" name="email" placeholder="you@example.com"
               autocomplete="off" class="wiz-input">
      </div>
      <div>
        <label for="mobile" class="wiz-label">Mobile <span class="text-slate-400 font-normal">(optional)</span></label>
        <input id="mobile" type="text" name="mobile" maxlength="10" placeholder="0712345678"
               inputmode="tel" class="wiz-input">
      </div>
    </div>

    <!-- Password + Confirm -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label for="password" class="wiz-label">Password</label>
        <div class="relative">
          <input id="password" type="password" name="password" required
                 autocomplete="new-password" placeholder="At least 6 characters"
                 class="wiz-input pr-11">
          <button type="button" onclick="togglePw(this)"
                  class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-700 transition"
                  aria-label="Show password">
            <i data-lucide="eye" class="w-4 h-4"></i>
          </button>
        </div>
      </div>
      <div>
        <label for="confirm" class="wiz-label">Confirm password</label>
        <div class="relative">
          <input id="confirm" type="password" name="confirm" required
                 autocomplete="new-password" placeholder="Re-enter password"
                 class="wiz-input pr-11">
          <button type="button" onclick="togglePw(this)"
                  class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-700 transition"
                  aria-label="Show password">
            <i data-lucide="eye" class="w-4 h-4"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- User level -->
    <div>
      <label for="userlevel" class="wiz-label">User level</label>
      <select id="userlevel" name="userlevel" required class="wiz-input">
        <option value="2">Cashier (Level 2)</option>
        <option value="1">Admin (Level 1)</option>
        <option value="3">Client (Level 3)</option>
      </select>
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-3 pt-5 border-t border-slate-100">
      <a href="user.php"
         class="inline-flex items-center px-5 py-2.5 rounded-xl font-medium text-slate-700
                bg-white border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition">
        Cancel
      </a>
      <button type="submit"
              class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white
                     bg-gradient-to-b from-slate-800 to-slate-900
                     shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all">
        <i data-lucide="user-plus" class="w-4 h-4"></i>
        Save user
      </button>
    </div>

  </form>

</div>

<script>
function togglePw(btn) {
  const input = btn.parentElement.querySelector('input');
  const isPw = input.type === 'password';
  input.type = isPw ? 'text' : 'password';
  btn.setAttribute('aria-label', isPw ? 'Hide password' : 'Show password');
}
</script>

<?php include 'footer.php'; ?>