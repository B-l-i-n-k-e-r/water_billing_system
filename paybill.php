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

// Which tab is active (cash default)
$activeTab = $_POST['payment_mode'] ?? $_GET['tab'] ?? 'cash';
if (!in_array($activeTab, ['cash', 'card', 'mpesa'])) $activeTab = 'cash';

// ---------- CASH POST HANDLER ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['payment_mode'] ?? '') === 'cash') {
    $bill_id      = intval($_POST['bill_id'] ?? 0);
    $amount       = (float) ($_POST['amount'] ?? 0);
    $reference    = trim($_POST['reference'] ?? '');
    $payment_date = trim($_POST['payment_date'] ?? date('Y-m-d'));

    if ($bill_id <= 0 || $amount <= 0) {
        $error = 'Invalid bill or amount.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT * FROM bill WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $bill_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
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
            elseif ($newPaid > 0)        $newStatus = 'partial';
            else                         $newStatus = 'unpaid';

            $upd = mysqli_prepare($conn, "UPDATE bill SET amount_paid = ?, status = ? WHERE id = ?");
            mysqli_stmt_bind_param($upd, "dsi", $newPaid, $newStatus, $bill_id);

            if (mysqli_stmt_execute($upd)) {
                mysqli_stmt_close($upd);

                $txn = mysqli_prepare($conn,
                    "INSERT INTO transactions
                     (account_number, transaction_date, description, payment_reference, payment_provider, amount, balance_after)
                     VALUES (?, ?, ?, ?, 'cash', ?, ?)"
                );
                if ($txn) {
                    $account   = 'OWNER-' . intval($bill['owners_id']);
                    $desc      = 'Cash payment' . ($reference ? " ({$reference})" : '') . ' — Bill #' . $bill_id;
                    $negAmount = -1 * $amount;
                    mysqli_stmt_bind_param($txn, "ssssdd", $account, $payment_date, $desc, $reference, $negAmount, $balance);
                    mysqli_stmt_execute($txn);
                    mysqli_stmt_close($txn);
                }

                $success = "Cash payment of KES " . number_format($amount, 2) . " recorded. New balance: KES " . number_format($balance, 2);
            } else {
                $error = 'Failed to record payment: ' . mysqli_error($conn);
                mysqli_stmt_close($upd);
            }
        }
    }
}

// ---------- Load bills for the dropdowns ----------
$bills = [];
$res = mysqli_query($conn,
    "SELECT b.id, b.amount, b.amount_paid, b.status, b.bill_month,
            o.fname, o.lname, o.contact, o.email
     FROM bill b
     LEFT JOIN owners o ON o.id = b.owners_id
     WHERE b.status != 'paid' OR b.status IS NULL
     ORDER BY b.id DESC
     LIMIT 500"
);
while ($row = mysqli_fetch_assoc($res)) $bills[] = $row;

// Preload from ?bill_id=N (used by viewbill.php "Record Payment" button)
$preload_id = intval($_GET['bill_id'] ?? 0);
$preload = null;
if ($preload_id > 0) {
    foreach ($bills as $b) {
        if (intval($b['id']) === $preload_id) { $preload = $b; break; }
    }
}

