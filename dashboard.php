<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';

// Staff → redirect to staff portal
$level = intval($_SESSION['userlevel'] ?? 3);
if (in_array($level, [1, 2])) {
    header("Location: admin_dashboard.php");
    exit();
}

$pageTitle = 'Dashboard';
include 'header.php';
?>

<?php
  $firstName = '';
  if (!empty($_SESSION['name'])) {
      $firstName = explode(' ', trim($_SESSION['name']))[0];
  }
  $cards = [
    [
      'icon'  => 'receipt',
      'title' => 'Self Care',
      'desc'  => 'Check your bills or download your customer statement.',
      'href'  => 'selfcare.php',
      'cta'   => 'Open self care',
    ],
    [
      'icon'  => 'droplet',
      'title' => 'Water / Sewer Application',
      'desc'  => 'Apply to become a new NCWSC customer for water or sewer service.',
      'href'  => 'apply.php',
      'cta'   => 'Start application',
    ],
    [
      'icon'  => 'wrench',
      'title' => 'Other Services',
      'desc'  => 'Access additional services such as adding a sewer connection.',
      'href'  => 'addsewer.php',
      'cta'   => 'View services',
    ],
    [
      'icon'  => 'truck',
      'title' => 'Private Exhauster Permit',
      'desc'  => 'Apply for and manage private exhauster service permits.',
      'href'  => 'exhauster.php',
      'cta'   => 'Apply for permit',
    ],

    [
    'icon'  => 'credit-card',
    'title' => 'Pay My Bill',
    'desc'  => 'Settle your outstanding bills securely via card or M-Pesa.',
    'href'  => 'pay_my_bill.php',
    'cta'   => 'Pay now',
],
  ];
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <!-- Greeting -->
  <div class="mb-10">
    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">
      <?= $firstName ? 'Hello, ' . htmlspecialchars($firstName) : 'Welcome back' ?>
    </h1>
    <p class="mt-1 text-sm text-slate-500">
      What would you like to do today? Pick a service below to get started.
    </p>
  </div>

  <!-- Service cards -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    <?php foreach ($cards as $c): ?>
      <a href="<?= htmlspecialchars($c['href']) ?>"
         class="group relative block rounded-2xl bg-white border border-slate-200 p-6 sm:p-7
                hover:border-teal-600/40 hover:shadow-xl hover:shadow-slate-900/5
                hover:-translate-y-0.5 transition-all duration-200">

        <div class="flex items-start gap-4">
          <div class="shrink-0 w-12 h-12 rounded-xl bg-slate-100 text-slate-700
                      group-hover:bg-teal-700 group-hover:text-white
                      flex items-center justify-center transition-colors duration-200">
            <i data-lucide="<?= htmlspecialchars($c['icon']) ?>" class="w-6 h-6"></i>
          </div>

          <div class="flex-1 min-w-0">
            <h2 class="text-base sm:text-lg font-semibold text-slate-900 leading-snug">
              <?= htmlspecialchars($c['title']) ?>
            </h2>
            <p class="mt-1.5 text-sm text-slate-500 leading-relaxed">
              <?= htmlspecialchars($c['desc']) ?>
            </p>

            <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-teal-700
                         group-hover:gap-2.5 transition-all">
              <?= htmlspecialchars($c['cta']) ?>
              <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </span>
          </div>
        </div>

      </a>
    <?php endforeach; ?>

  </div>

  <!-- Help / contact strip -->
  <div class="mt-12 rounded-2xl bg-slate-50 border border-slate-200 p-6 sm:p-8
              flex flex-col sm:flex-row items-start sm:items-center gap-4 justify-between">
    <div class="flex items-start gap-4">
      <div class="shrink-0 w-11 h-11 rounded-xl bg-white border border-slate-200
                  flex items-center justify-center text-teal-700">
        <i data-lucide="help-circle" class="w-5 h-5"></i>
      </div>
      <div>
        <h3 class="font-semibold text-slate-900">Need help?</h3>
        <p class="text-sm text-slate-500 mt-0.5">
          Our call center is available Mon–Fri, 7:30am – 9:30pm.
        </p>
      </div>
    </div>
    <a href="https://blinker09.co.ke" target="_blank" rel="noopener"
       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-medium text-slate-700
              bg-white border border-slate-200 hover:border-slate-300 hover:bg-white
              transition whitespace-nowrap">
      <i data-lucide="phone" class="w-4 h-4"></i>
      Contact support
    </a>
  </div>

</div>

<?php include 'footer.php'; ?>