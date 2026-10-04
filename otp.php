<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'header.php';
?>

<div class="flex items-center justify-center min-h-[70vh] px-4 py-10">
    <div class="w-full max-w-lg bg-white rounded-lg shadow-lg p-8 border border-gray-100">

        <div class="bg-green-50 border border-green-200 text-green-800 text-sm p-4 rounded-md mb-6">
            Please check your email we have sent you a One-time Password. The One-time Password will expire after 24 hours. 
            If you do not find the email, kindly check the SPAM folder and whitelist the email.
        </div>

        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">NCWSC Online Services</h1>
            <p class="text-sm text-gray-500 mt-2">Please enter OTP to activate account.</p>
        </div>

        <form action="otp_process.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Check your email for OTP<span class="text-red-500">*</span></label>
                <input type="text" name="otp" required placeholder="Enter OTP" maxlength="6"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none text-center tracking-widest text-lg">
            </div>

            <div class="bg-gray-100 border border-gray-300 rounded-md p-3 flex items-center justify-between">
                <span class="text-xs text-gray-500">Verifying...</span>
                <span class="text-xs font-bold text-gray-600">CLOUDFLARE <span class="font-normal text-gray-400">Privacy • Help</span></span>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button type="submit" class="btn-green text-white font-semibold py-2 px-6 rounded-md flex-1 transition">
                    Confirm OTP
                </button>
                <a href="index.php" class="btn-blue text-white font-semibold py-2 px-6 rounded-md flex-1 text-center transition">
                    Verified account? Login
                </a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>