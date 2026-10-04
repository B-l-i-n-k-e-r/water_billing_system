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
    $email      = trim($_POST['email'] ?? '');
    $mobile     = trim($_POST['mobile'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($mobile) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!preg_match('/^(07\d{8}|01\d{8})$/', $mobile)) {
        $error = 'Mobile must be a valid Kenyan number (e.g. 0712345678 or 0112345678).';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check duplicate (username or email)
        $check = mysqli_prepare($conn, "SELECT id FROM user WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($check, "ss", $email, $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = 'An account with that email already exists.';
        } else {
            mysqli_stmt_close($check);

            $hashed    = password_hash($password, PASSWORD_DEFAULT);
            $level     = 3; // default: staff/client
            $full_name = trim($first_name . ' ' . $last_name);

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO user (name, email, username, password, userlevel) VALUES (?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, "ssssi", $full_name, $email, $email, $hashed, $level);

            if (mysqli_stmt_execute($stmt)) {
                $success = 'Account created successfully! Please log in.';
            } else {
                $error = 'Registration failed: ' . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
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
                <a href="index.php" class="text-ncwsc-blue underline ml-1">Go to Login</a>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" class="space-y-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">First Name:<span class="text-red-500">*</span></label>
                <input type="text" name="first_name" required placeholder="Enter First Name"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name:<span class="text-red-500">*</span></label>
                <input type="text" name="last_name" required placeholder="Enter Last Name"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address:<span class="text-red-500">*</span></label>
                <input type="email" name="email" required placeholder="Enter Email Address"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Mobile Number: - (e.g. 0712345678 or 0112345678)<span class="text-red-500">*</span>
                </label>
                <input type="text" name="mobile" required placeholder="0712345678 or 0112345678"
                       pattern="07[0-9]{8}|01[0-9]{8}" maxlength="10"
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