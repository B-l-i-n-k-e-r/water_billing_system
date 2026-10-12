<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';
include_once 'tariff.php';
$pageTitle = 'Create Bill';
include 'admin_header.php';

$owners = [];
$res = mysqli_query($conn, "SELECT id, fname, lname, contact FROM owners ORDER BY lname, fname");
while ($row = mysqli_fetch_assoc($res)) $owners[] = $row;

$preselect = intval($_GET['owners_id'] ?? 0);
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
  .wiz-input::placeholder { color: #94a3b8; }
  .wiz-input:hover { border-color: #cbd5e1; }
  .wiz-input:focus {
    border-color: #0d7d8c;
    box-shadow: 0 0 0 3px rgba(13,125,140,0.18);
  }
  .wiz-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #334155;
    margin-bottom: 0.375rem;
  }
</style>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <a href="billing.php"
     class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 transition mb-6">
    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
    Back to billing
  </a>

  <div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Create new bill</h1>
    <p class="text-sm text-slate-500 mt-1">Enter the meter readings — the system calculates the bill using the current tariff.</p>
  </div>

  <!-- Info banner -->
  <div class="mb-6 flex items-start gap-3 p-4 rounded-xl bg-teal-50/70 ring-1 ring-teal-200/60 text-teal-900 text-sm">
    <i data-lucide="info" class="w-5 h-5 mt-0.5 shrink-0 text-teal-700"></i>
    <span>
      Present reading must be greater than previous reading.
      VAT and sewer charges are applied automatically based on the current tariff.
    </span>
  </div>

  <form action="addbillexec.php" method="POST"
        class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8 space-y-5">

    <!-- Customer -->
    <div>
      <label for="owners_id" class="wiz-label">Customer</label>
      <select id="owners_id" name="owners_id" required class="wiz-input">
        <option value="">— Select customer —</option>
        <?php foreach ($owners as $o): ?>
          <option value="<?= intval($o['id']) ?>"
              <?= ($preselect === intval($o['id'])) ? 'selected' : '' ?>>
            <?= htmlspecialchars(($o['fname'] ?? '') . ' ' . ($o['lname'] ?? '') . ' (' . ($o['contact'] ?? '') . ')') ?>
          </option>
        <?php endforeach; ?>
      </select>
      <?php if (empty($owners)): ?>
        <p class="text-xs text-slate-500 mt-2">
          You have no customers yet.
          <a href="addclient.php" class="text-teal-700 font-medium hover:underline">Add one first.</a>
        </p>
      <?php endif; ?>
    </div>

    <!-- Meter readings -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label for="prev" class="wiz-label">Previous reading (m³)</label>
        <input id="prev" type="number" name="prev" step="0.01" required
               placeholder="0.00" inputmode="decimal" class="wiz-input">
      </div>
      <div>
        <label for="pres" class="wiz-label">Present reading (m³)</label>
        <input id="pres" type="number" name="pres" step="0.01" required
               placeholder="0.00" inputmode="decimal" class="wiz-input">
      </div>
    </div>

    <!-- Bill month -->
    <div>
      <label for="bill_month" class="wiz-label">Bill month <span class="text-slate-400 font-normal">(optional)</span></label>
      <input id="bill_month" type="text" name="bill_month"
             value="<?= htmlspecialchars(date('M Y')) ?>"
             placeholder="e.g. Oct 2026" class="wiz-input">
      <p class="text-xs text-slate-500 mt-1.5">Defaults to the current month.</p>
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-3 pt-5 border-t border-slate-100">
      <a href="billing.php"
         class="inline-flex items-center px-5 py-2.5 rounded-xl font-medium text-slate-700
                bg-white border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition">
        Cancel
      </a>
      <button type="submit"
              class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white
                     bg-gradient-to-b from-teal-600 to-teal-700
                     shadow-lg shadow-teal-700/25 hover:-translate-y-0.5 transition-all">
        <i data-lucide="receipt" class="w-4 h-4"></i>
        Generate bill
      </button>
    </div>

  </form>

</div>

<?php include 'footer.php'; ?>