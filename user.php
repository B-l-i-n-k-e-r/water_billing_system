<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

include_once 'auth.php';
checkLevel([1]); // Admin only

$session = $_SESSION['id'];
include_once 'db.php';

$sessionname = "Admin";

$result_session = mysqli_query(
    $conn,
    "SELECT name FROM user WHERE id = '$session'"
);

if ($row_session = mysqli_fetch_array($result_session)) {
    $sessionname = $row_session['name'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Users - Water Billing System</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <script src="https://unpkg.com/lucide@latest"></script>

    <link href="src/facebox.css"
          media="screen"
          rel="stylesheet"
          type="text/css" />

    <script src="lib/jquery.js"
            type="text/javascript"></script>

    <script src="src/facebox.js"
            type="text/javascript"></script>

    <script type="text/javascript">

        jQuery(document).ready(function($) {

            $('a[rel*=facebox]').facebox({

                loadingImage: 'src/loading.gif',

                closeImage: 'src/closelabel.png'

            });

        });

    </script>

    <style>

        /* Strip Facebox default background and border */

        #facebox .popup {

            background: transparent !important;

            border: none !important;

            box-shadow: none !important;

            padding: 0 !important;

        }

        #facebox .content {

            background: transparent !important;

            border: none !important;

            padding: 0 !important;

        }

        #facebox .close {

            display: none !important;

        }

    </style>

</head>


