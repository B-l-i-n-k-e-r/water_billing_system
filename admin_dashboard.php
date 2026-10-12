<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';
$pageTitle = 'Admin Dashboard';
include 'admin_header.php';

$level = intval($_SESSION['userlevel']);
$isAdmin = ($level === 1);

$firstName = '';
if (!empty($_SESSION['name'])) {
    $firstName = explode(' ', trim($_SESSION['name']))[0];
}

$statPendingApps = 0;
$statUnpaidBills = 0;
$statOutstanding = 0;

$r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM applications WHERE status = 'pending'");
if ($r) $statPendingApps = intval(mysqli_fetch_assoc($r)['c']);

$r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM bill WHERE status = 'unpaid'");
if ($r) $statUnpaidBills = intval(mysqli_fetch_assoc($r)['c']);

$r = mysqli_query($conn, "SELECT COALESCE(SUM(amount - amount_paid),0) AS s FROM bill WHERE status != 'paid'");
if ($r) $statOutstanding = floatval(mysqli_fetch_assoc($r)['s']);

$cards = [
    ['title'=>'Billing',          'desc'=>'Create new bills from meter readings, review issued bills, and track unpaid balances.',      'href'=>'billing.php',                'icon'=>'receipt',      'tone'=>'navy',   'cta'=>'Go to billing'],
    ['title'=>'Payments',         'desc'=>'Record customer payments, generate receipts, and view payment history.',                      'href'=>'viewpayment.php',            'icon'=>'banknote',     'tone'=>'teal',   'cta'=>'View payments'],
    ['title'=>'Clients',          'desc'=>'Manage customer records — names, contacts, addresses, and account status.',                   'href'=>'clients.php',                'icon'=>'users',        'tone'=>'navy',   'cta'=>'View clients'],
    ['title'=>'Applications',     'desc'=>'Review, approve, or reject incoming water and sewer connection applications.',               'href'=>'admin_applications.php',     'icon'=>'file-check',   'tone'=>'indigo', 'cta'=>'Review applications'],
    ['title'=>'Sewer Requests',   'desc'=>'Process sewer connection requests submitted by existing customers.',                         'href'=>'admin_sewer_requests.php',   'icon'=>'waves',        'tone'=>'cyan',   'cta'=>'Review requests'],
    ['title'=>'Reports',          'desc'=>'Revenue, application trends, top customers by outstanding balance, and recent activity.',     'href'=>'admin_reports.php',          'icon'=>'bar-chart-3',  'tone'=>'amber',  'cta'=>'View reports'],
];

if ($isAdmin) {
    $cards[] = ['title'=>'Users', 'desc'=>'Manage staff accounts — create, edit, or remove system users and privilege levels.', 'href'=>'user.php', 'icon'=>'user-cog', 'tone'=>'purple', 'cta'=>'Manage users'];
}
?>

<style>
  .admin-card { transition: all .2s ease; }
  .admin-card:hover { transform: translateY(-2px); }

  .icon-tile { transition: background-color .2s ease, color .2s ease; }

  .admin-card:hover .icon-tile-navy   { background: #0f172a; color: #fff; }
  .admin-card:hover .icon-tile-teal   { background: #0f766e; color: #fff; }
  .admin-card:hover .icon-tile-indigo { background: #4338ca; color: #fff; }
  .admin-card:hover .icon-tile-cyan   { background: #0e7490; color: #fff; }
  .admin-card:hover .icon-tile-amber  { background: #d97706; color: #fff; }
  .admin-card:hover .icon-tile-purple { background: #7e22ce; color: #fff; }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <!-- Welcome banner -->
  <div class="relative overflow-hidden rounded-2xl bg-slate-900 p-6 sm:p-9 mb-8">
    <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-teal-400/10 blur-2xl"></div>
    <div class="absolute -bottom-24 -left-24 w-72 h-72 rounded-full bg-slate-400/10 blur-2xl"></div>

    <div class="relative">
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold uppercase tracking-wide
                   bg-white/10 ring-1 ring-white/20 text-white">
        <i data-lucide="<?= $isAdmin ? 'shield-check' : 'user-check' ?>" class="w-3.5 h-3.5"></i>
        <?= $isAdmin ? 'Administrator' : 'Cashier' ?> mode
      </span>

      <h1 class="mt-4 text-2xl sm:text-3xl font-bold tracking-tight text-white leading-tight">
        <?= $firstName ? 'Welcome back, ' . htmlspecialchars($firstName) : 'Control panel' ?>
      </h1>
      <p class="mt-2 text-slate-300 max-w-2xl text-sm sm:text-base">
        Manage customers, bills, payments, and applications — all from one place.
      </p>
    </div>
  </div>

  <!-- Quick stats -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10">

    <div class="bg-white rounded-2xl border border-slate-200 p-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Pending applications</p>
          <p class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2 tabular-nums"><?= $statPendingApps ?></p>
        </div>
        <div class="shrink-0 w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
          <i data-lucide="file-text" class="w-5 h-5"></i>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Unpaid bills</p>
          <p class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2 tabular-nums"><?= $statUnpaidBills ?></p>
        </div>
        <div class="shrink-0 w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
          <i data-lucide="receipt" class="w-5 h-5"></i>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-5">
      <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Outstanding (KES)</p>
          <p class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2 tabular-nums truncate">
            <?= number_format($statOutstanding, 2) ?>
          </p>
        </div>
        <div class="shrink-0 w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
          <i data-lucide="trending-up" class="w-5 h-5"></i>
        </div>
      </div>
    </div>

  </div>

  <!-- Action cards -->
  <div class="mb-4 flex items-center justify-between">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Quick actions</h2>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    <?php foreach ($cards as $c): ?>
      <a href="<?= htmlspecialchars($c['href']) ?>"
         class="admin-card group relative flex flex-col rounded-2xl bg-white border border-slate-200 p-6
                hover:border-teal-600/40 hover:shadow-xl hover:shadow-slate-900/5">

        <div class="icon-tile icon-tile-<?= htmlspecialchars($c['tone']) ?>
                    w-12 h-12 rounded-xl flex items-center justify-center mb-4
                    bg-slate-100 text-slate-700">
          <i data-lucide="<?= htmlspecialchars($c['icon']) ?>" class="w-5 h-5"></i>
        </div>

        <h3 class="text-base sm:text-lg font-semibold text-slate-900 leading-snug">
          <?= htmlspecialchars($c['title']) ?>
        </h3>
        <p class="mt-1.5 text-sm text-slate-500 leading-relaxed flex-1">
          <?= htmlspecialchars($c['desc']) ?>
        </p>

        <span class="mt-5 inline-flex items-center gap-1.5 text-sm font-medium text-teal-700
                     group-hover:gap-2.5 transition-all">
          <?= htmlspecialchars($c['cta']) ?>
          <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </span>
      </a>
    <?php endforeach; ?>
  </div>

</div>

<?php include 'footer.php'; ?>