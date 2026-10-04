<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
include_once 'db.php';
include 'header.php';

$user_id = intval($_SESSION['id']);

// Fetch this user's sewer requests
$stmt = mysqli_prepare($conn,
    "SELECT id, request_number, account_number, status, created_at
     FROM sewer_requests WHERE user_id = ? ORDER BY created_at DESC"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$requests = [];
while ($row = mysqli_fetch_assoc($res)) $requests[] = $row;
mysqli_stmt_close($stmt);

$statusColors = [
    'pending'   => 'bg-yellow-100 text-yellow-800',
    'in_review' => 'bg-blue-100 text-blue-800',
    'approved'  => 'bg-green-100 text-green-800',
    'rejected'  => 'bg-red-100 text-red-800',
];

$statusLabels = [
    'pending'   => 'Pending Review',
    'in_review' => 'In Review',
    'approved'  => 'Approved',
    'rejected'  => 'Rejected',
];
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">My Sewer Connection Requests</h1>
            <p class="text-sm text-gray-500 mt-1">Track the status of your submitted requests</p>
        </div>
        <a href="addsewer_form.php" class="btn-blue text-white font-semibold py-2 px-6 rounded-md inline-flex items-center gap-2 w-max">
            <i data-lucide="plus" class="w-4 h-4"></i> New Request
        </a>
    </div>

    <?php if (empty($requests)): ?>
        <div class="bg-blue-50 border border-blue-200 text-blue-800 text-sm p-6 rounded-md flex items-start gap-3">
            <i data-lucide="info" class="w-5 h-5 mt-0.5 flex-shrink-0"></i>
            <div>
                <p class="font-semibold mb-1">You have not submitted any sewer requests yet.</p>
                <p>
                    Submit one from the
                    <a href="addsewer_form.php" class="font-semibold underline">Add Sewer</a>
                    page. Once submitted, your request will appear here with its status.
                </p>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-md border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-600 text-white">
                        <tr>
                            <th class="p-3">REQUEST #</th>
                            <th class="p-3">ACCOUNT</th>
                            <th class="p-3">SUBMITTED</th>
                            <th class="p-3">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $r): 
                            $cls = $statusColors[$r['status']] ?? 'bg-gray-100 text-gray-800';
                            $lbl = $statusLabels[$r['status']] ?? ucfirst($r['status']);
                        ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="p-3 font-semibold text-gray-800">
                                    <?php echo htmlspecialchars($r['request_number']); ?>
                                </td>
                                <td class="p-3"><?php echo htmlspecialchars($r['account_number']); ?></td>
                                <td class="p-3">
                                    <?php echo htmlspecialchars(date('d M Y, H:i', strtotime($r['created_at']))); ?>
                                </td>
                                <td class="p-3">
                                    <span class="<?php echo $cls; ?> text-xs px-3 py-1 rounded-full font-semibold uppercase">
                                        <?php echo htmlspecialchars($lbl); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <p class="text-xs text-gray-500 mt-4">
            Status updates are made by NCWSC staff. Check this page regularly to see progress on your request.
        </p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>