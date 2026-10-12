<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
include_once 'db.php';
$pageTitle = 'Self Care';
include 'header.php';

$user_id = intval($_SESSION['id']);
$activeTab = $_GET['tab'] ?? 'my_accounts';

// ---- Fetch the logged-in user's own details ----
$profile = [
    'name'   => $_SESSION['name']  ?? '',
    'email'  => $_SESSION['email'] ?? '',
    'mobile' => '',
];
$profStmt = mysqli_prepare($conn, "SELECT name, email, mobile FROM user WHERE id = ? LIMIT 1");
if ($profStmt) {
    mysqli_stmt_bind_param($profStmt, "i", $user_id);
    mysqli_stmt_execute($profStmt);
    $profRes = mysqli_stmt_get_result($profStmt);
    if ($profRow = mysqli_fetch_assoc($profRes)) {
        $profile['name']   = $profRow['name']   ?: $profile['name'];
        $profile['email']  = $profRow['email']  ?: $profile['email'];
        $profile['mobile'] = $profRow['mobile'] ?? '';
    }
    mysqli_stmt_close($profStmt);
}

// ---- Fetch linked customer accounts ----
$accounts = [];
$stmt = mysqli_prepare($conn, "SELECT * FROM customer_accounts WHERE user_id = ? ORDER BY date_added DESC");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) {
    $accounts[] = $row;
}
mysqli_stmt_close($stmt);

// Flash messages
$flash = $_GET['success'] ?? null;
$err   = $_GET['err'] ?? null;

$tabs = [
    'my_accounts'   => 'My Accounts',
    'add_account'   => 'Add Account',
    'get_statement' => 'Get Statement',
];
?>

