<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
$pageTitle = 'Water & Sewer Application';
include 'header.php';

$step = isset($_GET['step']) ? intval($_GET['step']) : 1;

$steps = [
    1  => 'Application Type',
    2  => 'Service Type',
    3  => 'Document Upload',
    4  => 'Identification',
    5  => 'Applicant Details',
    6  => 'Contact Information',
    7  => 'Alternate Contact',
    8  => 'Supply Details',
    9  => 'Type of Supply',
    10 => 'Property Location',
    11 => 'Confirmation'
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
  .wiz-input:hover   { border-color: #cbd5e1; }
  .wiz-input:focus   {
    background: #fff;
    border-color: #0d7d8c;
    box-shadow: 0 0 0 3px rgba(13,125,140,0.18);
  }

  .radio-card {
    border: 2px solid #e2e8f0;
    border-radius: 1rem;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
    transition: all .2s ease;
    background: #fff;
  }
  .radio-card:hover {
    border-color: #cbd5e1;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -15px rgba(15,39,68,0.15);
  }
  .radio-card:has(input:checked) {
    border-color: #0d7d8c;
    background: rgba(13,125,140,0.05);
    box-shadow: 0 0 0 3px rgba(13,125,140,0.12);
  }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
  <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

    <!-- Sidebar Stepper -->
    <aside class="md:col-span-1">
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 sticky top-6">
        <h2 class="text-lg font-bold text-slate-900 leading-snug">Water &amp; Sewer Application</h2>
        <p class="text-xs text-slate-500 mt-1 mb-6">Complete your application in a few simple steps</p>

        <ol class="relative">
          <?php foreach ($steps as $num => $label):
              $isActive = ($num == $step);
              $isDone   = ($num < $step);
          ?>
            <li class="relative flex items-start gap-3 pb-5 last:pb-0">
              <!-- connecting line -->
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
        <h2 class="text-2xl font-bold text-slate-900">Application Type</h2>
        <p class="text-sm text-slate-500 mt-1 mb-6">Select customer type</p>

        <div class="flex items-start gap-3 p-4 mb-6 rounded-xl bg-teal-50/70 ring-1 ring-teal-200/60 text-teal-900 text-sm">
          <i data-lucide="info" class="w-5 h-5 mt-0.5 shrink-0 text-teal-700"></i>
          <span>Please select the type of application that best describes your situation.</span>
        </div>

        <form action="apply_step.php" method="POST">
          <input type="hidden" name="step" value="1">

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <label class="radio-card">
              <input type="radio" name="app_type" value="individual" class="sr-only" required>
              <i data-lucide="user" class="w-10 h-10 text-slate-700 mb-3 pointer-events-none"></i>
              <span class="font-semibold text-slate-800 pointer-events-none">Individual</span>
              <span class="text-xs text-slate-500 text-center mt-1 pointer-events-none">For personal water connection</span>
            </label>
            <label class="radio-card">
              <input type="radio" name="app_type" value="business" class="sr-only" required>
              <i data-lucide="building" class="w-10 h-10 text-slate-700 mb-3 pointer-events-none"></i>
              <span class="font-semibold text-slate-800 pointer-events-none">Business</span>
              <span class="text-xs text-slate-500 text-center mt-1 pointer-events-none">For commercial water connection</span>
            </label>
          </div>

          <div class="flex justify-end">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white
                           bg-gradient-to-b from-slate-800 to-slate-900
                           shadow-lg shadow-slate-900/20 hover:-translate-y-0.5
                           active:translate-y-0 transition-all">
              Continue <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
          </div>
        </form>

      <?php elseif ($step == 2): ?>
        <h2 class="text-2xl font-bold text-slate-900">Service Type</h2>
        <p class="text-sm text-slate-500 mt-1 mb-6">What kind of service do you need?</p>

        <form action="apply_step.php" method="POST">
          <input type="hidden" name="step" value="2">

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <label class="radio-card">
              <input type="radio" name="service_type" value="water" class="sr-only" required>
              <i data-lucide="droplet" class="w-10 h-10 text-slate-700 mb-3 pointer-events-none"></i>
              <span class="font-semibold text-slate-800 pointer-events-none">Water</span>
              <span class="text-xs text-slate-500 text-center mt-1 pointer-events-none">New or additional water connection</span>
            </label>
            <label class="radio-card">
              <input type="radio" name="service_type" value="sewer" class="sr-only" required>
              <i data-lucide="waves" class="w-10 h-10 text-slate-700 mb-3 pointer-events-none"></i>
              <span class="font-semibold text-slate-800 pointer-events-none">Sewer</span>
              <span class="text-xs text-slate-500 text-center mt-1 pointer-events-none">Sewer connection application</span>
            </label>
            <label class="radio-card md:col-span-2">
              <input type="radio" name="service_type" value="both" class="sr-only" required>
              <i data-lucide="layers" class="w-10 h-10 text-slate-700 mb-3 pointer-events-none"></i>
              <span class="font-semibold text-slate-800 pointer-events-none">Water &amp; Sewer</span>
              <span class="text-xs text-slate-500 text-center mt-1 pointer-events-none">Both connections</span>
            </label>
          </div>

          <div class="flex justify-between">
            <a href="apply.php?step=1" class="inline-flex items-center px-5 py-2.5 rounded-xl font-medium text-slate-700
                                              border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition">
              Back
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white
                           bg-gradient-to-b from-slate-800 to-slate-900
                           shadow-lg shadow-slate-900/20 hover:-translate-y-0.5
                           active:translate-y-0 transition-all">
              Continue <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
          </div>
        </form>

      <?php elseif ($step == 3): ?>
        <h2 class="text-2xl font-bold text-slate-900">Upload Documents</h2>
        <p class="text-sm text-slate-500 mt-1 mb-6">Upload all required supporting documents before proceeding</p>

        <div class="flex items-start gap-3 p-4 mb-6 rounded-xl bg-teal-50/70 ring-1 ring-teal-200/60 text-teal-900 text-sm">
          <i data-lucide="info" class="w-5 h-5 mt-0.5 shrink-0 text-teal-700"></i>
          <span>All documents must be in <strong>PDF</strong> format, maximum <strong>2 MB</strong> each. All four are mandatory.</span>
        </div>

        <form action="apply_step.php" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="step" value="3">

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

          <div class="flex justify-between mt-8">
            <a href="apply.php?step=2" class="inline-flex items-center px-5 py-2.5 rounded-xl font-medium text-slate-700
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
        <h2 class="text-2xl font-bold text-slate-900">Identification</h2>
        <p class="text-sm text-slate-500 mt-1 mb-6">Provide your identification details</p>

        <form action="apply_step.php" method="POST">
          <input type="hidden" name="step" value="4">

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
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">KRA PIN</label>
              <input type="text" name="kra_pin" required class="wiz-input">
            </div>
          </div>

          <div class="flex justify-between mt-8">
            <a href="apply.php?step=3" class="inline-flex items-center px-5 py-2.5 rounded-xl font-medium text-slate-700
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
        <h2 class="text-2xl font-bold text-slate-900">Applicant Details</h2>
        <p class="text-sm text-slate-500 mt-1 mb-6">Tell us about the applicant</p>

        <form action="apply_step.php" method="POST">
          <input type="hidden" name="step" value="5">

          <div class="space-y-4 max-w-md">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Full Name</label>
              <input type="text" name="full_name" required class="wiz-input">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Date of Birth</label>
              <input type="date" name="date_of_birth" required class="wiz-input">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Gender</label>
              <select name="gender" required class="wiz-input">
                <option value="">Select</option>
                <option>Male</option>
                <option>Female</option>
                <option>Prefer not to say</option>
              </select>
            </div>
          </div>

          <div class="flex justify-between mt-8">
            <a href="apply.php?step=4" class="inline-flex items-center px-5 py-2.5 rounded-xl font-medium text-slate-700
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
        <h2 class="text-2xl font-bold text-slate-900">Contact Information</h2>
        <p class="text-sm text-slate-500 mt-1 mb-6">How can we reach you?</p>

        <form action="apply_step.php" method="POST">
          <input type="hidden" name="step" value="6">

          <div class="space-y-4 max-w-md">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
              <input type="email" name="email" required class="wiz-input">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Phone</label>
              <input type="tel" name="phone" required class="wiz-input">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Postal Address <span class="text-slate-400 font-normal">(optional)</span></label>
              <input type="text" name="postal_address" class="wiz-input">
            </div>
          </div>

          <div class="flex justify-between mt-8">
            <a href="apply.php?step=5" class="inline-flex items-center px-5 py-2.5 rounded-xl font-medium text-slate-700
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

      <?php elseif ($step == 7): ?>
        <h2 class="text-2xl font-bold text-slate-900">Alternate Contact</h2>
        <p class="text-sm text-slate-500 mt-1 mb-6">Optional alternate contact person</p>

        <form action="apply_step.php" method="POST">
          <input type="hidden" name="step" value="7">

          <div class="space-y-4 max-w-md">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Alternate Name</label>
              <input type="text" name="alt_name" class="wiz-input">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Alternate Phone</label>
              <input type="tel" name="alt_phone" class="wiz-input">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Relationship</label>
              <input type="text" name="alt_relationship" class="wiz-input">
            </div>
          </div>

          <div class="flex justify-between mt-8">
            <a href="apply.php?step=6" class="inline-flex items-center px-5 py-2.5 rounded-xl font-medium text-slate-700
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

      <?php elseif ($step == 8): ?>
        <h2 class="text-2xl font-bold text-slate-900">Supply Details</h2>
        <p class="text-sm text-slate-500 mt-1 mb-6">Details about your supply requirements</p>

        <form action="apply_step.php" method="POST">
          <input type="hidden" name="step" value="8">

          <div class="space-y-4 max-w-md">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Premises Type</label>
              <select name="premises_type" required class="wiz-input">
                <option>Residential</option>
                <option>Commercial</option>
                <option>Industrial</option>
                <option>Institutional</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Number of Units</label>
              <input type="number" name="units" required min="1" class="wiz-input">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Estimated Daily Demand (m³) <span class="text-slate-400 font-normal">(optional)</span></label>
              <input type="number" name="daily_demand" step="0.1" class="wiz-input">
            </div>
          </div>

          <div class="flex justify-between mt-8">
            <a href="apply.php?step=7" class="inline-flex items-center px-5 py-2.5 rounded-xl font-medium text-slate-700
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

      <?php elseif ($step == 9): ?>
        <h2 class="text-2xl font-bold text-slate-900">Type of Supply</h2>
        <p class="text-sm text-slate-500 mt-1 mb-6">How will the supply be metered?</p>

        <form action="apply_step.php" method="POST">
          <input type="hidden" name="step" value="9">

          <div class="space-y-4 max-w-md">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Meter Size (inches)</label>
              <select name="meter_size" required class="wiz-input">
                <option>1/2"</option>
                <option>3/4"</option>
                <option>1"</option>
                <option>1.5"</option>
                <option>2"</option>
                <option>3"</option>
                <option>4"</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Supply Category</label>
              <select name="supply_category" required class="wiz-input">
                <option>Domestic</option>
                <option>Commercial</option>
                <option>Industrial</option>
                <option>Bulk</option>
              </select>
            </div>
          </div>

          <div class="flex justify-between mt-8">
            <a href="apply.php?step=8" class="inline-flex items-center px-5 py-2.5 rounded-xl font-medium text-slate-700
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

      <?php elseif ($step == 10): ?>
        <h2 class="text-2xl font-bold text-slate-900">Property Location</h2>
        <p class="text-sm text-slate-500 mt-1 mb-6">Where is the property located?</p>

        <form action="apply_step.php" method="POST">
          <input type="hidden" name="step" value="10">

          <div class="space-y-4 max-w-md">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">County</label>
              <input type="text" name="county" value="Nairobi" required class="wiz-input">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Estate / Area</label>
              <input type="text" name="estate" required class="wiz-input">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Plot / LR Number</label>
              <input type="text" name="plot_number" required class="wiz-input">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Street / Road <span class="text-slate-400 font-normal">(optional)</span></label>
              <input type="text" name="street" class="wiz-input">
            </div>
          </div>

          <div class="flex justify-between mt-8">
            <a href="apply.php?step=9" class="inline-flex items-center px-5 py-2.5 rounded-xl font-medium text-slate-700
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
        <h2 class="text-2xl font-bold text-slate-900">Confirmation</h2>
        <p class="text-sm text-slate-500 mt-1 mb-6">Review and submit your application</p>

        <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 mb-6 text-sm text-slate-600">
          Please confirm all details you've provided across all steps are accurate. Once submitted, this application will be reviewed by NCWSC staff.
        </div>

        <form action="apply_step.php" method="POST">
          <input type="hidden" name="step" value="11">

          <label class="flex items-start gap-3 mb-6 cursor-pointer">
            <input type="checkbox" name="confirm" required
                   class="w-4 h-4 mt-0.5 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
            <span class="text-sm text-slate-700">
              I confirm that all the information provided is accurate.
            </span>
          </label>

          <div class="flex justify-between">
            <a href="apply.php?step=10" class="inline-flex items-center px-5 py-2.5 rounded-xl font-medium text-slate-700
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