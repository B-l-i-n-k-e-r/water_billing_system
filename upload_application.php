<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
include_once 'db.php';
$pageTitle = 'Upload Documents';
include 'header.php';

// Fetch this user's applications for the dropdown
$user_id = intval($_SESSION['id']);
$apps = [];

$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'applications'");
if ($tableCheck && mysqli_num_rows($tableCheck) > 0) {
    $stmt = mysqli_prepare($conn,
        "SELECT application_number FROM applications WHERE user_id = ? ORDER BY created_at DESC"
    );
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($res)) {
            $apps[] = $row['application_number'];
        }
        mysqli_stmt_close($stmt);
    }
}

$preselect = $_GET['app'] ?? '';
?>

<style>
  .wiz-input {
    width: 100%;
    padding: 0.625rem 1rem;
    border-radius: 0.75rem;
    background: #fff;
    border: 1px solid #e2e8f0;
    color: #0f172a;
    outline: none;
    transition: all .15s ease;
  }
  .wiz-input:hover { border-color: #cbd5e1; }
  .wiz-input:focus {
    border-color: #0d7d8c;
    box-shadow: 0 0 0 3px rgba(13,125,140,0.18);
  }
</style>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Upload application documents</h1>
    <p class="text-sm text-slate-500 mt-1">Attach supporting documents for an existing application.</p>
  </div>

  <!-- Instructions -->
  <div class="rounded-2xl bg-teal-50/70 ring-1 ring-teal-200/60 p-5 sm:p-6 mb-6">
    <div class="flex items-center gap-3 mb-3">
      <div class="shrink-0 w-9 h-9 rounded-xl bg-teal-600/10 flex items-center justify-center">
        <i data-lucide="info" class="w-4 h-4 text-teal-700"></i>
      </div>
      <h2 class="font-semibold text-slate-900">Instructions</h2>
    </div>
    <p class="text-xs font-bold tracking-wide uppercase text-rose-600 mb-3">Read before you proceed</p>
    <ol class="list-decimal list-inside text-sm text-slate-700 space-y-1.5 leading-relaxed">
      <li>All documents must be in <strong>PDF</strong> format.</li>
      <li>Document names must contain only <strong>alphanumeric characters</strong> (no special chars like <code class="px-1 py-0.5 rounded bg-slate-100 text-xs">#*%\^</code>).</li>
      <li>Maximum upload size is <strong>2 MB</strong> per file.</li>
      <li>Upload documents to the <strong>correct fields</strong> to avoid rejection.</li>
      <li>This process is <strong class="text-rose-600">irreversible</strong> — ensure all documents are correct before submitting.</li>
      <li><strong>Track</strong> your application from the
        <a href="track_application.php" class="text-teal-700 font-medium hover:underline">Track Application</a> page.
      </li>
    </ol>
  </div>

  <!-- Required documents -->
  <div class="rounded-2xl bg-white border border-slate-200 p-5 sm:p-6 mb-6">
    <div class="flex items-center gap-3 mb-5">
      <div class="shrink-0 w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center">
        <i data-lucide="clipboard-list" class="w-4 h-4 text-slate-700"></i>
      </div>
      <h2 class="font-semibold text-slate-900">Required documents</h2>
    </div>

    <div class="space-y-5">
      <div>
        <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Individuals</h3>
        <ol class="list-decimal list-inside text-sm text-slate-700 space-y-1.5">
          <li>Photocopy of applicant's ID and KRA PIN</li>
          <li>Property location sketch map</li>
          <li>One passport size photograph</li>
          <li>Copy of Lease / Tenancy Agreement / Title Deed / Rates Demand Note</li>
          <li>Copy of landlord's ID &amp; KRA PIN (tenant applications)</li>
          <li><span class="text-rose-600 font-semibold">Passport photo is mandatory</span> — failure to upload will cause rejection</li>
        </ol>
      </div>

      <div>
        <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Companies</h3>
        <ol class="list-decimal list-inside text-sm text-slate-700 space-y-1.5">
          <li>Copy of PIN and Certificate of Incorporation</li>
          <li>Property location sketch map</li>
          <li>Copy of Lease / Tenancy Agreement / Title Deed / Rates Demand Note</li>
          <li>Copy of landlord's ID &amp; KRA PIN (tenant applications)</li>
        </ol>
      </div>

      <div>
        <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Sewer applications</h3>
        <p class="text-sm text-slate-700">Drawing showing plan, ground levels and invert levels of the proposed drain.</p>
        <p class="text-sm text-slate-700 mt-1">Borehole applicants: attach WRA approvals + NCWSC Letter of No Objection.</p>
      </div>
    </div>

    <div class="mt-5 pt-4 border-t border-slate-100 text-xs text-slate-500 flex flex-wrap gap-x-5 gap-y-2">
      <span class="flex items-center gap-1.5"><i data-lucide="phone" class="w-3.5 h-3.5"></i> +254 703 080 000 / +254 703 080 598</span>
      <span class="flex items-center gap-1.5"><i data-lucide="mail" class="w-3.5 h-3.5"></i> info@nairobiwater.co.ke</span>
    </div>
  </div>

  <!-- Select application -->
  <div class="rounded-2xl bg-white border border-slate-200 p-5 sm:p-6">
    <div class="flex items-center gap-3 mb-5">
      <div class="shrink-0 w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center">
        <i data-lucide="hash" class="w-4 h-4 text-slate-700"></i>
      </div>
      <h2 class="font-semibold text-slate-900">Select application</h2>
    </div>

    <form action="upload_application_process.php" method="POST" enctype="multipart/form-data">
      <div class="mb-5">
        <label class="block text-sm font-medium text-slate-700 mb-1.5">
          Application number
          <span class="ml-1 text-[10px] font-semibold uppercase tracking-wide text-white bg-rose-500 px-1.5 py-0.5 rounded">Required</span>
        </label>
        <select name="application_number" required class="wiz-input">
          <option value="">— Choose application number —</option>
          <?php foreach ($apps as $a): ?>
            <option value="<?= htmlspecialchars($a) ?>" <?= ($preselect === $a) ? 'selected' : '' ?>>
              <?= htmlspecialchars($a) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (empty($apps)): ?>
          <p class="text-xs text-slate-500 mt-2">
            You have no applications yet.
            <a href="apply.php" class="text-teal-700 font-medium hover:underline">Start one here.</a>
          </p>
        <?php endif; ?>
      </div>

      <div class="flex justify-end">
        <button type="submit"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white
                       bg-gradient-to-b from-slate-800 to-slate-900
                       shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all">
          Continue <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </button>
      </div>
    </form>
  </div>

</div>

<?php include 'footer.php'; ?>