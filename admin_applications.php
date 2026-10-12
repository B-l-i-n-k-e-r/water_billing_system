<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';
include_once 'tariff.php';
$pageTitle = 'Applications';
include 'admin_header.php';

$search = trim($_GET['q'] ?? '');
$status = trim($_GET['status'] ?? '');

$sql = "SELECT a.*, u.name AS user_name, u.email AS user_email
        FROM applications a
        LEFT JOIN user u ON u.id = a.user_id
        WHERE 1=1";
$params = [];
$types  = '';

if ($search !== '') {
    $sql .= " AND (a.application_number LIKE ? OR a.full_name LIKE ? OR a.email LIKE ? OR a.phone LIKE ?)";
    $like = "%$search%";
    for ($i = 0; $i < 4; $i++) $params[] = $like;
    $types .= 'ssss';
}
if ($status !== '' && in_array($status, ['pending','in_review','approved','rejected','completed'])) {
    $sql .= " AND a.status = ?";
    $params[] = $status;
    $types .= 's';
}
$sql .= " ORDER BY a.created_at DESC LIMIT 200";

$stmt = mysqli_prepare($conn, $sql);
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$apps = [];
while ($row = mysqli_fetch_assoc($res)) $apps[] = $row;
mysqli_stmt_close($stmt);

$flash = $_GET['success'] ?? null;

$statusStyles = [
    'pending'    => 'bg-amber-50 text-amber-700 ring-amber-200/70',
    'in_review'  => 'bg-blue-50 text-blue-700 ring-blue-200/70',
    'approved'   => 'bg-emerald-50 text-emerald-700 ring-emerald-200/70',
    'rejected'   => 'bg-rose-50 text-rose-700 ring-rose-200/70',
    'completed'  => 'bg-teal-50 text-teal-700 ring-teal-200/70',
];
$statusLabels = [
    'pending'   => 'Pending',
    'in_review' => 'In review',
    'approved'  => 'Approved',
    'rejected'  => 'Rejected',
    'completed' => 'Completed',
];
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Water / Sewer Applications</h1>
    <p class="text-sm text-slate-500 mt-1">Review and process new connection applications.</p>
  </div>

  <?php if ($flash === 'updated'): ?>
    <div class="mb-6 flex items-start gap-3 p-3.5 rounded-xl bg-emerald-50 ring-1 ring-emerald-200/70 text-emerald-800 text-sm">
      <i data-lucide="check-circle" class="w-5 h-5 mt-0.5 shrink-0"></i>
      <span>Application status updated.</span>
    </div>
  <?php endif; ?>

  <!-- Filters -->
  <form method="GET"
        class="bg-white rounded-2xl border border-slate-200 p-3 sm:p-4 mb-6
               flex flex-col sm:flex-row gap-3">

    <div class="relative flex-1">
      <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
             placeholder="Search by app no, name, email, or phone"
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
      <a href="admin_applications.php"
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
            <th class="px-5 py-3 font-semibold">App #</th>
            <th class="px-5 py-3 font-semibold">Applicant</th>
            <th class="px-5 py-3 font-semibold">Type</th>
            <th class="px-5 py-3 font-semibold">Service</th>
            <th class="px-5 py-3 font-semibold">Status</th>
            <th class="px-5 py-3 font-semibold">Date</th>
            <th class="px-5 py-3 font-semibold text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($apps)): ?>
            <tr>
              <td colspan="7" class="px-6 py-16 text-center">
                <div class="mx-auto w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
                  <i data-lucide="file-text" class="w-6 h-6 text-slate-500"></i>
                </div>
                <p class="text-base font-semibold text-slate-900">No applications found</p>
                <p class="text-sm text-slate-500 mt-1">
                  <?= ($search || $status) ? 'Try adjusting your filters.' : 'Applications will appear here once submitted.' ?>
                </p>
              </td>
            </tr>
          <?php else: foreach ($apps as $a):
            $key = strtolower($a['status'] ?? 'pending');
            $cls = $statusStyles[$key] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
            $lbl = $statusLabels[$key] ?? ucfirst(str_replace('_', ' ', $a['status'] ?? 'Unknown'));
          ?>
            <tr class="hover:bg-slate-50 transition">
              <td class="px-5 py-4 font-semibold text-slate-900 whitespace-nowrap">
                <?= htmlspecialchars($a['application_number']) ?>
              </td>
              <td class="px-5 py-4">
                <div class="font-medium text-slate-900"><?= htmlspecialchars($a['full_name']) ?></div>
                <div class="text-xs text-slate-500 mt-0.5"><?= htmlspecialchars($a['email']) ?></div>
              </td>
              <td class="px-5 py-4 text-slate-600 capitalize"><?= htmlspecialchars($a['application_type']) ?></td>
              <td class="px-5 py-4 text-slate-600 capitalize"><?= htmlspecialchars($a['service_type']) ?></td>
              <td class="px-5 py-4">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ring-1 <?= $cls ?>">
                  <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                  <?= htmlspecialchars($lbl) ?>
                </span>
              </td>
              <td class="px-5 py-4 text-slate-600 whitespace-nowrap">
                <?= htmlspecialchars(date('d M Y', strtotime($a['created_at']))) ?>
              </td>
              <td class="px-5 py-4 text-right">
                <a href="admin_application_view.php?id=<?= intval($a['id']) ?>"
                   class="inline-flex items-center gap-1 text-sm font-medium text-teal-700 hover:text-teal-800 hover:gap-2 transition-all">
                  Review <i data-lucide="arrow-right" class="w-4 h-4"></i>
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