// Config for JS
$config = require __DIR__ . '/config.php';
$pk = $config['paystack']['public_key'] ?? '';
$mpesaReady = !empty($config['mpesa']['consumer_key']) && !empty($config['mpesa']['callback_url']);
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
  .tab-btn {
    padding: 0.625rem 1.25rem;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all .15s ease;
    color: #475569;
    background: transparent;
    border: 1px solid transparent;
  }
  .tab-btn:hover { color: #0f172a; background: rgba(255,255,255,0.6); }
  .tab-btn.active {
    background: #fff;
    color: #0f172a;
    box-shadow: 0 1px 3px rgba(15,39,68,0.08);
    border-color: #e2e8f0;
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
    <p class="text-sm text-slate-500 mt-1">Apply a payment to an outstanding bill — cash, card, or M-Pesa.</p>
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

  <!-- Tabs -->
  <div class="inline-flex flex-wrap gap-1 p-1 rounded-2xl bg-slate-100 border border-slate-200 mb-6">
    <button type="button" class="tab-btn <?= $activeTab==='cash'?'active':'' ?>" data-tab="cash">
      <span class="inline-flex items-center gap-1.5">
        <i data-lucide="banknote" class="w-3.5 h-3.5"></i>
        Cash
      </span>
    </button>
    <button type="button" class="tab-btn <?= $activeTab==='card'?'active':'' ?>" data-tab="card">
      <span class="inline-flex items-center gap-1.5">
        <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
        Card
      </span>
    </button>
    <button type="button" class="tab-btn <?= $activeTab==='mpesa'?'active':'' ?>" data-tab="mpesa">
      <span class="inline-flex items-center gap-1.5">
        <i data-lucide="smartphone" class="w-3.5 h-3.5"></i>
        M-Pesa
      </span>
    </button>
  </div>

  <!-- ============ CASH TAB ============ -->
  <div id="tab-cash" class="tab-panel <?= $activeTab!=='cash'?'hidden':'' ?>">
    <form method="POST" action="paybill.php"
          class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8 space-y-5">

      <input type="hidden" name="payment_mode" value="cash">

      <div>
        <label for="cash_bill" class="wiz-label">Select bill</label>
        <select id="cash_bill" name="bill_id" required class="wiz-input">
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
          <label for="cash_amount" class="wiz-label">Amount (KES)</label>
          <input id="cash_amount" type="number" name="amount" step="0.01" min="0.01" required
                 class="wiz-input" placeholder="0.00" inputmode="decimal">
        </div>
        <div>
          <label for="cash_date" class="wiz-label">Payment date</label>
          <input id="cash_date" type="date" name="payment_date" value="<?= date('Y-m-d') ?>"
                 class="wiz-input">
        </div>
      </div>

      <div>
        <label for="cash_ref" class="wiz-label">Reference / receipt no. <span class="text-slate-400 font-normal">(optional)</span></label>
        <input id="cash_ref" type="text" name="reference" placeholder="e.g. QK123XYZ" class="wiz-input">
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
          Record cash payment
        </button>
      </div>

    </form>
  </div>

  <!-- ============ CARD TAB ============ -->
  <div id="tab-card" class="tab-panel <?= $activeTab!=='card'?'hidden':'' ?>">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8 space-y-5">

      <?php if (empty($pk)): ?>
        <div class="flex items-start gap-3 p-3.5 rounded-xl bg-amber-50 ring-1 ring-amber-200/70 text-amber-800 text-sm">
          <i data-lucide="alert-triangle" class="w-5 h-5 mt-0.5 shrink-0"></i>
          <span>Paystack is not configured. Add <code>PAYSTACK_PUBLIC_KEY</code> to <code>.env</code>.</span>
        </div>
      <?php endif; ?>

      <div>
        <label for="card_bill" class="wiz-label">Select bill</label>
        <select id="card_bill" class="wiz-input" onchange="onCardBillChange(this)">
          <option value="">— Choose unpaid bill —</option>
          <?php foreach ($bills as $b):
            $bal = (float)$b['amount'] - (float)$b['amount_paid'];
          ?>
            <option value="<?= intval($b['id']) ?>"
                    data-balance="<?= $bal ?>"
                    data-email="<?= htmlspecialchars($b['email'] ?? '', ENT_QUOTES) ?>"
                    data-name="<?= htmlspecialchars(trim(($b['fname'] ?? '') . ' ' . ($b['lname'] ?? '')), ENT_QUOTES) ?>">
              #<?= intval($b['id']) ?> — <?= htmlspecialchars(($b['fname'] ?? '') . ' ' . ($b['lname'] ?? '')) ?> (Balance: <?= number_format($bal, 2) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="card_amount" class="wiz-label">Amount (KES)</label>
        <input id="card_amount" type="number" step="0.01" min="0.01" class="wiz-input" placeholder="0.00">
      </div>

      <div class="flex justify-end gap-3 pt-5 border-t border-slate-100">
        <a href="billing.php"
           class="inline-flex items-center px-5 py-2.5 rounded-xl font-medium text-slate-700
                  bg-white border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition">
          Cancel
        </a>
        <button type="button" id="card_pay_btn"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white
                       bg-gradient-to-b from-slate-800 to-slate-900
                       shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all
                       disabled:opacity-50 disabled:cursor-not-allowed"
                <?= empty($pk) ? 'disabled' : '' ?>>
          <i data-lucide="credit-card" class="w-4 h-4"></i>
          Charge card via Paystack
        </button>
      </div>

    </div>
  </div>

  <!-- ============ MPESA TAB ============ -->
  <div id="tab-mpesa" class="tab-panel <?= $activeTab!=='mpesa'?'hidden':'' ?>">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8 space-y-5">

      <?php if (!$mpesaReady): ?>
        <div class="flex items-start gap-3 p-3.5 rounded-xl bg-amber-50 ring-1 ring-amber-200/70 text-amber-800 text-sm">
          <i data-lucide="alert-triangle" class="w-5 h-5 mt-0.5 shrink-0"></i>
          <span>M-Pesa is not configured. Check <code>MPESA_CONSUMER_KEY</code> and <code>MPESA_CALLBACK_URL</code> in <code>.env</code>.</span>
        </div>
      <?php endif; ?>

      <div>
        <label for="mpesa_bill" class="wiz-label">Select bill</label>
        <select id="mpesa_bill" class="wiz-input" onchange="onMpesaBillChange(this)">
          <option value="">— Choose unpaid bill —</option>
          <?php foreach ($bills as $b):
            $bal = (float)$b['amount'] - (float)$b['amount_paid'];
          ?>
            <option value="<?= intval($b['id']) ?>"
                    data-balance="<?= $bal ?>"
                    data-phone="<?= htmlspecialchars($b['contact'] ?? '', ENT_QUOTES) ?>">
              #<?= intval($b['id']) ?> — <?= htmlspecialchars(($b['fname'] ?? '') . ' ' . ($b['lname'] ?? '')) ?> (<?= htmlspecialchars($b['contact'] ?? '') ?>) — Balance: <?= number_format($bal, 2) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label for="mpesa_amount" class="wiz-label">Amount (KES)</label>
          <input id="mpesa_amount" type="number" step="0.01" min="1" class="wiz-input" placeholder="0.00">
        </div>
        <div>
          <label for="mpesa_phone" class="wiz-label">Customer's M-Pesa phone</label>
          <input id="mpesa_phone" type="text" class="wiz-input" placeholder="0712345678" inputmode="tel">
        </div>
      </div>

      <div class="flex justify-end gap-3 pt-5 border-t border-slate-100">
        <a href="billing.php"
           class="inline-flex items-center px-5 py-2.5 rounded-xl font-medium text-slate-700
                  bg-white border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition">
          Cancel
        </a>
        <button type="button" id="mpesa_send_btn"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white
                       bg-gradient-to-b from-emerald-600 to-emerald-700
                       shadow-lg shadow-emerald-700/25 hover:-translate-y-0.5 transition-all
                       disabled:opacity-50 disabled:cursor-not-allowed"
                <?= $mpesaReady ? '' : 'disabled' ?>>
          <i data-lucide="smartphone" class="w-4 h-4"></i>
          Send STK Push
        </button>
      </div>

    </div>
  </div>

</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
// ---- Tab switching ----
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    btn.classList.add('active');
    document.getElementById('tab-' + btn.dataset.tab).classList.remove('hidden');
  });
});

