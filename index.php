<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['id'])) {
    header("Location: dashboard.php");
    exit();
}
include 'header.php';
?>

<div class="flex items-center justify-center min-h-[70vh] px-4">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8 border border-gray-100">
        
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">NCWSC Online Services</h1>
            <p class="text-sm text-gray-500 mt-2">Please fill in your credentials to login.</p>
        </div>

        <?php if (isset($_GET['err'])): ?>
            <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                Invalid email or password. Please try again.
            </div>
        <?php endif; ?>

        <form action="process.php" method="post" class="space-y-5">
           <!-- Email / Username -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Email or Username:<span class="text-red-500">*</span></label>
    <input type="text" name="username" required placeholder="Enter Email or Username"
           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue focus:border-transparent outline-none transition">
</div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password:<span class="text-red-500">*</span></label>
                <input type="password" name="password" required placeholder="Enter Password"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue focus:border-transparent outline-none transition">
            </div>

            <!-- Forgot Password / Have OTP -->
            <div class="flex flex-col gap-1 text-sm">
                <a href="forgot_password.php" class="text-ncwsc-blue hover:underline">Forgot Password?</a>
                <a href="otp.php" class="text-ncwsc-blue hover:underline">Have OTP?</a>
            </div>

            <!-- Buttons -->
            <div class="flex gap-4 pt-2">
                <button type="submit" class="w-1/2 btn-green text-white font-semibold py-2 rounded-md transition">Login</button>
                <a href="register.php" class="w-1/2 btn-blue text-white font-semibold py-2 rounded-md text-center transition">Register</a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>