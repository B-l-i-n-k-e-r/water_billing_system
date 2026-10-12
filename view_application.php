<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
include_once 'db.php';
$pageTitle = 'Application Details';
include 'header.php';

$user_id = intval($_SESSION['id']);
$app_id  = intval($_GET['id'] ?? 0);

// Fetch the application — must belong to this user
$stmt = mysqli_prepare($conn,
    "SELECT * FROM applications WHERE id = ? AND user_id = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "ii", $app_id, $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$app = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$app) {
    ?>
    <div class="max-w-3xl mx-auto px-4 py-20 text-center">
      <div class="mx-auto w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
        <i data-lucide="file-x" class="w-6 h-6 text-slate-500"></i>
      </div>
      <h1 class="text-xl font-semibold text-slate-900">Application not found</h1>
      <p class="text-sm text-slate-500 mt-1">It may have been removed, or it doesn't belong to your account.</p>
      <a href="track_application.php"
         class="mt-5 inline-flex items-center gap-1.5 text-sm font-medium text-teal-700 hover:underline">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        Back to my applications
      </a>
    </div>
    <?php
    include 'footer.php';
    exit();
}

$statusColors = [
    'pending'    => 'bg-amber-50 text-amber-700 ring-amber-200/70',
    'in_review'  => 'bg-blue-50 text-blue-700 ring-blue-200/70',
    'approved'   => 'bg-emerald-50 text-emerald-700 ring-emerald-200/70',
    'rejected'   => 'bg-rose-50 text-rose-700 ring-rose-200/70',
    'completed'  => 'bg-teal-50 text-teal-700 ring-teal-200/70',
];
$statusLabels = [
    'pending'   => 'Pending review',
    'in_review' => 'In review',
    'approved'  => 'Approved',
    'rejected'  => 'Rejected',
    'completed' => 'Completed',
];

$key = strtolower($app['status'] ?? 'pending');
$statusClass = $statusColors[$key] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
$statusLabel = $statusLabels[$key] ?? ucfirst(str_replace('_', ' ', $app['status'] ?? 'Unknown'));

$created = !empty($app['created_at']) ? date('d M Y, H:i', strtotime($app['created_at'])) : '—';
$dob     = !empty($app['date_of_birth']) ? date('d M Y', strtotime($app['date_of_birth'])) : '—';

function h($v) { return htmlspecialchars($v ?? '—'); }
?>

