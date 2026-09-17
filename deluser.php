<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'auth.php';
checkLevel([1]); // Restricted to Admin

if (!isset($_SESSION['id']) && !isset($_SESSION['SESS_MEMBER_ID'])) {
    header("Location: index.php");
    exit();
}

include_once 'db.php';

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$username = "";

if ($id > 0) {
    // Fetch user details safely
    $stmt = mysqli_prepare($conn, "SELECT id, username FROM user WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($test = mysqli_fetch_assoc($result)) {
        $id       = $test['id'];
        $username = $test['username'];
    } else {
        die("<div class='p-4 text-red-400 bg-slate-900 rounded-xl'>Error: User not found.</div>");
    }
    mysqli_stmt_close($stmt);
} else {
    die("<div class='p-4 text-red-400 bg-slate-900 rounded-xl'>Invalid User ID.</div>");
}
?>

<div class="p-6 bg-slate-800 text-slate-100 rounded-2xl max-w-md w-full border border-slate-700 shadow-2xl relative z-50">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-slate-700 pb-4 mb-6">
        <h3 class="text-lg font-bold text-red-400 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-red-400"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
            Confirm Delete
        </h3>
        <button type="button" onclick="$(document).trigger('close.facebox')" class="text-slate-400 hover:text-white transition p-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg>
        </button>
    </div>

    <!-- Body Content -->
    <div class="space-y-4">
        <div class="p-4 bg-slate-900/60 border border-slate-700/60 rounded-xl flex items-start gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-amber-400 shrink-0 mt-0.5"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
            <p class="text-sm text-slate-300 leading-relaxed">
                Are you sure you want to delete the user <strong class="text-white font-semibold"><?php echo htmlspecialchars($username); ?></strong>? This action cannot be undone.
            </p>
        </div>

        <form action="deluserexec.php" method="post" class="pt-4 flex items-center justify-end gap-3 border-t border-slate-700/60 mt-6">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>" />
            <button type="button" onclick="$(document).trigger('close.facebox')" 
                    class="px-4 py-2.5 text-xs font-semibold text-slate-300 hover:text-white bg-slate-700 hover:bg-slate-600 rounded-xl transition">
                Cancel
            </button>
            <button type="submit" name="ok" 
                    class="px-4 py-2.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-500 rounded-xl transition shadow-md shadow-red-600/20 active:scale-[0.98]">
                Delete User
            </button>
        </form>
    </div>
</div>