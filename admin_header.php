<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn   = isset($_SESSION['id']);
$level        = intval($_SESSION['userlevel'] ?? 3);
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NCWSC Staff Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .bg-ncwsc-blue   { background-color: #0066CC; }
        .text-ncwsc-blue { color: #0066CC; }
        .border-ncwsc-blue { border-color: #0066CC; }
        .btn-green { background-color: #28a745; }
        .btn-green:hover { background-color: #218838; }
        .btn-blue { background-color: #0066CC; }
        .btn-blue:hover { background-color: #0056b3; }
        .btn-red { background-color: #dc3545; }
        .btn-red:hover { background-color: #c82333; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

<!-- Top Header -->
<header class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-3">
                <img src="img/ncwsc-logo.png" alt="NCWSC" class="h-10 w-auto" onerror="this.style.display='none'">
                <a href="admin_dashboard.php" class="text-xl font-bold text-gray-800">NCWSC Staff Portal</a>
            </div>
            <div class="flex items-center gap-4">
                <a href="admin_dashboard.php" class="text-sm font-medium text-gray-600 hover:text-ncwsc-blue">DASHBOARD</a>
                <?php if ($isLoggedIn): ?>
                    <a href="logout.php" class="text-sm font-medium text-gray-600 hover:text-ncwsc-blue">LOGOUT</a>
                <?php endif; ?>
                <a href="https://nairobiwater.co.ke" target="_blank" class="text-sm font-medium text-gray-600 hover:text-ncwsc-blue">MAIN WEBSITE</a>
            </div>
        </div>
    </div>
</header>

<!-- Staff Portal Tabs -->
<?php if ($isLoggedIn): ?>
<nav class="bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex space-x-6 overflow-x-auto">
            <?php
            $tabs = [
                'admin_dashboard.php'    => 'DASHBOARD',
                'clients.php'            => 'CLIENTS',
                'billing.php'            => 'BILLING',
                'viewpayment.php'        => 'PAYMENTS',
                'admin_applications.php' => 'APPLICATIONS',
                'admin_sewer_requests.php' => 'SEWER REQUESTS',
                'admin_exhauster.php'    => 'EXHAUSTER',
                 'admin_reports.php'      => 'REPORTS',
            ];
            // Admin-only tab
            if ($level === 1) {
                $tabs['user.php'] = 'USERS';
            }
            foreach ($tabs as $href => $label):
                $active = ($current_page === $href);
            ?>
                <a href="<?php echo $href; ?>"
                   class="py-4 px-1 border-b-2 whitespace-nowrap text-sm font-medium
                          <?php echo $active
                              ? 'border-ncwsc-blue text-ncwsc-blue'
                              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                    <?php echo $label; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</nav>
<?php endif; ?>

<main class="flex-grow">