<body class="bg-slate-900 text-slate-100 min-h-screen font-sans antialiased flex flex-col justify-between">


    <!-- Header -->

    <header class="border-b border-slate-800 bg-slate-950/50 backdrop-blur-md sticky top-0 z-50">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">

            <div class="flex items-center gap-3">

                <div class="p-2 bg-blue-600/20 text-blue-400 rounded-lg border border-blue-500/30">

                    <i data-lucide="droplet" class="w-6 h-6"></i>

                </div>

                <h1 class="text-lg font-bold tracking-tight text-white">
                    Water Billing System
                </h1>

            </div>


            <div class="flex items-center gap-4">

                <span class="text-sm font-medium text-slate-300 flex items-center gap-2">

                    <i data-lucide="user-check"
                       class="w-4 h-4 text-emerald-400"></i>

                    <?php echo htmlspecialchars($sessionname); ?>

                </span>


                <a href="logout.php"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-400 hover:text-red-300 hover:bg-red-500/10 border border-red-500/20 rounded-lg transition duration-200">

                    <i data-lucide="log-out" class="w-3.5 h-3.5"></i>

                    <span>Logout</span>

                </a>

            </div>

        </div>

    </header>


    <!-- Main -->

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-grow">


        <!-- Navigation -->

        <nav class="flex space-x-2 border-b border-slate-800 pb-4 mb-8">

            <a href="dashboard.php"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/60 rounded-xl transition">

                <i data-lucide="home" class="w-4 h-4"></i>

                Home

            </a>


            <a href="bill.php"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/60 rounded-xl transition">

                <i data-lucide="receipt" class="w-4 h-4"></i>

                Billing

            </a>


            <a href="user.php"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-xl shadow-md shadow-blue-600/20">

                <i data-lucide="users" class="w-4 h-4"></i>

                Users

            </a>


            <a href="clients.php"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/60 rounded-xl transition">

                <i data-lucide="database" class="w-4 h-4"></i>

                Clients

            </a>

        </nav>


        <!-- Users Card -->

        <div class="bg-slate-800/50 border border-slate-700/60 rounded-2xl shadow-xl overflow-hidden">


            <!-- Card Header -->

            <div class="p-6 border-b border-slate-700/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">

                <div>

                    <h2 class="text-xl font-bold text-white flex items-center gap-2">

                        <i data-lucide="shield"
                           class="w-5 h-5 text-blue-400"></i>

                        System Users & Roles

                    </h2>

                    <p class="text-slate-400 text-xs mt-1">
                        Manage system accounts, user roles, and access permissions
                    </p>

                </div>


                <div class="flex items-center gap-3">


                    <!-- Add User -->

                    <a rel="facebox"
                       href="adduser.php"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-500 rounded-lg shadow-sm transition">

                        <i data-lucide="user-plus" class="w-4 h-4"></i>

                        Add User

                    </a>


                    <!-- Delete All -->

                    <a href="deleteuser.php"
                       onclick="return confirm('Are you sure you want to delete all users?')"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-red-400 hover:text-red-300 hover:bg-red-500/10 border border-red-500/20 rounded-lg transition">

                        <i data-lucide="trash-2" class="w-4 h-4"></i>

                        Delete All

                    </a>

                </div>

            </div>


            <!-- Users Table -->

            <div class="overflow-x-auto">

                <table class="w-full text-left text-sm text-slate-300">


                    <!-- Table Header -->

                    <thead class="bg-slate-900/60 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-700/60">

                        <tr>

                            <th class="px-6 py-4 font-semibold">
                                ID
                            </th>

                            <th class="px-6 py-4 font-semibold">
                                Username
                            </th>

                            <th class="px-6 py-4 font-semibold">
                                Password
                            </th>

                            <th class="px-6 py-4 font-semibold">
                                Name
                            </th>

                            <!-- NEW EMAIL COLUMN -->

                            <th class="px-6 py-4 font-semibold">
                                Email
                            </th>

                            <th class="px-6 py-4 font-semibold">
                                Role Level
                            </th>

                            <th class="px-6 py-4 font-semibold text-right">
                                Action
                            </th>

                        </tr>

                    </thead>


                    <!-- Table Body -->

                    <tbody class="divide-y divide-slate-700/50">

                        <?php

                        $result = mysqli_query(
                            $conn,
                            "SELECT * FROM user WHERE id != $session"
                        );

                        while ($row = mysqli_fetch_array($result)):

                            $level = isset($row['userlevel'])
                                ? intval($row['userlevel'])
                                : 3;

                            /*
                             * Get email safely.
                             * This also prevents errors for older
                             * users who may not have an email.
                             */

                            $email = isset($row['email'])
                                ? $row['email']
                                : '';


                            // Map user levels to role names

                            switch ($level) {

                                case 1:

                                    $roleName = 'Administrator';

                                    $badgeStyle =
                                        'bg-indigo-500/10 text-indigo-400 border-indigo-500/20';

                                    break;


                                case 2:

                                    $roleName = 'Cashier';

                                    $badgeStyle =
                                        'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';

                                    break;


                                case 3:

                                default:

                                    $roleName = 'Staff / Manager';

                                    $badgeStyle =
                                        'bg-slate-700/50 text-slate-300 border-slate-600';

                                    break;

                            }

                        ?>


                            <tr class="hover:bg-slate-700/30 transition duration-150">


                                <!-- ID -->

                                <td class="px-6 py-4 font-mono text-slate-400 text-xs">

                                    #<?php echo htmlspecialchars($row['id']); ?>

                                </td>


                                <!-- Username -->

                                <td class="px-6 py-4 font-medium text-white">

                                    <?php echo htmlspecialchars($row['username']); ?>

                                </td>


                                <!-- Password -->

                                <td class="px-6 py-4 font-mono text-slate-400">

                                    ••••••••

                                </td>


                                <!-- Name -->

                                <td class="px-6 py-4">

                                    <?php echo htmlspecialchars($row['name']); ?>

                                </td>


                                <!-- Email -->

                                <td class="px-6 py-4 text-slate-300">

                                    <?php

                                    if (!empty($email)) {

                                        echo htmlspecialchars($email);

                                    } else {

                                        echo '<span class="text-slate-500 italic">
                                                No email
                                              </span>';

                                    }

                                    ?>

                                </td>


                                <!-- Role -->

                                <td class="px-6 py-4">

                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-lg border <?php echo $badgeStyle; ?>">

                                        <?php echo htmlspecialchars($roleName); ?>

                                        (Level <?php echo $level; ?>)

                                    </span>

                                </td>


                                <!-- Actions -->

                                <td class="px-6 py-4 text-right">

                                    <div class="inline-flex items-center gap-2">


                                        <!-- Edit User -->

                                        <a rel="facebox"
                                           href="edituser.php?id=<?php echo $row['id']; ?>"
                                           class="p-2 text-slate-400 hover:text-blue-400 hover:bg-blue-500/10 rounded-lg transition"
                                           title="Edit User">

                                            <i data-lucide="edit-3"
                                               class="w-4 h-4"></i>

                                        </a>


                                        <!-- Delete User -->

                                        <a rel="facebox"
                                           href="deluser.php?id=<?php echo $row['id']; ?>"
                                           class="p-2 text-slate-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition"
                                           title="Delete User">

                                            <i data-lucide="trash"
                                               class="w-4 h-4"></i>

                                        </a>

                                    </div>

                                </td>

                            </tr>


                        <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </main>


    <!-- Footer -->

    <footer class="border-t border-slate-800 py-4 text-center text-xs text-slate-500 mt-8">

        Water Billing System &copy; <?php echo date('Y'); ?>

    </footer>


    <!-- Lucide Icons -->

    <script>

        lucide.createIcons();

    </script>

</body>

</html>
```
