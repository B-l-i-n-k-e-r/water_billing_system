<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['id']);
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NCWSC Online Services</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .bg-ncwsc-blue { background-color: #0066CC; }
        .text-ncwsc-blue { color: #0066CC; }
        .border-ncwsc-blue { border-color: #0066CC; }
        .btn-green { background-color: #28a745; }
        .btn-green:hover { background-color: #218838; }
        .btn-blue { background-color: #0066CC; }
        .btn-blue:hover { background-color: #0056b3; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

<!-- Top Header Bar -->
<header class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-3">
                <img src="img/ncwsc-logo.png" alt="NCWSC Logo" class="h-10 w-auto" onerror="this.style.display='none'">
                <a href="dashboard.php" class="text-xl font-bold text-gray-800">NCWSC Online Services</a>
            </div>
            <div class="flex items-center gap-4">
                <a href="dashboard.php" class="text-sm font-medium text-gray-600 hover:text-ncwsc-blue">HOME</a>
                <?php if ($isLoggedIn): ?>
                    <a href="logout.php" class="text-sm font-medium text-gray-600 hover:text-ncwsc-blue">LOGOUT</a>
                <?php else: ?>
                    <a href="index.php" class="text-sm font-medium text-gray-600 hover:text-ncwsc-blue">LOGIN</a>
                <?php endif; ?>
                <a href="blinker09.co.ke" target="_blank" class="text-sm font-medium text-gray-600 hover:text-ncwsc-blue">MAIN WEBSITE</a>
            </div>
        </div>
    </div>
</header>

<!-- Portal Tabs (only when logged in) -->
<?php if ($isLoggedIn): ?>
<nav class="bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex space-x-8">
            <a href="apply.php" class="py-4 px-1 border-b-2 <?php echo $current_page == 'apply.php' ? 'border-ncwsc-blue text-ncwsc-blue' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> text-sm font-medium">
                APPLY WATER OR SEWER
            </a>
            <a href="upload_application.php" class="py-4 px-1 border-b-2 <?php echo $current_page == 'upload_application.php' ? 'border-ncwsc-blue text-ncwsc-blue' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> text-sm font-medium">
                UPLOAD APPLICATION
            </a>
            <a href="track_application.php" class="py-4 px-1 border-b-2 <?php echo $current_page == 'track_application.php' ? 'border-ncwsc-blue text-ncwsc-blue' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> text-sm font-medium">
                TRACK APPLICATION
            </a>
        </div>
    </div>
</nav>
<?php endif; ?>

<main class="flex-grow">