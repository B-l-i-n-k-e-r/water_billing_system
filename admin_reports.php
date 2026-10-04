<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';
include_once 'tariff.php';
include 'admin_header.php';

/* ------------------------------------------------------------------
   APPLICATIONS BY STATUS
------------------------------------------------------------------ */
$appStats = ['pending'=>0,'in_review'=>0,'approved'=>0,'rejected'=>0,'completed'=>0];
$r = mysqli_query($conn, "SELECT status, COUNT(*) AS c FROM applications GROUP BY status");
while ($row = mysqli_fetch_assoc($r)) {
    if (isset($appStats[$row['status']])) $appStats[$row['status']] = intval($row['c']);
}
$appTotal = array_sum($appStats);

/* ------------------------------------------------------------------
   SEWER REQUESTS BY STATUS
------------------------------------------------------------------ */
$sewerStats = ['pending'=>0,'in_review'=>0,'approved'=>0,'rejected'=>0];
$r = mysqli_query($conn, "SELECT status, COUNT(*) AS c FROM sewer_requests GROUP BY status");
while ($row = mysqli_fetch_assoc($r)) {
    if (isset($sewerStats[$row['status']])) $sewerStats[$row['status']] = intval($row['c']);
}
$sewerTotal = array_sum($sewerStats);

/* ------------------------------------------------------------------
   EXHAUSTER PERMITS BY STATUS
------------------------------------------------------------------ */
$exhStats = ['pending'=>0,'in_review'=>0,'approved'=>0,'rejected'=>0];
$r = mysqli_query($conn, "SELECT status, COUNT(*) AS c FROM exhauster_permits GROUP BY status");
while ($row = mysqli_fetch_assoc($r)) {
    if (isset($exhStats[$row['status']])) $exhStats[$row['status']] = intval($row['c']);
}
$exhTotal = array_sum($exhStats);

/* ------------------------------------------------------------------
   REVENUE & OUTSTANDING
------------------------------------------------------------------ */
$today = date('Y-m-d');
$monthStart = date('Y-m-01');

// All-time revenue = sum of positive payments (billings) minus credits? 
// We count only actual payments received (negative amounts in transactions)
$revenueAllTime = 0;
$revenueMonth   = 0;
$revenueToday   = 0;

$r = mysqli_query($conn, "SELECT COALESCE(SUM(ABS(amount)),0) AS total FROM transactions WHERE amount < 0");
if ($row = mysqli_fetch_assoc($r)) $revenueAllTime = (float) $row['total'];

$stmt = mysqli_prepare($conn, "SELECT COALESCE(SUM(ABS(amount)),0) AS total FROM transactions WHERE amount < 0 AND transaction_date >= ?");
mysqli_stmt_bind_param($stmt, "s", $monthStart);
mysqli_stmt_execute($stmt);
$r = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($r)) $revenueMonth = (float) $row['total'];
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, "SELECT COALESCE(SUM(ABS(amount)),0) AS total FROM transactions WHERE amount < 0 AND transaction_date = ?");
mysqli_stmt_bind_param($stmt, "s", $today);
mysqli_stmt_execute($stmt);
$r = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($r)) $revenueToday = (float) $row['total'];
mysqli_stmt_close($stmt);

// Total billed and outstanding
$totalBilled = 0;
$totalOutstanding = 0;
$r = mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) AS billed, COALESCE(SUM(amount - amount_paid),0) AS outstanding FROM bill");
if ($row = mysqli_fetch_assoc($r)) {
    $totalBilled = (float) $row['billed'];
    $totalOutstanding = (float) $row['outstanding'];
}

// Count unpaid / partial bills
$unpaidBills = 0;
$r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM bill WHERE status != 'paid'");
if ($row = mysqli_fetch_assoc($r)) $unpaidBills = intval($row['c']);

// Customer counts
$customerCount = 0;
$r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM owners");
if ($row = mysqli_fetch_assoc($r)) $customerCount = intval($row['c']);

/* ------------------------------------------------------------------
   TOP 5 CUSTOMERS BY OUTSTANDING
------------------------------------------------------------------ */
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

