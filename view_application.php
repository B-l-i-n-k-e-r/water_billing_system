<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
include_once 'db.php';
include 'header.php';

$user_id = intval($_SESSION['id']);
$app_id  = intval($_GET['id'] ?? 0);

// Fetch the application — must belong to this user
$stmt = mysqli_prepare($conn,
    "SELECT * FROM applications WHERE id = ? AND user_id = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "ii", $app_id, $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$app = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$app) {
    echo '<div class="max-w-3xl mx-auto px-4 py-16 text-center">';
    echo '<h1 class="text-2xl font-bold text-gray-800 mb-4">Application not found</h1>';
    echo '<a href="track_application.php" class="text-ncwsc-blue hover:underline">&larr; Back to applications</a>';
    echo '</div>';
    include 'footer.php';
    exit();
}

// Status badge colors
$statusColors = [
    'pending'    => 'bg-yellow-100 text-yellow-800',
    'in_review'  => 'bg-blue-100 text-blue-800',
    'approved'   => 'bg-green-100 text-green-800',
    'rejected'   => 'bg-red-100 text-red-800',
    'completed'  => 'bg-emerald-100 text-emerald-800',
];
$statusClass = $statusColors[$app['status']] ?? 'bg-gray-100 text-gray-800';

// Format dates safely
$created = $app['created_at'] ? date('d M Y, H:i', strtotime($app['created_at'])) : '—';
$dob     = $app['date_of_birth'] ? date('d M Y', strtotime($app['date_of_birth'])) : '—';

function h($v) { return htmlspecialchars($v ?? '—'); }
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Application Details</h1>
            <p class="text-sm text-gray-500 mt-1">Reference: <span class="font-semibold text-ncwsc-blue"><?php echo h($app['application_number']); ?></span></p>
        </div>
        <div class="flex items-center gap-3">
            <span class="<?php echo $statusClass; ?> text-xs px-3 py-1 rounded-full font-semibold uppercase">
                <?php echo h(str_replace('_', ' ', $app['status'])); ?>
            </span>
            <a href="track_application.php" class="text-sm text-gray-500 hover:text-ncwsc-blue">&larr; Back</a>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Summary</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Application Number</p>
                <p class="font-semibold text-gray-800"><?php echo h($app['application_number']); ?></p>
            </div>
            <div>
                <p class="text-gray-500">Date Submitted</p>
                <p class="font-semibold text-gray-800"><?php echo h($created); ?></p>
            </div>
            <div>
                <p class="text-gray-500">Application Type</p>
                <p class="font-semibold text-gray-800 capitalize"><?php echo h($app['application_type']); ?></p>
            </div>
            <div>
                <p class="text-gray-500">Service Type</p>
                <p class="font-semibold text-gray-800 capitalize"><?php echo h($app['service_type']); ?></p>
            </div>
        </div>
    </div>

    <!-- Identification -->
    <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Identification</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-gray-500">ID Number</p>
                <p class="font-semibold text-gray-800"><?php echo h($app['id_number']); ?></p>
            </div>
            <div>
                <p class="text-gray-500">ID Type</p>
                <p class="font-semibold text-gray-800"><?php echo h($app['id_type']); ?></p>
            </div>
            <div>
                <p class="text-gray-500">KRA PIN</p>
                <p class="font-semibold text-gray-800"><?php echo h($app['kra_pin']); ?></p>
            </div>
        </div>
    </div>

    <!-- Applicant Details -->
    <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Applicant Details</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Full Name</p>
                <p class="font-semibold text-gray-800"><?php echo h($app['full_name']); ?></p>
            </div>
            <div>
                <p class="text-gray-500">Date of Birth</p>
                <p class="font-semibold text-gray-800"><?php echo h($dob); ?></p>
            </div>
            <div>
                <p class="text-gray-500">Gender</p>
                <p class="font-semibold text-gray-800"><?php echo h($app['gender']); ?></p>
            </div>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Contact Information</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Email</p>
                <p class="font-semibold text-gray-800 break-all"><?php echo h($app['email']); ?></p>
            </div>
            <div>
                <p class="text-gray-500">Phone</p>
                <p class="font-semibold text-gray-800"><?php echo h($app['phone']); ?></p>
            </div>
            <div>
                <p class="text-gray-500">Postal Address</p>
                <p class="font-semibold text-gray-800"><?php echo h($app['postal_address']); ?></p>
            </div>
        </div>
    </div>

    <!-- Alternate Contact (only show if any value present) -->
    <?php if (!empty($app['alt_name']) || !empty($app['alt_phone']) || !empty($app['alt_relationship'])): ?>
    <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Alternate Contact</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Name</p>
                <p class="font-semibold text-gray-800"><?php echo h($app['alt_name']); ?></p>
            </div>
            <div>
                <p class="text-gray-500">Phone</p>
                <p class="font-semibold text-gray-800"><?php echo h($app['alt_phone']); ?></p>
            </div>
            <div>
                <p class="text-gray-500">Relationship</p>
                <p class="font-semibold text-gray-800"><?php echo h($app['alt_relationship']); ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Supply Details -->
    <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Supply Details</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Premises Type</p>
                <p class="font-semibold text-gray-800"><?php echo h($app['premises_type']); ?></p>
            </div>
            <div>
                <p class="text-gray-500">Number of Units</p>
                <p class="font-semibold text-gray-800"><?php echo h($app['units']); ?></p>
            </div>
            <div>
                <p class="text-gray-500">Daily Demand (m³)</p>
                <p class="font-semibold text-gray-800"><?php echo h($app['daily_demand']); ?></p>
            </div>
            <div>
                <p class="text-gray-500">Meter Size</p>
                <p class="font-semibold text-gray-800"><?php echo h($app['meter_size']); ?></p>
            </div>
            <div>
                <p class="text-gray-500">Supply Category</p>
                <p class="font-semibold text-gray-800"><?php echo h($app['supply_category']); ?></p>
            </div>
        </div>
    </div>

    <!-- Property Location -->
    <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Property Location</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500">County</p>
                <p class="font-semibold text-gray-800"><?php echo h($app['county']); ?></p>
            </div>
            <div>
                <p class="text-gray-500">Estate / Area</p>
                <p class="font-semibold text-gray-800"><?php echo h($app['estate']); ?></p>
            </div>
            <div>
                <p class="text-gray-500">Plot / LR Number</p>
                <p class="font-semibold text-gray-800"><?php echo h($app['plot_number']); ?></p>
            </div>
            <div>
                <p class="text-gray-500">Street / Road</p>
                <p class="font-semibold text-gray-800"><?php echo h($app['street']); ?></p>
            </div>
        </div>
    </div>

    <!-- Documents -->
    <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Uploaded Documents</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">

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
                    <span class="text-gray-700"><?php echo htmlspecialchars($label); ?></span>
                    <?php if ($path && file_exists($path)): ?>
                        <a href="<?php echo htmlspecialchars($path); ?>" target="_blank"
                           class="text-ncwsc-blue hover:underline text-xs font-semibold">
                           View PDF
                        </a>
                    <?php else: ?>
                        <span class="text-gray-400 text-xs">Not uploaded</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

        </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-3">
        <a href="track_application.php" class="text-gray-600 hover:text-ncwsc-blue font-semibold py-2 px-4">
            Back to My Applications
        </a>
        <a href="upload_application.php?app=<?php echo urlencode($app['application_number']); ?>"
           class="btn-blue text-white font-semibold py-2 px-6 rounded-md">
            Upload More Documents
        </a>
    </div>

</div>

<?php include 'footer.php'; ?>