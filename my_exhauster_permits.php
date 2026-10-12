<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
include_once 'db.php';
$pageTitle = 'My Exhauster Permits';
include 'header.php';

$user_id = intval($_SESSION['id']);

$stmt = mysqli_prepare($conn,
    "SELECT id, permit_number, vehicle_registration, vehicle_capacity, status, created_at
     FROM exhauster_permits WHERE user_id = ? ORDER BY created_at DESC"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$permits = [];
while ($row = mysqli_fetch_assoc($res)) $permits[] = $row;
mysqli_stmt_close($stmt);

$statusColors = [
    'pending'   => 'bg-amber-50 text-amber-700 ring-amber-200/70',
    'in_review' => 'bg-blue-50 text-blue-700 ring-blue-200/70',
    'approved'  => 'bg-emerald-50 text-emerald-700 ring-emerald-200/70',
    'rejected'  => 'bg-rose-50 text-rose-700 ring-rose-200/70',
];
$statusLabels = [
    'pending'   => 'Pending review',
    'in_review' => 'In review',
    'approved'  => 'Approved',
    'rejected'  => 'Rejected',
];
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between mb-8 gap-4">
    <div>
      <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">My exhauster permits</h1>
      <p class="text-sm text-slate-500 mt-1">Track the status of your submitted permit applications.</p>
    </div>
    <a href="exhauster.php"
       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-white
              bg-gradient-to-b from-slate-800 to-slate-900
              shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all w-max">
      <i data-lucide="plus" class="w-4 h-4"></i>
      New permit
    </a>
  </div>

  <?php if (empty($permits)): ?>
    <div class="rounded-2xl bg-white border border-slate-200 p-10 text-center">
      <div class="mx-auto w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
        <i data-lucide="truck" class="w-6 h-6 text-slate-500"></i>
      </div>
      <h2 class="text-base font-semibold text-slate-900">No exhauster permits yet</h2>
      <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">
        Apply for a permit — once submitted, it will appear here with its real-time status.
      </p>
      <a href="exhauster.php"
         class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-white
                bg-gradient-to-b from-slate-800 to-slate-900
                shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all">
        <i data-lucide="file-plus" class="w-4 h-4"></i>
        Apply for permit
      </a>
    </div>
  <?php else: ?>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead>
            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
              <th class="px-5 py-3 font-semibold">Permit #</th>
              <th class="px-5 py-3 font-semibold">Vehicle</th>
              <th class="px-5 py-3 font-semibold">Capacity</th>
              <th class="px-5 py-3 font-semibold">Submitted</th>
              <th class="px-5 py-3 font-semibold">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php foreach ($permits as $p):
              $key = strtolower($p['status'] ?? 'pending');
              $cls = $statusColors[$key] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
              $lbl = $statusLabels[$key] ?? ucfirst($p['status'] ?? 'Unknown');
            ?>
              <tr class="hover:bg-slate-50 transition">
                <td class="px-5 py-4 font-semibold text-slate-900">
                  <?= htmlspecialchars($p['permit_number']) ?>
                </td>
                <td class="px-5 py-4 text-slate-600"><?= htmlspecialchars($p['vehicle_registration'] ?: '—') ?></td>
                <td class="px-5 py-4 text-slate-600">
                  <?= $p['vehicle_capacity'] ? htmlspecialchars($p['vehicle_capacity']) . ' L' : '—' ?>
                </td>
                <td class="px-5 py-4 text-slate-600">
                  <?= htmlspecialchars(date('d M Y, H:i', strtotime($p['created_at']))) ?>
                </td>
                <td class="px-5 py-4">
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ring-1 <?= $cls ?>">
                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                    <?= htmlspecialchars($lbl) ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <p class="text-xs text-slate-500 mt-4 flex items-center gap-1.5">
      <i data-lucide="info" class="w-3.5 h-3.5"></i>
      Status updates are made by NCWSC staff. Check this page regularly for progress.
    </p>
  <?php endif; ?>
</div>

<?php include 'footer.php'; ?>