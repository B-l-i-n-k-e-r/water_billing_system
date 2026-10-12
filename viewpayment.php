<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';
include_once 'tariff.php';
$pageTitle = 'Payments';
include 'admin_header.php';

$search = trim($_GET['q'] ?? '');

$sql = "SELECT t.*, o.fname, o.lname, o.contact
        FROM transactions t
        LEFT JOIN owners o ON CONCAT('OWNER-', o.id) = t.account_number
        WHERE t.amount < 0";

$params = [];
$types = '';
if ($search !== '') {
    $sql .= " AND (o.fname LIKE ? OR o.lname LIKE ? OR o.contact LIKE ? OR t.description LIKE ?)";
    $like = "%$search%";
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= 'ssss';
}
$sql .= " ORDER BY t.transaction_date DESC, t.id DESC LIMIT 200";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) die("SQL error: " . mysqli_error($conn));
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$payments = [];
while ($row = mysqli_fetch_assoc($res)) $payments[] = $row;
mysqli_stmt_close($stmt);

$totalToday = 0;
$today = date('Y-m-d');
foreach ($payments as $p) {
    if ($p['transaction_date'] === $today) {
        $totalToday += abs((float) $p['amount']);
    }
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between mb-8 gap-4">
    <div>
      <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Payments</h1>
      <p class="text-sm text-slate-500 mt-1">Record and review customer payments.</p>
    </div>
    <a href="paybill.php"
       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-white
              bg-gradient-to-b from-teal-600 to-teal-700
              shadow-lg shadow-teal-700/20 hover:-translate-y-0.5 transition-all w-max">
      <i data-lucide="plus" class="w-4 h-4"></i>
      Record payment
    </a>
  </div>

  <!-- Quick stats -->
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-slate-200 p-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Received today</p>
          <p class="text-2xl font-bold text-slate-900 mt-2 tabular-nums">
            <?= htmlspecialchars(kes($totalToday)) ?>
          </p>
        </div>
        <div class="shrink-0 w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
          <i data-lucide="trending-up" class="w-5 h-5"></i>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Transactions shown</p>
          <p class="text-2xl font-bold text-slate-900 mt-2 tabular-nums"><?= count($payments) ?></p>
        </div>
        <div class="shrink-0 w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
          <i data-lucide="list" class="w-5 h-5"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- Search -->
  <form method="GET"
        class="bg-white rounded-2xl border border-slate-200 p-3 sm:p-4 mb-6
               flex flex-col sm:flex-row gap-3">

    <div class="relative flex-1">
      <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
             placeholder="Search by name, phone, or description"
             class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white text-slate-900 placeholder-slate-400
                    border border-slate-200 outline-none transition hover:border-slate-300
                    focus:border-teal-600 focus:ring-3 focus:ring-teal-600/15">
    </div>

    <button type="submit"
            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white
                   bg-gradient-to-b from-slate-800 to-slate-900
                   shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all whitespace-nowrap">
      Search
    </button>

    <?php if ($search !== ''): ?>
      <a href="viewpayment.php"
         class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-medium text-slate-700
                bg-white border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition whitespace-nowrap">
        <i data-lucide="x" class="w-4 h-4"></i>
        Clear
      </a>
    <?php endif; ?>
  </form>

  <!-- Table -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead>
          <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
            <th class="px-5 py-3 font-semibold">Txn #</th>
            <th class="px-5 py-3 font-semibold">Date</th>
            <th class="px-5 py-3 font-semibold">Customer</th>
            <th class="px-5 py-3 font-semibold">Description</th>
            <th class="px-5 py-3 font-semibold text-right">Amount</th>
            <th class="px-5 py-3 font-semibold text-right">Balance after</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($payments)): ?>
            <tr>
              <td colspan="6" class="px-6 py-16 text-center">
                <div class="mx-auto w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
                  <i data-lucide="banknote" class="w-6 h-6 text-slate-500"></i>
                </div>
                <p class="text-base font-semibold text-slate-900">No payments recorded</p>
                <p class="text-sm text-slate-500 mt-1">
                  <?= $search ? 'Try a different search term.' : 'Record the first payment to get started.' ?>
                </p>
                <?php if (!$search): ?>
                  <a href="paybill.php"
                     class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-white
                            bg-gradient-to-b from-teal-600 to-teal-700
                            shadow-md shadow-teal-700/20 hover:-translate-y-0.5 transition-all">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Record payment
                  </a>
                <?php endif; ?>
              </td>
            </tr>
          <?php else: foreach ($payments as $p):
            $customerName = trim(($p['fname'] ?? '') . ' ' . ($p['lname'] ?? ''));
          ?>
            <tr class="hover:bg-slate-50 transition">
              <td class="px-5 py-4 font-semibold text-slate-900 tabular-nums">#<?= intval($p['id']) ?></td>
              <td class="px-5 py-4 text-slate-600 whitespace-nowrap"><?= htmlspecialchars($p['transaction_date']) ?></td>
              <td class="px-5 py-4 text-slate-700"><?= htmlspecialchars($customerName ?: '—') ?></td>
              <td class="px-5 py-4 text-slate-600"><?= htmlspecialchars($p['description']) ?></td>
              <td class="px-5 py-4 text-right font-semibold text-emerald-700 tabular-nums">
                <?= number_format(abs((float)$p['amount']), 2) ?>
              </td>
              <td class="px-5 py-4 text-right text-slate-600 tabular-nums">
                <?= number_format((float)($p['balance_after'] ?? 0), 2) ?>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php include 'footer.php'; ?>