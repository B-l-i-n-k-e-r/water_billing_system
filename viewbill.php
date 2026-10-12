<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';
include_once 'tariff.php';
$pageTitle = 'Bill Details';
include 'admin_header.php';

$bill_id = intval($_GET['id'] ?? 0);

$stmt = mysqli_prepare($conn,
    "SELECT b.*, o.fname, o.lname, o.address, o.contact, o.email
     FROM bill b
     LEFT JOIN owners o ON o.id = b.owners_id
     WHERE b.id = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "i", $bill_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$bill = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$bill) {
    ?>
    <div class="max-w-3xl mx-auto px-4 py-20 text-center">
      <div class="mx-auto w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
        <i data-lucide="file-x" class="w-6 h-6 text-slate-500"></i>
      </div>
      <h1 class="text-xl font-semibold text-slate-900">Bill not found</h1>
      <p class="text-sm text-slate-500 mt-1">The bill you're looking for doesn't exist or was removed.</p>
      <a href="billing.php"
         class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-white
                bg-gradient-to-b from-slate-800 to-slate-900
                shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        Back to billing
      </a>
    </div>
    <?php
    include 'footer.php'; exit();
}

$consumption = (float) ($bill['consumption'] ?? ((float)$bill['pres'] - (float)$bill['prev']));
$calc = calculateBill($consumption, $conn);

$statusStyles = [
    'unpaid'  => 'bg-rose-50 text-rose-700 ring-rose-200/70',
    'partial' => 'bg-amber-50 text-amber-700 ring-amber-200/70',
    'paid'    => 'bg-emerald-50 text-emerald-700 ring-emerald-200/70',
];
$key = strtolower($bill['status'] ?? 'unpaid');
$statusClass = $statusStyles[$key] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
$statusLabel = ucfirst($key);

$statusHeaderColors = [
    'unpaid'  => 'from-rose-700 to-rose-800',
    'partial' => 'from-amber-600 to-amber-700',
    'paid'    => 'from-emerald-700 to-emerald-800',
];
$headerGradient = $statusHeaderColors[$key] ?? 'from-slate-800 to-slate-900';

$created = !empty($_GET['created']);

