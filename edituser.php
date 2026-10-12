<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1]);
include_once 'db.php';
$pageTitle = 'Edit User';
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
$errorMessages = [
    'empty'  => 'Required fields are missing.',
    'match'  => 'Passwords do not match.',
    'exists' => 'Username or email already used by another account.',
    'db'     => 'Database error. Please try again.',
];

$levelLabel = [1 => 'Admin (Level 1)', 2 => 'Cashier (Level 2)', 3 => 'Client (Level 3)'];
$currentLevel = intval($user['userlevel']);
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
    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Edit user</h1>
    <p class="text-sm text-slate-500 mt-1">
      Editing <span class="font-medium text-slate-700"><?= htmlspecialchars($user['username']) ?></span>
      &middot; <?= htmlspecialchars($levelLabel[$currentLevel] ?? 'Unknown') ?>
    </p>
  </div>

  <?php if ($err && isset($errorMessages[$err])): ?>
    <div class="mb-6 flex items-start gap-3 p-3.5 rounded-xl bg-rose-50 ring-1 ring-rose-200/70 text-rose-800 text-sm">
      <i data-lucide="alert-triangle" class="w-5 h-5 mt-0.5 shrink-0"></i>
      <span><?= htmlspecialchars($errorMessages[$err]) ?></span>
    </div>
  <?php endif; ?>

  <form action="edituserecex.php" method="POST"
        class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8 space-y-5">

    <input type="hidden" name="id" value="<?= intval($user['id']) ?>">

    <!-- Name + Username -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label for="name" class="wiz-label">Full name</label>
        <input id="name" type="text" name="name" required
               value="<?= htmlspecialchars($user['name']) ?>"
               class="wiz-input">
      </div>
      <div>
        <label for="username" class="wiz-label">Username</label>
        <input id="username" type="text" name="username" required
               value="<?= htmlspecialchars($user['username']) ?>"
               autocomplete="off" class="wiz-input">
      </div>
    </div>

    <!-- Email + Mobile -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label for="email" class="wiz-label">Email <span class="text-slate-400 font-normal">(optional)</span></label>
        <input id="email" type="email" name="email"
               value="<?= htmlspecialchars($user['email'] ?? '') ?>"
               autocomplete="off" class="wiz-input">
      </div>
      <div>
        <label for="mobile" class="wiz-label">Mobile <span class="text-slate-400 font-normal">(optional)</span></label>
        <input id="mobile" type="text" name="mobile" maxlength="10"
               value="<?= htmlspecialchars($user['mobile'] ?? '') ?>"
               inputmode="tel" class="wiz-input">
      </div>
    </div>

    <!-- Password section — collapsible style -->
    <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
      <div class="flex items-start gap-3 mb-4">
        <div class="shrink-0 w-8 h-8 rounded-lg bg-slate-200/60 flex items-center justify-center">
          <i data-lucide="lock" class="w-3.5 h-3.5 text-slate-600"></i>
        </div>
        <div>
          <p class="text-sm font-medium text-slate-800">Change password</p>
          <p class="text-xs text-slate-500 mt-0.5">Leave blank to keep the current password.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label for="password" class="wiz-label">New password</label>
          <div class="relative">
            <input id="password" type="password" name="password"
                   autocomplete="new-password" placeholder="Leave blank to keep current"
                   class="wiz-input pr-11">
            <button type="button" onclick="togglePw(this)"
                    class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-700 transition"
                    aria-label="Show password">
              <i data-lucide="eye" class="w-4 h-4"></i>
            </button>
          </div>
        </div>
        <div>
          <label for="confirm" class="wiz-label">Confirm new password</label>
          <div class="relative">
            <input id="confirm" type="password" name="confirm"
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
    </div>

    <!-- User level -->
    <div>
      <label for="userlevel" class="wiz-label">User level</label>
      <select id="userlevel" name="userlevel" required class="wiz-input">
        <?php foreach ([1 => 'Admin (Level 1)', 2 => 'Cashier (Level 2)', 3 => 'Client (Level 3)'] as $lvl => $label): ?>
          <option value="<?= $lvl ?>" <?= ($currentLevel === $lvl) ? 'selected' : '' ?>>
            <?= htmlspecialchars($label) ?>
          </option>
        <?php endforeach; ?>
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
        <i data-lucide="save" class="w-4 h-4"></i>
        Update user
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