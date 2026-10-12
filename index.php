<?php
$pageTitle = 'Nairobi City Water & Sewerage Company';
include 'header.php';
?>

<style>
  .lp-navy   { color: #0f2744; }
  .lp-bg-navy{ background-color: #0f2744; }
  .lp-teal   { color: #0d7d8c; }
  .lp-bg-teal{ background-color: #0d7d8c; }
  .lp-slate  { color: #475569; }
  .lp-hero-bg {
    background:
      radial-gradient(1000px 500px at 80% -10%, rgba(13,125,140,0.18), transparent 60%),
      radial-gradient(800px 400px at -10% 20%, rgba(15,39,68,0.15), transparent 60%),
      linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
  }
  .lp-card {
    background:#fff; border:1px solid #e2e8f0; border-radius:1rem;
    transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
  }
  .lp-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 18px 40px -18px rgba(15,39,68,0.25);
    border-color:#cbd5e1;
  }
  .lp-fade { animation: lpRise .6s cubic-bezier(.2,.8,.2,1) both; }
  .lp-fade.d1 { animation-delay:.08s; }
  .lp-fade.d2 { animation-delay:.16s; }
  .lp-fade.d3 { animation-delay:.24s; }
  @keyframes lpRise {
    from { opacity:0; transform: translateY(14px); }
    to   { opacity:1; transform: translateY(0); }
  }
</style>

<!-- ============ HERO ============ -->
<section class="lp-hero-bg">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28">
    <div class="grid lg:grid-cols-2 gap-12 items-center">

      <div class="lp-fade">
        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium
                     bg-white border border-slate-200 lp-slate">
          <span class="w-1.5 h-1.5 rounded-full bg-teal-600"></span>
          Official NCWSC Online Portal
        </span>

        <h1 class="mt-5 text-4xl md:text-5xl font-extrabold tracking-tight lp-navy leading-tight">
          Water &amp; sewerage services,<br class="hidden md:block">
          <span class="lp-teal">now fully online.</span>
        </h1>

        <p class="mt-5 text-lg lp-slate max-w-xl">
          Apply for new water or sewer connections, upload documents, track your
          application, and manage exhauster permits — all from one secure portal.
        </p>

        <div class="mt-8 flex flex-wrap gap-3">
          <a href="register.php"
             class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold text-white
                    bg-slate-900 hover:bg-slate-800 transition shadow-lg shadow-slate-900/10">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            Get started
          </a>
          <a href="login.php"
             class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold
                    lp-navy bg-white border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition">
            <i data-lucide="log-in" class="w-4 h-4"></i>
            Sign in
          </a>
        </div>

        <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm lp-slate">
          <span class="flex items-center gap-2"><i data-lucide="shield-check" class="w-4 h-4 lp-teal"></i> Secure &amp; verified</span>
          <span class="flex items-center gap-2"><i data-lucide="clock" class="w-4 h-4 lp-teal"></i> 24/7 access</span>
          <span class="flex items-center gap-2"><i data-lucide="smartphone" class="w-4 h-4 lp-teal"></i> Mobile friendly</span>
        </div>
      </div>

      <!-- Hero card — Quick Actions -->
      <div class="lp-fade d1">
        <div class="lp-card p-6 md:p-8 shadow-xl shadow-slate-900/5">
          <div class="flex items-center justify-between mb-6">
            <h3 class="font-semibold lp-navy">Quick actions</h3>
            <span class="text-xs lp-slate">Most used</span>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <?php
              $actions = [
                ['icon'=>'droplet',   'label'=>'Apply for water',   'href'=>'login.php'],
                ['icon'=>'wrench',    'label'=>'Sewer connection',  'href'=>'login.php'],
                ['icon'=>'upload',    'label'=>'Upload documents',  'href'=>'login.php'],
                ['icon'=>'search',    'label'=>'Track application', 'href'=>'login.php'],
                ['icon'=>'truck',     'label'=>'Exhauster permit',  'href'=>'login.php'],
                ['icon'=>'file-text', 'label'=>'My requests',       'href'=>'login.php'],
              ];
              foreach ($actions as $a): ?>
              <a href="<?= htmlspecialchars($a['href']) ?>"
                 class="group flex items-center gap-3 p-3 rounded-xl border border-slate-200
                        hover:border-teal-600/40 hover:bg-teal-50/50 transition">
                <span class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700
                             group-hover:bg-teal-700 group-hover:text-white
                             flex items-center justify-center transition-colors duration-200">
                  <i data-lucide="<?= htmlspecialchars($a['icon']) ?>" class="w-4 h-4"></i>
                </span>
                <span class="text-sm font-medium text-slate-800"><?= htmlspecialchars($a['label']) ?></span>
              </a>
            <?php endforeach; ?>
          </div>

          <p class="mt-5 text-xs lp-slate">
            Sign in to use these services. Don't have an account?
            <a href="register.php" class="lp-teal font-medium hover:underline">Register here</a>.
          </p>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ============ SERVICES ============ -->
<section class="bg-white py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-2xl mx-auto mb-14">
      <p class="text-xs font-semibold tracking-widest lp-teal uppercase">Our services</p>
      <h2 class="mt-2 text-3xl md:text-4xl font-bold lp-navy">Everything you need, in one place</h2>
      <p class="mt-3 lp-slate">From new connections to permit renewals, the portal streamlines
        every step of your interaction with NCWSC.</p>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
      <?php
        $services = [
          ['icon'=>'droplets', 'title'=>'Water Supply',
           'text'=>'Apply for new water connections, report supply issues, and manage your account details online.'],
          ['icon'=>'waves',    'title'=>'Sewerage Services',
           'text'=>'Request sewer connections, book exhauster services, and track the status of every request in real time.'],
          ['icon'=>'headset',  'title'=>'Customer Support',
           'text'=>'Access FAQs, the customer charter, and reach our call center — all without leaving the portal.'],
        ];
        foreach ($services as $i => $s): ?>
        <div class="lp-card lp-fade d<?= $i+1 ?> p-7">
          <div class="w-12 h-12 rounded-xl bg-slate-900 flex items-center justify-center mb-5">
            <i data-lucide="<?= htmlspecialchars($s['icon']) ?>" class="w-6 h-6 text-white"></i>
          </div>
          <h3 class="text-lg font-semibold lp-navy"><?= htmlspecialchars($s['title']) ?></h3>
          <p class="mt-2 text-sm lp-slate leading-relaxed"><?= htmlspecialchars($s['text']) ?></p>
          <a href="register.php" class="mt-5 inline-flex items-center gap-1 text-sm font-medium lp-teal hover:gap-2 transition-all">
            Learn more <i data-lucide="arrow-right" class="w-4 h-4"></i>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ STATS ============ -->
<section class="bg-slate-900 text-white py-16">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
      <?php
        $stats = [
          ['n'=>'500K+', 'l'=>'Customers served'],
          ['n'=>'24/7',  'l'=>'Online services'],
          ['n'=>'48hrs', 'l'=>'Avg. application review'],
          ['n'=>'100%',  'l'=>'Digital tracking'],
        ];
        foreach ($stats as $s): ?>
        <div>
          <div class="text-3xl md:text-4xl font-extrabold tracking-tight"><?= htmlspecialchars($s['n']) ?></div>
          <div class="mt-2 text-sm text-slate-300"><?= htmlspecialchars($s['l']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ ABOUT ============ -->
<section class="bg-white py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid lg:grid-cols-2 gap-14 items-center">
      <div class="relative">
        <div class="aspect-[4/3] rounded-2xl overflow-hidden bg-gradient-to-br
                    from-slate-100 to-slate-200 ring-1 ring-slate-200 flex items-center justify-center">
          <i data-lucide="image" class="w-16 h-16 text-slate-300"></i>
        </div>
        <div class="hidden md:block absolute -bottom-6 -right-6 w-40 h-40 rounded-2xl
                    bg-teal-500/10 border border-teal-200/50 -z-0"></div>
      </div>

      <div>
        <p class="text-xs font-semibold tracking-widest lp-teal uppercase">About NCWSC</p>
        <h2 class="mt-2 text-3xl md:text-4xl font-bold lp-navy">
          Committed to reliable water &amp; sanitation for Nairobi
        </h2>
        <p class="mt-4 lp-slate leading-relaxed">
          Nairobi City Water and Sewerage Company is dedicated to providing safe,
          reliable, and affordable water and sewerage services to residents and
          businesses across the county.
        </p>
        <ul class="mt-6 space-y-3">
          <?php
            $points = [
              'Streamlined digital applications — no queues.',
              'Real-time tracking of every request.',
              'Transparent fees, materials, and timelines.',
              'Dedicated customer support Mon–Fri, 7:30am–9:30pm.',
            ];
            foreach ($points as $p): ?>
            <li class="flex items-start gap-3">
              <span class="w-5 h-5 rounded-full bg-teal-500/10 flex items-center justify-center shrink-0 mt-0.5">
                <i data-lucide="check" class="w-3 h-3 lp-teal"></i>
              </span>
              <span class="text-sm lp-slate"><?= htmlspecialchars($p) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
        <a href="#" class="mt-8 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                          lp-navy bg-white border border-slate-200 hover:bg-slate-50 transition font-medium">
          Read our story <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ============ NEWS ============ -->
<section class="bg-slate-50 py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
      <div>
        <p class="text-xs font-semibold tracking-widest lp-teal uppercase">Updates</p>
        <h2 class="mt-2 text-3xl md:text-4xl font-bold lp-navy">Latest news &amp; notices</h2>
      </div>
      <a href="#" class="inline-flex items-center gap-2 text-sm font-medium lp-teal hover:gap-3 transition-all">
        View all updates <i data-lucide="arrow-right" class="w-4 h-4"></i>
      </a>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
      <?php
        $news = [
          ['tag'=>'Notice',   'date'=>'Feb 12, 2026', 'title'=>'Planned water interruption in Westlands',
           'text'=>'Scheduled maintenance will affect supply in select areas on Saturday.'],
          ['tag'=>'Update',   'date'=>'Feb 08, 2026', 'title'=>'New online exhauster permit system live',
           'text'=>'Apply and renew exhauster permits entirely online — faster approvals.'],
          ['tag'=>'Tender',   'date'=>'Feb 03, 2026', 'title'=>'Tender: Supply of water meters',
           'text'=>'NCWSC invites qualified suppliers to submit bids by end of month.'],
        ];
        foreach ($news as $n): ?>
        <article class="lp-card p-6">
          <div class="flex items-center gap-3 text-xs mb-3">
            <span class="px-2.5 py-0.5 rounded-full bg-teal-50 lp-teal font-medium"><?= htmlspecialchars($n['tag']) ?></span>
            <span class="lp-slate"><?= htmlspecialchars($n['date']) ?></span>
          </div>
          <h3 class="font-semibold lp-navy leading-snug"><?= htmlspecialchars($n['title']) ?></h3>
          <p class="mt-2 text-sm lp-slate"><?= htmlspecialchars($n['text']) ?></p>
          <a href="#" class="mt-4 inline-flex items-center gap-1 text-sm font-medium lp-teal hover:gap-2 transition-all">
            Read more <i data-lucide="arrow-right" class="w-4 h-4"></i>
          </a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ CTA BANNER ============ -->
<section class="py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="relative overflow-hidden rounded-3xl bg-slate-900 p-10 md:p-14">
      <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-teal-400/10 blur-2xl"></div>
      <div class="absolute -bottom-24 -left-24 w-72 h-72 rounded-full bg-slate-400/10 blur-2xl"></div>

      <div class="relative grid md:grid-cols-2 gap-8 items-center">
        <div>
          <h2 class="text-3xl md:text-4xl font-bold text-white leading-tight">
            Ready to get started?
          </h2>
          <p class="mt-3 text-slate-300 max-w-md">
            Create an account in minutes and manage all your NCWSC services online.
          </p>
        </div>
        <div class="flex flex-wrap md:justify-end gap-3">
          <a href="register.php"
             class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold
                    bg-white lp-navy hover:bg-slate-100 transition">
            <i data-lucide="user-plus" class="w-4 h-4"></i> Create account
          </a>
          <a href="login.php"
             class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold
                    text-white border border-white/30 hover:bg-white/10 transition">
            <i data-lucide="log-in" class="w-4 h-4"></i> Sign in
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>