/* ------------------------------------------------------------------
   RECENT ACTIVITY
------------------------------------------------------------------ */
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
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Reports &amp; Analytics</h1>
            <p class="text-sm text-gray-500 mt-1">Overview of system activity — generated <?php echo date('d M Y, H:i'); ?></p>
        </div>
        <button onclick="window.print()" class="border border-ncwsc-blue text-ncwsc-blue font-semibold py-2 px-4 rounded-md hover:bg-blue-50">
            Print Report
        </button>
    </div>

    <!-- Revenue highlights -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase">Revenue (All Time)</p>
            <p class="text-2xl font-bold text-green-700 mt-1"><?php echo kes($revenueAllTime); ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase">Revenue (This Month)</p>
            <p class="text-2xl font-bold text-green-700 mt-1"><?php echo kes($revenueMonth); ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase">Revenue (Today)</p>
            <p class="text-2xl font-bold text-green-700 mt-1"><?php echo kes($revenueToday); ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase">Outstanding</p>
            <p class="text-2xl font-bold text-red-600 mt-1"><?php echo kes($totalOutstanding); ?></p>
        </div>
    </div>

    <!-- Key counters -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="p-3 bg-blue-50 rounded-lg text-ncwsc-blue"><i data-lucide="users" class="w-6 h-6"></i></div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Customers</p>
                <p class="text-xl font-bold text-gray-800"><?php echo $customerCount; ?></p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="p-3 bg-yellow-50 rounded-lg text-yellow-600"><i data-lucide="receipt" class="w-6 h-6"></i></div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Unpaid Bills</p>
                <p class="text-xl font-bold text-gray-800"><?php echo $unpaidBills; ?></p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="p-3 bg-indigo-50 rounded-lg text-indigo-600"><i data-lucide="file-text" class="w-6 h-6"></i></div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Applications</p>
                <p class="text-xl font-bold text-gray-800"><?php echo $appTotal; ?></p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="p-3 bg-teal-50 rounded-lg text-teal-600"><i data-lucide="banknote" class="w-6 h-6"></i></div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Total Billed</p>
                <p class="text-xl font-bold text-gray-800"><?php echo number_format($totalBilled, 0); ?></p>
            </div>
        </div>
    </div>

    <!-- Status breakdowns -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6">
            <h2 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i data-lucide="file-text" class="w-5 h-5 text-ncwsc-blue"></i> Applications
                <span class="text-xs font-normal text-gray-500">(<?php echo $appTotal; ?> total)</span>
            </h2>
            <ul class="space-y-2 text-sm">
                <?php foreach ($appStats as $k => $v): ?>
                    <li class="flex items-center justify-between">
                        <span class="text-gray-600 capitalize"><?php echo str_replace('_',' ',$k); ?></span>
                        <span class="font-semibold text-gray-800"><?php echo $v; ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6">
            <h2 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i data-lucide="waves" class="w-5 h-5 text-cyan-600"></i> Sewer Requests
                <span class="text-xs font-normal text-gray-500">(<?php echo $sewerTotal; ?> total)</span>
            </h2>
            <ul class="space-y-2 text-sm">
                <?php foreach ($sewerStats as $k => $v): ?>
                    <li class="flex items-center justify-between">
                        <span class="text-gray-600 capitalize"><?php echo str_replace('_',' ',$k); ?></span>
                        <span class="font-semibold text-gray-800"><?php echo $v; ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6">
            <h2 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i data-lucide="truck" class="w-5 h-5 text-purple-600"></i> Exhauster Permits
                <span class="text-xs font-normal text-gray-500">(<?php echo $exhTotal; ?> total)</span>
            </h2>
            <ul class="space-y-2 text-sm">
                <?php foreach ($exhStats as $k => $v): ?>
                    <li class="flex items-center justify-between">
                        <span class="text-gray-600 capitalize"><?php echo str_replace('_',' ',$k); ?></span>
                        <span class="font-semibold text-gray-800"><?php echo $v; ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

    </div>

    <!-- Top customers + recent activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Top 5 outstanding -->
        <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6">
            <h2 class="font-bold text-gray-800 mb-4">Top 5 Customers by Outstanding Balance</h2>
            <?php if (empty($topCustomers)): ?>
                <p class="text-sm text-gray-500">No outstanding balances.</p>
            <?php else: ?>
                <ul class="divide-y divide-gray-100">
                    <?php foreach ($topCustomers as $c): ?>
                        <li class="py-3 flex items-center justify-between text-sm">
                            <div>
                                <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($c['fname'] . ' ' . $c['lname']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($c['contact']); ?></p>
                            </div>
                            <span class="font-bold text-red-600"><?php echo kes($c['outstanding']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- Recent activity -->
        <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6">
            <h2 class="font-bold text-gray-800 mb-4">Recent Activity</h2>

            <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">Latest Bills</h3>
            <?php if (empty($recentBills)): ?>
                <p class="text-sm text-gray-500 mb-4">No bills yet.</p>
            <?php else: ?>
                <ul class="text-sm space-y-1 mb-6">
                    <?php foreach ($recentBills as $b): ?>
                        <li class="flex items-center justify-between">
                            <span class="text-gray-600">
                                #<?php echo intval($b['id']); ?> —
                                <?php echo htmlspecialchars(trim(($b['fname'] ?? '') . ' ' . ($b['lname'] ?? ''))); ?>
                            </span>
                            <span class="text-gray-800 font-semibold"><?php echo number_format((float)$b['amount'], 0); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">Latest Payments</h3>
            <?php if (empty($recentPayments)): ?>
                <p class="text-sm text-gray-500">No payments yet.</p>
            <?php else: ?>
                <ul class="text-sm space-y-1">
                    <?php foreach ($recentPayments as $p): ?>
                        <li class="flex items-center justify-between">
                            <span class="text-gray-600">
                                <?php echo htmlspecialchars(substr($p['description'], 0, 40)); ?>
                            </span>
                            <span class="text-green-700 font-semibold">
                                <?php echo number_format(abs((float)$p['amount']), 0); ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

    </div>

</div>

<?php include 'footer.php'; ?>