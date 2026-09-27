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
    <title>Water Bill Receipt</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #printableReceipt, #printableReceipt * {
                visibility: visible;
            }
            #printableReceipt {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 15px;
                background: #ffffff !important;
                color: #000000 !important;
                border: none !important;
                box-shadow: none !important;
            }
            /* Force all text elements inside receipt to print dark/black */
            #printableReceipt h4, 
            #printableReceipt p, 
            #printableReceipt span, 
            #printableReceipt div {
                color: #000000 !important;
            }
            /* Convert dark borders and backgrounds into clean print lines */
            #printableReceipt .border-dashed {
                border-color: #000000 !important;
            }
            #printableReceipt .bg-slate-900,
            #printableReceipt .bg-slate-950 {
                background-color: transparent !important;
                border: 1px solid #000000 !important;
            }
        }
    </style>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">

<div class="p-6 bg-slate-800 text-slate-100 rounded-2xl max-w-lg w-full border border-slate-700 shadow-2xl relative z-50 my-6 mx-auto">
    <!-- Header Modal Bar -->
    <div class="flex items-center justify-between border-b border-slate-700 pb-4 mb-6">
        <h3 class="text-lg font-bold text-white flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-blue-400"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            Payment Receipt
        </h3>
        <button type="button" 
                onclick="if (typeof jQuery !== 'undefined' && jQuery('#facebox').is(':visible')) { jQuery(document).trigger('close.facebox'); } else if (window.history.length > 1) { window.history.back(); } else { window.location.href='paybill.php'; }" 
                class="text-slate-400 hover:text-white hover:bg-slate-700/50 p-1.5 rounded-lg transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg>
        </button>
    </div>

    <?php if ($id > 0 && !empty($owners_id)): ?>
        <!-- Thermal / Classic Receipt Container -->
        <div id="printableReceipt" class="p-6 bg-slate-950 text-slate-200 border border-slate-800 rounded-xl font-mono text-xs space-y-4 shadow-inner">
            
            <!-- Receipt Header -->
            <div class="text-center space-y-1 pb-3 border-b border-dashed border-slate-700">
                <h4 class="text-sm font-bold tracking-widest text-white uppercase">WATER BILLING SYSTEM</h4>
                <p class="text-[11px] text-slate-400">ESPSN - ESSP</p>
                <p class="text-[11px] text-slate-400">Phone: +255 (0) 654 235</p>
            </div>

            <!-- Receipt Meta Info -->
            <div class="space-y-1 pb-3 border-b border-dashed border-slate-700">
                <div class="flex justify-between">
                    <span class="text-slate-400">Receipt No:</span>
                    <span class="font-bold text-blue-400">SMART/00<?php echo htmlspecialchars($id); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Date/Time:</span>
                    <span class="text-slate-300"><?php echo htmlspecialchars($date); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Cashier:</span>
                    <span class="text-slate-300"><?php echo htmlspecialchars($sessionname); ?></span>
                </div>
            </div>

            <!-- Client Info -->
            <div class="space-y-1 pb-3 border-b border-dashed border-slate-700">
                <div class="flex justify-between">
                    <span class="text-slate-400">Client Name:</span>
                    <span class="font-bold text-white"><?php echo htmlspecialchars($fname . ' ' . $lname); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Address:</span>
                    <span class="text-slate-300"><?php echo htmlspecialchars($address); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Contact:</span>
                    <span class="text-slate-300"><?php echo htmlspecialchars($contact); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Meter Number:</span>
                    <span class="text-slate-300"><?php echo htmlspecialchars($mi); ?></span>
                </div>
            </div>

            <!-- Consumption Calculation Breakdown -->
            <div class="space-y-1.5 pb-3 border-b border-dashed border-slate-700">
                <div class="flex justify-between text-slate-400">
                    <span>Previous Reading:</span>
                    <span><?php echo htmlspecialchars(number_format($prev, 2)); ?></span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Present Reading:</span>
                    <span><?php echo htmlspecialchars(number_format($pres, 2)); ?></span>
                </div>
                <div class="flex justify-between text-slate-300 font-semibold">
                    <span>Total Consumption:</span>
                    <span class="text-amber-400"><?php echo htmlspecialchars(number_format($totalcons, 2)); ?></span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Price / Unit:</span>
                    <span><?php echo htmlspecialchars(number_format($price, 2)); ?></span>
                </div>
            </div>

            <!-- Total Amount Due -->
            <div class="py-2 bg-slate-900/80 px-3 rounded-lg border border-slate-800 flex justify-between items-center text-sm font-bold">
                <span class="text-emerald-400 uppercase tracking-wider text-xs">Total Amount:</span>
                <span class="text-emerald-400 text-base"><?php echo htmlspecialchars(number_format($bill, 2)); ?> Tshs</span>
            </div>

            <!-- Receipt Footer Message -->
            <div class="text-center pt-2 text-[10px] text-slate-500 uppercase tracking-wider">
                <p>*** Thank You For Your Payment ***</p>
                <p>Please keep this receipt for your records</p>
            </div>

        </div>

        <!-- Action Footer -->
        <div class="pt-4 flex items-center justify-between border-t border-slate-700/60 mt-6">
            <button type="button" 
                    onclick="if (typeof jQuery !== 'undefined' && jQuery('#facebox').is(':visible')) { jQuery(document).trigger('close.facebox'); } else if (window.history.length > 1) { window.history.back(); } else { window.location.href='paybill.php'; }" 
                    class="px-4 py-2.5 text-xs font-semibold text-slate-300 hover:text-white bg-slate-700 hover:bg-slate-600 rounded-xl transition">
                Close
            </button>
            <button type="button" onclick="window.print()" 
                    class="px-4 py-2.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-500 rounded-xl transition shadow-md shadow-blue-600/20 active:scale-[0.98] flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Print Receipt
            </button>
        </div>

    <?php else: ?>
        <div class="p-4 bg-amber-500/10 border border-amber-500/20 text-amber-400 rounded-xl text-sm flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
            Invalid Invoice or Owner Record.
        </div>
    <?php endif; ?>
</div>

</body>
</html>