function h($v) { return htmlspecialchars($v ?? '—'); }
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <a href="billing.php"
     class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 transition mb-6">
    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
    Back to billing
  </a>

  <?php if ($created): ?>
    <div class="mb-6 flex items-start gap-3 p-3.5 rounded-xl bg-emerald-50 ring-1 ring-emerald-200/70 text-emerald-800 text-sm">
      <i data-lucide="check-circle" class="w-5 h-5 mt-0.5 shrink-0"></i>
      <span>Bill <strong class="font-semibold">#<?= intval($bill['id']) ?></strong> created successfully.</span>
    </div>
  <?php endif; ?>

  <!-- Invoice card -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

    <!-- Header -->
    <div class="bg-gradient-to-br <?= $headerGradient ?> px-6 sm:px-8 py-6 text-white">
      <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide opacity-80">Water Bill</p>
          <h1 class="text-2xl font-bold mt-1 tabular-nums">Bill #<?= intval($bill['id']) ?></h1>
          <p class="text-sm opacity-90 mt-1"><?= h($bill['bill_month']) ?></p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                     bg-white/15 backdrop-blur ring-1 ring-white/25 text-white
                     text-xs font-semibold uppercase tracking-wide w-max">
          <span class="w-1.5 h-1.5 rounded-full bg-current opacity-80"></span>
          <?= h($statusLabel) ?>
        </span>
      </div>
    </div>

    <!-- Customer -->
    <div class="px-6 sm:px-8 py-6 border-b border-slate-100 grid grid-cols-1 sm:grid-cols-3 gap-5">
      <div>
        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Customer</p>
        <p class="font-semibold text-slate-900 mt-1"><?= h(trim(($bill['fname'] ?? '') . ' ' . ($bill['lname'] ?? ''))) ?></p>
      </div>
      <div>
        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Contact</p>
        <p class="font-semibold text-slate-900 mt-1 tabular-nums"><?= h($bill['contact']) ?></p>
      </div>
      <div>
        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Address</p>
        <p class="font-semibold text-slate-900 mt-1"><?= h($bill['address']) ?></p>
      </div>
    </div>

    <!-- Meter readings -->
    <div class="px-6 sm:px-8 py-6 border-b border-slate-100 grid grid-cols-3 gap-5">
      <div>
        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Previous reading</p>
        <p class="font-semibold text-slate-900 mt-1 tabular-nums"><?= h($bill['prev']) ?> m³</p>
      </div>
      <div>
        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Present reading</p>
        <p class="font-semibold text-slate-900 mt-1 tabular-nums"><?= h($bill['pres']) ?> m³</p>
      </div>
      <div>
        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Consumption</p>
        <p class="font-semibold text-teal-700 mt-1 tabular-nums"><?= number_format($consumption, 2) ?> m³</p>
      </div>
    </div>

    <!-- Charge breakdown -->
    <div class="px-6 sm:px-8 py-6 border-b border-slate-100">
      <h2 class="font-semibold text-slate-900 mb-4">Charge breakdown</h2>

      <div class="overflow-x-auto -mx-6 sm:mx-0">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-100 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
              <th class="px-6 sm:px-0 py-2 text-left">Range</th>
              <th class="py-2 text-right">Units</th>
              <th class="py-2 text-right">Rate</th>
              <th class="px-6 sm:px-0 py-2 text-right">Cost</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php foreach ($calc['breakdown'] as $row): ?>
              <tr>
                <td class="px-6 sm:px-0 py-2.5 text-slate-700"><?= htmlspecialchars($row['range']) ?></td>
                <td class="py-2.5 text-right text-slate-600 tabular-nums"><?= number_format($row['units'], 2) ?></td>
                <td class="py-2.5 text-right text-slate-600 tabular-nums"><?= number_format($row['rate'], 2) ?></td>
                <td class="px-6 sm:px-0 py-2.5 text-right font-medium text-slate-900 tabular-nums"><?= number_format($row['cost'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="mt-6 space-y-2 text-sm">
        <?php
        $lines = [
            ['Water charge',  number_format($calc['water_charge'], 2), false],
            ['Sewer charge',  number_format($calc['sewer_charge'], 2), false],
            ['Meter rent',    number_format($calc['meter_rent'],   2), false],
            ['Subtotal',      number_format($calc['subtotal'],     2), true],
            ['VAT (16%)',     number_format($calc['vat'],          2), false],
        ];
        foreach ($lines as [$label, $value, $divider]):
        ?>
          <div class="flex justify-between <?= $divider ? 'border-t border-slate-100 pt-2 mt-2' : '' ?>">
            <span class="text-slate-500"><?= $label ?></span>
            <span class="font-medium text-slate-900 tabular-nums"><?= $value ?></span>
          </div>
        <?php endforeach; ?>

        <div class="flex justify-between items-center border-t-2 border-slate-200 pt-3 mt-3">
          <span class="text-base font-semibold text-slate-900">Total due</span>
          <span class="text-xl font-bold text-slate-900 tabular-nums"><?= kes($calc['total']) ?></span>
        </div>

        <div class="flex justify-between pt-3 border-t border-slate-100">
          <span class="text-slate-500">Amount paid</span>
          <span class="font-medium text-emerald-700 tabular-nums"><?= number_format((float)$bill['amount_paid'], 2) ?></span>
        </div>
        <?php $balance = $calc['total'] - (float)$bill['amount_paid']; ?>
        <div class="flex justify-between">
          <span class="text-slate-500">Balance</span>
          <span class="font-bold <?= $balance > 0 ? 'text-rose-600' : 'text-emerald-700' ?> tabular-nums">
            <?= number_format($balance, 2) ?>
          </span>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="px-6 sm:px-8 py-4 bg-slate-50/60 flex flex-wrap justify-end gap-3">
      <button onclick="window.print()"
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-medium text-slate-700
                     bg-white border border-slate-200 hover:border-slate-300 hover:bg-white transition">
        <i data-lucide="printer" class="w-4 h-4"></i>
        Print
      </button>
      <?php if (($bill['status'] ?? 'unpaid') !== 'paid'): ?>
        <a href="paybill.php?bill_id=<?= intval($bill['id']) ?>"
           class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white
                  bg-gradient-to-b from-teal-600 to-teal-700
                  shadow-lg shadow-teal-700/25 hover:-translate-y-0.5 transition-all">
          <i data-lucide="banknote" class="w-4 h-4"></i>
          Record payment
        </a>
      <?php endif; ?>
    </div>

  </div>

</div>

<?php include 'footer.php'; ?>