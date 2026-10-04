<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';
include_once 'tariff.php';
include 'admin_header.php';

$app_id = intval($_GET['id'] ?? 0);

$stmt = mysqli_prepare($conn,
    "SELECT a.*, u.name AS user_name, u.email AS user_email
     FROM applications a
     LEFT JOIN user u ON u.id = a.user_id
     WHERE a.id = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "i", $app_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$app = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$app) {
    echo '<div class="max-w-3xl mx-auto px-4 py-16 text-center">';
    echo '<h1 class="text-2xl font-bold text-gray-800 mb-4">Application not found</h1>';
    echo '<a href="admin_applications.php" class="text-ncwsc-blue hover:underline">&larr; Back</a>';
    echo '</div>';
    include 'footer.php';
    exit();
}

$flash = $_GET['success'] ?? null;
$statusColors = [
    'pending'   => 'bg-yellow-100 text-yellow-800',
    'in_review' => 'bg-blue-100 text-blue-800',
    'approved'  => 'bg-green-100 text-green-800',
    'rejected'  => 'bg-red-100 text-red-800',
    'completed' => 'bg-emerald-100 text-emerald-800',
];
$cls = $statusColors[$app['status']] ?? 'bg-gray-100 text-gray-800';

function h($v) { return htmlspecialchars($v ?? '—'); }
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Application Review</h1>
            <p class="text-sm text-gray-500 mt-1"><?php echo h($app['application_number']); ?></p>
        </div>
        <a href="admin_applications.php" class="text-sm text-gray-500 hover:text-ncwsc-blue">&larr; Back</a>
    </div>

    <?php if ($flash === 'updated'): ?>
        <div class="mb-4 p-3 rounded-md bg-green-50 border border-green-200 text-green-700 text-sm">
            Application updated.
        </div>
    <?php endif; ?>

    <!-- Status banner -->
    <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Current Status</p>
                <p class="text-xl font-bold text-gray-800 capitalize">
                    <?php echo str_replace('_',' ', $app['status']); ?>
                </p>
            </div>
            <span class="<?php echo $cls; ?> text-xs px-3 py-1 rounded-full font-semibold uppercase">
                <?php echo str_replace('_',' ', $app['status']); ?>
            </span>
        </div>

        <!-- Quick status update form -->
        <form action="admin_application_process.php" method="POST" class="mt-5 pt-5 border-t border-gray-200 flex flex-col sm:flex-row gap-3">
            <input type="hidden" name="id" value="<?php echo intval($app['id']); ?>">
            <select name="status" required
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                <?php foreach (['pending','in_review','approved','rejected','completed'] as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo $app['status']===$s?'selected':''; ?>>
                        <?php echo ucwords(str_replace('_',' ', $s)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-blue text-white font-semibold py-2 px-6 rounded-md">Update Status</button>
        </form>
    </div>

    <!-- Applicant summary -->
    <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Applicant</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div><p class="text-gray-500">Full Name</p><p class="font-semibold text-gray-800"><?php echo h($app['full_name']); ?></p></div>
            <div><p class="text-gray-500">ID Number</p><p class="font-semibold text-gray-800"><?php echo h($app['id_number']); ?></p></div>
            <div><p class="text-gray-500">KRA PIN</p><p class="font-semibold text-gray-800"><?php echo h($app['kra_pin']); ?></p></div>
            <div><p class="text-gray-500">Email</p><p class="font-semibold text-gray-800"><?php echo h($app['email']); ?></p></div>
            <div><p class="text-gray-500">Phone</p><p class="font-semibold text-gray-800"><?php echo h($app['phone']); ?></p></div>
            <div><p class="text-gray-500">Gender</p><p class="font-semibold text-gray-800"><?php echo h($app['gender']); ?></p></div>
        </div>
    </div>

    <!-- Supply details -->
    <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Supply Details</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div><p class="text-gray-500">Application Type</p><p class="font-semibold text-gray-800 capitalize"><?php echo h($app['application_type']); ?></p></div>
            <div><p class="text-gray-500">Service Type</p><p class="font-semibold text-gray-800 capitalize"><?php echo h($app['service_type']); ?></p></div>
            <div><p class="text-gray-500">Premises</p><p class="font-semibold text-gray-800"><?php echo h($app['premises_type']); ?></p></div>
            <div><p class="text-gray-500">Units</p><p class="font-semibold text-gray-800"><?php echo h($app['units']); ?></p></div>
            <div><p class="text-gray-500">Daily Demand (m³)</p><p class="font-semibold text-gray-800"><?php echo h($app['daily_demand']); ?></p></div>
            <div><p class="text-gray-500">Meter Size</p><p class="font-semibold text-gray-800"><?php echo h($app['meter_size']); ?></p></div>
        </div>
    </div>

    <!-- Location -->
    <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Property Location</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div><p class="text-gray-500">County</p><p class="font-semibold text-gray-800"><?php echo h($app['county']); ?></p></div>
            <div><p class="text-gray-500">Estate / Area</p><p class="font-semibold text-gray-800"><?php echo h($app['estate']); ?></p></div>
            <div><p class="text-gray-500">Plot / LR Number</p><p class="font-semibold text-gray-800"><?php echo h($app['plot_number']); ?></p></div>
            <div><p class="text-gray-500">Street / Road</p><p class="font-semibold text-gray-800"><?php echo h($app['street']); ?></p></div>
        </div>
    </div>

    <!-- Documents -->
    <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Uploaded Documents</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
            <?php
            $docs = [
                'doc_kra_pin'         => 'KRA PIN Certificate',
                'doc_nema'            => 'NEMA Certificate',
                'doc_vehicle_logbook' => 'Vehicle Logbook',
                'doc_vehicle_photo'   => 'Motor Vehicle Photo',
            ];
            foreach ($docs as $key => $label):
                $path = $app[$key] ?? null;
            ?>
                <div class="border border-gray-200 rounded-md p-3 flex items-center justify-between">
                    <span class="text-gray-700"><?php echo $label; ?></span>
                    <?php if ($path && file_exists($path)): ?>
                        <a href="<?php echo htmlspecialchars($path); ?>" target="_blank"
                           class="text-ncwsc-blue hover:underline text-xs font-semibold">Open PDF</a>
                    <?php else: ?>
                        <span class="text-gray-400 text-xs">Not uploaded</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Convert to Client (if approved) -->
    <?php if (in_array($app['status'], ['approved', 'completed'])): ?>
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-bold text-green-800 mb-2">Ready to Convert</h2>
            <p class="text-sm text-green-700 mb-4">
                This application is approved. You can create a customer record in <strong>owners</strong> and start billing.
            </p>
            <a href="addclient.php" class="btn-green text-white font-semibold py-2 px-6 rounded-md inline-block">
                Create Customer Record
            </a>
        </div>
    <?php endif; ?>

</div>

<?php include 'footer.php'; ?>