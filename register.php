<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $username   = trim($_POST['username'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $mobile     = trim($_POST['mobile'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($mobile) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $error = 'Username must be 3–30 characters (letters, numbers, underscore only).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!preg_match('/^(07\d{8}|01\d{8})$/', $mobile)) {
        $error = 'Mobile must be a valid Kenyan number (e.g. 0712345678 or 0112345678).';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Duplicate check on username OR email
        $check = mysqli_prepare($conn, "SELECT id FROM user WHERE username = ? OR email = ? LIMIT 1");
        mysqli_stmt_bind_param($check, "ss", $username, $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = 'An account with that username or email already exists.';
        } else {
            mysqli_stmt_close($check);

            $hashed    = password_hash($password, PASSWORD_DEFAULT);
            $level     = 3; // default: client
            $full_name = trim($first_name . ' ' . $last_name);

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO user (name, email, username, mobile, password, userlevel, verified) 
                 VALUES (?, ?, ?, ?, ?, ?, 0)"
            );
            mysqli_stmt_bind_param($stmt, "sssssi", $full_name, $email, $username, $mobile, $hashed, $level);

            if (mysqli_stmt_execute($stmt)) {
                $new_user_id = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt);

                // Generate OTP and send email
                require_once 'mailer.php';
                require_once 'otp_helper.php';
                require_once 'emails/otp_email.php';

            $rl = otpRateLimit($conn, $new_user_id, 'verify');
if (!$rl['allowed']) {
    // Too many OTPs — silently proceed but don't send another
    error_log("OTP rate limited for user {$new_user_id}");
    session_regenerate_id(true);
    $_SESSION['pending_user_id']    = $new_user_id;
    $_SESSION['pending_user_email'] = $email;
    $_SESSION['pending_user_name']  = $full_name;
    header("Location: otp.php");
    exit();
}   
                $code = createOTP($conn, $new_user_id, 'verify', 10);
                $body = otpEmailBody($full_name, $code, 'verify');
                $mail = sendEmail($email, 'Verify Your NCWSC Account', $body, $full_name);

                if (!$mail['ok']) {
                    error_log("OTP email failed for {$email}: " . ($mail['error'] ?? 'unknown'));
                }

                session_regenerate_id(true);
                $_SESSION['pending_user_id']    = $new_user_id;
                $_SESSION['pending_user_email'] = $email;
                $_SESSION['pending_user_name']  = $full_name;

                header("Location: otp.php");
                exit();
            } else {
                $error = 'Registration failed: ' . mysqli_error($conn);
                mysqli_stmt_close($stmt);
            }
        }
    }
}

include 'header.php';
?>

<div class="flex items-center justify-center min-h-[70vh] px-4 py-10">
    <div class="w-full max-w-lg bg-white rounded-lg shadow-lg p-8 border border-gray-100">

        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Create An Account</h1>
            <p class="text-sm text-gray-500 mt-2">Please fill this form to register with us</p>
        </div>

        <?php if ($error): ?>
            <div class="mb-4 p-3 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="mb-4 p-3 rounded-md bg-green-50 border border-green-200 text-green-700 text-sm">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" class="space-y-4">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name:<span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" required placeholder="Enter First Name"
                           value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name:<span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" required placeholder="Enter Last Name"
                           value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username:<span class="text-red-500">*</span></label>
                <input type="text" name="username" required placeholder="e.g. johndoe"
                       pattern="[A-Za-z0-9_]{3,30}" maxlength="30"
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                <p class="text-xs text-gray-500 mt-1">3–30 characters. Letters, numbers, underscore only.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address:<span class="text-red-500">*</span></label>
                <input type="email" name="email" required placeholder="Enter Email Address"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Mobile Number: - (e.g. 0712345678 or 0112345678)<span class="text-red-500">*</span>
                </label>
                <input type="text" name="mobile" required placeholder="0712345678 or 0112345678"
                       pattern="07[0-9]{8}|01[0-9]{8}" maxlength="10"
                       value="<?php echo htmlspecialchars($_POST['mobile'] ?? ''); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password:<span class="text-red-500">*</span></label>
                <input type="password" name="password" required placeholder="Enter Password"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password:<span class="text-red-500">*</span></label>
                <input type="password" name="confirm_password" required placeholder="Confirm Password"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
            </div>

            <div class="flex items-center justify-between pt-2">
                <button type="submit" class="btn-green text-white font-semibold py-2 px-6 rounded-md transition">
                    Register
                </button>
                <a href="index.php" class="text-sm text-gray-600 hover:text-ncwsc-blue">Login</a>
            </div>

        </form>
    </div>
</div>

<?php include 'footer.php'; ?>