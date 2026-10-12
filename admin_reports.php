<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';
include_once 'tariff.php';
$pageTitle = 'Reports';
include 'admin_header.php';

/* --- APPLICATIONS BY STATUS --- */
$appStats = ['pending'=>0,'in_review'=>0,'approved'=>0,'rejected'=>0,'completed'=>0];
$r = mysqli_query($conn, "SELECT status, COUNT(*) AS c FROM applications GROUP BY status");
while ($row = mysqli_fetch_assoc($r)) if (isset($appStats[$row['status']])) $appStats[$row['status']] = intval($row['c']);
$appTotal = array_sum($appStats);

/* --- SEWER REQUESTS BY STATUS --- */
$sewerStats = ['pending'=>0,'in_review'=>0,'approved'=>0,'rejected'=>0];
$r = mysqli_query($conn, "SELECT status, COUNT(*) AS c FROM sewer_requests GROUP BY status");
while ($row = mysqli_fetch_assoc($r)) if (isset($sewerStats[$row['status']])) $sewerStats[$row['status']] = intval($row['c']);
$sewerTotal = array_sum($sewerStats);

/* --- EXHAUSTER PERMITS BY STATUS --- */
$exhStats = ['pending'=>0,'in_review'=>0,'approved'=>0,'rejected'=>0];
$r = mysqli_query($conn, "SELECT status, COUNT(*) AS c FROM exhauster_permits GROUP BY status");
while ($row = mysqli_fetch_assoc($r)) if (isset($exhStats[$row['status']])) $exhStats[$row['status']] = intval($row['c']);
$exhTotal = array_sum($exhStats);

/* --- REVENUE & OUTSTANDING --- */
$today      = date('Y-m-d');
$monthStart = date('Y-m-01');

$revenueAllTime = 0; $revenueMonth = 0; $revenueToday = 0;

$r = mysqli_query($conn, "SELECT COALESCE(SUM(ABS(amount)),0) AS total FROM transactions WHERE amount < 0");
if ($row = mysqli_fetch_assoc($r)) $revenueAllTime = (float) $row['total'];

$stmt = mysqli_prepare($conn, "SELECT COALESCE(SUM(ABS(amount)),0) AS total FROM transactions WHERE amount < 0 AND transaction_date >= ?");
mysqli_stmt_bind_param($stmt, "s", $monthStart);
mysqli_stmt_execute($stmt); $r = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($r)) $revenueMonth = (float) $row['total'];
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, "SELECT COALESCE(SUM(ABS(amount)),0) AS total FROM transactions WHERE amount < 0 AND transaction_date = ?");
mysqli_stmt_bind_param($stmt, "s", $today);
mysqli_stmt_execute($stmt); $r = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($r)) $revenueToday = (float) $row['total'];
mysqli_stmt_close($stmt);

$totalBilled = 0; $totalOutstanding = 0;
$r = mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) AS billed, COALESCE(SUM(amount - amount_paid),0) AS outstanding FROM bill");
if ($row = mysqli_fetch_assoc($r)) {
    $totalBilled      = (float) $row['billed'];
    $totalOutstanding = (float) $row['outstanding'];
}

$unpaidBills = 0;
$r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM bill WHERE status != 'paid'");
if ($row = mysqli_fetch_assoc($r)) $unpaidBills = intval($row['c']);

$customerCount = 0;
$r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM owners");
if ($row = mysqli_fetch_assoc($r)) $customerCount = intval($row['c']);

/* --- LAST 30 DAYS REVENUE --- */
$revLabels = []; $revData = [];
for ($i = 29; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $revLabels[] = date('M j', strtotime($day));
    $stmt = mysqli_prepare($conn, "SELECT COALESCE(SUM(ABS(amount)),0) AS total FROM transactions WHERE amount < 0 AND transaction_date = ?");
    mysqli_stmt_bind_param($stmt, "s", $day);
    mysqli_stmt_execute($stmt); $r = mysqli_stmt_get_result($stmt);
    $revData[] = (float) (mysqli_fetch_assoc($r)['total'] ?? 0);
    mysqli_stmt_close($stmt);
}

/* --- TOP 5 OUTSTANDING --- */
$topCustomers = [];
$r = mysqli_query($conn,
    "SELECT o.id, o.fname, o.lname, o.contact,
            COALESCE(SUM(b.amount - b.amount_paid),0) AS outstanding
     FROM owners o
     LEFT JOIN bill b ON b.owners_id = o.id AND b.status != 'paid'
     GROUP BY o.id
     HAVING outstanding > 0
     ORDER BY outstanding DESC
     LIMIT 5"
);
while ($row = mysqli_fetch_assoc($r)) $topCustomers[] = $row;

/* --- RECENT ACTIVITY --- */
$recentBills = [];
$r = mysqli_query($conn,
    "SELECT b.id, b.amount, b.bill_month, b.created_at, o.fname, o.lname
     FROM bill b LEFT JOIN owners o ON o.id = b.owners_id
     ORDER BY b.id DESC LIMIT 5"
);
while ($row = mysqli_fetch_assoc($r)) $recentBills[] = $row;

