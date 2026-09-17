<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'auth.php';
checkLevel([1, 2]); // Restricted to Admin & Staff

$logged_in_user_id = $_SESSION['id'] ?? $_SESSION['SESS_MEMBER_ID'] ?? null;

if (!$logged_in_user_id) {
    header("Location: index.php");
    exit();
}

include_once 'db.php';

// Sanitize inputs
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

$prev = $owners_id = $pres = $price = $totalcons = $bill = $date = "";
$lname = $fname = $mi = $address = $contact = "";
$sessionname = "";

if ($id > 0) {
    // 1. Fetch Bill Information
    $stmt = mysqli_prepare($conn, "SELECT * FROM bill WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $prev      = floatval($row['prev']);
        $owners_id = intval($row['owners_id']);
        $pres      = floatval($row['pres']);
        
        // Ensure both price per unit and consumption are strictly positive values
        $price     = abs(floatval($row['price']));
        $totalcons = abs($pres - $prev);
        $bill      = $totalcons * $price;
        $date      = $row['date'];
    }
    mysqli_stmt_close($stmt);

    // 2. Fetch Owner Information
    if ($owners_id > 0) {
        $stmt_owner = mysqli_prepare($conn, "SELECT * FROM owners WHERE id = ?");
        mysqli_stmt_bind_param($stmt_owner, "i", $owners_id);
        mysqli_stmt_execute($stmt_owner);
        $result_owner = mysqli_stmt_get_result($stmt_owner);

        if ($test = mysqli_fetch_assoc($result_owner)) {
            $lname   = $test['lname'];
            $fname   = $test['fname'];
            $mi      = $test['mi'];
            $address = $test['address'];
            $contact = $test['contact'];
        }
        mysqli_stmt_close($stmt_owner);
    }

    // 3. Fetch User / Cashier Session Name
    $session = intval($logged_in_user_id);
    $stmt_user = mysqli_prepare($conn, "SELECT name FROM user WHERE id = ?");
    mysqli_stmt_bind_param($stmt_user, "i", $session);
    mysqli_stmt_execute($stmt_user);
    $result_user = mysqli_stmt_get_result($stmt_user);

    if ($row_user = mysqli_fetch_assoc($result_user)) {
        $sessionname = $row_user['name'];
    }
    mysqli_stmt_close($stmt_user);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Bill Invoice</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">

<div class="p-6 bg-slate-800 text-slate-100 rounded-2xl max-w-2xl w-full border border-slate-700 shadow-2xl relative z-50 my-6 mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-slate-700 pb-4 mb-6">
        <h3 class="text-lg font-bold text-white flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-blue-400"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            Water Bill Invoice
        </h3>
        <!-- Top Right X Button -->
        <button type="button" 
                onclick="if (typeof jQuery !== 'undefined' && jQuery('#facebox').is(':visible')) { jQuery(document).trigger('close.facebox'); } else if (window.history.length > 1) { window.history.back(); } else { window.location.href='paybill.php'; }" 
                class="text-slate-400 hover:text-white hover:bg-slate-700/50 p-1.5 rounded-lg transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg>
        </button>
    </div>

    <?php if ($id > 0 && !empty($owners_id)): ?>
        <!-- Printable Invoice Container -->
        <div id="printableInvoice" class="p-6 bg-slate-900/80 border border-slate-700/60 rounded-xl space-y-6">
            <!-- Header Invoice Details -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
                <div>
                    <h4 class="text-base font-bold text-white uppercase tracking-wider">Water Billing System</h4>
                    <p class="text-xs text-slate-400">ESPSN - ESSP</p>
                    <p class="text-xs text-slate-400">Phone: +255 (0) 654 235</p>
                </div>
                <div class="sm:text-right">
                    <span class="inline-block px-2.5 py-1 text-xs font-mono font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20 rounded-lg mb-1">
                        SMART/00<?php echo htmlspecialchars($id); ?>
                    </span>
                    <p class="text-xs text-slate-400">Date: <span class="text-slate-200"><?php echo htmlspecialchars($date); ?></span></p>
                </div>
            </div>

            <!-- Client & Meter Info -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="space-y-1.5 p-3 bg-slate-800/50 rounded-lg border border-slate-700/40">
                    <p class="text-slate-400">Client Name: <strong class="text-white"><?php echo htmlspecialchars($fname . ' ' . $lname); ?></strong></p>
                    <p class="text-slate-400">Address: <strong class="text-white"><?php echo htmlspecialchars($address); ?></strong></p>
                    <p class="text-slate-400">Contact: <strong class="text-white"><?php echo htmlspecialchars($contact); ?></strong></p>
                </div>
                <div class="space-y-1.5 p-3 bg-slate-800/50 rounded-lg border border-slate-700/40">
                    <p class="text-slate-400">Meter Number: <strong class="text-white"><?php echo htmlspecialchars($mi); ?></strong></p>
                    <p class="text-slate-400">Cashier: <strong class="text-white"><?php echo htmlspecialchars($sessionname); ?></strong></p>
                </div>
            </div>

            <!-- Meter Readings & Calculation -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                <div class="p-3 bg-slate-800/80 rounded-xl border border-slate-700/50">
                    <span class="block text-[10px] uppercase font-semibold text-slate-400 mb-1">Previous</span>
                    <span class="font-mono text-sm text-slate-200"><?php echo htmlspecialchars(number_format($prev, 2)); ?></span>
                </div>
                <div class="p-3 bg-slate-800/80 rounded-xl border border-slate-700/50">
                    <span class="block text-[10px] uppercase font-semibold text-slate-400 mb-1">Present</span>
                    <span class="font-mono text-sm text-slate-200"><?php echo htmlspecialchars(number_format($pres, 2)); ?></span>
                </div>
                <div class="p-3 bg-slate-800/80 rounded-xl border border-slate-700/50">
                    <span class="block text-[10px] uppercase font-semibold text-slate-400 mb-1">Consumption</span>
                    <span class="font-mono text-sm text-amber-400 font-semibold"><?php echo htmlspecialchars(number_format($totalcons, 2)); ?></span>
                </div>
                <div class="p-3 bg-slate-800/80 rounded-xl border border-slate-700/50">
                    <span class="block text-[10px] uppercase font-semibold text-slate-400 mb-1">Price / Unit</span>
                    <span class="font-mono text-sm text-slate-200"><?php echo htmlspecialchars(number_format($price, 2)); ?></span>
                </div>
            </div>

            <!-- Total Amount Card -->
            <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl flex items-center justify-between">
                <div>
                    <span class="text-xs uppercase font-semibold text-emerald-400">Total Invoice</span>
                    <p class="text-xs text-slate-400">Tanzanian Shillings</p>
                </div>
                <div class="font-mono text-2xl font-bold text-emerald-400">
                    <?php echo htmlspecialchars(number_format($bill, 2)); ?> Tshs
                </div>
            </div>
        </div>

        <!-- Action Footer -->
        <div class="pt-4 flex items-center justify-between border-t border-slate-700/60 mt-6">
            <!-- Bottom Close Button -->
            <button type="button" 
                    onclick="if (typeof jQuery !== 'undefined' && jQuery('#facebox').is(':visible')) { jQuery(document).trigger('close.facebox'); } else if (window.history.length > 1) { window.history.back(); } else { window.location.href='paybill.php'; }" 
                    class="px-4 py-2.5 text-xs font-semibold text-slate-300 hover:text-white bg-slate-700 hover:bg-slate-600 rounded-xl transition">
                Close
            </button>
            <button type="button" onclick="printReceipt()" 
                    class="px-4 py-2.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-500 rounded-xl transition shadow-md shadow-blue-600/20 active:scale-[0.98] flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Print Invoice
            </button>
        </div>

        <script>
            function printReceipt() {
                var content = document.getElementById('printableInvoice').innerHTML;
                var frame = document.createElement('iframe');
                frame.style.position = 'absolute';
                frame.style.width = '0px';
                frame.style.height = '0px';
                frame.style.border = 'none';
                document.body.appendChild(frame);

                var doc = frame.contentWindow.document;
                doc.open();
                doc.write('<html><head><title>Print Invoice</title>');
                doc.write('<style>');
                doc.write('body { font-family: monospace; padding: 20px; color: #000; }');
                doc.write('.grid { display: flex; gap: 10px; margin-bottom: 10px; }');
                doc.write('.border { border: 1px solid #ccc; padding: 10px; border-radius: 5px; }');
                doc.write('</style></head><body>');
                doc.write(content);
                doc.write('</body></html>');
                doc.close();

                setTimeout(function() {
                    frame.contentWindow.focus();
                    frame.contentWindow.print();
                    document.body.removeChild(frame);
                }, 250);
            }
        </script>

    <?php else: ?>
        <div class="p-4 bg-amber-500/10 border border-amber-500/20 text-amber-400 rounded-xl text-sm flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
            Invalid Invoice or Owner Record.
        </div>
    <?php endif; ?>
</div>

</body>
</html>