<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';

// Staff → redirect to staff portal
$level = intval($_SESSION['userlevel'] ?? 3);
if (in_array($level, [1, 2])) {
    header("Location: admin_dashboard.php");
    exit();
}

include 'header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-2xl font-bold text-gray-800 mb-8 text-center">NCWSC Online Services</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100 flex flex-col justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-800 mb-2">SELF CARE</h2>
                <p class="text-sm text-gray-600 mb-4">This is for persons/entity who want to check their bills or get Customer Statement.</p>
            </div>
            <a href="selfcare.php" class="btn-blue text-white text-center font-semibold py-2 px-4 rounded-md w-max">Click Here</a>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100 flex flex-col justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-800 mb-2">WATER/SEWER APPLICATION</h2>
                <p class="text-sm text-gray-600 mb-4">This is for persons/entity who want to be contracted as new NCWSC customers.</p>
            </div>
            <a href="apply.php" class="btn-blue text-white text-center font-semibold py-2 px-4 rounded-md w-max">Click Here</a>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100 flex flex-col justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-800 mb-2">OTHER SERVICES</h2>
                <p class="text-sm text-gray-600 mb-4">Access Other Services like: Add Sewer.</p>
            </div>
            <a href="addsewer.php" class="btn-blue text-white text-center font-semibold py-2 px-4 rounded-md w-max">Click Here</a>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100 flex flex-col justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-800 mb-2">PRIVATE EXHAUSTER PERMIT</h2>
                <p class="text-sm text-gray-600 mb-4">Facilitates applications and approvals for private exhauster services within the company's jurisdiction.</p>
            </div>
            <a href="exhauster.php" class="btn-blue text-white text-center font-semibold py-2 px-4 rounded-md w-max">Click Here</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>