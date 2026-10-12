<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';
$pageTitle = 'Billing';
include 'admin_header.php';

$search = trim($_GET['q'] ?? '');
$status = trim($_GET['status'] ?? '');

$sql = "SELECT b.*, o.fname, o.lname, o.contact
        FROM bill b
        LEFT JOIN owners o ON o.id = b.owners_id
        WHERE 1=1";
$params = [];
$types  = '';

if ($search !== '') {
    $sql .= " AND (o.fname LIKE ? OR o.lname LIKE ? OR o.contact LIKE ? OR b.id = ?)";
    $like = "%$search%";
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $search;
    $types .= 'ssss';
}
if ($status !== '' && in_array($status, ['unpaid','partial','paid'])) {
    $sql .= " AND b.status = ?";
    $params[] = $status;
    $types .= 's';
}
$sql .= " ORDER BY b.id DESC LIMIT 200";

$stmt = mysqli_prepare($conn, $sql);
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$bills = [];
while ($row = mysqli_fetch_assoc($res)) $bills[] = $row;
mysqli_stmt_close($stmt);

$statusStyles = [
    'unpaid'  => 'bg-rose-50 text-rose-700 ring-rose-200/70',
    'partial' => 'bg-amber-50 text-amber-700 ring-amber-200/70',
    'paid'    => 'bg-emerald-50 text-emerald-700 ring-emerald-200/70',
];
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between mb-8 gap-4">
    <div>
      <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Billing</h1>
      <p class="text-sm text-slate-500 mt-1">Create new bills from meter readings and track payment status.</p>
    </div>
    <a href="addbill.php"
       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-white
              bg-gradient-to-b from-slate-800 to-slate-900
              shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all w-max">
      <i data-lucide="plus" class="w-4 h-4"></i>
      Create new bill
    </a>
  </div>

  <!-- Filters -->
  <form method="GET"
        class="bg-white rounded-2xl border border-slate-200 p-3 sm:p-4 mb-6
               flex flex-col sm:flex-row gap-3">

    <div class="relative flex-1">
      <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
             placeholder="Search by name, phone, or bill ID"
             class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white text-slate-900 placeholder-slate-400
                    border border-slate-200 outline-none transition hover:border-slate-300
                    focus:border-teal-600 focus:ring-3 focus:ring-teal-600/15">
    </div>

    <select name="status"
            class="px-4 py-2.5 rounded-xl bg-white text-slate-900 border border-slate-200
                   outline-none transition hover:border-slate-300
                   focus:border-teal-600 focus:ring-3 focus:ring-teal-600/15">
      <option value="">All statuses</option>
      <option value="unpaid"  <?= $status==='unpaid'?'selected':'' ?>>Unpaid</option>
      <option value="partial" <?= $status==='partial'?'selected':'' ?>>Partial</option>
      <option value="paid"    <?= $status==='paid'?'selected':'' ?>>Paid</option>
    </select>

    <button type="submit"
            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white
                   bg-gradient-to-b from-slate-800 to-slate-900
                   shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all whitespace-nowrap">
      Filter
    </button>

    <?php if ($search !== '' || $status !== ''): ?>
      <a href="billing.php"
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
            <th class="px-4 py-3 font-semibold">#</th>
            <th class="px-4 py-3 font-semibold">Customer</th>
            <th class="px-4 py-3 font-semibold">Contact</th>
            <th class="px-4 py-3 font-semibold text-right">Prev</th>
            <th class="px-4 py-3 font-semibold text-right">Pres</th>
            <th class="px-4 py-3 font-semibold text-right">Consumption</th>
            <th class="px-4 py-3 font-semibold text-right">Amount</th>
            <th class="px-4 py-3 font-semibold text-right">Paid</th>
            <th class="px-4 py-3 font-semibold">Status</th>
            <th class="px-4 py-3 font-semibold">Date</th>
            <th class="px-4 py-3 font-semibold text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($bills)): ?>
            <tr>
              <td colspan="11" class="px-6 py-16 text-center">
                <div class="mx-auto w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
                  <i data-lucide="receipt" class="w-6 h-6 text-slate-500"></i>
                </div>
                <p class="text-base font-semibold text-slate-900">No bills found</p>
                <p class="text-sm text-slate-500 mt-1">
                  <?= ($search || $status) ? 'Try adjusting your filters.' : 'Create your first bill to get started.' ?>
                </p>
              </td>
            </tr>
          <?php else: foreach ($bills as $b):
            $key = strtolower($b['status'] ?? 'unpaid');
            $cls = $statusStyles[$key] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
            $outstanding = (float)($b['amount'] ?? 0) - (float)($b['amount_paid'] ?? 0);
          ?>
            <tr class="hover:bg-slate-50 transition">
              <td class="px-4 py-3 font-semibold text-slate-900 tabular-nums">#<?= intval($b['id']) ?></td>
              <td class="px-4 py-3">
                <div class="font-medium text-slate-900">
                  <?= htmlspecialchars(trim(($b['fname'] ?? '') . ' ' . ($b['lname'] ?? ''))) ?>
                </div>
              </td>
              <td class="px-4 py-3 text-slate-600 tabular-nums"><?= htmlspecialchars($b['contact'] ?? '—') ?></td>
              <td class="px-4 py-3 text-right text-slate-600 tabular-nums"><?= htmlspecialchars($b['prev']) ?></td>
              <td class="px-4 py-3 text-right text-slate-600 tabular-nums"><?= htmlspecialchars($b['pres']) ?></td>
              <td class="px-4 py-3 text-right text-slate-600 tabular-nums"><?= htmlspecialchars($b['consumption'] ?? '—') ?></td>
              <td class="px-4 py-3 text-right font-semibold text-slate-900 tabular-nums">
                <?= number_format((float)($b['amount'] ?? 0), 2) ?>
              </td>
              <td class="px-4 py-3 text-right text-slate-600 tabular-nums">
                <?= number_format((float)($b['amount_paid'] ?? 0), 2) ?>
              </td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ring-1 <?= $cls ?>">
                  <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                  <?= ucfirst($key) ?>
                </span>
              </td>
              <td class="px-4 py-3 text-slate-600 whitespace-nowrap"><?= htmlspecialchars($b['date'] ?? '—') ?></td>
              <td class="px-4 py-3 text-right">
                <a href="viewbill.php?id=<?= intval($b['id']) ?>"
                   class="inline-flex items-center gap-1 text-sm font-medium text-teal-700 hover:text-teal-800 hover:gap-2 transition-all">
                  View <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php include 'footer.php'; ?>