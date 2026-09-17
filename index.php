<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Optional: Redirect if already logged in
if (isset($_SESSION['id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Water Billing System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="min-h-screen bg-slate-900 bg-cover bg-center flex items-center justify-center p-4" style="background-image: url('img/warer-flow-pipe4.gif');">

    <div class="w-full max-w-md bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl shadow-2xl p-8 text-white">
        
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold tracking-tight text-white">Welcome Back</h1>
            <p class="text-slate-300 text-sm mt-2">Please sign in to your water billing account</p>
        </div>

        <?php if (isset($_GET['err'])): ?>
            <div id="login-alert" class="mb-6 p-4 rounded-xl bg-rose-500/20 border border-rose-500/50 text-rose-200 text-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 text-rose-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>Invalid username or password. Please try again.</span>
            </div>
        <?php endif; ?>

        <form action="process.php" method="post" class="space-y-6">
            
            <div>
                <label for="login-username" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Username</label>
                <div class="relative rounded-xl shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </div>
                    <input 
                        id="login-username" 
                        type="text" 
                        name="username" 
                        placeholder="Enter your username"
                        required
                        autofocus
                        class="block w-full pl-11 pr-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm font-medium"
                    >
                </div>
            </div>

            <div>
                <label for="login-password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Password</label>
                <div class="relative rounded-xl shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="lock" class="w-5 h-5"></i>
                    </div>
                    <input 
                        id="login-password" 
                        type="password" 
                        name="password" 
                        placeholder="••••••••"
                        required
                        class="block w-full pl-11 pr-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm font-medium"
                    >
                </div>
            </div>

            <div>
                <button 
                    type="submit" 
                    class="w-full flex justify-center items-center gap-2 py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200 shadow-indigo-600/30 active:scale-[0.98]"
                >
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Sign In</span>
                </button>
            </div>
        </form>
    </div>

    <script>
        lucide.createIcons();
    </script>

    <?php if(isset($_GET['err'])): ?>
        <audio autoplay hidden>
            <source src="Ding-dong-intercom.mp3" type="audio/mpeg">
        </audio>
    <?php endif; ?>

</body>
</html>