// ---- CARD tab ----
function onCardBillChange(sel) {
  const opt = sel.options[sel.selectedIndex];
  const amountInput = document.getElementById('card_amount');
  if (!opt.value) { amountInput.value = ''; return; }
  amountInput.value = parseFloat(opt.dataset.balance || 0).toFixed(2);
}

document.getElementById('card_pay_btn')?.addEventListener('click', function () {
  const sel = document.getElementById('card_bill');
  const opt = sel.options[sel.selectedIndex];
  const billId = opt.value;
  const amount = parseFloat(document.getElementById('card_amount').value);
  const email  = opt.dataset.email || 'cashier@ncwsc.co.ke';

  if (!billId) { alert('Please select a bill.'); return; }
  if (!amount || amount <= 0) { alert('Enter a valid amount.'); return; }
  if (amount > parseFloat(opt.dataset.balance)) { alert('Amount exceeds balance.'); return; }

  const handler = PaystackPop.setup({
    key: '<?= htmlspecialchars($pk, ENT_QUOTES) ?>',
    email: email,
    amount: Math.round(amount * 100),
    currency: 'KES',
    ref: 'NCWSC-ADMIN-' + billId + '-' + Date.now(),
    metadata: { custom_fields: [{ display_name: 'Bill ID', variable_name: 'bill_id', value: billId }] },
    callback: function (response) {
      fetch('pay_verify_admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'reference=' + encodeURIComponent(response.reference) + '&bill_id=' + encodeURIComponent(billId)
      })
      .then(r => r.json())
      .then(data => {
        if (data.ok) {
          alert('Payment recorded. Ref: ' + data.reference);
          window.location.reload();
        } else {
          alert('Verification failed: ' + (data.error || 'unknown'));
        }
      })
      .catch(() => alert('Network error during verification.'));
    },
    onClose: function () {}
  });
  handler.openIframe();
});

