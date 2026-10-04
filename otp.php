<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'db.php';
include_once 'mailer.php';
include_once 'otp_helper.php';
include_once 'emails/otp_email.php';

// Handle resend action
if (isset($_GET['resend']) && isset($_SESSION['pending_user_id'])) {
    $uid  = intval($_SESSION['pending_user_id']);
    $name = $_SESSION['pending_user_name'] ?? 'User';
    $mail = $_SESSION['pending_user_email'] ?? '';

    if ($uid > 0 && $mail) {
        require_once 'otp_helper.php';
        $rl = otpRateLimit($conn, $uid, 'verify');
        if (!$rl['allowed']) {
            header("Location: otp.php?cooldown=" . intval($rl['wait_seconds']));
            exit();
        }
        $code = createOTP($conn, $uid, 'verify', 10);
        $body = otpEmailBody($name, $code, 'verify');
        sendEmail($mail, 'Verify Your NCWSC Account', $body, $name);
        header("Location: otp.php?resent=1");
        exit();
    }
}
// Must have pending user in session
if (empty($_SESSION['pending_user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
if (isset($_GET['err'])) {
    $error = $_GET['err'] === 'code'
        ? 'Invalid or expired code. Please try again.'
        : ($_GET['err'] === 'empty' ? 'Please enter the code.' : 'Something went wrong.');
}

include 'header.php';
?>

<div class="flex items-center justify-center min-h-[70vh] px-4 py-10">
    <div class="w-full max-w-lg bg-white rounded-lg shadow-lg p-8 border border-gray-100">

        <div class="bg-green-50 border border-green-200 text-green-800 text-sm p-4 rounded-md mb-6">
            Please check your email — we have sent you a One-time Password. It will expire after <strong>10 minutes</strong>.
            If you do not find the email, kindly check the SPAM folder and whitelist the email.
        </div>

        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Verify Your Account</h1>
            <p class="text-sm text-gray-500 mt-2">Enter the OTP sent to your email to activate your account.</p>
        </div>

        <?php if ($error): ?>
            <div class="mb-4 p-3 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['resent'])): ?>
            <div class="mb-4 p-3 rounded-md bg-green-50 border border-green-200 text-green-700 text-sm">
                A new OTP has been sent to your email.
            </div>
        <?php endif; ?>

        <form action="otp_process.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Enter 6-digit OTP:<span class="text-red-500">*</span></label>
                <input type="text" name="otp" required placeholder="123456" maxlength="6" pattern="\d{6}"
                       autocomplete="one-time-code" inputmode="numeric"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none text-center tracking-widest text-lg font-semibold">
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button type="submit" class="btn-green text-white font-semibold py-2 px-6 rounded-md flex-1 transition">
                    Confirm OTP
                </button>
                <a href="otp.php?resend=1" class="btn-blue text-white font-semibold py-2 px-6 rounded-md flex-1 text-center transition">
                    Resend OTP
                </a>
            </div>

            <div class="text-center pt-2 text-sm text-gray-500">
                Wrong email? <a href="register.php" class="text-ncwsc-blue hover:underline">Start over</a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>