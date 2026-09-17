<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'auth.php';
checkLevel([1, 2]); // Restricted to Admin & Staff

if (!isset($_SESSION['id']) && !isset($_SESSION['SESS_MEMBER_ID'])) {
    header("Location: index.php");
    exit();
}

include_once 'db.php';

// Sanitize input
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
?>

<div class="p-6 bg-slate-800 text-slate-100 rounded-2xl max-w-4xl w-full border border-slate-700 shadow-2xl relative z-50">
    <!-- Modal Header -->
    <div class="flex items-center justify-between border-b border-slate-700 pb-4 mb-4">
        <div>
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-blue-400"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                Billing History & Details
            </h3>
            <p class="text-xs text-slate-400 mt-1">
                Bill Amount = Total Consumption &times; Price per unit
            </p>
        </div>
        <button type="button" onclick="$(document).trigger('close.facebox')" class="text-slate-400 hover:text-white transition p-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg>
        </button>
    </div>

    <?php if ($id > 0): ?>
        <?php
        $stmt = mysqli_prepare($conn, "SELECT * FROM bill WHERE owners_id = ? ORDER BY id DESC");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        ?>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <!-- Table Container -->
            <div class="overflow-x-auto rounded-xl border border-slate-700/60 max-h-[60vh]">
                <table class="w-full text-left border-collapse text-xs">
                    <thead class="bg-slate-900/90 text-slate-400 uppercase tracking-wider sticky top-0 border-b border-slate-700">
                        <tr>
                            <th class="py-3 px-4">Bill ID</th>
                            <th class="py-3 px-4">Prev Read</th>
                            <th class="py-3 px-4">Pres Read</th>
                            <th class="py-3 px-4">Consumption</th>
                            <th class="py-3 px-4">Price/Unit</th>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Bill Amount</th>
                            <th class="py-3 px-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50 bg-slate-900/30 text-slate-300">
                        <?php while ($row = mysqli_fetch_assoc($result)): 
                            $prev = floatval($row['prev']);
                            $pres = floatval($row['pres']);
                            $price = floatval($row['price']);
                            $totalcons = $pres - $prev;
                            $bill = $totalcons * $price;
                        ?>
                            <tr class="hover:bg-slate-700/30 transition">
                                <td class="py-3 px-4 font-mono font-semibold text-slate-400">#<?php echo htmlspecialchars($row['id']); ?></td>
                                <td class="py-3 px-4 font-mono"><?php echo htmlspecialchars(number_format($prev, 2)); ?></td>
                                <td class="py-3 px-4 font-mono"><?php echo htmlspecialchars(number_format($pres, 2)); ?></td>
                                <td class="py-3 px-4 font-mono font-medium text-amber-400"><?php echo htmlspecialchars(number_format($totalcons, 2)); ?></td>
                                <td class="py-3 px-4 font-mono"><?php echo htmlspecialchars(number_format($price, 2)); ?></td>
                                <td class="py-3 px-4 whitespace-nowrap text-slate-400"><?php echo htmlspecialchars($row['date']); ?></td>
                                <td class="py-3 px-4 font-mono font-bold text-emerald-400"><?php echo htmlspecialchars(number_format($bill, 2)); ?></td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <a rel="facebox" href="viewpayment.php?id=<?php echo urlencode($row['id']); ?>" 
                                           class="p-1.5 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded-lg transition inline-flex items-center gap-1 font-semibold" title="View Payment">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            View
                                        </a>
                                        <a rel="facebox" href="delbill.php?id=<?php echo urlencode($row['id']); ?>" 
                                           class="p-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg transition inline-flex items-center gap-1 font-semibold" title="Delete Bill">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                            Del
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-8 text-center bg-slate-900/40 border border-slate-700/50 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8 text-slate-500 mx-auto mb-2"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                <p class="text-sm text-slate-400">No billing history found for this account.</p>
            </div>
        <?php endif; ?>

        <?php mysqli_stmt_close($stmt); ?>

    <?php else: ?>
        <div class="p-4 bg-amber-500/10 border border-amber-500/20 text-amber-400 rounded-xl text-sm flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
            Invalid Owner ID provided.
        </div>
    <?php endif; ?>

    <!-- Modal Footer -->
    <div class="pt-4 flex items-center justify-end border-t border-slate-700/60 mt-6">
        <button type="button" onclick="$(document).trigger('close.facebox')" 
                class="px-4 py-2.5 text-xs font-semibold text-slate-300 hover:text-white bg-slate-700 hover:bg-slate-600 rounded-xl transition">
            Close
        </button>
    </div>
</div>