$recentPayments = [];
$r = mysqli_query($conn,
    "SELECT id, amount, description, transaction_date
     FROM transactions WHERE amount < 0
     ORDER BY id DESC LIMIT 5"
);
while ($row = mysqli_fetch_assoc($r)) $recentPayments[] = $row;

$statusLabels = [
    'pending'   => 'Pending',
    'in_review' => 'In review',
    'approved'  => 'Approved',
    'rejected'  => 'Rejected',
    'completed' => 'Completed',
];
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between mb-8 gap-4">
    <div>
      <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Reports &amp; analytics</h1>
      <p class="text-sm text-slate-500 mt-1">System overview — generated <?= date('d M Y, H:i') ?></p>
    </div>
    <button onclick="window.print()"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-medium text-slate-700
                   bg-white border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition no-print">
      <i data-lucide="printer" class="w-4 h-4"></i>
      Print report
    </button>
  </div>

  <!-- Revenue highlights -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

    <div class="bg-white rounded-2xl border border-slate-200 p-5">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Revenue (all time)</p>
          <p class="text-xl sm:text-2xl font-bold text-emerald-700 mt-2 tabular-nums"><?= kes($revenueAllTime) ?></p>
        </div>
        <div class="shrink-0 w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
          <i data-lucide="trending-up" class="w-5 h-5"></i>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-5">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Revenue (this month)</p>
          <p class="text-xl sm:text-2xl font-bold text-emerald-700 mt-2 tabular-nums"><?= kes($revenueMonth) ?></p>
        </div>
        <div class="shrink-0 w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
          <i data-lucide="calendar" class="w-5 h-5"></i>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-5">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Revenue (today)</p>
          <p class="text-xl sm:text-2xl font-bold text-emerald-700 mt-2 tabular-nums"><?= kes($revenueToday) ?></p>
        </div>
        <div class="shrink-0 w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
          <i data-lucide="sun" class="w-5 h-5"></i>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-5">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Outstanding</p>
          <p class="text-xl sm:text-2xl font-bold text-rose-600 mt-2 tabular-nums"><?= kes($totalOutstanding) ?></p>
        </div>
        <div class="shrink-0 w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
          <i data-lucide="alert-circle" class="w-5 h-5"></i>
        </div>
      </div>
    </div>

  </div>

  <!-- Key counters -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

    <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4">
      <div class="shrink-0 w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
        <i data-lucide="users" class="w-5 h-5"></i>
      </div>
      <div class="min-w-0">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Customers</p>
        <p class="text-lg font-bold text-slate-900 tabular-nums"><?= $customerCount ?></p>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4">
      <div class="shrink-0 w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
        <i data-lucide="receipt" class="w-5 h-5"></i>
      </div>
      <div class="min-w-0">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Unpaid bills</p>
        <p class="text-lg font-bold text-slate-900 tabular-nums"><?= $unpaidBills ?></p>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4">
      <div class="shrink-0 w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
        <i data-lucide="file-text" class="w-5 h-5"></i>
      </div>
      <div class="min-w-0">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Applications</p>
        <p class="text-lg font-bold text-slate-900 tabular-nums"><?= $appTotal ?></p>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4">
      <div class="shrink-0 w-11 h-11 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
        <i data-lucide="banknote" class="w-5 h-5"></i>
      </div>
      <div class="min-w-0">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Total billed</p>
        <p class="text-lg font-bold text-slate-900 tabular-nums"><?= number_format($totalBilled, 0) ?></p>
      </div>
    </div>

  </div>

  <!-- Charts -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    <div class="bg-white rounded-2xl border border-slate-200 p-6 lg:col-span-2">
      <h2 class="font-semibold text-slate-900 mb-4">Revenue — last 30 days</h2>
      <div style="height: 280px;">
        <canvas id="revenueChart"></canvas>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6">
      <h2 class="font-semibold text-slate-900 mb-4">Applications by status</h2>
      <div style="height: 280px;">
        <canvas id="appsChart"></canvas>
      </div>
    </div>

  </div>

  <!-- Status breakdowns -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    <?php
    $breakdowns = [
        ['title'=>'Applications',     'icon'=>'file-text', 'color'=>'indigo', 'stats'=>$appStats,   'total'=>$appTotal],
        ['title'=>'Sewer requests',   'icon'=>'waves',     'color'=>'cyan',   'stats'=>$sewerStats, 'total'=>$sewerTotal],
        ['title'=>'Exhauster permits','icon'=>'truck',     'color'=>'purple', 'stats'=>$exhStats,   'total'=>$exhTotal],
    ];
    foreach ($breakdowns as $b):
        $colorStyles = [
            'indigo' => 'text-indigo-600',
            'cyan'   => 'text-cyan-600',
            'purple' => 'text-purple-600',
        ];
    ?>
      <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-semibold text-slate-900 flex items-center gap-2">
            <i data-lucide="<?= $b['icon'] ?>" class="w-4 h-4 <?= $colorStyles[$b['color']] ?>"></i>
            <?= $b['title'] ?>
          </h2>
          <span class="text-xs text-slate-400"><?= $b['total'] ?> total</span>
        </div>
        <ul class="space-y-2.5 text-sm">
          <?php foreach ($b['stats'] as $k => $v): ?>
            <li class="flex items-center justify-between">
              <span class="text-slate-500"><?= $statusLabels[$k] ?? ucfirst($k) ?></span>
              <span class="font-semibold text-slate-900 tabular-nums"><?= $v ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endforeach; ?>

  </div>

  <!-- Top customers + recent activity -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <div class="bg-white rounded-2xl border border-slate-200 p-6">
      <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
        <i data-lucide="trending-down" class="w-4 h-4 text-rose-500"></i>
        Top 5 customers by outstanding
      </h2>
      <?php if (empty($topCustomers)): ?>
        <p class="text-sm text-slate-500 py-6 text-center">No outstanding balances. 🎉</p>
      <?php else: ?>
        <ul class="divide-y divide-slate-100">
          <?php foreach ($topCustomers as $c): ?>
            <li class="py-3 flex items-center justify-between gap-4">
              <div class="min-w-0">
                <p class="font-medium text-slate-900 truncate"><?= htmlspecialchars($c['fname'] . ' ' . $c['lname']) ?></p>
                <p class="text-xs text-slate-500 mt-0.5"><?= htmlspecialchars($c['contact']) ?></p>
              </div>
              <span class="shrink-0 font-bold text-rose-600 tabular-nums"><?= kes($c['outstanding']) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6">
      <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
        <i data-lucide="activity" class="w-4 h-4 text-teal-600"></i>
        Recent activity
      </h2>

      <h3 class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-2">Latest bills</h3>
      <?php if (empty($recentBills)): ?>
        <p class="text-sm text-slate-500 mb-5">No bills yet.</p>
      <?php else: ?>
        <ul class="text-sm space-y-2 mb-5">
          <?php foreach ($recentBills as $b): ?>
            <li class="flex items-center justify-between gap-3">
              <span class="text-slate-600 truncate">
                <span class="font-medium text-slate-800">#<?= intval($b['id']) ?></span> —
                <?= htmlspecialchars(trim(($b['fname'] ?? '') . ' ' . ($b['lname'] ?? '')) ?: '—') ?>
              </span>
              <span class="shrink-0 text-slate-900 font-semibold tabular-nums"><?= number_format((float)$b['amount'], 0) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <h3 class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-2 pt-4 border-t border-slate-100">Latest payments</h3>
      <?php if (empty($recentPayments)): ?>
        <p class="text-sm text-slate-500">No payments yet.</p>
      <?php else: ?>
        <ul class="text-sm space-y-2">
          <?php foreach ($recentPayments as $p): ?>
            <li class="flex items-center justify-between gap-3">
              <span class="text-slate-600 truncate"><?= htmlspecialchars(substr($p['description'], 0, 40)) ?></span>
              <span class="shrink-0 text-emerald-700 font-semibold tabular-nums"><?= number_format(abs((float)$p['amount']), 0) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

    </div>

  </div>

