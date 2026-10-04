<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
include 'header.php';
?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-white rounded-lg shadow-md border border-gray-100 p-8">
        <h1 class="text-xl font-bold text-gray-800 mb-3">ADD SEWER CONNECTION</h1>
        <p class="text-sm text-gray-600 mb-6">
            Addition of sewer connection to an existing <strong>water only</strong> account installation.
        </p>
        <a href="addsewer_form.php"
           class="btn-blue text-white font-semibold py-2 px-6 rounded-md inline-block">
            Click Here
        </a>
    </div>
</div>

<?php include 'footer.php'; ?>