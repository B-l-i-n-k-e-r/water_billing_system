<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
include_once 'db.php';
include 'header.php';

$error = '';
$success = '';

// Success flash from processor
if (isset($_SESSION['sewer_request_success'])) {
    $ref = $_SESSION['sewer_request_success'];
    $success = "Sewer connection request submitted. Your reference number is <strong>" 
             . htmlspecialchars($ref) 
             . "</strong>. You will receive a confirmation shortly.";
    unset($_SESSION['sewer_request_success']);
}

// Error flash
if (isset($_GET['err'])) {
    if ($_GET['err'] === 'empty') {
        $error = 'Please enter your account number.';
    } elseif ($_GET['err'] === 'db') {
        $error = 'Database error. Please try again.';
    }
}
?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-white rounded-lg shadow-md border border-gray-100 p-8">

        <!-- Top buttons -->
        <div class="flex flex-wrap gap-3 mb-6 pb-6 border-b border-gray-200">
            <a href="addsewer_process.php" class="btn-blue text-white text-sm font-semibold py-2 px-4 rounded-md">ADD SEWER</a>
            <a href="#" class="border border-ncwsc-blue text-ncwsc-blue text-sm font-semibold py-2 px-4 rounded-md hover:bg-blue-50">
                DOWNLOAD APPLICATION
            </a>
            <a href="upload_application.php" class="border border-ncwsc-blue text-ncwsc-blue text-sm font-semibold py-2 px-4 rounded-md hover:bg-blue-50">
                UPLOAD APPLICATION
            </a>
        </div>

        <h1 class="text-xl font-bold text-gray-800 mb-2">
            Please read the instructions carefully before you proceed with the application.
        </h1>
        <p class="text-sm font-semibold text-gray-700 mb-4">Sewer Application Instructions.</p>

        <ol class="list-decimal list-inside text-sm text-gray-600 space-y-2 mb-8 leading-relaxed">
            <li>No application for connection shall be allowed unless drawing showing plan, Ground levels and invert levels of the proposed drain have been approved and stamped with the Nairobi Water Company stamp.</li>
            <li>The company shall make all connections to the public systems and no connection shall be made until the remainder of the new drain has been completed within plot Boundary and approved by the Building/Sewerage Inspector. The applicant must ensure that the excavation is completed and the sewer exposed for connection.</li>
            <li>In-case where work is required within the public Road Reserve " permission to Open public Highway " must be obtained by the Drain layer from Government Road Agencies before the commencement of works.</li>
            <li>The sewer line reverts to NCWSC and adjacent plots to connect without reference to you.</li>
            <li>Customers requesting for sewer connection should provide their water account or evidence of water source during the application process.</li>
            <li>For sewers requiring micro tunneling /road opening, concrete/Non-corrosive Metallic materials shall be required whereas for sewers to be connected on road reserves UPVC pipes may be used. For Aerial crossing cases (e.g. across rivers/valleys) Non-corrosive Metallic materials are recommended.</li>
        </ol>

        <?php if ($error): ?>
            <div class="mb-4 p-3 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
    <div class="mb-4 p-3 rounded-md bg-green-50 border border-green-200 text-green-700 text-sm">
        <?php echo $success; ?>
        <a href="my_sewer_requests.php" class="font-semibold underline ml-1">View all my requests &rarr;</a>
    </div>
<?php endif; ?>

        <h2 class="text-center text-lg font-semibold text-gray-800 mb-3">ENTER ACCOUNT NUMBER</h2>

        <form action="addsewer_process.php" method="POST" class="max-w-md mx-auto">
            <input type="text" name="account_number" required placeholder="1234567"
                   pattern="\d{6,10}" maxlength="10"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none mb-4">
            <button type="submit" class="w-full btn-green text-white font-semibold py-2 rounded-md transition">
                ENTER ACCOUNT NUMBER
            </button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>