<style>
  .detail-card { background:#fff; border:1px solid #e2e8f0; border-radius:1rem; }
  .detail-label { font-size:0.6875rem; font-weight:600; letter-spacing:0.05em; text-transform:uppercase; color:#94a3b8; }
  .detail-value { font-size:0.9375rem; font-weight:500; color:#0f172a; margin-top:0.25rem; word-break:break-word; }
</style>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between mb-8 gap-4">
    <div>
      <div class="flex items-center gap-2 mb-2">
        <a href="track_application.php"
           class="inline-flex items-center gap-1 text-xs font-medium text-slate-500 hover:text-slate-700 transition">
          <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
          Back to applications
        </a>
      </div>
      <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Application details</h1>
      <p class="text-sm text-slate-500 mt-1">
        Reference: <span class="font-semibold text-teal-700"><?= h($app['application_number']) ?></span>
      </p>
    </div>

    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold ring-1 <?= $statusClass ?> w-max">
      <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
      <?= htmlspecialchars($statusLabel) ?>
    </span>
  </div>

  <!-- Summary -->
  <section class="detail-card p-6 sm:p-7 mb-5">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-5 flex items-center gap-2">
      <i data-lucide="file-text" class="w-4 h-4"></i>
      Summary
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
      <div>
        <p class="detail-label">Application Number</p>
        <p class="detail-value"><?= h($app['application_number']) ?></p>
      </div>
      <div>
        <p class="detail-label">Date Submitted</p>
        <p class="detail-value"><?= h($created) ?></p>
      </div>
      <div>
        <p class="detail-label">Application Type</p>
        <p class="detail-value capitalize"><?= h($app['application_type']) ?></p>
      </div>
      <div>
        <p class="detail-label">Service Type</p>
        <p class="detail-value capitalize"><?= h($app['service_type']) ?></p>
      </div>
    </div>
  </section>

  <!-- Identification -->
  <section class="detail-card p-6 sm:p-7 mb-5">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-5 flex items-center gap-2">
      <i data-lucide="id-card" class="w-4 h-4"></i>
      Identification
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
      <div><p class="detail-label">ID Number</p><p class="detail-value"><?= h($app['id_number']) ?></p></div>
      <div><p class="detail-label">ID Type</p><p class="detail-value"><?= h($app['id_type']) ?></p></div>
      <div><p class="detail-label">KRA PIN</p><p class="detail-value"><?= h($app['kra_pin']) ?></p></div>
    </div>
  </section>

  <!-- Applicant Details -->
  <section class="detail-card p-6 sm:p-7 mb-5">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-5 flex items-center gap-2">
      <i data-lucide="user" class="w-4 h-4"></i>
      Applicant details
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
      <div><p class="detail-label">Full Name</p><p class="detail-value"><?= h($app['full_name']) ?></p></div>
      <div><p class="detail-label">Date of Birth</p><p class="detail-value"><?= h($dob) ?></p></div>
      <div><p class="detail-label">Gender</p><p class="detail-value"><?= h($app['gender']) ?></p></div>
    </div>
  </section>

  <!-- Contact Information -->
  <section class="detail-card p-6 sm:p-7 mb-5">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-5 flex items-center gap-2">
      <i data-lucide="phone" class="w-4 h-4"></i>
      Contact information
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
      <div><p class="detail-label">Email</p><p class="detail-value"><?= h($app['email']) ?></p></div>
      <div><p class="detail-label">Phone</p><p class="detail-value"><?= h($app['phone']) ?></p></div>
      <div><p class="detail-label">Postal Address</p><p class="detail-value"><?= h($app['postal_address']) ?></p></div>
    </div>
  </section>

  <!-- Alternate Contact -->
  <?php if (!empty($app['alt_name']) || !empty($app['alt_phone']) || !empty($app['alt_relationship'])): ?>
  <section class="detail-card p-6 sm:p-7 mb-5">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-5 flex items-center gap-2">
      <i data-lucide="users" class="w-4 h-4"></i>
      Alternate contact
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
      <div><p class="detail-label">Name</p><p class="detail-value"><?= h($app['alt_name']) ?></p></div>
      <div><p class="detail-label">Phone</p><p class="detail-value"><?= h($app['alt_phone']) ?></p></div>
      <div><p class="detail-label">Relationship</p><p class="detail-value"><?= h($app['alt_relationship']) ?></p></div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Supply Details -->
  <section class="detail-card p-6 sm:p-7 mb-5">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-5 flex items-center gap-2">
      <i data-lucide="droplet" class="w-4 h-4"></i>
      Supply details
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
      <div><p class="detail-label">Premises Type</p><p class="detail-value"><?= h($app['premises_type']) ?></p></div>
      <div><p class="detail-label">Number of Units</p><p class="detail-value"><?= h($app['units']) ?></p></div>
      <div><p class="detail-label">Daily Demand (m³)</p><p class="detail-value"><?= h($app['daily_demand']) ?></p></div>
      <div><p class="detail-label">Meter Size</p><p class="detail-value"><?= h($app['meter_size']) ?></p></div>
      <div><p class="detail-label">Supply Category</p><p class="detail-value"><?= h($app['supply_category']) ?></p></div>
    </div>
  </section>

  <!-- Property Location -->
  <section class="detail-card p-6 sm:p-7 mb-5">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-5 flex items-center gap-2">
      <i data-lucide="map-pin" class="w-4 h-4"></i>
      Property location
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
      <div><p class="detail-label">County</p><p class="detail-value"><?= h($app['county']) ?></p></div>
      <div><p class="detail-label">Estate / Area</p><p class="detail-value"><?= h($app['estate']) ?></p></div>
      <div><p class="detail-label">Plot / LR Number</p><p class="detail-value"><?= h($app['plot_number']) ?></p></div>
      <div><p class="detail-label">Street / Road</p><p class="detail-value"><?= h($app['street']) ?></p></div>
    </div>
  </section>

  <!-- Documents -->
  <section class="detail-card p-6 sm:p-7 mb-6">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-5 flex items-center gap-2">
      <i data-lucide="paperclip" class="w-4 h-4"></i>
      Uploaded documents
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <?php
      $docs = [
          'doc_kra_pin'         => 'KRA PIN Certificate',
          'doc_nema'            => 'NEMA Certificate',
          'doc_vehicle_logbook' => 'Vehicle Logbook',
          'doc_vehicle_photo'   => 'Motor Vehicle Photo',
      ];
      foreach ($docs as $docKey => $label):
          $path = $app[$docKey] ?? null;
          $hasFile = $path && file_exists($path);
      ?>
        <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
          <div class="flex items-center gap-2.5 min-w-0">
            <i data-lucide="<?= $hasFile ? 'file-check' : 'file-x' ?>"
               class="w-4 h-4 shrink-0 <?= $hasFile ? 'text-teal-600' : 'text-slate-400' ?>"></i>
            <span class="text-sm text-slate-700 truncate"><?= htmlspecialchars($label) ?></span>
          </div>
          <?php if ($hasFile): ?>
            <a href="<?= htmlspecialchars($path) ?>" target="_blank"
               class="shrink-0 inline-flex items-center gap-1 text-xs font-semibold text-teal-700 hover:text-teal-800 hover:underline transition">
              View PDF <i data-lucide="external-link" class="w-3 h-3"></i>
            </a>
          <?php else: ?>
            <span class="shrink-0 text-[10px] font-medium uppercase tracking-wide text-slate-400">Not uploaded</span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Actions -->
  <div class="flex flex-col sm:flex-row justify-end gap-3">
    <a href="track_application.php"
       class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl font-medium text-slate-700
              bg-white border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition">
      Back to applications
    </a>
    <a href="upload_application.php?app=<?= urlencode($app['application_number']) ?>"
       class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white
              bg-gradient-to-b from-slate-800 to-slate-900
              shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all">
      <i data-lucide="upload" class="w-4 h-4"></i>
      Upload more documents
    </a>
  </div>

</div>

<?php include 'footer.php'; ?>