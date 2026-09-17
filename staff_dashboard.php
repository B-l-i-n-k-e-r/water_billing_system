<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'auth.php';
// Allows Staff (3), and can also be accessed by Cashiers/Admins if needed
checkLevel([1, 2, 3]);

include_once 'db.php';

// Fetch basic summary stats for the dashboard
$total_clients = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM owners");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total_clients = $row['total'];
    mysqli_free_result($result);
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Water Billing System</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen font-sans flex flex-col justify-between">

    <!-- Navigation Bar -->
    <nav class="bg-slate-800/90 backdrop-blur border-b border-slate-700/60 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Brand / Logo -->
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-indigo-500/10 text-indigo-400 rounded-xl border border-indigo-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                    </div>
                    <a href="dashboard.php" class="text-base font-bold text-white tracking-wide">Water Billing System</a>
                </div>

                <!-- Desktop Navigation Links -->
                <div class="hidden md:flex items-center gap-1.5">
                    <a href="staff_dashboard.php" class="px-3.5 py-2 text-xs font-semibold rounded-xl transition flex items-center gap-2 bg-indigo-600 text-white shadow-md shadow-indigo-600/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        Dashboard
                    </a>

                    <a href="clients.php" class="px-3.5 py-2 text-xs font-semibold rounded-xl transition flex items-center gap-2 text-slate-300 hover:text-white hover:bg-slate-800/80">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        Clients
                    </a>

                    <!-- Logout Button -->
                    <a href="logout.php" class="ml-2 px-3.5 py-2 text-xs font-semibold text-rose-300 hover:text-white bg-rose-500/10 hover:bg-rose-600/80 border border-rose-500/20 rounded-xl transition flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Logout
                    </a>
                </div>

                <!-- Mobile Menu Toggle -->
                <div class="md:hidden flex items-center">
                    <button type="button" 
                            onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" 
                            class="text-slate-400 hover:text-white p-2 rounded-lg bg-slate-800 border border-slate-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Drawer -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-slate-700/60 bg-slate-800/95 px-4 pt-2 pb-4 space-y-2">
            <a href="staff_dashboard.php" class="block px-3 py-2 text-sm font-semibold rounded-xl bg-indigo-600 text-white">Dashboard</a>
            <a href="clients.php" class="block px-3 py-2 text-sm font-semibold rounded-xl text-slate-300 hover:text-white hover:bg-slate-700/50">Clients</a>
            <a href="logout.php" class="block px-3 py-2 text-sm font-semibold rounded-xl text-rose-400 hover:bg-rose-500/10">Logout</a>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-grow">
        
        <!-- Welcome Banner -->
        <div class="bg-gradient-to-r from-indigo-900/50 via-slate-800 to-slate-800 border border-indigo-500/20 rounded-2xl p-6 sm:p-8 mb-8 shadow-xl">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider bg-indigo-500/20 text-indigo-300 rounded-full border border-indigo-500/30">Staff Portal</span>
                    <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight mt-2">Welcome Back, Staff Member</h1>
                    <p class="text-xs sm:text-sm text-slate-300 mt-1">Manage customer records and navigate water service operations from your workspace.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="clients.php" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs rounded-xl shadow-lg shadow-indigo-600/20 transition flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4"></i>
                        Manage Clients
                    </a>
                </div>
            </div>
        </div>

        <!-- Metric Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            
            <!-- Card 1: Total Clients -->
            <div class="bg-slate-800/80 border border-slate-700/60 rounded-2xl p-6 shadow-xl flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Registered Clients</p>
                    <h3 class="text-3xl font-bold font-mono text-white mt-2"><?php echo $total_clients; ?></h3>
                    <p class="text-[11px] text-indigo-400 mt-1 flex items-center gap-1">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Active database records
                    </p>
                </div>
                <div class="p-4 bg-indigo-500/10 text-indigo-400 rounded-2xl border border-indigo-500/20">
                    <i data-lucide="users" class="w-7 h-7"></i>
                </div>
            </div>

            <!-- Card 2: Quick Action Shortcut -->
            <div class="bg-slate-800/80 border border-slate-700/60 rounded-2xl p-6 shadow-xl flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Client Directory</p>
                    <h3 class="text-base font-bold text-white mt-1">Add & View Profiles</h3>
                    <a href="clients.php" class="inline-flex items-center gap-1 text-xs text-indigo-400 hover:text-indigo-300 font-semibold mt-2">
                        Open client registry &rarr;
                    </a>
                </div>
                <div class="p-4 bg-emerald-500/10 text-emerald-400 rounded-2xl border border-emerald-500/20">
                    <i data-lucide="user-plus" class="w-7 h-7"></i>
                </div>
            </div>

            <!-- Card 3: System Status -->
            <div class="bg-slate-800/80 border border-slate-700/60 rounded-2xl p-6 shadow-xl flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">System Status</p>
                    <h3 class="text-base font-bold text-emerald-400 mt-1 flex items-center gap-2">
                        Operational
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-1">Database connection online</p>
                </div>
                <div class="p-4 bg-emerald-500/10 text-emerald-400 rounded-2xl border border-emerald-500/20">
                    <i data-lucide="shield-check" class="w-7 h-7"></i>
                </div>
            </div>

        </div>

    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800 py-4 text-center text-xs text-slate-500 mt-8">
        Water Billing System &copy; <?php echo date('Y'); ?> &bull; Staff Portal
    </footer>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>