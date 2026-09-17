<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'auth.php';
checkLevel([1, 2, 3]); // Admin, Cashier, and Manager access

include_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Client</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">

<!-- Modal Container -->
<div class="p-6 bg-slate-800 text-slate-100 rounded-2xl max-w-lg w-full border border-slate-700 shadow-2xl relative z-50 my-4 mx-auto">
    
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-slate-700 pb-4 mb-5">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-indigo-500/10 text-indigo-400 rounded-xl border border-indigo-500/20">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-white leading-tight">Add New Client</h3>
                <p class="text-xs text-slate-400 mt-0.5">Register client details & initial meter reading</p>
            </div>
        </div>

        <!-- Close Button -->
        <button type="button" 
                onclick="if (typeof jQuery !== 'undefined' && jQuery('#facebox').is(':visible')) { jQuery(document).trigger('close.facebox'); } else if (window.history.length > 1) { window.history.back(); } else { window.location.href='clients.php'; }" 
                class="text-slate-400 hover:text-white hover:bg-slate-700/50 p-1.5 rounded-lg transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg>
        </button>
    </div>

    <!-- Form -->
    <form method="post" action="addclient1.php" class="space-y-4">
        
        <!-- Name Fields Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1.5">First Name <span class="text-rose-400">*</span></label>
                <input type="text" 
                       name="fname" 
                       required 
                       placeholder="John" 
                       autofocus
                       class="w-full bg-slate-900 border border-slate-700 text-white text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition" />
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1.5">Last Name <span class="text-rose-400">*</span></label>
                <input type="text" 
                       name="lname" 
                       required 
                       placeholder="Doe" 
                       class="w-full bg-slate-900 border border-slate-700 text-white text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition" />
            </div>
        </div>

        <!-- Meter Number & Contact Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1.5">Meter Number <span class="text-rose-400">*</span></label>
                <input type="text" 
                       name="mi" 
                       required 
                       placeholder="MTR-0001" 
                       class="w-full bg-slate-900 border border-slate-700 text-white text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition font-mono" />
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1.5">Contact Number <span class="text-rose-400">*</span></label>
                <input type="text" 
                       name="contact" 
                       required 
                       placeholder="07XXXXXXXX" 
                       class="w-full bg-slate-900 border border-slate-700 text-white text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition font-mono" />
            </div>
        </div>

        <!-- Address -->
        <div>
            <label class="block text-xs font-medium text-slate-300 mb-1.5">Address <span class="text-rose-400">*</span></label>
            <input type="text" 
                   name="address" 
                   required 
                   placeholder="Physical location or house no." 
                   class="w-full bg-slate-900 border border-slate-700 text-white text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition" />
        </div>

        <!-- Initial Meter Reading -->
        <div>
            <label class="block text-xs font-medium text-slate-300 mb-1.5">First Meter Reading <span class="text-rose-400">*</span></label>
            <div class="relative flex items-center">
                <input type="number" 
                       step="any" 
                       name="meterReader" 
                       required 
                       placeholder="0" 
                       class="w-full bg-slate-900 border border-slate-700 text-white text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition pr-12 font-mono" />
                <span class="absolute right-3.5 text-xs text-slate-400 font-semibold">m³</span>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-700/60 mt-6">
            <button type="button" 
                    onclick="if (typeof jQuery !== 'undefined' && jQuery('#facebox').is(':visible')) { jQuery(document).trigger('close.facebox'); } else if (window.history.length > 1) { window.history.back(); } else { window.location.href='clients.php'; }" 
                    class="px-4 py-2.5 text-xs font-semibold text-slate-300 hover:text-white bg-slate-700 hover:bg-slate-600 rounded-xl transition w-full sm:w-auto text-center">
                Cancel
            </button>
            
            <button type="submit" 
                    name="add" 
                    class="px-5 py-2.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl transition shadow-md shadow-indigo-600/20 active:scale-[0.98] w-full sm:w-auto text-center flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                Add Client
            </button>
        </div>
    </form>
</div>

</body>
</html>