<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once 'auth.php';
include_once 'db.php';
$pageTitle = 'Account Balance';
include 'header.php';

$user_id = intval($_SESSION['id']);
$account = trim($_GET['account'] ?? '');

$stmt = mysqli_prepare($conn, "SELECT * FROM customer_accounts WHERE user_id = ? AND account_number = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "is", $user_id, $account);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);
?>

<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <!-- Back link -->
  <a href="selfcare.php?tab=my_accounts"
     class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 transition mb-6">
    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
    Back to my accounts
  </a>

  <?php if (!$row): ?>

    <!-- Empty / not found -->
    <div class="rounded-2xl bg-white border border-slate-200 p-10 text-center">
      <div class="mx-auto w-14 h-14 rounded-2xl bg-rose-50 ring-1 ring-rose-200/70 flex items-center justify-center mb-4">
        <i data-lucide="alert-circle" class="w-6 h-6 text-rose-600"></i>
      </div>
      <h1 class="text-lg font-semibold text-slate-900">Account not found</h1>
      <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">
        We couldn't find that account number on your profile. It may have been removed, or the number is incorrect.
      </p>
      <a href="selfcare.php?tab=my_accounts"
         class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-white
                bg-gradient-to-b from-slate-800 to-slate-900
                shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        Back to my accounts
      </a>
    </div>

  <?php else: ?>

    <!-- Balance card -->
    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden">

      <!-- Header strip -->
      <div class="bg-gradient-to-br from-slate-800 to-slate-900 px-6 sm:px-8 py-6">
        <div class="flex items-center gap-3">
          <div class="w-11 h-11 rounded-xl bg-white/10 backdrop-blur ring-1 ring-white/20
                      flex items-center justify-center">
            <i data-lucide="wallet" class="w-5 h-5 text-white"></i>
          </div>
          <div>
            <p class="text-xs font-medium uppercase tracking-wider text-slate-300">Current balance</p>
            <p class="text-sm text-white font-medium mt-0.5">Account balance</p>
          </div>
        </div>
      </div>

      <!-- Amount -->
      <div class="px-6 sm:px-8 py-10 text-center">

        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Amount due</p>

        <?php
          $balance = (float) $row['balance'];
          $isZero  = abs($balance) < 0.01;
          $isNeg   = $balance < 0;
          $display = number_format(abs($balance), 2);
        ?>

        <p class="mt-3 text-4xl sm:text-5xl font-bold tracking-tight
                  <?= $isZero ? 'text-emerald-600' : ($isNeg ? 'text-teal-700' : 'text-slate-900') ?>">
          <?= $isNeg ? '–' : '' ?>KES <?= $display ?>
        </p>

        <?php if ($isZero): ?>
          <p class="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-emerald-700
                    px-3 py-1 rounded-full bg-emerald-50 ring-1 ring-emerald-200/70">
            <i data-lucide="check-circle-2" class="w-4 h-4"></i>
            You're all paid up
          </p>
        <?php elseif ($isNeg): ?>
          <p class="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-teal-700
                    px-3 py-1 rounded-full bg-teal-50 ring-1 ring-teal-200/70">
            <i data-lucide="info" class="w-4 h-4"></i>
            Credit balance
          </p>
        <?php else: ?>
          <p class="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-amber-700
                    px-3 py-1 rounded-full bg-amber-50 ring-1 ring-amber-200/70">
            <i data-lucide="alert-circle" class="w-4 h-4"></i>
            Payment outstanding
          </p>
        <?php endif; ?>

      </div>

      <!-- Account details -->
      <div class="border-t border-slate-100 px-6 sm:px-8 py-5">
        <div class="flex items-center justify-between gap-4 text-sm">
          <div class="flex items-center gap-2 text-slate-500">
            <i data-lucide="hash" class="w-4 h-4"></i>
            <span>Account number</span>
          </div>
          <span class="font-semibold text-slate-900 tabular-nums">
            <?= htmlspecialchars($row['account_number']) ?>
          </span>
        </div>
      </div>

      <!-- Footer actions -->
      <div class="border-t border-slate-100 px-6 sm:px-8 py-4 bg-slate-50/60 flex flex-wrap gap-3 justify-end">
        <a href="selfcare.php?tab=my_accounts"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-medium text-sm text-slate-700
                  bg-white border border-slate-200 hover:border-slate-300 transition">
          <i data-lucide="list" class="w-3.5 h-3.5"></i>
          All accounts
        </a>
        <a href="view_ebill.php?account=<?= urlencode($row['account_number']) ?>"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-semibold text-sm text-white
                  bg-gradient-to-b from-teal-600 to-teal-700
                  shadow-md shadow-teal-700/20 hover:-translate-y-0.5 transition-all">
          <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
          View latest e-bill
        </a>
      </div>

    </div>

  <?php endif; ?>

</div>

<?php include 'footer.php'; ?>