// ---- MPESA tab ----
function onMpesaBillChange(sel) {
  const opt = sel.options[sel.selectedIndex];
  const amountInput = document.getElementById('mpesa_amount');
  const phoneInput  = document.getElementById('mpesa_phone');
  if (!opt.value) { amountInput.value = ''; phoneInput.value = ''; return; }
  amountInput.value = parseFloat(opt.dataset.balance || 0).toFixed(2);
  phoneInput.value  = opt.dataset.phone || '';
}

document.getElementById('mpesa_send_btn')?.addEventListener('click', function () {
  const sel = document.getElementById('mpesa_bill');
  const opt = sel.options[sel.selectedIndex];
  const billId = opt.value;
  const amount = parseFloat(document.getElementById('mpesa_amount').value);
  const phone  = document.getElementById('mpesa_phone').value.trim();

  if (!billId) { alert('Please select a bill.'); return; }
  if (!amount || amount <= 0) { alert('Enter a valid amount.'); return; }
  if (!phone) { alert('Enter the customer phone number.'); return; }

  const btn = this;
  const originalHTML = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = 'Sending...';

  fetch('mpesa_initiate_admin.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'bill_id=' + encodeURIComponent(billId)
         + '&amount=' + encodeURIComponent(amount)
         + '&phone=' + encodeURIComponent(phone)
  })
  .then(r => r.json())
  .then(data => {
    if (data.ok) {
      alert('STK Push sent. Ask the customer to enter their M-Pesa PIN.');
      let tries = 0;
      const interval = setInterval(() => {
        tries++;
        fetch('mpesa_query.php?bill_id=' + billId)
          .then(r => r.json())
          .then(q => {
            if (q.status === 'paid') {
              clearInterval(interval);
              window.location.reload();
            } else if (tries >= 22) {
              clearInterval(interval);
              alert('Payment not confirmed yet. Refresh in a minute if the customer completes it.');
              btn.disabled = false;
              btn.innerHTML = originalHTML;
            }
          });
      }, 4000);
    } else {
      alert('Failed: ' + (data.error || 'unknown'));
      btn.disabled = false;
      btn.innerHTML = originalHTML;
    }
  })
  .catch(() => {
    alert('Network error.');
    btn.disabled = false;
    btn.innerHTML = originalHTML;
  });
});
</script>

<?php include 'footer.php'; ?>