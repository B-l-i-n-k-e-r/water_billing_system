<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn   = isset($_SESSION['id']);
$current_page = basename($_SERVER['PHP_SELF']);

// Optional page title (set before including this file)
$pageTitle = $pageTitle ?? 'NCWSC Online Services';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> — NCWSC</title>

    <!-- Favicon -->
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

        /* Legacy class names — remapped to new palette so older pages still work */
        .bg-ncwsc-blue     { background-color: var(--ncwsc-navy); }
        .text-ncwsc-blue   { color: var(--ncwsc-teal); }
        .border-ncwsc-blue { border-color: var(--ncwsc-teal); }

        /* Legacy buttons — recolored, not removed */
        .btn-green       { background-color: var(--ncwsc-navy); }
        .btn-green:hover { background-color: #0a1c33; }
        .btn-blue        { background-color: var(--ncwsc-teal); }
        .btn-blue:hover  { background-color: #0a6572; }

        /* Button spinner */
        .btn-loading {
            position: relative;
            pointer-events: none;
            opacity: 0.75;
        }
        .btn-loading::after {
            content: '';
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255,255,255,0.4);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }
        @keyframes spin { to { transform: translateY(-50%) rotate(360deg); } }

        /* Global top loading bar */
        #topbar-loader {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            width: 0%;
            background: var(--ncwsc-teal);
            z-index: 9999;
            transition: width 0.3s ease;
        }
        #topbar-loader.active { width: 80%; }

        /* Print styles */
        @media print {
            header, nav, footer, .no-print {
                display: none !important;
            }
            body { background: white !important; font-size: 11pt !important; }
            main { padding: 0 !important; max-width: 100% !important; }
            .print-card { page-break-inside: avoid; }
            a { color: #000 !important; text-decoration: none !important; }
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col">

<!-- Top loading bar -->
<div id="topbar-loader"></div>

<!-- Top Header Bar -->
<header class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-3">
                <img src="img/ncwsc-logo.png" alt="NCWSC Logo" class="h-10 w-auto" onerror="this.style.display='none'">
                <a href="index.php" class="text-xl font-bold text-slate-800 hover:text-slate-900 transition">
                    NCWSC Online Services
                </a>
            </div>

            <!-- Desktop nav -->
            <div class="hidden md:flex items-center gap-6">
                <a href="dashboard.php" class="text-sm font-medium text-slate-600 hover:text-teal-700 transition">HOME</a>
                <?php if ($isLoggedIn): ?>
                    <a href="logout.php" class="text-sm font-medium text-slate-600 hover:text-teal-700 transition">LOGOUT</a>
                <?php else: ?>
                    <a href="login.php" class="text-sm font-medium text-slate-600 hover:text-teal-700 transition">LOGIN</a>
                <?php endif; ?>
                <a href="index.php" class="text-sm font-medium text-slate-600 hover:text-teal-700 transition">MAIN WEBSITE</a>
            </div>

            <!-- Mobile menu button -->
            <button type="button"
                    onclick="document.getElementById('mobile-nav').classList.toggle('hidden')"
                    class="md:hidden p-2 rounded-md border border-slate-300 text-slate-600 hover:bg-slate-50 transition">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>
        </div>
    </div>

    <!-- Mobile dropdown -->
    <div id="mobile-nav" class="hidden md:hidden border-t border-slate-200 bg-white px-4 py-3 space-y-2">
        <a href="index.php" class="block py-2 text-sm font-medium text-slate-700 hover:text-teal-700 transition">HOME</a>
        <?php if ($isLoggedIn): ?>
            <a href="logout.php" class="block py-2 text-sm font-medium text-rose-600">LOGOUT</a>
        <?php else: ?>
            <a href="login.php" class="block py-2 text-sm font-medium text-slate-700 hover:text-teal-700 transition">LOGIN</a>
        <?php endif; ?>
        <a href="index.php" class="block py-2 text-sm font-medium text-slate-700 hover:text-teal-700 transition">MAIN WEBSITE</a>
    </div>
</header>

<!-- Portal Tabs (only when logged in) -->
<?php if ($isLoggedIn): ?>
<nav class="bg-white border-b border-slate-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex space-x-6 overflow-x-auto">
            <a href="apply.php" class="py-4 px-1 border-b-2 whitespace-nowrap <?php echo $current_page == 'apply.php' ? 'border-teal-700 text-teal-700' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'; ?> text-sm font-medium transition">
                APPLY WATER OR SEWER
            </a>
            <a href="upload_application.php" class="py-4 px-1 border-b-2 whitespace-nowrap <?php echo $current_page == 'upload_application.php' ? 'border-teal-700 text-teal-700' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'; ?> text-sm font-medium transition">
                UPLOAD APPLICATION
            </a>
            <a href="track_application.php" class="py-4 px-1 border-b-2 whitespace-nowrap <?php echo $current_page == 'track_application.php' ? 'border-teal-700 text-teal-700' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'; ?> text-sm font-medium transition">
                TRACK APPLICATION
            </a>
            <a href="my_sewer_requests.php" class="py-4 px-1 border-b-2 whitespace-nowrap <?php echo $current_page == 'my_sewer_requests.php' ? 'border-teal-700 text-teal-700' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'; ?> text-sm font-medium transition">
                MY SEWER REQUESTS
            </a>
            <a href="my_exhauster_permits.php" class="py-4 px-1 border-b-2 whitespace-nowrap <?php echo $current_page == 'my_exhauster_permits.php' ? 'border-teal-700 text-teal-700' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'; ?> text-sm font-medium transition">
                MY EXHAUSTER PERMITS
            </a>
        </div>
    </div>
</nav>
<?php endif; ?>

<main class="flex-grow">

<script>
    // Button spinner on submit
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.dataset.noSpinner === 'true') return;
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.classList.add('btn-loading');
        }
    });

    // Top loading bar on navigation
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (!link) return;
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || link.target === '_blank') return;

        const loader = document.getElementById('topbar-loader');
        if (loader) loader.classList.add('active');
    });

    // Remove loader when the new page loads
    window.addEventListener('pageshow', function() {
        const loader = document.getElementById('topbar-loader');
        if (loader) {
            loader.classList.remove('active');
        }
    });
</script>