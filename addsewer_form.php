<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
include_once 'db.php';
$pageTitle = 'Add Sewer Connection';
include 'header.php';

$error = '';
$success = '';

// Success flash from processor
if (isset($_SESSION['sewer_request_success'])) {
    $ref = $_SESSION['sewer_request_success'];
    $success = "Sewer connection request submitted. Your reference number is <strong>"
             . htmlspecialchars($ref)
             . "</strong>. You will receive a confirmation shortly.";
    unset($_SESSION['sewer_request_success']);
}

// Error flash
if (isset($_GET['err'])) {
    if ($_GET['err'] === 'empty') {
        $error = 'Please enter your account number.';
    } elseif ($_GET['err'] === 'db') {
        $error = 'Database error. Please try again.';
    }
}
?>

<style>
  .wiz-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border-radius: 0.75rem;
    background: #fff;
    border: 1px solid #e2e8f0;
    color: #0f172a;
    outline: none;
    font-size: 1rem;
    letter-spacing: 0.05em;
    transition: all .15s ease;
  }
  .wiz-input::placeholder { color: #94a3b8; letter-spacing: normal; }
  .wiz-input:hover { border-color: #cbd5e1; }
  .wiz-input:focus {
    border-color: #0d7d8c;
    box-shadow: 0 0 0 3px rgba(13,125,140,0.18);
  }
</style>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-9">

    <!-- Quick actions row -->
    <div class="flex flex-wrap gap-2 mb-8 pb-6 border-b border-slate-100">
      <a href="addsewer_process.php"
         class="inline-flex items-center gap-1.5 text-sm font-semibold px-3.5 py-2 rounded-xl
                bg-slate-900 text-white hover:bg-slate-800 transition">
        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
        Add sewer
      </a>
      <a href="#"
         class="inline-flex items-center gap-1.5 text-sm font-medium px-3.5 py-2 rounded-xl
                bg-white text-slate-700 border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition">
        <i data-lucide="download" class="w-3.5 h-3.5"></i>
        Download application
      </a>
      <a href="upload_application.php"
         class="inline-flex items-center gap-1.5 text-sm font-medium px-3.5 py-2 rounded-xl
                bg-white text-slate-700 border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition">
        <i data-lucide="upload" class="w-3.5 h-3.5"></i>
        Upload application
      </a>
    </div>

    <div class="mb-6">
      <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-slate-800 to-teal-700
                  flex items-center justify-center shadow-md shadow-slate-900/20 mb-4">
        <i data-lucide="waves" class="w-5 h-5 text-white"></i>
      </div>
      <h1 class="text-xl sm:text-2xl font-bold text-slate-900 leading-snug">
        Please read the instructions carefully before you proceed.
      </h1>
      <p class="text-sm font-semibold text-slate-600 mt-1">Sewer Application Instructions</p>
    </div>

    <!-- Instructions -->
    <div class="rounded-2xl bg-teal-50/70 ring-1 ring-teal-200/60 p-5 mb-8">
      <ol class="list-decimal list-inside text-sm text-slate-700 space-y-3 leading-relaxed">
        <li>No application for connection shall be allowed unless drawing showing plan, ground levels and invert levels of the proposed drain have been approved and stamped with the Nairobi Water Company stamp.</li>
        <li>The company shall make all connections to the public systems and no connection shall be made until the remainder of the new drain has been completed within plot boundary and approved by the Building/Sewerage Inspector. The applicant must ensure that the excavation is completed and the sewer exposed for connection.</li>
        <li>In cases where work is required within the public Road Reserve, "Permission to Open Public Highway" must be obtained by the Drain layer from Government Road Agencies before commencement of works.</li>
        <li>The sewer line reverts to NCWSC and adjacent plots may connect without reference to you.</li>
        <li>Customers requesting a sewer connection should provide their water account or evidence of water source during the application process.</li>
        <li>For sewers requiring micro-tunneling or road opening, concrete / non-corrosive metallic materials shall be required. For sewers connected on road reserves, UPVC pipes may be used. For aerial crossing cases (e.g. across rivers/valleys), non-corrosive metallic materials are recommended.</li>
      </ol>
    </div>

    <!-- Alerts -->
    <?php if ($error): ?>
      <div class="mb-5 flex items-start gap-3 p-3.5 rounded-xl bg-rose-50 ring-1 ring-rose-200/70 text-rose-800 text-sm">
        <i data-lucide="alert-triangle" class="w-5 h-5 mt-0.5 shrink-0"></i>
        <span><?= htmlspecialchars($error) ?></span>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="mb-5 flex items-start gap-3 p-3.5 rounded-xl bg-emerald-50 ring-1 ring-emerald-200/70 text-emerald-800 text-sm">
        <i data-lucide="check-circle" class="w-5 h-5 mt-0.5 shrink-0"></i>
        <div>
          <?= $success ?>
          <a href="my_sewer_requests.php" class="font-semibold underline ml-1 whitespace-nowrap">View my requests &rarr;</a>
        </div>
      </div>
    <?php endif; ?>

    <!-- Form -->
    <form action="addsewer_process.php" method="POST" class="max-w-md mx-auto">
      <label for="account_number" class="block text-center text-sm font-semibold text-slate-700 mb-3">
        Enter account number
      </label>

      <input id="account_number" type="text" name="account_number" required
             placeholder="1234567"
             pattern="\d{6,10}" maxlength="10" inputmode="numeric"
             class="wiz-input text-center font-semibold mb-4">

      <button type="submit"
              class="w-full py-3 rounded-xl font-semibold text-white
                     bg-gradient-to-b from-slate-800 to-slate-900
                     shadow-lg shadow-slate-900/20
                     hover:shadow-xl hover:-translate-y-0.5
                     active:translate-y-0 transition-all">
        Submit request
      </button>

      <p class="text-center text-xs text-slate-500 mt-4">
        You'll receive a reference number by email once submitted.
      </p>
    </form>

  </div>

</div>

<?php include 'footer.php'; ?>