</div>

<!-- Chart.js -->
<script src="js/chart.min.js"></script>
<script>
  // ---- Revenue Line Chart ----
  const revenueCtx = document.getElementById('revenueChart');
  if (revenueCtx) {
    new Chart(revenueCtx, {
      type: 'line',
      data: {
        labels: <?= json_encode($revLabels) ?>,
        datasets: [{
          label: 'Revenue (KES)',
          data: <?= json_encode($revData) ?>,
          borderColor: '#0d7d8c',
          backgroundColor: 'rgba(13,125,140,0.10)',
          fill: true,
          tension: 0.3,
          pointRadius: 2,
          pointHoverRadius: 5,
          borderWidth: 2
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: { callbacks: { label: ctx => 'KES ' + Number(ctx.parsed.y).toLocaleString() } }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { callback: v => 'KES ' + Number(v).toLocaleString(), font: { size: 11 }, color: '#64748b' },
            grid: { color: 'rgba(0,0,0,0.05)' }
          },
          x: {
            ticks: { font: { size: 10 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 10, color: '#64748b' },
            grid: { display: false }
          }
        }
      }
    });
  }

  // ---- Applications Doughnut Chart ----
  const appsCtx = document.getElementById('appsChart');
  if (appsCtx) {
    new Chart(appsCtx, {
      type: 'doughnut',
      data: {
        labels: ['Pending', 'In review', 'Approved', 'Rejected', 'Completed'],
        datasets: [{
          data: [
            <?= intval($appStats['pending']) ?>,
            <?= intval($appStats['in_review']) ?>,
            <?= intval($appStats['approved']) ?>,
            <?= intval($appStats['rejected']) ?>,
            <?= intval($appStats['completed']) ?>
          ],
          backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#f43f5e', '#0d7d8c'],
          borderWidth: 0,
          hoverOffset: 6
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        cutout: '65%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: { boxWidth: 12, font: { size: 11 }, padding: 10, color: '#475569' }
          }
        }
      }
    });
  }
</script>

<?php include 'footer.php'; ?>