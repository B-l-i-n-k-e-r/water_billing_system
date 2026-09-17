<?php
session_start();

include_once 'auth.php';
checkLevel([1, 2, 3]);

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

include_once 'db.php';

$owner_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

$id = $lname = $fname = $mi = $address = $contact = "";

if ($owner_id > 0) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM owners WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $owner_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($test = mysqli_fetch_assoc($result)) {
        $id      = $test['id'];
        $lname   = $test['lname'];
        $fname   = $test['fname'];
        $mi      = $test['mi'];
        $address = $test['address'];
        $contact = $test['contact'];
    } else {
        die("Error: Data not found.");
    }
    mysqli_stmt_close($stmt);
} else {
    die("Invalid owner ID.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Client</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">

<div class="p-6 bg-slate-800 text-slate-100 rounded-2xl max-w-lg w-full border border-slate-700 shadow-2xl">
    <div class="flex items-center justify-between border-b border-slate-700 pb-4 mb-6">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-blue-400"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            Edit Client Details
        </h2>
        <a href="clients.php" class="text-slate-400 hover:text-white transition p-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg>
        </a>
    </div>

    <form method="post" action="editecex.php" class="space-y-4">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>" />

        <div>
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">First Name</label>
            <input type="text" name="fname" value="<?php echo htmlspecialchars($fname); ?>" required 
                   class="w-full px-3.5 py-2 bg-slate-900/80 border border-slate-700 rounded-lg text-white text-sm focus:outline-none focus:border-blue-500 transition" />
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Last Name</label>
            <input type="text" name="lname" value="<?php echo htmlspecialchars($lname); ?>" required 
                   class="w-full px-3.5 py-2 bg-slate-900/80 border border-slate-700 rounded-lg text-white text-sm focus:outline-none focus:border-blue-500 transition" />
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Meter Number / M.I.</label>
            <input type="text" name="mi" value="<?php echo htmlspecialchars($mi); ?>" required 
                   class="w-full px-3.5 py-2 bg-slate-900/80 border border-slate-700 rounded-lg text-white text-sm focus:outline-none focus:border-blue-500 transition" />
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Address</label>
            <input type="text" name="address" value="<?php echo htmlspecialchars($address); ?>" required 
                   class="w-full px-3.5 py-2 bg-slate-900/80 border border-slate-700 rounded-lg text-white text-sm focus:outline-none focus:border-blue-500 transition" />
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Contact</label>
            <input type="text" name="contact" value="<?php echo htmlspecialchars($contact); ?>" required 
                   class="w-full px-3.5 py-2 bg-slate-900/80 border border-slate-700 rounded-lg text-white text-sm focus:outline-none focus:border-blue-500 transition" />
        </div>

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-700/60 mt-6">
            <a href="clients.php" 
               class="px-4 py-2 text-xs font-semibold text-slate-300 hover:text-white bg-slate-700 hover:bg-slate-600 rounded-lg transition">
                Cancel
            </a>
            <button type="submit" name="save" 
                    class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-500 rounded-lg transition shadow-sm">
                Save Changes
            </button>
        </div>
    </form>
</div>

</body>
</html>