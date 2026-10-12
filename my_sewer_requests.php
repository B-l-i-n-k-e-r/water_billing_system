<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
include_once 'db.php';
$pageTitle = 'My Sewer Requests';
include 'header.php';

$user_id = intval($_SESSION['id']);

$stmt = mysqli_prepare($conn,
    "SELECT id, request_number, account_number, status, created_at
     FROM sewer_requests WHERE user_id = ? ORDER BY created_at DESC"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$requests = [];
while ($row = mysqli_fetch_assoc($res)) $requests[] = $row;
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
      <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">My sewer requests</h1>
      <p class="text-sm text-slate-500 mt-1">Track the status of your submitted connection requests.</p>
    </div>
    <a href="addsewer_form.php"
       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-white
              bg-gradient-to-b from-slate-800 to-slate-900
              shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all w-max">
      <i data-lucide="plus" class="w-4 h-4"></i>
      New request
    </a>
  </div>

  <?php if (empty($requests)): ?>
    <div class="rounded-2xl bg-white border border-slate-200 p-10 text-center">
      <div class="mx-auto w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
        <i data-lucide="waves" class="w-6 h-6 text-slate-500"></i>
      </div>
      <h2 class="text-base font-semibold text-slate-900">No sewer requests yet</h2>
      <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">
        Submit your first sewer connection request — it will appear here with its status.
      </p>
      <a href="addsewer_form.php"
         class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-white
                bg-gradient-to-b from-slate-800 to-slate-900
                shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Add sewer connection
      </a>
    </div>
  <?php else: ?>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead>
            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
              <th class="px-5 py-3 font-semibold">Request #</th>
              <th class="px-5 py-3 font-semibold">Account</th>
              <th class="px-5 py-3 font-semibold">Submitted</th>
              <th class="px-5 py-3 font-semibold">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php foreach ($requests as $r):
              $key = strtolower($r['status'] ?? 'pending');
              $cls = $statusColors[$key] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
              $lbl = $statusLabels[$key] ?? ucfirst($r['status'] ?? 'Unknown');
            ?>
              <tr class="hover:bg-slate-50 transition">
                <td class="px-5 py-4 font-semibold text-slate-900">
                  <?= htmlspecialchars($r['request_number']) ?>
                </td>
                <td class="px-5 py-4 text-slate-600"><?= htmlspecialchars($r['account_number']) ?></td>
                <td class="px-5 py-4 text-slate-600">
                  <?= htmlspecialchars(date('d M Y, H:i', strtotime($r['created_at']))) ?>
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