<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]); // Admin and Cashier only
include_once 'db.php';
include 'admin_header.php';

$level = intval($_SESSION['userlevel']);
$isAdmin = ($level === 1);

// Quick stats
$statPendingApps = 0;
$statUnpaidBills = 0;
$statOutstanding = 0;

$r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM applications WHERE status = 'pending'");
if ($r) $statPendingApps = intval(mysqli_fetch_assoc($r)['c']);

$r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM bill WHERE status = 'unpaid'");
if ($r) $statUnpaidBills = intval(mysqli_fetch_assoc($r)['c']);

$r = mysqli_query($conn, "SELECT COALESCE(SUM(amount - amount_paid),0) AS s FROM bill WHERE status != 'paid'");
if ($r) $statOutstanding = floatval(mysqli_fetch_assoc($r)['s']);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Welcome banner -->
    <div class="mb-10 p-6 sm:p-8 rounded-lg bg-ncwsc-blue text-white shadow-md">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/20 mb-3">
            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
            <?php echo $isAdmin ? 'Administrator' : 'Cashier'; ?> Mode
        </span>
        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
            <?php echo $isAdmin ? 'Administrator Control Panel' : 'Cashier Control Panel'; ?>
        </h2>
        <p class="text-blue-100 mt-2 max-w-2xl">
            Welcome back. Use the tools below to manage customers, bills, payments, and applications.
        </p>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Pending Applications</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo $statPendingApps; ?></p>
                </div>
                <div class="p-3 bg-indigo-50 rounded-lg text-indigo-600">
                    <i data-lucide="file-text" class="w-6 h-6"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Unpaid Bills</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo $statUnpaidBills; ?></p>
                </div>
                <div class="p-3 bg-yellow-50 rounded-lg text-yellow-600">
                    <i data-lucide="receipt" class="w-6 h-6"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Outstanding (KES)</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo number_format($statOutstanding, 2); ?></p>
                </div>
                <div class="p-3 bg-red-50 rounded-lg text-red-600">
                    <i data-lucide="trending-up" class="w-6 h-6"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- Billing -->
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100 flex flex-col justify-between hover:shadow-lg transition">
            <div>
                <div class="w-12 h-12 bg-blue-50 text-ncwsc-blue rounded-lg flex items-center justify-center mb-4">
                    <i data-lucide="receipt" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Billing</h3>
                <p class="text-sm text-gray-600 mb-4">Create new bills from meter readings, review issued bills, and track unpaid balances.</p>
            </div>
            <a href="billing.php" class="btn-blue text-white text-center font-semibold py-2 px-4 rounded-md w-full">Go to Billing</a>
        </div>

        <!-- Payments -->
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100 flex flex-col justify-between hover:shadow-lg transition">
            <div>
                <div class="w-12 h-12 bg-green-50 text-green-600 rounded-lg flex items-center justify-center mb-4">
                    <i data-lucide="banknote" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Payments</h3>
                <p class="text-sm text-gray-600 mb-4">Record customer payments, generate receipts, and view payment history.</p>
            </div>
            <a href="viewpayment.php" class="btn-green text-white text-center font-semibold py-2 px-4 rounded-md w-full">View Payments</a>
        </div>

        <!-- Clients -->
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100 flex flex-col justify-between hover:shadow-lg transition">
            <div>
                <div class="w-12 h-12 bg-teal-50 text-teal-600 rounded-lg flex items-center justify-center mb-4">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Clients</h3>
                <p class="text-sm text-gray-600 mb-4">Manage customer records — names, contacts, addresses, and account status.</p>
            </div>
            <a href="clients.php" class="btn-blue text-white text-center font-semibold py-2 px-4 rounded-md w-full">View Clients</a>
        </div>

        <!-- Applications -->
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100 flex flex-col justify-between hover:shadow-lg transition">
            <div>
                <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center mb-4">
                    <i data-lucide="file-check" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Applications</h3>
                <p class="text-sm text-gray-600 mb-4">Review, approve, or reject incoming water and sewer connection applications.</p>
            </div>
            <a href="admin_applications.php" class="btn-blue text-white text-center font-semibold py-2 px-4 rounded-md w-full">Review Applications</a>
        </div>

        <!-- Sewer Requests -->
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100 flex flex-col justify-between hover:shadow-lg transition">
            <div>
                <div class="w-12 h-12 bg-cyan-50 text-cyan-600 rounded-lg flex items-center justify-center mb-4">
                    <i data-lucide="waves" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Sewer Requests</h3>
                <p class="text-sm text-gray-600 mb-4">Process sewer connection requests submitted by existing customers.</p>
            </div>
            <a href="admin_sewer_requests.php" class="btn-blue text-white text-center font-semibold py-2 px-4 rounded-md w-full">Review Sewer Requests</a>
        </div>

        <!-- Reports -->
<div class="bg-white p-6 rounded-lg shadow-md border border-gray-100 flex flex-col justify-between hover:shadow-lg transition">
    <div>
        <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center mb-4">
            <i data-lucide="bar-chart-3" class="w-6 h-6"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-800 mb-2">Reports</h3>
        <p class="text-sm text-gray-600 mb-4">Revenue, application trends, top customers by outstanding balance, and recent activity.</p>
    </div>
    <a href="admin_reports.php" class="btn-blue text-white text-center font-semibold py-2 px-4 rounded-md w-full">View Reports</a>
</div>

        <?php if ($isAdmin): ?>
        <!-- Users (Admin only) -->
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100 flex flex-col justify-between hover:shadow-lg transition">
            <div>
                <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center mb-4">
                    <i data-lucide="user-cog" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Users</h3>
                <p class="text-sm text-gray-600 mb-4">Manage staff accounts — create, edit, or remove system users and privilege levels.</p>
            </div>
            <a href="user.php" class="btn-blue text-white text-center font-semibold py-2 px-4 rounded-md w-full">Manage Users</a>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php include 'footer.php'; ?>