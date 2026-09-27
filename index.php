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

<body class="min-h-screen bg-slate-900 bg-cover bg-center flex items-center justify-center p-4"
      style="background-image: url('img/warer-flow-pipe4.gif');">

    <div class="w-full max-w-md bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl shadow-2xl p-8 text-white">

        <div class="mb-8 text-center">

            <h1 class="text-3xl font-bold tracking-tight text-white">
                Welcome Back
            </h1>

            <p class="text-slate-300 text-sm mt-2">
                Please sign in to your water billing account
            </p>

        </div>


        <?php if (isset($_GET['err'])): ?>

            <div id="login-alert"
                 class="mb-6 p-4 rounded-xl bg-rose-500/20 border border-rose-500/50 text-rose-200 text-sm flex items-center gap-2">

                <svg xmlns="http://www.w3.org/2000/svg"
                     width="20"
                     height="20"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="2"
                     stroke-linecap="round"
                     stroke-linejoin="round">

                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>

                </svg>

                <span>
                    Invalid username or password. Please try again.
                </span>

            </div>

        <?php endif; ?>


        <form action="process.php" method="post" class="space-y-6">

            <!-- Username -->

            <div>

                <label class="block text-sm font-medium text-slate-200 mb-2">
                    Username
                </label>

                <div class="relative">

                    <i data-lucide="user"
                       class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400">
                    </i>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        required
                        autocomplete="username"
                        placeholder="Enter your username"
                        class="w-full pl-11 pr-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-slate-400 outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >

                </div>

            </div>


            <!-- Password -->

            <div>

                <label class="block text-sm font-medium text-slate-200 mb-2">
                    Password
                </label>

                <div class="relative">

                    <i data-lucide="lock"
                       class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400">
                    </i>

                    <input
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Enter your password"
                        class="w-full pl-11 pr-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-slate-400 outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >

                </div>

            </div>


            <!-- Forgot Password -->

            <div class="flex justify-end -mt-2">

                <a
                    id="forgotPasswordLink"
                    href="#"
                    class="text-sm font-medium text-slate-500 cursor-not-allowed pointer-events-none transition duration-200"
                >
                    Forgot Password?
                </a>

            </div>


            <!-- Sign In -->

            <div>

                <button
                    type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 rounded-xl transition duration-200"
                >
                    Sign In
                </button>

            </div>

        </form>

    </div>


    <script>

        lucide.createIcons();


        /*
        |--------------------------------------------------------------------------
        | Enable Forgot Password only when username is entered
        |--------------------------------------------------------------------------
        */

        const usernameInput = document.getElementById('username');
        const forgotPasswordLink = document.getElementById('forgotPasswordLink');


        function updateForgotPasswordLink() {

            const username = usernameInput.value.trim();


            if (username !== '') {

                /*
                | Send the username to forgot_password.php
                */

                forgotPasswordLink.href =
                    'forgot_password.php?username=' +
                    encodeURIComponent(username);


                forgotPasswordLink.classList.remove(
                    'text-slate-500',
                    'cursor-not-allowed',
                    'pointer-events-none'
                );

                forgotPasswordLink.classList.add(
                    'text-indigo-400',
                    'hover:text-indigo-300'
                );

            } else {

                forgotPasswordLink.href = '#';

                forgotPasswordLink.classList.remove(
                    'text-indigo-400',
                    'hover:text-indigo-300'
                );

                forgotPasswordLink.classList.add(
                    'text-slate-500',
                    'cursor-not-allowed',
                    'pointer-events-none'
                );

            }

        }


        usernameInput.addEventListener(
            'input',
            updateForgotPasswordLink
        );


        updateForgotPasswordLink();

    </script>


    <?php if(isset($_GET['err'])): ?>

        <audio autoplay hidden>

            <source
                src="Ding-dong-intercom.mp3"
                type="audio/mpeg"
            >

        </audio>

    <?php endif; ?>


</body>

</html>