<style>
  .wiz-input {
    width: 100%;
    padding: 0.625rem 1rem;
    border-radius: 0.75rem;
    background: rgba(255,255,255,0.7);
    border: 1px solid #e2e8f0;
    color: #0f172a;
    outline: none;
    transition: all .15s ease;
  }
  .wiz-input::placeholder { color: #94a3b8; }
  .wiz-input:hover { border-color: #cbd5e1; }
  .wiz-input:focus {
    background: #fff;
    border-color: #0d7d8c;
    box-shadow: 0 0 0 3px rgba(13,125,140,0.18);
  }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <div class="mb-6">
    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Self Care</h1>
    <p class="text-sm text-slate-500 mt-1">Manage your NCWSC accounts and statements.</p>
  </div>

  <!-- Profile card -->
  <div class="mb-6 rounded-2xl bg-gradient-to-br from-slate-800 to-slate-900 p-6 sm:p-7">
    <div class="flex flex-col sm:flex-row sm:items-center gap-5">
      <div class="shrink-0 w-14 h-14 rounded-2xl bg-white/10 backdrop-blur ring-1 ring-white/20
                  flex items-center justify-center">
        <i data-lucide="user" class="w-6 h-6 text-white"></i>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-300">Signed in as</p>
        <p class="text-lg font-semibold text-white mt-0.5 truncate">
          <?= htmlspecialchars($profile['name'] ?: 'NCWSC User') ?>
        </p>
        <div class="flex flex-wrap gap-x-5 gap-y-1 mt-2 text-sm text-slate-300">
          <?php if (!empty($profile['email'])): ?>
            <span class="inline-flex items-center gap-1.5 min-w-0">
              <i data-lucide="mail" class="w-3.5 h-3.5 shrink-0"></i>
              <span class="truncate"><?= htmlspecialchars($profile['email']) ?></span>
            </span>
          <?php endif; ?>
          <?php if (!empty($profile['mobile'])): ?>
            <span class="inline-flex items-center gap-1.5">
              <i data-lucide="phone" class="w-3.5 h-3.5 shrink-0"></i>
              <?= htmlspecialchars($profile['mobile']) ?>
            </span>
          <?php endif; ?>
        </div>
      </div>
      <div class="shrink-0 text-left sm:text-right">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-300">Linked accounts</p>
        <p class="text-2xl font-bold text-white tabular-nums mt-0.5"><?= count($accounts) ?></p>
      </div>
    </div>
  </div>

  <!-- Pill tabs -->
  <div class="inline-flex flex-wrap gap-1 p-1 rounded-xl bg-slate-100 border border-slate-200 mb-8">
    <?php foreach ($tabs as $key => $label): $isActive = ($activeTab === $key); ?>
      <a href="?tab=<?= urlencode($key) ?>"
         class="px-4 py-2 rounded-lg text-sm font-medium transition
                <?= $isActive
                    ? 'bg-white text-slate-900 shadow-sm ring-1 ring-slate-200'
                    : 'text-slate-600 hover:text-slate-900 hover:bg-white/60' ?>">
        <?= htmlspecialchars($label) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- Flash messages -->
  <?php if ($flash === '1'): ?>
    <div class="mb-6 flex items-start gap-3 p-3.5 rounded-xl bg-emerald-50 ring-1 ring-emerald-200/70 text-emerald-800 text-sm">
      <i data-lucide="check-circle" class="w-5 h-5 mt-0.5 shrink-0"></i>
      <span>Action completed successfully.</span>
    </div>
  <?php endif; ?>
  <?php if ($err): ?>
    <div class="mb-6 flex items-start gap-3 p-3.5 rounded-xl bg-rose-50 ring-1 ring-rose-200/70 text-rose-800 text-sm">
      <i data-lucide="alert-triangle" class="w-5 h-5 mt-0.5 shrink-0"></i>
      <span><?php
        $errMap = [
            'empty' => 'Please fill all required fields.',
            'db'    => 'Database error. Please try again.',
        ];
        echo htmlspecialchars($errMap[$err] ?? 'Something went wrong.');
      ?></span>
    </div>
  <?php endif; ?>

  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

    <?php if ($activeTab === 'my_accounts'): ?>
      <div class="p-6 sm:p-8">
        <h2 class="text-lg font-semibold text-slate-900 mb-1">My accounts</h2>
        <p class="text-sm text-slate-500 mb-6">Your linked NCWSC customer accounts.</p>

        <div class="overflow-x-auto -mx-6 sm:mx-0">
          <table class="w-full text-left text-sm">
            <thead>
              <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                <th class="px-4 sm:px-6 py-3 font-semibold">Account Number</th>
                <th class="px-4 sm:px-6 py-3 font-semibold">Date Added</th>
                <th class="px-4 sm:px-6 py-3 font-semibold">Check Balance</th>
                <th class="px-4 sm:px-6 py-3 font-semibold">Latest E-Bill</th>
                <th class="px-4 sm:px-6 py-3 font-semibold text-right">Remove</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <?php if (empty($accounts)): ?>
                <tr>
                  <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                    <i data-lucide="inbox" class="w-10 h-10 mx-auto text-slate-300 mb-3"></i>
                    <p>You have no accounts yet.</p>
                    <a href="?tab=add_account" class="mt-2 inline-flex items-center gap-1 text-sm font-medium text-teal-700 hover:underline">
                      Add your first account <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                  </td>
                </tr>
              <?php else: foreach ($accounts as $acc): ?>
                <tr class="hover:bg-slate-50 transition">
                  <td class="px-4 sm:px-6 py-4 font-semibold text-slate-900">
                    <?= htmlspecialchars($acc['account_number']) ?>
                  </td>
                  <td class="px-4 sm:px-6 py-4 text-slate-600"><?= htmlspecialchars($acc['date_added']) ?></td>
                  <td class="px-4 sm:px-6 py-4">
                    <a href="check_balance.php?account=<?= urlencode($acc['account_number']) ?>"
                       class="inline-flex items-center gap-1 text-teal-700 hover:underline font-medium">
                      KES <?= number_format((float)$acc['balance'], 2) ?>
                    </a>
                  </td>
                  <td class="px-4 sm:px-6 py-4">
                    <a href="view_ebill.php?account=<?= urlencode($acc['account_number']) ?>"
                       class="inline-flex items-center gap-1 text-teal-700 hover:underline font-medium">
                      KES <?= number_format((float)$acc['latest_ebill'], 2) ?>
                    </a>
                  </td>
                  <td class="px-4 sm:px-6 py-4 text-right">
                    <a href="remove_account.php?id=<?= intval($acc['id']) ?>"
                       onclick="return confirm('Remove this account?');"
                       class="inline-flex items-center gap-1 text-rose-600 hover:text-rose-700 font-medium">
                      <i data-lucide="trash-2" class="w-4 h-4"></i>
                      Remove
                    </a>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    <?php elseif ($activeTab === 'add_account'): ?>
      <div class="p-6 sm:p-8 max-w-xl">
        <h2 class="text-lg font-semibold text-slate-900 mb-1">Add account number</h2>
        <p class="text-sm text-slate-500 mb-6">Link an NCWSC customer account to your profile.</p>

        <form action="add_account_process.php" method="POST">
          <div class="mb-5">
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Account Number</label>
            <input type="text" name="account_number" required placeholder="e.g. 1234567"
                   class="wiz-input">
          </div>
          <button type="submit"
                  class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white
                         bg-gradient-to-b from-slate-800 to-slate-900
                         shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add account
          </button>
        </form>
      </div>

    <?php elseif ($activeTab === 'get_statement'): ?>
      <div class="p-6 sm:p-8">
        <h2 class="text-lg font-semibold text-slate-900 mb-1">Get statement</h2>
        <p class="text-sm text-slate-500 mb-6">Request a customer statement for a specific account and period.</p>

        <?php if (!empty($_SESSION['statement'])): ?>
          <div class="mb-8 rounded-2xl border border-slate-200 overflow-hidden">
            <div class="bg-slate-50 px-5 py-4 border-b border-slate-200">
              <div class="flex items-start gap-3">
                <i data-lucide="file-text" class="w-5 h-5 text-slate-500 mt-0.5"></i>
                <div class="text-sm text-slate-700">
                  <p><strong>Account:</strong> <?= htmlspecialchars($_SESSION['statement']['account']) ?></p>
                  <p class="text-slate-500 mt-0.5">
                    <?= htmlspecialchars($_SESSION['statement']['from']) ?>
                    &rarr;
                    <?= htmlspecialchars($_SESSION['statement']['to']) ?>
                  </p>
                </div>
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-left text-sm">
                <thead>
                  <tr class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wide">
                    <th class="px-5 py-3 font-semibold">Date</th>
                    <th class="px-5 py-3 font-semibold">Description</th>
                    <th class="px-5 py-3 font-semibold text-right">Amount</th>
                    <th class="px-5 py-3 font-semibold text-right">Balance</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <?php if (empty($_SESSION['statement']['rows'])): ?>
                    <tr>
                      <td colspan="4" class="px-5 py-10 text-center text-slate-500">
                        No transactions found for this period.
                      </td>
                    </tr>
                  <?php else: foreach ($_SESSION['statement']['rows'] as $row): ?>
                    <tr class="hover:bg-slate-50">
                      <td class="px-5 py-3 text-slate-600"><?= htmlspecialchars($row['transaction_date']) ?></td>
                      <td class="px-5 py-3 text-slate-800"><?= htmlspecialchars($row['description']) ?></td>
                      <td class="px-5 py-3 text-right text-slate-700 font-medium"><?= number_format((float)$row['amount'], 2) ?></td>
                      <td class="px-5 py-3 text-right text-slate-700 font-medium"><?= number_format((float)$row['balance_after'], 2) ?></td>
                    </tr>
                  <?php endforeach; endif; ?>
                </tbody>
              </table>
            </div>
          </div>
          <?php unset($_SESSION['statement']); ?>
        <?php endif; ?>

        <form action="get_statement_process.php" method="POST"
              class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Date from</label>
            <input type="date" name="date_from" required class="wiz-input">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Date to</label>
            <input type="date" name="date_to" required class="wiz-input">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Account number</label>
            <input type="text" name="account_number" required placeholder="e.g. 1234567" class="wiz-input">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Mobile number</label>
            <input type="tel" name="mobile_number" required
                   value="<?= htmlspecialchars($profile['mobile']) ?>"
                   placeholder="0700000000" class="wiz-input">
            <?php if (!empty($profile['mobile'])): ?>
              <p class="text-xs text-slate-500 mt-1.5">Auto-filled from your profile — edit if needed.</p>
            <?php endif; ?>
          </div>
          <div class="md:col-span-2 mt-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white
                           bg-gradient-to-b from-slate-800 to-slate-900
                           shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all">
              <i data-lucide="search" class="w-4 h-4"></i>
              Get statement
            </button>
          </div>
        </form>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php include 'footer.php'; ?>