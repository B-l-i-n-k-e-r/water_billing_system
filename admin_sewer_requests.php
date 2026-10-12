<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';
include_once 'tariff.php';
$pageTitle = 'Sewer Requests';
include 'admin_header.php';

$search = trim($_GET['q'] ?? '');
$status = trim($_GET['status'] ?? '');

$sql = "SELECT sr.*, u.name AS user_name, u.email AS user_email
        FROM sewer_requests sr
        LEFT JOIN user u ON u.id = sr.user_id
        WHERE 1=1";
$params = [];
$types  = '';

if ($search !== '') {
    $sql .= " AND (sr.request_number LIKE ? OR sr.account_number LIKE ? OR u.name LIKE ?)";
    $like = "%$search%";
    for ($i = 0; $i < 3; $i++) $params[] = $like;
    $types .= 'sss';
}
if ($status !== '' && in_array($status, ['pending','in_review','approved','rejected'])) {
    $sql .= " AND sr.status = ?";
    $params[] = $status;
    $types .= 's';
}
$sql .= " ORDER BY sr.created_at DESC LIMIT 200";

$stmt = mysqli_prepare($conn, $sql);
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$requests = [];
while ($row = mysqli_fetch_assoc($res)) $requests[] = $row;
mysqli_stmt_close($stmt);

$flash = $_GET['success'] ?? null;
$statusStyles = [
    'pending'   => 'bg-amber-50 text-amber-700 ring-amber-200/70',
    'in_review' => 'bg-blue-50 text-blue-700 ring-blue-200/70',
    'approved'  => 'bg-emerald-50 text-emerald-700 ring-emerald-200/70',
    'rejected'  => 'bg-rose-50 text-rose-700 ring-rose-200/70',
];
$statusLabels = [
    'pending'   => 'Pending',
    'in_review' => 'In review',
    'approved'  => 'Approved',
    'rejected'  => 'Rejected',
];
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Sewer connection requests</h1>
    <p class="text-sm text-slate-500 mt-1">Requests from existing customers to add sewer service.</p>
  </div>

  <?php if ($flash === 'updated'): ?>
    <div class="mb-6 flex items-start gap-3 p-3.5 rounded-xl bg-emerald-50 ring-1 ring-emerald-200/70 text-emerald-800 text-sm">
      <i data-lucide="check-circle" class="w-5 h-5 mt-0.5 shrink-0"></i>
      <span>Sewer request updated.</span>
    </div>
  <?php endif; ?>

  <!-- Filters -->
  <form method="GET"
        class="bg-white rounded-2xl border border-slate-200 p-3 sm:p-4 mb-6
               flex flex-col sm:flex-row gap-3">

    <div class="relative flex-1">
      <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
             placeholder="Search by request number, account, or name"
             class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white text-slate-900 placeholder-slate-400
                    border border-slate-200 outline-none transition hover:border-slate-300
                    focus:border-teal-600 focus:ring-3 focus:ring-teal-600/15">
    </div>

    <select name="status"
            class="px-4 py-2.5 rounded-xl bg-white text-slate-900 border border-slate-200
                   outline-none transition hover:border-slate-300
                   focus:border-teal-600 focus:ring-3 focus:ring-teal-600/15">
      <option value="">All statuses</option>
      <?php foreach ($statusLabels as $k => $label): ?>
        <option value="<?= $k ?>" <?= $status===$k?'selected':'' ?>><?= $label ?></option>
      <?php endforeach; ?>
    </select>

    <button type="submit"
            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white
                   bg-gradient-to-b from-slate-800 to-slate-900
                   shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all whitespace-nowrap">
      Filter
    </button>

    <?php if ($search !== '' || $status !== ''): ?>
      <a href="admin_sewer_requests.php"
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
            <th class="px-5 py-3 font-semibold">Request #</th>
            <th class="px-5 py-3 font-semibold">Customer</th>
            <th class="px-5 py-3 font-semibold">Account</th>
            <th class="px-5 py-3 font-semibold">Status</th>
            <th class="px-5 py-3 font-semibold">Date</th>
            <th class="px-5 py-3 font-semibold text-right">Update status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($requests)): ?>
            <tr>
              <td colspan="6" class="px-6 py-16 text-center">
                <div class="mx-auto w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
                  <i data-lucide="waves" class="w-6 h-6 text-slate-500"></i>
                </div>
                <p class="text-base font-semibold text-slate-900">No sewer requests</p>
                <p class="text-sm text-slate-500 mt-1">
                  <?= ($search || $status) ? 'Try adjusting your filters.' : 'Requests will appear here once submitted.' ?>
                </p>
              </td>
            </tr>
          <?php else: foreach ($requests as $r):
            $key = strtolower($r['status'] ?? 'pending');
            $cls = $statusStyles[$key] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
            $lbl = $statusLabels[$key] ?? ucfirst(str_replace('_', ' ', $r['status'] ?? 'Unknown'));
          ?>
            <tr class="hover:bg-slate-50 transition">
              <td class="px-5 py-4 font-semibold text-slate-900 whitespace-nowrap">
                <?= htmlspecialchars($r['request_number']) ?>
              </td>
              <td class="px-5 py-4">
                <div class="font-medium text-slate-900"><?= htmlspecialchars($r['user_name'] ?: '—') ?></div>
                <div class="text-xs text-slate-500 mt-0.5"><?= htmlspecialchars($r['user_email'] ?: '') ?></div>
              </td>
              <td class="px-5 py-4 text-slate-600 tabular-nums"><?= htmlspecialchars($r['account_number']) ?></td>
              <td class="px-5 py-4">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ring-1 <?= $cls ?>">
                  <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                  <?= htmlspecialchars($lbl) ?>
                </span>
              </td>
              <td class="px-5 py-4 text-slate-600 whitespace-nowrap">
                <?= htmlspecialchars(date('d M Y', strtotime($r['created_at']))) ?>
              </td>
              <td class="px-5 py-4">
                <form action="admin_sewer_process.php" method="POST"
                      class="flex items-center justify-end gap-2">
                  <input type="hidden" name="id" value="<?= intval($r['id']) ?>">
                  <select name="status"
                          class="text-xs px-3 py-1.5 rounded-lg bg-white border border-slate-200
                                 outline-none transition hover:border-slate-300
                                 focus:border-teal-600 focus:ring-3 focus:ring-teal-600/15">
                    <?php foreach (['pending','in_review','approved','rejected'] as $s): ?>
                      <option value="<?= $s ?>" <?= $r['status']===$s?'selected':'' ?>>
                        <?= $statusLabels[$s] ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <button type="submit"
                          class="inline-flex items-center gap-1 text-xs font-semibold text-white px-3 py-1.5 rounded-lg
                                 bg-gradient-to-b from-slate-800 to-slate-900 hover:from-slate-700 hover:to-slate-800
                                 shadow-sm transition">
                    <i data-lucide="check" class="w-3 h-3"></i>
                    Save
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php include 'footer.php'; ?>