<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
include_once 'db.php';
$pageTitle = 'Track Application';
include 'header.php';

$user_id = intval($_SESSION['id']);
$applications = [];

// Try to fetch applications — the table may not exist yet, so guard it
$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'applications'");
if ($tableCheck && mysqli_num_rows($tableCheck) > 0) {
    $stmt = mysqli_prepare($conn,
        "SELECT id, application_number, service_type AS type, status, created_at 
         FROM applications WHERE user_id = ? ORDER BY created_at DESC"
    );
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($res)) {
            $applications[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}

$count = count($applications);

$statusColors = [
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

  <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between mb-8 gap-4">
    <div>
      <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Track your application</h1>
      <p class="text-sm text-slate-500 mt-1">Check the status of your water and sewer applications.</p>
    </div>
    <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200
                 text-sm font-medium text-slate-700 w-max">
      <i data-lucide="file-text" class="w-4 h-4 text-slate-500"></i>
      <?= $count ?> application<?= $count === 1 ? '' : 's' ?>
    </span>
  </div>

  <?php if ($count === 0): ?>
    <div class="rounded-2xl bg-white border border-slate-200 p-10 text-center">
      <div class="mx-auto w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
        <i data-lucide="inbox" class="w-6 h-6 text-slate-500"></i>
      </div>
      <h2 class="text-base font-semibold text-slate-900">No applications to track</h2>
      <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">
        Once you submit a water or sewer application, it will appear here with its real-time status.
      </p>
      <a href="apply.php"
         class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-white
                bg-gradient-to-b from-slate-800 to-slate-900
                shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Start an application
      </a>
    </div>
  <?php else: ?>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead>
            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
              <th class="px-5 py-3 font-semibold">App number</th>
              <th class="px-5 py-3 font-semibold">Type</th>
              <th class="px-5 py-3 font-semibold">Status</th>
              <th class="px-5 py-3 font-semibold">Date submitted</th>
              <th class="px-5 py-3 font-semibold text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php foreach ($applications as $app):
              $statusKey = strtolower($app['status'] ?? 'pending');
              $cls = $statusColors[$statusKey] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
              $lbl = $statusLabels[$statusKey] ?? ucfirst($app['status'] ?? 'Unknown');
            ?>
              <tr class="hover:bg-slate-50 transition">
                <td class="px-5 py-4 font-semibold text-slate-900">
                  <?= htmlspecialchars($app['application_number']) ?>
                </td>
                <td class="px-5 py-4 text-slate-600 capitalize"><?= htmlspecialchars($app['type']) ?></td>
                <td class="px-5 py-4">
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ring-1 <?= $cls ?>">
                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                    <?= htmlspecialchars($lbl) ?>
                  </span>
                </td>
                <td class="px-5 py-4 text-slate-600">
                  <?= htmlspecialchars(date('d M Y', strtotime($app['created_at']))) ?>
                </td>
                <td class="px-5 py-4 text-right">
                  <a href="view_application.php?id=<?= intval($app['id']) ?>"
                     class="inline-flex items-center gap-1 text-sm font-medium text-teal-700 hover:text-teal-800 hover:gap-2 transition-all">
                    View <i data-lucide="arrow-right" class="w-4 h-4"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

</div>

<?php include 'footer.php'; ?>