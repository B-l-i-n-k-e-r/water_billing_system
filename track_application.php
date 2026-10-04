<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
include_once 'db.php';
include 'header.php';

$user_id = intval($_SESSION['id']);
$applications = [];

// Try to fetch applications — the table may not exist yet, so guard it
$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'applications'");
if ($tableCheck && mysqli_num_rows($tableCheck) > 0) {
    $stmt = mysqli_prepare($conn,
       "SELECT id, application_number, service_type AS type, status, created_at 
 FROM applications WHERE user_id = ? ORDER BY created_at DESC"    );
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($res)) {
            $applications[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}

$count = count($applications);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
        <h1 class="text-2xl font-bold text-gray-800">
            Check Your Water/Sewer Application Status
        </h1>
        <span class="bg-gray-500 text-white text-sm px-3 py-1 rounded-md w-max">
            <?php echo $count; ?> applications
        </span>
    </div>

    <?php if ($count === 0): ?>
        <div class="bg-blue-50 border border-blue-200 text-blue-800 text-sm p-4 rounded-md flex items-start gap-3">
            <i data-lucide="info" class="w-5 h-5 mt-0.5 flex-shrink-0"></i>
            <div>
                You have no applications to track.
                <a href="apply.php" class="font-semibold underline">Apply for a new connection</a>.
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-md border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-600 text-white">
                        <tr>
                            <th class="p-3">APP NUMBER</th>
                            <th class="p-3">TYPE</th>
                            <th class="p-3">STATUS</th>
                            <th class="p-3">DATE SUBMITTED</th>
                            <th class="p-3">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                            <tr class="border-b border-gray-200">
                                <td class="p-3 font-semibold text-gray-800">
                                    <?php echo htmlspecialchars($app['application_number']); ?>
                                </td>
                                <td class="p-3"><?php echo htmlspecialchars($app['type']); ?></td>
                                <td class="p-3">
                                    <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">
                                        <?php echo htmlspecialchars($app['status']); ?>
                                    </span>
                                </td>
                                <td class="p-3"><?php echo htmlspecialchars($app['created_at']); ?></td>
                                <td class="p-3">
                                    <a href="view_application.php?id=<?php echo intval($app['id']); ?>"
                                       class="text-ncwsc-blue hover:underline">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php include 'footer.php'; ?>