<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
$pageTitle = 'Exhauster Permit';
include 'header.php';

$step = isset($_GET['step']) ? intval($_GET['step']) : 1;

$steps = [
    1 => 'Documents',
    2 => 'Identification',
    3 => 'Personal Information',
    4 => 'Contact Information',
    5 => 'Alternate Contact',
    6 => 'Vehicle Details',
    7 => 'Review & Submit'
];
?>

<style>
  .wiz-input {
    width: 100%;
    padding: 0.625rem 1rem;
    border-radius: 0.75rem;
    background: rgba(255,255,255,0.7);
    border: 1px solid #e2e8f0;
    color: #0f172a;
    outline: none;
    transition: all .15s ease;
  }
  .wiz-input::placeholder { color: #94a3b8; }
  .wiz-input:hover { border-color: #cbd5e1; }
  .wiz-input:focus {
    background: #fff;
    border-color: #0d7d8c;
    box-shadow: 0 0 0 3px rgba(13,125,140,0.18);
  }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Private Exhauster Permit</h1>
    <p class="text-sm text-slate-500 mt-1">Apply for an exhauster permit for your vehicle.</p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

    <!-- Sidebar Stepper -->
    <aside class="md:col-span-1">
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 sticky top-6">
        <ol class="relative">
          <?php foreach ($steps as $num => $label):
              $isActive = ($num == $step);
              $isDone   = ($num < $step);
          ?>
            <li class="relative flex items-start gap-3 pb-5 last:pb-0">
              <?php if ($num < count($steps)): ?>
                <span class="absolute left-[15px] top-8 bottom-0 w-px
                             <?= $isDone ? 'bg-teal-600/40' : 'bg-slate-200' ?>"></span>
              <?php endif; ?>

              <span class="relative z-10 shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-colors
                <?php
                  if ($isDone)       echo 'bg-teal-600 text-white';
                  elseif ($isActive) echo 'bg-slate-900 text-white ring-4 ring-slate-900/10';
                  else               echo 'bg-slate-100 text-slate-500';
                ?>">
                <?php if ($isDone): ?>
                  <i data-lucide="check" class="w-4 h-4"></i>
                <?php else: ?>
                  <?= $num ?>
                <?php endif; ?>
              </span>

              <span class="text-sm pt-1.5 leading-tight
                <?php
                  if ($isActive) echo 'text-slate-900 font-semibold';
                  elseif ($isDone) echo 'text-slate-600';
                  else echo 'text-slate-400';
                ?>">
                <?= htmlspecialchars($label) ?>
              </span>
            </li>
          <?php endforeach; ?>
        </ol>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="md:col-span-2">
      <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200">

        <?php if ($step == 1): ?>
          <h2 class="text-2xl font-bold text-slate-900">Upload Documents</h2>
          <p class="text-sm text-slate-500 mt-1 mb-6">Upload all required supporting documents before proceeding</p>

          <div class="flex items-start gap-3 p-4 mb-6 rounded-xl bg-teal-50/70 ring-1 ring-teal-200/60 text-teal-900 text-sm">
            <i data-lucide="info" class="w-5 h-5 mt-0.5 shrink-0 text-teal-700"></i>
            <span>All documents must be in <strong>PDF</strong> format, maximum <strong>2 MB</strong> each. All four are mandatory.</span>
          </div>

          <form action="exhauster_step.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="step" value="1">

            <?php
            $docs = [
                'doc_kra_pin'         => 'KRA PIN CERTIFICATE',
                'doc_nema'            => 'NEMA CERTIFICATE',
                'doc_vehicle_logbook' => 'VEHICLE LOGBOOK',
                'doc_vehicle_photo'   => 'MOTOR VEHICLE PHOTO'
            ];
            foreach ($docs as $key => $doc): ?>
              <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 mb-4">
                <div class="flex items-center justify-between mb-3">
                  <label class="font-semibold text-slate-800 text-sm"><?= htmlspecialchars($doc) ?></label>
                  <span class="bg-rose-500 text-white text-[10px] font-semibold px-2 py-0.5 rounded-full uppercase tracking-wide">Mandatory</span>
                </div>
                <input type="file" name="<?= $key ?>" accept="application/pdf" required
                       class="block w-full text-sm text-slate-500
                              file:mr-4 file:py-2 file:px-4
                              file:rounded-lg file:border-0 file:text-sm file:font-semibold
                              file:bg-slate-900 file:text-white hover:file:bg-slate-800 cursor-pointer">
                <p class="text-xs text-slate-400 mt-1.5">PDF only · max 2 MB</p>
              </div>
            <?php endforeach; ?>

            <div class="flex justify-end mt-8">
              <button type="submit"
                      class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white
                             bg-gradient-to-b from-slate-800 to-slate-900
                             shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all">
                Continue <i data-lucide="arrow-right" class="w-4 h-4"></i>
              </button>
            </div>
          </form>

        <?php elseif ($step == 2): ?>
          <h2 class="text-2xl font-bold text-slate-900">Identification</h2>
          <p class="text-sm text-slate-500 mt-1 mb-6">Provide your identification details</p>

          <form action="exhauster_step.php" method="POST">
            <input type="hidden" name="step" value="2">

            <div class="space-y-4 max-w-md">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">ID Number</label>
                <input type="text" name="id_number" required class="wiz-input">
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">ID Type</label>
                <select name="id_type" required class="wiz-input">
                  <option>National ID</option>
                  <option>Passport</option>
                  <option>Alien ID</option>
                </select>
              </div>
            </div>

            <div class="flex justify-between mt-8">
              <a href="exhauster.php?step=1" class="inline-flex items-center px-5 py-2.5 rounded-xl font-medium text-slate-700
                                                   border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition">
                Back
              </a>
              <button type="submit"
                      class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white
                             bg-gradient-to-b from-slate-800 to-slate-900
                             shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all">
                Continue <i data-lucide="arrow-right" class="w-4 h-4"></i>
              </button>
            </div>
          </form>

        <?php elseif ($step == 3): ?>
          <h2 class="text-2xl font-bold text-slate-900">Personal Information</h2>
          <p class="text-sm text-slate-500 mt-1 mb-6">Tell us about yourself</p>

          <form action="exhauster_step.php" method="POST">
            <input type="hidden" name="step" value="3">

            <div class="space-y-4 max-w-md">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Full Name</label>
                <input type="text" name="full_name" required class="wiz-input">
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Date of Birth</label>
                <input type="date" name="date_of_birth" required class="wiz-input">
              </div>
            </div>

            <div class="flex justify-between mt-8">
              <a href="exhauster.php?step=2" class="inline-flex items-center px-5 py-2.5 rounded-xl font-medium text-slate-700
                                                   border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition">
                Back
              </a>
              <button type="submit"
                      class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white
                             bg-gradient-to-b from-slate-800 to-slate-900
                             shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all">
                Continue <i data-lucide="arrow-right" class="w-4 h-4"></i>
              </button>
            </div>
          </form>

        <?php elseif ($step == 4): ?>
          <h2 class="text-2xl font-bold text-slate-900">Contact Information</h2>
          <p class="text-sm text-slate-500 mt-1 mb-6">How can we reach you?</p>

          <form action="exhauster_step.php" method="POST">
            <input type="hidden" name="step" value="4">

            <div class="space-y-4 max-w-md">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                <input type="email" name="email" required class="wiz-input">
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Phone</label>
                <input type="tel" name="phone" required class="wiz-input">
              </div>
            </div>

            <div class="flex justify-between mt-8">
              <a href="exhauster.php?step=3" class="inline-flex items-center px-5 py-2.5 rounded-xl font-medium text-slate-700
                                                   border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition">
                Back
              </a>
              <button type="submit"
                      class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white
                             bg-gradient-to-b from-slate-800 to-slate-900
                             shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all">
                Continue <i data-lucide="arrow-right" class="w-4 h-4"></i>
              </button>
            </div>
          </form>

        <?php elseif ($step == 5): ?>
          <h2 class="text-2xl font-bold text-slate-900">Alternate Contact</h2>
          <p class="text-sm text-slate-500 mt-1 mb-6">Optional alternate contact person</p>

          <form action="exhauster_step.php" method="POST">
            <input type="hidden" name="step" value="5">

            <div class="space-y-4 max-w-md">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Alternate Name</label>
                <input type="text" name="alt_name" class="wiz-input">
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Alternate Phone</label>
                <input type="tel" name="alt_phone" class="wiz-input">
              </div>
            </div>

            <div class="flex justify-between mt-8">
              <a href="exhauster.php?step=4" class="inline-flex items-center px-5 py-2.5 rounded-xl font-medium text-slate-700
                                                   border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition">
                Back
              </a>
              <button type="submit"
                      class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white
                             bg-gradient-to-b from-slate-800 to-slate-900
                             shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all">
                Continue <i data-lucide="arrow-right" class="w-4 h-4"></i>
              </button>
            </div>
          </form>

        <?php elseif ($step == 6): ?>
          <h2 class="text-2xl font-bold text-slate-900">Vehicle Details</h2>
          <p class="text-sm text-slate-500 mt-1 mb-6">Details of the exhauster vehicle</p>

          <form action="exhauster_step.php" method="POST">
            <input type="hidden" name="step" value="6">

            <div class="space-y-4 max-w-md">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Vehicle Registration</label>
                <input type="text" name="vehicle_registration" required placeholder="e.g. KDA 123X" class="wiz-input">
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Vehicle Capacity (Litres)</label>
                <input type="number" name="vehicle_capacity" required class="wiz-input">
              </div>
            </div>

            <div class="flex justify-between mt-8">
              <a href="exhauster.php?step=5" class="inline-flex items-center px-5 py-2.5 rounded-xl font-medium text-slate-700
                                                   border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition">
                Back
              </a>
              <button type="submit"
                      class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white
                             bg-gradient-to-b from-slate-800 to-slate-900
                             shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all">
                Continue <i data-lucide="arrow-right" class="w-4 h-4"></i>
              </button>
            </div>
          </form>

        <?php else: ?>
          <h2 class="text-2xl font-bold text-slate-900">Review &amp; Submit</h2>
          <p class="text-sm text-slate-500 mt-1 mb-6">Please review your application before submitting</p>

          <?php $d = $_SESSION['exhauster_wizard'] ?? []; ?>

          <dl class="rounded-2xl bg-slate-50 border border-slate-200 divide-y divide-slate-200 mb-6">
            <div class="grid grid-cols-3 gap-4 px-5 py-3 text-sm">
              <dt class="text-slate-500">ID Number</dt>
              <dd class="col-span-2 font-medium text-slate-900"><?= htmlspecialchars($d['id_number'] ?? '—') ?></dd>
            </div>
            <div class="grid grid-cols-3 gap-4 px-5 py-3 text-sm">
              <dt class="text-slate-500">Full Name</dt>
              <dd class="col-span-2 font-medium text-slate-900"><?= htmlspecialchars($d['full_name'] ?? '—') ?></dd>
            </div>
            <div class="grid grid-cols-3 gap-4 px-5 py-3 text-sm">
              <dt class="text-slate-500">Email</dt>
              <dd class="col-span-2 font-medium text-slate-900"><?= htmlspecialchars($d['email'] ?? '—') ?></dd>
            </div>
            <div class="grid grid-cols-3 gap-4 px-5 py-3 text-sm">
              <dt class="text-slate-500">Phone</dt>
              <dd class="col-span-2 font-medium text-slate-900"><?= htmlspecialchars($d['phone'] ?? '—') ?></dd>
            </div>
            <div class="grid grid-cols-3 gap-4 px-5 py-3 text-sm">
              <dt class="text-slate-500">Vehicle</dt>
              <dd class="col-span-2 font-medium text-slate-900">
                <?= htmlspecialchars($d['vehicle_registration'] ?? '—') ?>
                <?php if (!empty($d['vehicle_capacity'])): ?>
                  <span class="text-slate-500 font-normal">(<?= htmlspecialchars($d['vehicle_capacity']) ?> L)</span>
                <?php endif; ?>
              </dd>
            </div>
          </dl>

          <form action="exhauster_step.php" method="POST">
            <input type="hidden" name="step" value="7">

            <label class="flex items-start gap-3 mb-6 cursor-pointer">
              <input type="checkbox" name="confirm" required
                     class="w-4 h-4 mt-0.5 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
              <span class="text-sm text-slate-700">
                I confirm that all the information provided is accurate.
              </span>
            </label>

            <div class="flex justify-between">
              <a href="exhauster.php?step=6" class="inline-flex items-center px-5 py-2.5 rounded-xl font-medium text-slate-700
                                                   border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition">
                Back
              </a>
              <button type="submit"
                      class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white
                             bg-gradient-to-b from-teal-600 to-teal-700
                             shadow-lg shadow-teal-700/25 hover:-translate-y-0.5 transition-all">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                Submit Application
              </button>
            </div>
          </form>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>