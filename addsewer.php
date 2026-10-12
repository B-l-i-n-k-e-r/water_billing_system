<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
$pageTitle = 'Add Sewer Connection';
include 'header.php';
?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 sm:p-10">

    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-slate-800 to-teal-700
                flex items-center justify-center shadow-lg shadow-slate-900/20 mb-5">
      <i data-lucide="waves" class="w-6 h-6 text-white"></i>
    </div>

    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Add sewer connection</h1>
    <p class="text-sm text-slate-500 mt-2 leading-relaxed">
      Add a sewer connection to an existing <strong class="text-slate-700">water-only</strong> account installation.
    </p>

    <div class="mt-6 flex items-start gap-3 p-4 rounded-xl bg-teal-50/70 ring-1 ring-teal-200/60 text-teal-900 text-sm">
      <i data-lucide="info" class="w-5 h-5 mt-0.5 shrink-0 text-teal-700"></i>
      <span>You'll need your existing account number and a scanned copy of the sewer connection drawing (if applicable).</span>
    </div>

    <div class="mt-8 flex flex-wrap gap-3">
      <a href="addsewer_form.php"
         class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white
                bg-gradient-to-b from-slate-800 to-slate-900
                shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all">
        Start application <i data-lucide="arrow-right" class="w-4 h-4"></i>
      </a>
      <a href="dashboard.php"
         class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-medium text-slate-700
                bg-white border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition">
        Cancel
      </a>
    </div>

  </div>

</div>

<?php include 'footer.php'; ?>