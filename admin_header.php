<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn   = isset($_SESSION['id']);
$level        = intval($_SESSION['userlevel'] ?? 3);
$current_page = basename($_SERVER['PHP_SELF']);
$pageTitle    = $pageTitle ?? 'Staff Portal';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> — NCWSC Staff</title>

    <link rel="icon" type="image/png" href="img/favicon.png">
    <link rel="apple-touch-icon" href="img/favicon.png">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* ===== NCWSC brand palette (muted navy + teal) ===== */
        :root {
            --ncwsc-navy: #0f2744;
            --ncwsc-teal: #0d7d8c;
        }

        /* Legacy class names — remapped so older admin pages keep working */
        .bg-ncwsc-blue     { background-color: var(--ncwsc-navy); }
        .text-ncwsc-blue   { color: var(--ncwsc-teal); }
        .border-ncwsc-blue { border-color: var(--ncwsc-teal); }

        .btn-green       { background-color: var(--ncwsc-navy); }
        .btn-green:hover { background-color: #0a1c33; }
        .btn-blue        { background-color: var(--ncwsc-teal); }
        .btn-blue:hover  { background-color: #0a6572; }
        .btn-red         { background-color: #dc3545; }
        .btn-red:hover   { background-color: #b02030; }

        /* Button spinner */
        .btn-loading { position: relative; pointer-events: none; opacity: 0.75; }
        .btn-loading::after {
            content: '';
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            width: 14px; height: 14px;
            border: 2px solid rgba(255,255,255,0.4);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }
        @keyframes spin { to { transform: translateY(-50%) rotate(360deg); } }

        /* Top loading bar */
        #topbar-loader {
            position: fixed; top: 0; left: 0; height: 3px; width: 0%;
            background: var(--ncwsc-teal); z-index: 9999; transition: width 0.3s ease;
        }
        #topbar-loader.active { width: 80%; }

        /* Print styles */
        @media print {
            header, nav, footer, .no-print { display: none !important; }
            body { background: white !important; font-size: 11pt !important; }
            main { padding: 0 !important; max-width: 100% !important; }
            .print-card { page-break-inside: avoid; }
            a { color: #000 !important; text-decoration: none !important; }
        }

        /* Print-only block */
        .print-only { display: none; }
        @media print { .print-only { display: block !important; } }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col">

<div id="topbar-loader" class="no-print"></div>

<!-- Top Header -->
<header class="bg-white border-b border-slate-200 no-print">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-3">
                <img src="img/ncwsc-logo.png" alt="NCWSC" class="h-10 w-auto" onerror="this.style.display='none'">
                <a href="admin_dashboard.php" class="text-xl font-bold text-slate-800 hover:text-slate-900 transition">
                    NCWSC Staff Portal
                </a>
            </div>

            <div class="hidden md:flex items-center gap-6">
                <a href="admin_dashboard.php" class="text-sm font-medium text-slate-600 hover:text-teal-700 transition">DASHBOARD</a>
                <?php if ($isLoggedIn): ?>
                    <a href="logout.php" class="text-sm font-medium text-slate-600 hover:text-teal-700 transition">LOGOUT</a>
                <?php endif; ?>
                <a href="index.php" class="text-sm font-medium text-slate-600 hover:text-teal-700 transition">MAIN WEBSITE</a>
            </div>

            <button type="button"
                    onclick="document.getElementById('mobile-nav').classList.toggle('hidden')"
                    class="md:hidden p-2 rounded-md border border-slate-300 text-slate-600 hover:bg-slate-50 transition">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>
        </div>
    </div>

    <!-- Mobile dropdown -->
    <div id="mobile-nav" class="hidden md:hidden border-t border-slate-200 bg-white px-4 py-3 space-y-2 no-print">
        <a href="admin_dashboard.php" class="block py-2 text-sm font-medium text-slate-700 hover:text-teal-700 transition">DASHBOARD</a>
        <a href="clients.php"              class="block py-2 text-sm font-medium text-slate-700 hover:text-teal-700 transition">CLIENTS</a>
        <a href="billing.php"              class="block py-2 text-sm font-medium text-slate-700 hover:text-teal-700 transition">BILLING</a>
        <a href="viewpayment.php"          class="block py-2 text-sm font-medium text-slate-700 hover:text-teal-700 transition">PAYMENTS</a>
        <a href="admin_applications.php"   class="block py-2 text-sm font-medium text-slate-700 hover:text-teal-700 transition">APPLICATIONS</a>
        <a href="admin_sewer_requests.php" class="block py-2 text-sm font-medium text-slate-700 hover:text-teal-700 transition">SEWER REQUESTS</a>
        <a href="admin_exhauster.php"      class="block py-2 text-sm font-medium text-slate-700 hover:text-teal-700 transition">EXHAUSTER</a>
        <a href="admin_reports.php"        class="block py-2 text-sm font-medium text-slate-700 hover:text-teal-700 transition">REPORTS</a>
        <?php if ($level === 1): ?>
            <a href="user.php" class="block py-2 text-sm font-medium text-slate-700 hover:text-teal-700 transition">USERS</a>
        <?php endif; ?>
        <a href="logout.php" class="block py-2 text-sm font-medium text-rose-600">LOGOUT</a>
    </div>
</header>

<!-- Staff Tabs -->
<?php if ($isLoggedIn): ?>
<nav class="bg-white border-b border-slate-200 shadow-sm no-print">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex space-x-6 overflow-x-auto">
            <?php
            $tabs = [
                'admin_dashboard.php'      => 'DASHBOARD',
                'clients.php'              => 'CLIENTS',
                'billing.php'              => 'BILLING',
                'viewpayment.php'          => 'PAYMENTS',
                'admin_applications.php'   => 'APPLICATIONS',
                'admin_sewer_requests.php' => 'SEWER REQUESTS',
                'admin_exhauster.php'      => 'EXHAUSTER',
                'admin_reports.php'        => 'REPORTS',
            ];
            if ($level === 1) {
                $tabs['user.php'] = 'USERS';
            }
            foreach ($tabs as $href => $label):
                $active = ($current_page === $href);
            ?>
                <a href="<?php echo $href; ?>"
                   class="py-4 px-1 border-b-2 whitespace-nowrap text-sm font-medium transition
                          <?php echo $active
                              ? 'border-teal-700 text-teal-700'
                              : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'; ?>">
                    <?php echo $label; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</nav>
<?php endif; ?>

<!-- Print-only header -->
<div class="print-only" style="margin-bottom:20px;">
    <div style="border-bottom:2px solid #0f2744;padding-bottom:12px;">
        <h1 style="font-size:20px;color:#0f2744;margin:0;font-weight:bold;">NCWSC Online Services</h1>
        <p style="font-size:11px;color:#666;margin:4px 0 0;">
            Nairobi City Water and Sewerage Company — Staff Report
        </p>
    </div>
    <p style="font-size:11px;color:#666;text-align:right;margin:8px 0 0;">
        Printed: <?php echo date('d M Y, H:i'); ?>
    </p>
</div>

<main class="flex-grow">

<script>
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.dataset.noSpinner === 'true') return;
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.classList.add('btn-loading');
    });

    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (!link) return;
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || link.target === '_blank') return;
        const loader = document.getElementById('topbar-loader');
        if (loader) loader.classList.add('active');
    });

    window.addEventListener('pageshow', function() {
        const loader = document.getElementById('topbar-loader');
        if (loader) loader.classList.remove('active');
    });
</script>