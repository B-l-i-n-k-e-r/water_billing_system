<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'auth.php';
checkLevel([1, 2]); // Restricted to Cashiers & Admin

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

include_once 'db.php';

$session = $_SESSION['id'];
$sessionname = "Cashier";

// Fetch current user details securely
$stmt_user = mysqli_prepare($conn, "SELECT name FROM user WHERE id = ?");
if ($stmt_user) {
    mysqli_stmt_bind_param($stmt_user, "i", $session);
    mysqli_stmt_execute($stmt_user);
    $res_user = mysqli_stmt_get_result($stmt_user);
    if ($user_row = mysqli_fetch_assoc($res_user)) {
        $sessionname = $user_row['name'];
    }
    mysqli_stmt_close($stmt_user);
}

// Fetch total users count
$res_users_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM user");
$users = ($res_users_count) ? mysqli_fetch_assoc($res_users_count)['total'] : 0;

// Fetch total bills count & total income sum
$res_bill = mysqli_query($conn, "SELECT COUNT(*) as total_count, SUM(price) as total_income FROM bill");
$bill_data = ($res_bill) ? mysqli_fetch_assoc($res_bill) : ['total_count' => 0, 'total_income' => 0];
$bill = $bill_data['total_count'] ?? 0;
$total = $bill_data['total_income'] ?? 0;

// Fetch total clients count
$res_clients = mysqli_query($conn, "SELECT COUNT(*) as total FROM owners");
$client = ($res_clients) ? mysqli_fetch_assoc($res_clients)['total'] : 0;

// Process POST client addition if triggered directly
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {    
    $lname   = trim($_POST['lname'] ?? '');         
    $fname   = trim($_POST['fname'] ?? '');
    $mi      = substr(trim($_POST['mi'] ?? ''), 0, 50);
    $address = trim($_POST['address'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    
    $stmt_add = mysqli_prepare($conn, "INSERT INTO owners (lname, fname, mi, address, contact) VALUES (?, ?, ?, ?, ?)");
    if ($stmt_add) {
        mysqli_stmt_bind_param($stmt_add, "sssss", $lname, $fname, $mi, $address, $contact);
        if (mysqli_stmt_execute($stmt_add)) {
            echo '<script>alert("Client added successfully!"); window.location.href="cashier_dashboard.php";</script>';
            exit();
        }
        mysqli_stmt_close($stmt_add);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Dashboard - Water Billing System</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
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

      function addCommas(nStr) {
        nStr += '';
        var x = nStr.split('.');
        var x1 = x[0];
        var x2 = x.length > 1 ? '.' + x[1] : '';
        var rgx = /(\d+)(\d{3})/;
        while (rgx.test(x1)) {
          x1 = x1.replace(rgx, '$1' + ',' + '$2');
        }
        return x1 + x2;
      }
    </script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen font-sans flex flex-col justify-between">

  <header class="bg-slate-800/80 backdrop-blur border-b border-slate-700/60 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      
      <div class="flex items-center gap-3">
        <div class="p-2 bg-indigo-500/10 text-indigo-400 rounded-xl border border-indigo-500/20">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
        </div>
        <h1 class="text-lg font-bold text-white tracking-wide">Water Billing System</h1>
      </div>

      <div class="flex items-center gap-4">
        <div class="flex items-center gap-2 text-sm text-slate-300">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
          <span class="font-medium"><?php echo htmlspecialchars($sessionname); ?></span>
        </div>
        <a href="logout.php" class="px-3.5 py-1.5 text-xs font-semibold text-rose-300 hover:text-white bg-rose-500/10 hover:bg-rose-600/80 border border-rose-500/20 rounded-xl transition flex items-center gap-1.5">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Logout
        </a>
      </div>

    </div>
  </header>

  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-grow">
    
    <nav class="flex items-center gap-2 border-b border-slate-800 pb-4 mb-8 overflow-x-auto">
      <a href="cashier_dashboard.php" class="px-4 py-2 text-xs font-semibold bg-indigo-600 text-white rounded-xl shadow-lg shadow-indigo-600/20 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Home
      </a>
      <a href="bill.php" class="px-4 py-2 text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 rounded-xl transition flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        Billing
      </a>
      <a href="user.php" class="px-4 py-2 text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 rounded-xl transition flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
        Users
      </a>
      <a href="clients.php" class="px-4 py-2 text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 rounded-xl transition flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Clients
      </a>
    </nav>

    <div class="mb-8">
      <h2 class="text-2xl font-bold text-white tracking-tight">Welcome back, <?php echo htmlspecialchars($sessionname); ?>!</h2>
      <p class="text-sm text-slate-400 mt-1">Here is the current overview of clients, users, and billing metrics.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      
      <div class="bg-slate-800/80 border border-slate-700/60 rounded-2xl p-6 shadow-xl relative overflow-hidden flex flex-col justify-between group hover:border-indigo-500/50 transition">
        <div>
          <div class="flex items-center justify-between mb-4">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Clients</span>
            <div class="p-2.5 bg-indigo-500/10 text-indigo-400 rounded-xl">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
          </div>
          <div class="text-3xl font-extrabold text-white mb-2 font-mono"><?php echo number_format($client); ?></div>
        </div>
        <a href="clients.php" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-400 hover:text-indigo-300 mt-4 group-hover:translate-x-1 transition-transform">
          View Clients List
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
      </div>

      <div class="bg-slate-800/80 border border-slate-700/60 rounded-2xl p-6 shadow-xl relative overflow-hidden flex flex-col justify-between group hover:border-emerald-500/50 transition">
        <div>
          <div class="flex items-center justify-between mb-4">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">System Users</span>
            <div class="p-2.5 bg-emerald-500/10 text-emerald-400 rounded-xl">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
          </div>
          <div class="text-3xl font-extrabold text-white mb-2 font-mono"><?php echo number_format($users); ?></div>
        </div>
        <a href="user.php" class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-400 hover:text-emerald-300 mt-4 group-hover:translate-x-1 transition-transform">
          Manage System Users
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
      </div>

      <div class="bg-slate-800/80 border border-slate-700/60 rounded-2xl p-6 shadow-xl relative overflow-hidden flex flex-col justify-between group hover:border-amber-500/50 transition">
        <div>
          <div class="flex items-center justify-between mb-4">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Bills & Total Revenue</span>
            <div class="p-2.5 bg-amber-500/10 text-amber-400 rounded-xl">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
          </div>
          <div class="text-3xl font-extrabold text-white font-mono"><?php echo number_format($bill); ?> <span class="text-xs text-slate-400 font-sans">Bills</span></div>
          <div class="text-xs text-amber-400 font-semibold font-mono mt-1">
            Total Revenue: <?php echo number_format($total, 2); ?> Tshs
          </div>
        </div>
        <a href="bill.php" class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-400 hover:text-amber-300 mt-4 group-hover:translate-x-1 transition-transform">
          View Invoices & Billing
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
      </div>

    </div>
  </main>

  <footer class="border-t border-slate-800 py-4 text-center text-xs text-slate-500">
    Powered By Water Billing System &copy; <?php echo date('Y'); ?>
  </footer>

</body>
</html>