<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'auth.php';
checkLevel([1, 2]); // Restricted to Admin & Staff

include_once 'db.php';

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$lname = "";
$fname = "";

if ($id > 0) {
    // If $id is the bill ID, join with owners to display client name accurately
    $stmt = mysqli_prepare($conn, "SELECT b.id, o.lname, o.fname FROM bill b LEFT JOIN owners o ON b.owners_id = o.id WHERE b.id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $id    = $row['id'];
        $lname = $row['lname'] ?? '';
        $fname = $row['fname'] ?? '';
    } else {
        // Fallback: If $id belongs directly to the owners table
        mysqli_stmt_close($stmt);
        $stmt_owner = mysqli_prepare($conn, "SELECT id, lname, fname FROM owners WHERE id = ?");
        mysqli_stmt_bind_param($stmt_owner, "i", $id);
        mysqli_stmt_execute($stmt_owner);
        $result_owner = mysqli_stmt_get_result($stmt_owner);

        if ($owner = mysqli_fetch_assoc($result_owner)) {
            $id    = $owner['id'];
            $lname = $owner['lname'];
            $fname = $owner['fname'];
        } else {
            die("<div class='p-4 bg-rose-500/10 text-rose-400 rounded-xl text-sm border border-rose-500/20'>Error: Record not found.</div>");
        }
        mysqli_stmt_close($stmt_owner);
    }
} else {
    die("<div class='p-4 bg-rose-500/10 text-rose-400 rounded-xl text-sm border border-rose-500/20'>Invalid record ID.</div>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Bill Confirmation</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">

<div class="p-6 bg-slate-800 text-slate-100 rounded-2xl max-w-md w-full border border-slate-700 shadow-2xl relative z-50 my-6 mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-slate-700 pb-4 mb-5">
        <h3 class="text-lg font-bold text-rose-400 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-rose-400"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
            Confirm Deletion
        </h3>
        <!-- Top Right X Button -->
        <button type="button" 
                onclick="if (typeof jQuery !== 'undefined' && jQuery('#facebox').is(':visible')) { jQuery(document).trigger('close.facebox'); } else if (window.history.length > 1) { window.history.back(); } else { window.location.href='paybill.php'; }" 
                class="text-slate-400 hover:text-white hover:bg-slate-700/50 p-1.5 rounded-lg transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg>
        </button>
    </div>

    <!-- Alert Box -->
    <div class="p-4 bg-rose-500/10 border border-rose-500/20 rounded-xl text-slate-200 text-sm mb-6 space-y-2">
        <div class="flex items-start gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-rose-400 shrink-0 mt-0.5"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
            <div>
                <p class="font-semibold text-rose-400">Warning: Permanent Action</p>
                <p class="text-xs text-slate-300 mt-1">
                    Are you sure you want to delete the bill record for <strong class="text-white"><?php echo htmlspecialchars(trim($fname . ' ' . $lname)); ?></strong>?
                </p>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <form action="delbillexec.php" method="post" class="space-y-4">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>" />
        
        <div class="flex items-center justify-between gap-3 pt-2 border-t border-slate-700/60">
            <!-- Cancel / Close Button -->
            <button type="button" 
                    onclick="if (typeof jQuery !== 'undefined' && jQuery('#facebox').is(':visible')) { jQuery(document).trigger('close.facebox'); } else if (window.history.length > 1) { window.history.back(); } else { window.location.href='paybill.php'; }" 
                    class="px-4 py-2.5 text-xs font-semibold text-slate-300 hover:text-white bg-slate-700 hover:bg-slate-600 rounded-xl transition w-full sm:w-auto text-center">
                Cancel
            </button>
            
            <!-- Delete Submit Button -->
            <button type="submit" 
                    name="ok" 
                    class="px-4 py-2.5 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-500 rounded-xl transition shadow-md shadow-rose-600/20 active:scale-[0.98] w-full sm:w-auto text-center flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                Delete Record
            </button>
        </div>
    </form>
</div>

</body>
</html>