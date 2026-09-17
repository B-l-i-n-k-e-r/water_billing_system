<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Retrieve user role level safely
$level = isset($_SESSION['userlevel']) ? intval($_SESSION['userlevel']) : 3;

// Helper function to mark active links
$current_page = basename($_SERVER['PHP_SELF']);
function isActive($pageName, $current_page) {
    return $pageName === $current_page 
        ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' 
        : 'text-slate-300 hover:text-white hover:bg-slate-800/80';
}
?>

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
                
                <!-- Home / Dashboard -->
                <a href="dashboard.php" class="px-3.5 py-2 text-xs font-semibold rounded-xl transition flex items-center gap-2 <?php echo isActive('dashboard.php', $current_page); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Home
                </a>

                <!-- All logged-in users (Clients) -->
                <a href="clients.php" class="px-3.5 py-2 text-xs font-semibold rounded-xl transition flex items-center gap-2 <?php echo isActive('clients.php', $current_page); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    Clients
                </a>

                <!-- Cashiers & Admins (Levels 1 & 2) -->
                <?php if (in_array($level, [1, 2])): ?>
                    <a href="billing.php" class="px-3.5 py-2 text-xs font-semibold rounded-xl transition flex items-center gap-2 <?php echo isActive('billing.php', $current_page); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        Billing
                    </a>

                    <a href="viewpayment.php" class="px-3.5 py-2 text-xs font-semibold rounded-xl transition flex items-center gap-2 <?php echo isActive('viewpayment.php', $current_page); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        Payments
                    </a>
                <?php endif; ?>

                <!-- Admins Only (Level 1) -->
                <?php if ($level === 1): ?>
                    <a href="user.php" class="px-3.5 py-2 text-xs font-semibold rounded-xl transition flex items-center gap-2 <?php echo isActive('user.php', $current_page); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                        Manage Users
                    </a>
                <?php endif; ?>

                <!-- Logout Button -->
                <a href="logout.php" class="ml-2 px-3.5 py-2 text-xs font-semibold text-rose-300 hover:text-white bg-rose-500/10 hover:bg-rose-600/80 border border-rose-500/20 rounded-xl transition flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Logout
                </a>
            </div>

            <!-- Mobile Menu Toggle Button -->
            <div class="md:hidden flex items-center">
                <button type="button" 
                        onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" 
                        class="text-slate-400 hover:text-white p-2 rounded-lg bg-slate-800 border border-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Drawer -->
    <div id="mobile-menu" class="hidden md:hidden border-t border-slate-700/60 bg-slate-800/95 px-4 pt-2 pb-4 space-y-2">
        <a href="dashboard.php" class="block px-3 py-2 text-sm font-semibold rounded-xl text-slate-300 hover:text-white hover:bg-slate-700/50">Home</a>
        <a href="clients.php" class="block px-3 py-2 text-sm font-semibold rounded-xl text-slate-300 hover:text-white hover:bg-slate-700/50">Clients</a>
        
        <?php if (in_array($level, [1, 2])): ?>
            <a href="billing.php" class="block px-3 py-2 text-sm font-semibold rounded-xl text-slate-300 hover:text-white hover:bg-slate-700/50">Billing</a>
            <a href="viewpayment.php" class="block px-3 py-2 text-sm font-semibold rounded-xl text-slate-300 hover:text-white hover:bg-slate-700/50">Payments</a>
        <?php endif; ?>

        <?php if ($level === 1): ?>
            <a href="user.php" class="block px-3 py-2 text-sm font-semibold rounded-xl text-slate-300 hover:text-white hover:bg-slate-700/50">Manage Users</a>
        <?php endif; ?>

        <a href="logout.php" class="block px-3 py-2 text-sm font-semibold rounded-xl text-rose-400 hover:bg-rose-500/10">Logout</a>
    </div>
</nav>