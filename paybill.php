<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';
include_once 'tariff.php';
$pageTitle = 'Record Payment';
include 'admin_header.php';

$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bill_id      = intval($_POST['bill_id'] ?? 0);
    $amount       = (float) ($_POST['amount'] ?? 0);
    $method       = trim($_POST['method'] ?? 'Cash');
    $reference    = trim($_POST['reference'] ?? '');
    $payment_date = trim($_POST['payment_date'] ?? date('Y-m-d'));

    if ($bill_id <= 0 || $amount <= 0) {
        $error = 'Invalid bill or amount.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT * FROM bill WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $bill_id);
        mysqli_stmt_execute($stmt); $res = mysqli_stmt_get_result($stmt);
        $bill = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        if (!$bill) {
            $error = 'Bill not found.';
        } else {
            $existingPaid = (float) ($bill['amount_paid'] ?? 0);
            $billAmount   = (float) ($bill['amount'] ?? $bill['price'] ?? 0);
            $newPaid      = $existingPaid + $amount;
            $balance      = max(0, $billAmount - $newPaid);

            if ($newPaid >= $billAmount) $newStatus = 'paid';
            elseif ($newPaid > 0)       $newStatus = 'partial';
            else                        $newStatus = 'unpaid';

            $upd = mysqli_prepare($conn, "UPDATE bill SET amount_paid = ?, status = ? WHERE id = ?");
            mysqli_stmt_bind_param($upd, "dsi", $newPaid, $newStatus, $bill_id);

            if (mysqli_stmt_execute($upd)) {
                mysqli_stmt_close($upd);

                $txn = mysqli_prepare($conn,
                    "INSERT INTO transactions (account_number, transaction_date, description, amount, balance_after)
                     VALUES (?, ?, ?, ?, ?)"
                );
                if ($txn) {
                    $account   = 'OWNER-' . intval($bill['owners_id']);
                    $desc      = 'Payment via ' . $method . ($reference ? " ({$reference})" : '') . ' — Bill #' . $bill_id;
                    $negAmount = -1 * $amount;
                    mysqli_stmt_bind_param($txn, "sssdd", $account, $payment_date, $desc, $negAmount, $balance);
                    mysqli_stmt_execute($txn);
                    mysqli_stmt_close($txn);
                }

                $success = "Payment of KES " . number_format($amount, 2) . " recorded. New balance: KES " . number_format($balance, 2);

                $stmt = mysqli_prepare($conn, "SELECT * FROM bill WHERE id = ? LIMIT 1");
                mysqli_stmt_bind_param($stmt, "i", $bill_id);
                mysqli_stmt_execute($stmt); $res = mysqli_stmt_get_result($stmt);
                $bill = mysqli_fetch_assoc($res);
                mysqli_stmt_close($stmt);
            } else {
                $error = 'Failed to record payment: ' . mysqli_error($conn);
                mysqli_stmt_close($upd);
            }
        }
    }
} else {
    $preload_id = intval($_GET['bill_id'] ?? 0);
}

$bills = [];
$res = mysqli_query($conn,
    "SELECT b.id, b.amount, b.amount_paid, b.status, b.bill_month, o.fname, o.lname, o.contact
     FROM bill b
     LEFT JOIN owners o ON o.id = b.owners_id
     WHERE b.status != 'paid' OR b.status IS NULL
     ORDER BY b.id DESC
     LIMIT 500"
);
while ($row = mysqli_fetch_assoc($res)) $bills[] = $row;

$preload = null;
if (isset($preload_id) && $preload_id > 0) {
    foreach ($bills as $b) {
        if (intval($b['id']) === $preload_id) { $preload = $b; break; }
    }
}
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

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <a href="viewpayment.php"
     class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 transition mb-6">
    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
    All payments
  </a>

  <div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Record payment</h1>
    <p class="text-sm text-slate-500 mt-1">Apply a payment to an outstanding bill.</p>
  </div>

  <?php if ($error): ?>
    <div class="mb-6 flex items-start gap-3 p-3.5 rounded-xl bg-rose-50 ring-1 ring-rose-200/70 text-rose-800 text-sm">
      <i data-lucide="alert-triangle" class="w-5 h-5 mt-0.5 shrink-0"></i>
      <span><?= htmlspecialchars($error) ?></span>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="mb-6 flex items-start gap-3 p-3.5 rounded-xl bg-emerald-50 ring-1 ring-emerald-200/70 text-emerald-800 text-sm">
      <i data-lucide="check-circle" class="w-5 h-5 mt-0.5 shrink-0"></i>
      <span><?= htmlspecialchars($success) ?></span>
    </div>
  <?php endif; ?>

  <form method="POST" action="paybill.php"
        class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8 space-y-5">

    <div>
      <label for="bill_id" class="wiz-label">Select bill</label>
      <select id="bill_id" name="bill_id" required class="wiz-input">
        <option value="">— Choose unpaid bill —</option>
        <?php foreach ($bills as $b):
          $bal = (float)$b['amount'] - (float)$b['amount_paid'];
        ?>
          <option value="<?= intval($b['id']) ?>"
              <?= ($preload && intval($preload['id']) === intval($b['id'])) ? 'selected' : '' ?>>
            #<?= intval($b['id']) ?> —
            <?= htmlspecialchars(($b['fname'] ?? '') . ' ' . ($b['lname'] ?? '')) ?>
            (<?= htmlspecialchars($b['contact'] ?? '') ?>) —
            Balance: <?= number_format($bal, 2) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label for="amount" class="wiz-label">Amount (KES)</label>
        <input id="amount" type="number" name="amount" step="0.01" min="0.01" required
               class="wiz-input" placeholder="0.00" inputmode="decimal">
      </div>
      <div>
        <label for="payment_date" class="wiz-label">Payment date</label>
        <input id="payment_date" type="date" name="payment_date" value="<?= date('Y-m-d') ?>"
               class="wiz-input">
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label for="method" class="wiz-label">Payment method</label>
        <select id="method" name="method" class="wiz-input">
          <option>Cash</option>
          <option>M-Pesa</option>
          <option>Bank Transfer</option>
          <option>Cheque</option>
          <option>Card</option>
        </select>
      </div>
      <div>
        <label for="reference" class="wiz-label">Reference / receipt no. <span class="text-slate-400 font-normal">(optional)</span></label>
        <input id="reference" type="text" name="reference" placeholder="e.g. QK123XYZ"
               class="wiz-input">
      </div>
    </div>

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
        <i data-lucide="banknote" class="w-4 h-4"></i>
        Record payment
      </button>
    </div>

  </form>

</div>

<?php include 'footer.php'; ?>