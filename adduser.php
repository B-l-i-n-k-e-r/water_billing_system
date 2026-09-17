<?php
include_once 'auth.php';
checkLevel([1]); // Restricted to Admin
include_once 'db.php';
?>

<div class="p-6 bg-slate-800 text-slate-100 rounded-2xl max-w-md w-full border border-slate-700 shadow-2xl relative z-50">
    <!-- Modal Header -->
    <div class="flex items-center justify-between border-b border-slate-700 pb-4 mb-6">
        <h3 class="text-lg font-bold text-white flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-blue-400"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
            Add New User
        </h3>
        <button type="button" onclick="$(document).trigger('close.facebox')" class="text-slate-400 hover:text-white transition p-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg>
        </button>
    </div>

    <!-- Form -->
    <form method="post" action="useradd.php" class="space-y-4">
        <div>
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                Username
            </label>
            <input type="text" name="username" required placeholder="e.g. jdoe"
                   class="w-full px-3.5 py-2.5 bg-slate-900/90 border border-slate-700 rounded-xl text-white text-sm placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition" />
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                Password
            </label>
            <input type="password" name="password" required placeholder="••••••••"
                   class="w-full px-3.5 py-2.5 bg-slate-900/90 border border-slate-700 rounded-xl text-white text-sm placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition" />
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                Full Name
            </label>
            <input type="text" name="name" required placeholder="e.g. John Doe"
                   class="w-full px-3.5 py-2.5 bg-slate-900/90 border border-slate-700 rounded-xl text-white text-sm placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition" />
        </div>

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-700/60 mt-6">
            <button type="button" onclick="$(document).trigger('close.facebox')" 
                    class="px-4 py-2 text-xs font-semibold text-slate-300 hover:text-white bg-slate-700 hover:bg-slate-600 rounded-xl transition">
                Cancel
            </button>
            <button type="submit" name="ok" 
                    class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-500 rounded-xl transition shadow-md shadow-blue-600/20 active:scale-[0.98]">
                Add User
            </button>
        </div>
    </form>
</div>