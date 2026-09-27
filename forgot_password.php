<?php

session_start();

include_once 'db.php';


$message = "";
$messageType = "";

$emailHint = "";

$showPasswordForm = false;

$verifiedUserId = null;


/*
|--------------------------------------------------------------------------
| Get username from login page
|--------------------------------------------------------------------------
*/

$username = trim($_GET['username'] ?? $_POST['username'] ?? '');


/*
|--------------------------------------------------------------------------
| STEP 1: Verify Username + Email
|--------------------------------------------------------------------------
*/

if (isset($_POST['verify_email'])) {

    $username = trim($_POST['username'] ?? '');

    $email = trim($_POST['email'] ?? '');


    if (empty($username)) {

        $message = "Username is required.";
        $messageType = "error";

    } elseif (empty($email)) {

        $message = "Please enter your email address.";
        $messageType = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "error";

    } else {


        /*
        |--------------------------------------------------------------------------
        | Check BOTH username and email
        |--------------------------------------------------------------------------
        */

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, username, email
             FROM user
             WHERE username = ? AND email = ?
             LIMIT 1"
        );


        mysqli_stmt_bind_param(
            $stmt,
            "ss",
            $username,
            $email
        );


        mysqli_stmt_execute($stmt);


        $result = mysqli_stmt_get_result($stmt);


        if ($row = mysqli_fetch_assoc($result)) {


            /*
            |--------------------------------------------------------------------------
            | Username and email match
            |--------------------------------------------------------------------------
            */

            $verifiedUserId = $row['id'];


            /*
            | Create masked email
            |
            | john@gmail.com
            | becomes
            | j**n@gmail.com
            */

            $fullEmail = $row['email'];

            $emailParts = explode('@', $fullEmail);


            if (count($emailParts) === 2) {

                $emailName = $emailParts[0];

                $emailDomain = $emailParts[1];

                $nameLength = strlen($emailName);


                if ($nameLength <= 2) {

                    $maskedName =
                        substr($emailName, 0, 1) . "*";

                } else {

                    $maskedName =
                        substr($emailName, 0, 1) .
                        str_repeat("*", max(1, $nameLength - 2)) .
                        substr($emailName, -1);

                }


                $emailHint =
                    $maskedName . "@" . $emailDomain;

            }


            /*
            |--------------------------------------------------------------------------
            | Store verified user in session
            |--------------------------------------------------------------------------
            */

            $_SESSION['password_reset_user'] = $verifiedUserId;


            $showPasswordForm = true;


        } else {


            /*
            |--------------------------------------------------------------------------
            | Username and email do not match
            |--------------------------------------------------------------------------
            */

            $message =
                "The email address does not match the username.";

            $messageType = "error";

        }


        mysqli_stmt_close($stmt);

    }

}


/*
|--------------------------------------------------------------------------
| STEP 2: Create New Password
|--------------------------------------------------------------------------
*/

if (isset($_POST['reset_password'])) {


    $userId =
        $_SESSION['password_reset_user'] ?? null;


    $newPassword =
        $_POST['new_password'] ?? '';


    $confirmPassword =
        $_POST['confirm_password'] ?? '';


    if (!$userId) {

        $message =
            "Your verification session has expired. Please verify your email again.";

        $messageType = "error";


    } elseif (empty($newPassword) || empty($confirmPassword)) {

        $message =
            "Please enter and confirm your new password.";

        $messageType = "error";

        $showPasswordForm = true;


    } elseif (strlen($newPassword) < 6) {

        $message =
            "Your new password must be at least 6 characters long.";

        $messageType = "error";

        $showPasswordForm = true;


    } elseif ($newPassword !== $confirmPassword) {

        $message =
            "The passwords do not match.";

        $messageType = "error";

        $showPasswordForm = true;


    } else {


        /*
        |--------------------------------------------------------------------------
        | Securely hash the new password
        |--------------------------------------------------------------------------
        */

        $hashedPassword =
            password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            );


        $stmt = mysqli_prepare(
            $conn,
            "UPDATE user SET password = ? WHERE id = ?"
        );


        mysqli_stmt_bind_param(
            $stmt,
            "si",
            $hashedPassword,
            $userId
        );


        if (mysqli_stmt_execute($stmt)) {


            /*
            |--------------------------------------------------------------------------
            | Clear password reset session
            |--------------------------------------------------------------------------
            */

            unset($_SESSION['password_reset_user']);


            $message =
                "Your password has been changed successfully.";

            $messageType = "success";

            $showPasswordForm = false;


        } else {

            $message =
                "Something went wrong. Please try again.";

            $messageType = "error";

            $showPasswordForm = true;

        }


        mysqli_stmt_close($stmt);

    }

}


