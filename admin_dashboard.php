<?php
include 'auth.php';
checkLevel([1]); // Admin only
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Water Billing System</title>
    <!-- Tailwind CSS for modern styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen font-sans antialiased">

    <!-- Top Navigation Header -->
    <header class="border-b border-slate-800 bg-slate-950/50 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-600/20 text-blue-400 rounded-lg border border-blue-500/30">
                    <i data-lucide="droplet" class="w-6 h-6"></i>
                </div>
                <h1 class="text-lg font-bold tracking-tight text-white">Water Billing System</h1>
            </div>
            
            <a href="logout.php" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-red-400 hover:text-red-300 hover:bg-red-500/10 border border-red-500/20 rounded-lg transition duration-200">
                <i data-lucide="log-out" class="w-4 h-4"></i>
                <span>Logout</span>
            </a>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        
        <!-- Welcome Banner -->
        <div class="mb-10 p-6 sm:p-8 rounded-2xl bg-gradient-to-r from-blue-900/40 via-slate-800/60 to-slate-800/40 border border-slate-700/50 shadow-xl relative overflow-hidden">
            <div class="relative z-10">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20 mb-3">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Admin Mode
                </span>
                <h2 class="text-3xl font-extrabold text-white tracking-tight">Administrator Control Panel</h2>
                <p class="text-slate-400 mt-2 text-base max-w-2xl">Welcome, Administrator. Full system privilege is enabled for system configurations, client records, and transaction logs.</p>
            </div>
        </div>

        <!-- Dashboard Action Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- User Management Card -->
            <div class="group relative bg-slate-800/50 border border-slate-700/60 rounded-2xl p-6 hover:border-blue-500/50 transition-all duration-300 shadow-lg flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 bg-blue-500/10 text-blue-400 rounded-xl flex items-center justify-center mb-5 border border-blue-500/20 group-hover:scale-110 transition-transform duration-300">
                        <i data-lucide="users" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">User Management</h3>
                    <p class="text-slate-400 text-sm leading-relaxed mb-6">Add, edit, or remove system accounts, credentials, and access privilege levels.</p>
                </div>
                <a href="user.php" class="w-full inline-flex justify-center items-center gap-2 py-3 px-4 rounded-xl font-semibold text-sm text-white bg-blue-600 hover:bg-blue-500 shadow-md shadow-blue-600/20 transition duration-200">
                    <span>Manage Users</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            <!-- Billing & Payments Card -->
            <div class="group relative bg-slate-800/50 border border-slate-700/60 rounded-2xl p-6 hover:border-cyan-500/50 transition-all duration-300 shadow-lg flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 bg-cyan-500/10 text-cyan-400 rounded-xl flex items-center justify-center mb-5 border border-cyan-500/20 group-hover:scale-110 transition-transform duration-300">
                        <i data-lucide="receipt" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Billing & Payments</h3>
                    <p class="text-slate-400 text-sm leading-relaxed mb-6">View active bills, process meter readings, generate invoices, and issue receipts.</p>
                </div>
                <a href="bill.php" class="w-full inline-flex justify-center items-center gap-2 py-3 px-4 rounded-xl font-semibold text-sm text-white bg-cyan-600 hover:bg-cyan-500 shadow-md shadow-cyan-600/20 transition duration-200">
                    <span>Go to Billing</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            <!-- Client Database Card -->
            <div class="group relative bg-slate-800/50 border border-slate-700/60 rounded-2xl p-6 hover:border-emerald-500/50 transition-all duration-300 shadow-lg flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 bg-emerald-500/10 text-emerald-400 rounded-xl flex items-center justify-center mb-5 border border-emerald-500/20 group-hover:scale-110 transition-transform duration-300">
                        <i data-lucide="database" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Client Database</h3>
                    <p class="text-slate-400 text-sm leading-relaxed mb-6">Manage registered water service account holders, connection statuses, and details.</p>
                </div>
                <a href="clients.php" class="w-full inline-flex justify-center items-center gap-2 py-3 px-4 rounded-xl font-semibold text-sm text-white bg-emerald-600 hover:bg-emerald-500 shadow-md shadow-emerald-600/20 transition duration-200">
                    <span>View Clients</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

        </div>
    </main>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>