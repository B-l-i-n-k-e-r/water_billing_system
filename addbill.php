<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'auth.php';
checkLevel([1, 2]); // Restricted to Admin & Cashier

include_once 'db.php';

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

$prev = 0;
$owners_id = 0;
$lname = '';
$fname = '';

if ($id > 0) {
    // Fetch previous meter reading and owner details
    $stmt = mysqli_prepare($conn, "SELECT t.id, t.Prev, o.lname, o.fname 
                                   FROM tempo_bill t 
                                   LEFT JOIN owners o ON t.id = o.id 
                                   WHERE t.id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $owners_id = $row['id'];
            $prev      = $row['Prev'];
            $lname     = $row['lname'] ?? '';
            $fname     = $row['fname'] ?? '';
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Client Bill</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">

<!-- Modal Container -->
<div class="p-6 bg-slate-800 text-slate-100 rounded-2xl max-w-md w-full border border-slate-700 shadow-2xl relative z-50 my-4 mx-auto">
    
    <!-- Modal Header -->
    <div class="flex items-center justify-between border-b border-slate-700 pb-4 mb-5">
        <div class="flex items-center gap-2">
            <div class="p-2 bg-indigo-500/10 text-indigo-400 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-white leading-tight">Add Client Bill</h3>
                <p class="text-xs text-slate-400 mt-0.5"><?php echo date('Y-m-d H:i:s'); ?></p>
            </div>
        </div>

        <!-- Top Right Close Button -->
        <button type="button" 
                onclick="if (typeof jQuery !== 'undefined' && jQuery('#facebox').is(':visible')) { jQuery(document).trigger('close.facebox'); } else if (window.history.length > 1) { window.history.back(); } else { window.location.href='bill.php'; }" 
                class="text-slate-400 hover:text-white hover:bg-slate-700/50 p-1.5 rounded-lg transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg>
        </button>
    </div>

    <!-- Client Info Card -->
    <div class="p-3 bg-slate-900/60 border border-slate-700/50 rounded-xl mb-5 flex items-center justify-between">
        <span class="text-xs text-slate-400 uppercase font-semibold tracking-wider">Client Name</span>
        <span class="text-sm font-semibold text-indigo-300"><?php echo htmlspecialchars(trim($fname . ' ' . $lname)); ?></span>
    </div>

    <!-- Form -->
    <form action="addbillexec.php" method="post" class="space-y-4">
        <input type="hidden" name="owners_id" value="<?php echo htmlspecialchars($owners_id); ?>" />
        <input type="hidden" name="date" value="<?php echo date('Y-m-d'); ?>" />

        <!-- Previous Reading -->
        <div>
            <label class="block text-xs font-medium text-slate-300 mb-1.5">Previous Reading</label>
            <div class="relative flex items-center">
                <input type="number" 
                       step="any" 
                       name="prev" 
                       value="<?php echo htmlspecialchars($prev); ?>" 
                       readonly 
                       class="w-full bg-slate-900/80 border border-slate-700 text-slate-400 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none cursor-not-allowed pr-12 font-mono" />
                <span class="absolute right-3.5 text-xs text-slate-500 font-medium">m³</span>
            </div>
        </div>

        <!-- Present Reading -->
        <div>
            <label class="block text-xs font-medium text-slate-300 mb-1.5">Present Reading <span class="text-rose-400">*</span></label>
            <div class="relative flex items-center">
                <input type="number" 
                       step="any" 
                       name="pres" 
                       required 
                       placeholder="Enter new reading" 
                       autofocus
                       class="w-full bg-slate-900 border border-slate-700 text-white text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition pr-12 font-mono" />
                <span class="absolute right-3.5 text-xs text-slate-400 font-medium">m³</span>
            </div>
        </div>

        <!-- Rate / Price -->
        <div>
            <label class="block text-xs font-medium text-slate-300 mb-1.5">Price per m³</label>
            <div class="relative flex items-center">
                <input type="number" 
                       step="any" 
                       name="price" 
                       value="10" 
                       required 
                       class="w-full bg-slate-900 border border-slate-700 text-white text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition pr-16 font-mono" />
                <span class="absolute right-3.5 text-xs text-slate-400 font-medium">Tshs</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-700/60 mt-6">
            <button type="button" 
                    onclick="if (typeof jQuery !== 'undefined' && jQuery('#facebox').is(':visible')) { jQuery(document).trigger('close.facebox'); } else if (window.history.length > 1) { window.history.back(); } else { window.location.href='bill.php'; }" 
                    class="px-4 py-2.5 text-xs font-semibold text-slate-300 hover:text-white bg-slate-700 hover:bg-slate-600 rounded-xl transition w-full sm:w-auto text-center">
                Cancel
            </button>
            
            <button type="submit" 
                    class="px-5 py-2.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl transition shadow-md shadow-indigo-600/20 active:scale-[0.98] w-full sm:w-auto text-center flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Add Bill
            </button>
        </div>
    </form>
</div>

</body>
</html>