/*
|--------------------------------------------------------------------------
| Get email hint again if needed
|--------------------------------------------------------------------------
*/

if ($showPasswordForm && empty($emailHint)) {


    $userId =
        $_SESSION['password_reset_user'] ?? null;


    if ($userId) {


        $stmt = mysqli_prepare(
            $conn,
            "SELECT email FROM user WHERE id = ? LIMIT 1"
        );


        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $userId
        );


        mysqli_stmt_execute($stmt);


        $result =
            mysqli_stmt_get_result($stmt);


        if ($row = mysqli_fetch_assoc($result)) {


            $fullEmail = $row['email'];

            $emailParts = explode('@', $fullEmail);


            if (count($emailParts) === 2) {

                $emailName = $emailParts[0];

                $emailDomain = $emailParts[1];

                $nameLength = strlen($emailName);


                if ($nameLength <= 2) {

                    $maskedName =
                        substr($emailName, 0, 1) . "*";

                } else {

                    $maskedName =
                        substr($emailName, 0, 1) .
                        str_repeat("*", max(1, $nameLength - 2)) .
                        substr($emailName, -1);

                }


                $emailHint =
                    $maskedName . "@" . $emailDomain;

            }

        }


        mysqli_stmt_close($stmt);

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Forgot Password - Water Billing System</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <script src="https://unpkg.com/lucide@latest"></script>

</head>


<body
    class="min-h-screen bg-slate-900 bg-cover bg-center flex items-center justify-center p-4"
    style="background-image: url('img/warer-flow-pipe4.gif');"
>


<div
    class="w-full max-w-md bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl shadow-2xl p-8 text-white"
>


    <!-- Header -->

    <div class="mb-8 text-center">


        <div class="flex justify-center mb-4">

            <div
                class="w-14 h-14 rounded-full bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center"
            >

                <i
                    data-lucide="key-round"
                    class="w-7 h-7 text-indigo-300"
                ></i>

            </div>

        </div>


        <h1 class="text-3xl font-bold tracking-tight text-white">

            Forgot Password?

        </h1>


        <p class="text-slate-300 text-sm mt-2">

            Verify your email to reset your password

        </p>

    </div>



    <!-- Messages -->

    <?php if (!empty($message)): ?>


        <?php if ($messageType === "success"): ?>


            <div
                class="mb-6 p-4 rounded-xl bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 text-sm flex items-center gap-2"
            >

                <i
                    data-lucide="check-circle"
                    class="w-5 h-5"
                ></i>


                <span>

                    <?php echo htmlspecialchars($message); ?>

                </span>

            </div>


        <?php else: ?>


            <div
                class="mb-6 p-4 rounded-xl bg-rose-500/20 border border-rose-500/50 text-rose-200 text-sm flex items-center gap-2"
            >

                <i
                    data-lucide="alert-circle"
                    class="w-5 h-5"
                ></i>


                <span>

                    <?php echo htmlspecialchars($message); ?>

                </span>

            </div>


        <?php endif; ?>


    <?php endif; ?>



    <?php if ($messageType === "success"): ?>


        <!-- SUCCESS -->

        <div class="text-center">


            <p class="text-slate-300 mb-6">

                You can now sign in using your new password.

            </p>


            <a
                href="index.php"
                class="block w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 rounded-xl transition duration-200"
            >

                Back to Login

            </a>


        </div>



    <?php elseif (!$showPasswordForm): ?>


        <!-- STEP 1 -->

        <div
            class="mb-5 p-4 rounded-xl bg-slate-500/10 border border-white/10"
        >

            <p class="text-xs text-slate-400">
                Username
            </p>


            <p class="font-semibold text-white mt-1">

                <?php echo htmlspecialchars($username); ?>

            </p>

        </div>


        <form method="POST" class="space-y-6">


            <!-- Keep username -->

            <input
                type="hidden"
                name="username"
                value="<?php echo htmlspecialchars($username); ?>"
            >


            <div>

                <label
                    class="block text-sm font-medium text-slate-200 mb-2"
                >

                    Enter the email registered to this username

                </label>


                <div class="relative">

                    <i
                        data-lucide="mail"
                        class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"
                    ></i>


                    <input
                        type="email"
                        name="email"
                        required
                        placeholder="your@email.com"
                        class="w-full pl-11 pr-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-slate-400 outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >

                </div>

            </div>


            <button
                type="submit"
                name="verify_email"
                class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 rounded-xl transition duration-200 flex items-center justify-center gap-2"
            >

                <i
                    data-lucide="shield-check"
                    class="w-5 h-5"
                ></i>


                Verify Email

            </button>


        </form>



    <?php else: ?>


        <!-- STEP 2 -->

        <div
            class="mb-6 p-4 rounded-xl bg-indigo-500/10 border border-indigo-400/30"
        >

            <div class="flex items-center gap-3">


                <i
                    data-lucide="mail-check"
                    class="w-5 h-5 text-indigo-300"
                ></i>


                <div>

                    <p class="text-xs text-slate-400">

                        Email verified

                    </p>


                    <p class="font-semibold text-indigo-200">

                        <?php echo htmlspecialchars($emailHint); ?>

                    </p>

                </div>


            </div>

        </div>



        <form method="POST" class="space-y-6">


            <div>

                <label
                    class="block text-sm font-medium text-slate-200 mb-2"
                >

                    New Password

                </label>


                <div class="relative">


                    <i
                        data-lucide="lock"
                        class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"
                    ></i>


                    <input
                        type="password"
                        name="new_password"
                        required
                        minlength="6"
                        placeholder="Enter new password"
                        class="w-full pl-11 pr-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-slate-400 outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >

                </div>

            </div>



            <div>

                <label
                    class="block text-sm font-medium text-slate-200 mb-2"
                >

                    Confirm New Password

                </label>


                <div class="relative">


                    <i
                        data-lucide="lock-keyhole"
                        class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"
                    ></i>


                    <input
                        type="password"
                        name="confirm_password"
                        required
                        minlength="6"
                        placeholder="Confirm new password"
                        class="w-full pl-11 pr-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-slate-400 outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >

                </div>

            </div>



            <button
                type="submit"
                name="reset_password"
                class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-semibold py-3 rounded-xl transition duration-200 flex items-center justify-center gap-2"
            >

                <i
                    data-lucide="key-round"
                    class="w-5 h-5"
                ></i>


                Create New Password

            </button>


        </form>


        <div class="mt-6 text-center">

            <a
                href="forgot_password.php?username=<?php echo urlencode($username); ?>"
                class="text-sm text-indigo-300 hover:text-indigo-200"
            >

                Try again

            </a>

        </div>


    <?php endif; ?>



    <!-- Back to Login -->

    <?php if ($messageType !== "success"): ?>

        <div class="mt-6 text-center">

            <a
                href="index.php"
                class="text-sm text-slate-300 hover:text-white transition"
            >

                ← Back to Login

            </a>

        </div>

    <?php endif; ?>


</div>



<script>

    lucide.createIcons();

</script>


</body>

</html>
