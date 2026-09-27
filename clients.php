<?php
session_start();

// Include authentication and check authorization
include_once 'auth.php';
checkLevel([1, 2, 3]);

// Check whether user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

$session = $_SESSION['id'];

// Database connection
include_once 'db.php';

// Get logged-in user's name and userlevel securely
$sessionname = "User";
$home_link = "dashboard.php"; // Default fallback
$userlevel = 3; // Default fallback level

if ($stmt = mysqli_prepare($conn, "SELECT name, userlevel FROM user WHERE id = ?")) {
    mysqli_stmt_bind_param($stmt, "i", $session);
    mysqli_stmt_execute($stmt);
    $resultUser = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultUser)) {
        $sessionname = $row['name'] ?? 'User';
        $userlevel = isset($row['userlevel']) ? intval($row['userlevel']) : 3;
        
        // Set dynamic home destination based on privilege level
        if ($userlevel === 1) {
            $home_link = "dashboard.php"; // Admin Dashboard
        } else {
            $home_link = "staff_dashboard.php"; // Staff / Cashier Dashboard
        }
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <style>
  /* Remove Facebox default container padding, background, and border */
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
  /* Hide Facebox default close icon to avoid duplicate 'X' buttons */
  #facebox .close {
      display: none !important;
  }
</style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clients - Water Billing System</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- jQuery & Facebox -->
    <link href="src/facebox.css" media="screen" rel="stylesheet" type="text/css" />
    <script src="lib/jquery.js" type="text/javascript"></script>
    <script src="src/facebox.js" type="text/javascript"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen font-sans antialiased">

    <!-- Top Navigation Header -->
    <header class="border-b border-slate-800 bg-slate-950/50 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-600/20 text-blue-400 rounded-lg border border-blue-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 15.1 5 17 5 15a7 7 0 0 0 7 7z"/></svg>
                </div>
                <h1 class="text-lg font-bold tracking-tight text-white">Water Billing System</h1>
            </div>
            
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-slate-300 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-emerald-400"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
                    <?php echo htmlspecialchars($sessionname); ?>
                </span>
                <a href="logout.php" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-400 hover:text-red-300 hover:bg-red-500/10 border border-red-500/20 rounded-lg transition duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Navigation Tabs (Role-Based Display) -->
        <nav class="flex space-x-2 border-b border-slate-800 pb-4 mb-8">
            <a href="<?php echo htmlspecialchars($home_link); ?>" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/60 rounded-xl transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg> Home
            </a>
            
            <?php if ($userlevel === 1 || $userlevel === 2): ?>
            <a href="bill.php" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/60 rounded-xl transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 6v12"/></svg> Billing
            </a>
            <?php endif; ?>

            <?php if ($userlevel === 1): ?>
            <a href="user.php" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/60 rounded-xl transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> Users
            </a>
            <?php endif; ?>

            <a href="clients.php" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-xl shadow-md shadow-blue-600/20">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/><path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"/></svg> Clients
            </a>
        </nav>

        <!-- Main Client Panel -->
        <div class="bg-slate-800/50 border border-slate-700/60 rounded-2xl shadow-xl overflow-hidden">
            
            <!-- Panel Header -->
            <div class="p-6 border-b border-slate-700/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-emerald-400"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/><path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"/></svg> System Clients
                    </h2>
                    <p class="text-slate-400 text-xs mt-1">Manage registered water service account holders and details</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <a rel="facebox" href="addclient.php" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-500 rounded-lg shadow-sm transition">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg> Add Client
                    </a>
                    <a href="deleteclient.php" onclick="return confirm('Are you sure you want to delete all clients?');" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-red-400 hover:text-red-300 hover:bg-red-500/10 border border-red-500/20 rounded-lg transition">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg> Delete All
                    </a>
                </div>
            </div>

            <!-- Data Table -->
            <div class="overflow-x-auto">
                <?php
                $result = mysqli_query($conn, "SELECT * FROM owners ORDER BY id DESC");

                if (!$result) {
                    echo '<div class="p-6 text-red-400 text-sm">Error loading clients: ' . htmlspecialchars(mysqli_error($conn)) . '</div>';
                } else {
                ?>
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="bg-slate-900/60 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-700/60">
                        <tr>
                            <th class="px-6 py-4 font-semibold shrink-0">ID</th>
                            <th class="px-6 py-4 font-semibold whitespace-nowrap">First Name</th>
                            <th class="px-6 py-4 font-semibold whitespace-nowrap">Last Name</th>
                            <th class="px-6 py-4 font-semibold shrink-0">M.I.</th>
                            <th class="px-6 py-4 font-semibold whitespace-nowrap">Address</th>
                            <th class="px-6 py-4 font-semibold whitespace-nowrap">Contact</th>
                            <th class="px-6 py-4 font-semibold text-right whitespace-nowrap">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        <?php if (mysqli_num_rows($result) == 0): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500">No clients found.</td>
                        </tr>
                        <?php else: ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="record hover:bg-slate-700/30 transition duration-150">
                                <td class="px-6 py-4 font-mono text-slate-400 text-xs shrink-0"><?php echo htmlspecialchars($row['id']); ?></td>
                                <td class="px-6 py-4 font-medium text-white whitespace-nowrap"><?php echo htmlspecialchars($row['fname']); ?></td>
                                <td class="px-6 py-4 font-medium text-white whitespace-nowrap"><?php echo htmlspecialchars($row['lname']); ?></td>
                                <td class="px-6 py-4 text-slate-400 shrink-0"><?php echo htmlspecialchars($row['mi']); ?></td>
                                <td class="px-6 py-4 text-slate-300 whitespace-nowrap"><?php echo htmlspecialchars($row['address']); ?></td>
                                <td class="px-6 py-4 font-mono text-slate-400 text-xs whitespace-nowrap"><?php echo htmlspecialchars($row['contact']); ?></td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center justify-end gap-2">
                                        <!-- Edit Action Button -->
                                        <a rel="facebox" href="edit.php?id=<?php echo urlencode($row['id']); ?>" 
                                           class="p-2 text-slate-400 hover:text-blue-400 hover:bg-blue-500/10 rounded-lg transition inline-flex items-center justify-center border border-slate-700/50" 
                                           title="Edit Client">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                        </a>

                                        <!-- Delete Action Button -->
                                        <a rel="facebox" href="del.php?id=<?php echo urlencode($row['id']); ?>" 
                                           id="<?php echo $row['id']; ?>" 
                                           class="delbutton p-2 text-slate-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition inline-flex items-center justify-center border border-slate-700/50" 
                                           title="Delete Client">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php } ?>
            </div>

        </div>
    </main>

    <!-- Scripts Initialization -->
    <script type="text/javascript">
    $(document).ready(function() {
        // Initialize Facebox
        $('a[rel*=facebox]').facebox({
            loadingImage : 'src/loading.gif',
            closeImage   : 'src/closelabel.png'
        });

        // AJAX Delete action handler
        $(".delbutton").click(function() {
            var element = $(this);
            var del_id = element.attr("id");
            var info = 'id=' + del_id;
            if (confirm("Sure you want to delete this record? There is NO undo!")) {
                $.ajax({
                    type: "GET",
                    url: "delete.php",
                    data: info,
                    success: function() {}
                });
                $(this).parents(".record")
                    .animate({ backgroundColor: "#fbc7c7" }, "fast")
                    .animate({ opacity: "hide" }, "slow");
            }
            return false;
        });
    });
    </script>
</body>
</html>