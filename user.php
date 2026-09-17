<?php 
session_start();
if(!isset($_SESSION['id'])){
  header("Location: index.php");
  exit();
}

include 'auth.php';
checkLevel([1]);
$session = $_SESSION['id'];
include 'db.php';

$result = mysqli_query($conn, "SELECT * FROM user WHERE id= '$session'");
while($row = mysqli_fetch_array($result)) {
  $sessionname = $row['name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Water Billing System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="src/facebox.css" media="screen" rel="stylesheet" type="text/css" />
    <script src="lib/jquery.js" type="text/javascript"></script>
    <script src="src/facebox.js" type="text/javascript"></script>
    <script type="text/javascript">
      jQuery(document).ready(function($) {
        $('a[rel*=facebox]').facebox({
          loadingImage : 'src/loading.gif',
          closeImage   : 'src/closelabel.png'
        });
      });
    </script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen font-sans antialiased">

    <header class="border-b border-slate-800 bg-slate-950/50 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-600/20 text-blue-400 rounded-lg border border-blue-500/30">
                    <i data-lucide="droplet" class="w-6 h-6"></i>
                </div>
                <h1 class="text-lg font-bold tracking-tight text-white">Water Billing System</h1>
            </div>
            
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-slate-300 flex items-center gap-2">
                    <i data-lucide="user-check" class="w-4 h-4 text-emerald-400"></i>
                    <?php echo htmlspecialchars($sessionname); ?>
                </span>
                <a href="logout.php" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-400 hover:text-red-300 hover:bg-red-500/10 border border-red-500/20 rounded-lg transition duration-200">
                    <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <nav class="flex space-x-2 border-b border-slate-800 pb-4 mb-8">
            <a href="billing.php" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/60 rounded-xl transition">
                <i data-lucide="home" class="w-4 h-4"></i> Home
            </a>
            <a href="bill.php" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/60 rounded-xl transition">
                <i data-lucide="receipt" class="w-4 h-4"></i> Billing
            </a>
            <a href="user.php" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-xl shadow-md shadow-blue-600/20">
                <i data-lucide="users" class="w-4 h-4"></i> Users
            </a>
            <a href="clients.php" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/60 rounded-xl transition">
                <i data-lucide="database" class="w-4 h-4"></i> Clients
            </a>
        </nav>

        <div class="bg-slate-800/50 border border-slate-700/60 rounded-2xl shadow-xl overflow-hidden">
            
            <div class="p-6 border-b border-slate-700/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <i data-lucide="shield" class="w-5 h-5 text-blue-400"></i> System Users
                    </h2>
                    <p class="text-slate-400 text-xs mt-1">Manage system accounts, user levels, and access permissions</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <a rel="facebox" href="adduser.php" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-500 rounded-lg shadow-sm transition">
                        <i data-lucide="user-plus" class="w-4 h-4"></i> Add User
                    </a>
                    <a href="deleteuser.php" onclick="return confirm('Are you sure you want to delete all users?')" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-red-400 hover:text-red-300 hover:bg-red-500/10 border border-red-500/20 rounded-lg transition">
                        <i data-lucide="trash-2" class="w-4 h-4"></i> Delete All
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="bg-slate-900/60 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-700/60">
                        <tr>
                            <th class="px-6 py-4 font-semibold">ID</th>
                            <th class="px-6 py-4 font-semibold">Username</th>
                            <th class="px-6 py-4 font-semibold">Password</th>
                            <th class="px-6 py-4 font-semibold">Name</th>
                            <th class="px-6 py-4 font-semibold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        <?php
                        $result = mysqli_query($conn, "SELECT * FROM user WHERE id != $session");
                        while($row = mysqli_fetch_array($result)):
                        ?>
                        <tr class="hover:bg-slate-700/30 transition duration-150">
                            <td class="px-6 py-4 font-mono text-slate-400 text-xs"><?php echo htmlspecialchars($row['id']); ?></td>
                            <td class="px-6 py-4 font-medium text-white"><?php echo htmlspecialchars($row['username']); ?></td>
                            <td class="px-6 py-4 font-mono text-slate-400">••••••••</td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a rel="facebox" href="edituser.php?id=<?php echo $row['id']; ?>" class="p-2 text-slate-400 hover:text-blue-400 hover:bg-blue-500/10 rounded-lg transition" title="Edit User">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                    <a rel="facebox" href="deluser.php?id=<?php echo $row['id']; ?>" class="p-2 text-slate-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition" title="Delete User">
                                        <i data-lucide="trash" class="w-4 h-4"></i>
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

    <script>
        lucide.createIcons();
    </script>

    <script type="text/javascript">
    $(function() {
        $(".delbutton").click(function(){
            var element = $(this);
            var del_id = element.attr("id");
            var info = 'id=' + del_id;
            if(confirm("Sure you want to delete this update? There is NO undo!")) {
                $.ajax({
                    type: "GET",
                    url: "delete.php",
                    data: info,
                    success: function(){
                        element.parents(".record").animate({ backgroundColor: "#fbc7c7" }, "fast")
                        .animate({ opacity: "hide" }, "slow");
                    }
                });
            }
            return false;
        });
    });
    </script>
</body>
</html>