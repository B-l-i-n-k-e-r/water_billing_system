<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';
include_once 'tariff.php';
$pageTitle = 'Clients';
include 'admin_header.php';

$search = trim($_GET['q'] ?? '');

$sql = "SELECT o.*,
          (SELECT COUNT(*) FROM bill b WHERE b.owners_id = o.id) AS bill_count,
          (SELECT COALESCE(SUM(b.amount - b.amount_paid), 0) FROM bill b 
             WHERE b.owners_id = o.id AND b.status != 'paid') AS outstanding
        FROM owners o
        WHERE 1=1";
$params = [];
$types = '';
if ($search !== '') {
    $sql .= " AND (o.fname LIKE ? OR o.lname LIKE ? OR o.contact LIKE ? OR o.email LIKE ? OR o.address LIKE ?)";
    $like = "%$search%";
    for ($i = 0; $i < 5; $i++) $params[] = $like;
    $types .= 'sssss';
}
$sql .= " ORDER BY o.id DESC LIMIT 200";

$stmt = mysqli_prepare($conn, $sql);
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$clients = [];
while ($row = mysqli_fetch_assoc($res)) $clients[] = $row;
mysqli_stmt_close($stmt);

$flash = $_GET['success'] ?? null;
$clientCount = count($clients);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between mb-8 gap-4">
    <div>
      <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Clients</h1>
      <p class="text-sm text-slate-500 mt-1">Manage customer records, bills, and outstanding balances.</p>
    </div>
    <a href="addclient.php"
       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-white
              bg-gradient-to-b from-slate-800 to-slate-900
              shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all w-max">
      <i data-lucide="user-plus" class="w-4 h-4"></i>
      Add new client
    </a>
  </div>

  <!-- Flash messages -->
  <?php if ($flash === '1'): ?>
    <div class="mb-6 flex items-start gap-3 p-3.5 rounded-xl bg-emerald-50 ring-1 ring-emerald-200/70 text-emerald-800 text-sm">
      <i data-lucide="check-circle" class="w-5 h-5 mt-0.5 shrink-0"></i>
      <span>Client saved successfully.</span>
    </div>
  <?php elseif ($flash === 'deleted'): ?>
    <div class="mb-6 flex items-start gap-3 p-3.5 rounded-xl bg-emerald-50 ring-1 ring-emerald-200/70 text-emerald-800 text-sm">
      <i data-lucide="trash-2" class="w-5 h-5 mt-0.5 shrink-0"></i>
      <span>Client deleted.</span>
    </div>
  <?php endif; ?>

  <!-- Search -->
  <form method="GET"
        class="bg-white rounded-2xl border border-slate-200 p-3 sm:p-4 mb-6
               flex flex-col sm:flex-row gap-3">

    <div class="relative flex-1">
      <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
             placeholder="Search by name, phone, email, or address"
             class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white text-slate-900 placeholder-slate-400
                    border border-slate-200 outline-none transition
                    hover:border-slate-300
                    focus:border-teal-600 focus:ring-3 focus:ring-teal-600/15">
    </div>

    <button type="submit"
            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white
                   bg-gradient-to-b from-slate-800 to-slate-900
                   shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all whitespace-nowrap">
      Search
    </button>

    <?php if ($search !== ''): ?>
      <a href="clients.php"
         class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-medium text-slate-700
                bg-white border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition whitespace-nowrap">
        <i data-lucide="x" class="w-4 h-4"></i>
        Clear
      </a>
    <?php endif; ?>
  </form>

  <!-- Results info -->
  <?php if ($search !== '' && $clientCount > 0): ?>
    <p class="text-sm text-slate-500 mb-4">
      Showing <strong class="text-slate-700"><?= $clientCount ?></strong> result<?= $clientCount === 1 ? '' : 's' ?>
      for <span class="font-medium text-slate-700">"<?= htmlspecialchars($search) ?>"</span>
    </p>
  <?php endif; ?>

  <!-- Table -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead>
          <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
            <th class="px-4 py-3 font-semibold w-16">#</th>
            <th class="px-4 py-3 font-semibold">Name</th>
            <th class="px-4 py-3 font-semibold">Contact</th>
            <th class="px-4 py-3 font-semibold">Email</th>
            <th class="px-4 py-3 font-semibold">Address</th>
            <th class="px-4 py-3 font-semibold text-center">Bills</th>
            <th class="px-4 py-3 font-semibold text-right">Outstanding</th>
            <th class="px-4 py-3 font-semibold text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($clients)): ?>
            <tr>
              <td colspan="8" class="px-6 py-16 text-center">
                <?php emptyState(
                    'users',
                    'No clients yet',
                    'Get started by adding your first customer. You can create bills, record payments, and track outstanding balances for each one.',
                    'Add First Client',
                    'addclient.php'
                ); ?>
              </td>
            </tr>
          <?php else: foreach ($clients as $c):
            $outstanding = (float) $c['outstanding'];
            $hasOutstanding = $outstanding > 0;
            $fullName = trim(($c['fname'] ?? '') . ' ' . ($c['mi'] ?? '') . ' ' . ($c['lname'] ?? ''));
          ?>
            <tr class="hover:bg-slate-50 transition">
              <td class="px-4 py-4 font-semibold text-slate-900 tabular-nums">
                #<?= intval($c['id']) ?>
              </td>
              <td class="px-4 py-4">
                <div class="font-medium text-slate-900"><?= htmlspecialchars($fullName) ?></div>
              </td>
              <td class="px-4 py-4 text-slate-600 tabular-nums"><?= htmlspecialchars($c['contact']) ?></td>
              <td class="px-4 py-4 text-slate-600">
                <?php if (!empty($c['email'])): ?>
                  <a href="mailto:<?= htmlspecialchars($c['email']) ?>" class="hover:text-teal-700 transition">
                    <?= htmlspecialchars($c['email']) ?>
                  </a>
                <?php else: ?>
                  <span class="text-slate-400">—</span>
                <?php endif; ?>
              </td>
              <td class="px-4 py-4 text-slate-600 max-w-[14rem] truncate" title="<?= htmlspecialchars($c['address']) ?>">
                <?= htmlspecialchars($c['address']) ?>
              </td>
              <td class="px-4 py-4 text-center">
                <span class="inline-flex items-center justify-center min-w-[2rem] px-2 py-0.5 rounded-full text-xs font-medium
                             <?= intval($c['bill_count']) > 0 ? 'bg-slate-100 text-slate-700' : 'bg-slate-50 text-slate-400' ?>">
                  <?= intval($c['bill_count']) ?>
                </span>
              </td>
              <td class="px-4 py-4 text-right font-semibold tabular-nums
                         <?= $hasOutstanding ? 'text-rose-600' : 'text-slate-700' ?>">
                <?= number_format($outstanding, 2) ?>
              </td>
              <td class="px-4 py-4">
                <div class="flex items-center justify-end gap-3">
                  <a href="addbill.php?owners_id=<?= intval($c['id']) ?>"
                     class="inline-flex items-center gap-1 text-sm font-medium text-teal-700 hover:text-teal-800 transition">
                    <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                    Bill
                  </a>
                  <a href="deleteclient.php?id=<?= intval($c['id']) ?>"
                     onclick="return confirm('Delete this client and all their bills?');"
                     class="inline-flex items-center gap-1 text-sm font-medium text-rose-600 hover:text-rose-700 transition">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    Delete
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if ($clientCount >= 200): ?>
    <p class="text-xs text-slate-500 mt-4 flex items-center gap-1.5">
      <i data-lucide="info" class="w-3.5 h-3.5"></i>
      Showing the most recent 200 clients. Use search to narrow the list.
    </p>
  <?php endif; ?>

</div>

<?php include 'footer.php'; ?>