<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
include_once 'db.php';
$pageTitle = 'Pay My Bill';
include 'header.php';

$user_id = intval($_SESSION['id']);

// ---- Find this user's owner record ----
// Primary lookup: by user_id (set when they verified via otp_process.php)
// Fallback: by email match (in case the link is missing from old accounts)
$owner = null;

$stmt = mysqli_prepare($conn, "SELECT * FROM owners WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$owner = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$owner && !empty($_SESSION['email'])) {
    // Fallback: match by email — and auto-link so future loads are fast
    $email = $_SESSION['email'];
    $fb = mysqli_prepare($conn, "SELECT * FROM owners WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($fb, "s", $email);
    mysqli_stmt_execute($fb);
    $fbRes = mysqli_stmt_get_result($fb);
    $owner = mysqli_fetch_assoc($fbRes);
    mysqli_stmt_close($fb);

    if ($owner && empty($owner['user_id'])) {
        // Auto-link the owner record to this user
        $link = mysqli_prepare($conn, "UPDATE owners SET user_id = ? WHERE id = ?");
        mysqli_stmt_bind_param($link, "ii", $user_id, $owner['id']);
        mysqli_stmt_execute($link);
        mysqli_stmt_close($link);
        $owner['user_id'] = $user_id;
    }
}

// ---- Load unpaid bills for this owner ----
$bills = [];
if ($owner) {
    $stmt = mysqli_prepare($conn,
        "SELECT id, amount, amount_paid, status, bill_month, prev, pres, date
         FROM bill
         WHERE owners_id = ? AND (status != 'paid' OR status IS NULL)
         ORDER BY id DESC"
    );
    mysqli_stmt_bind_param($stmt, "i", $owner['id']);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) $bills[] = $row;
    mysqli_stmt_close($stmt);
}

$config = require __DIR__ . '/config.php';
$pk = $config['paystack']['public_key'] ?? '';
$mpesaReady = !empty($config['mpesa']['consumer_key'])
           && !empty($config['mpesa']['callback_url']);

$flash = $_GET['paid'] ?? null;
$debug = isset($_GET['debug']);
?>

<style>
  .pay-card { background:#fff; border:1px solid #e2e8f0; border-radius:1rem; }
</style>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Pay my bill</h1>
    <p class="text-sm text-slate-500 mt-1">Settle outstanding bills via card or M-Pesa.</p>
  </div>

  <?php if ($flash === '1'): ?>
    <div class="mb-6 flex items-start gap-3 p-3.5 rounded-xl bg-emerald-50 ring-1 ring-emerald-200/70 text-emerald-800 text-sm">
      <i data-lucide="check-circle" class="w-5 h-5 mt-0.5 shrink-0"></i>
      <span>Payment received and recorded.</span>
    </div>
  <?php endif; ?>

  <?php if ($debug): ?>
    <div class="mb-6 p-4 rounded-xl bg-slate-100 border border-slate-200 text-xs font-mono text-slate-700">
      <p><strong>Debug:</strong></p>
      <p>user_id = <?= $user_id ?></p>
      <p>email = <?= htmlspecialchars($_SESSION['email'] ?? '(none)') ?></p>
      <p>owner = <?= $owner ? 'id=' . intval($owner['id']) . ' user_id=' . intval($owner['user_id'] ?? 0) : 'NOT FOUND' ?></p>
      <p>bills found = <?= count($bills) ?></p>
      <p>paystack configured = <?= !empty($pk) ? 'yes' : 'no' ?></p>
      <p>mpesa configured = <?= $mpesaReady ? 'yes' : 'no' ?></p>
    </div>
  <?php endif; ?>

  <?php if (!$owner): ?>
    <div class="pay-card p-8 text-center">
      <div class="mx-auto w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
        <i data-lucide="user-x" class="w-6 h-6 text-slate-500"></i>
      </div>
      <h2 class="text-base font-semibold text-slate-900">No customer account found</h2>
      <p class="text-sm text-slate-500 mt-2 max-w-md mx-auto">
        Your user account isn't linked to a customer record yet. This usually happens right after registration —
        if you just verified, try refreshing. Otherwise, contact NCWSC support.
      </p>
      <a href="pay_my_bill.php?debug=1" class="text-xs text-slate-400 hover:text-slate-600 mt-4 inline-block">
        Show debug info
      </a>
    </div>
  <?php elseif (empty($bills)): ?>
    <div class="pay-card p-8 text-center">
      <div class="mx-auto w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center mb-4">
        <i data-lucide="check-circle-2" class="w-6 h-6 text-emerald-600"></i>
      </div>
      <h2 class="text-base font-semibold text-slate-900">You're all paid up</h2>
      <p class="text-sm text-slate-500 mt-1">No outstanding bills on your account.</p>
      <a href="selfcare.php?tab=my_accounts"
         class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-medium text-slate-700
                bg-white border border-slate-200 hover:border-slate-300 transition">
        <i data-lucide="receipt" class="w-4 h-4"></i>
        View my accounts
      </a>
    </div>
  <?php else: ?>

    <div class="space-y-4">
      <?php foreach ($bills as $b):
        $balance = (float)$b['amount'] - (float)$b['amount_paid'];
        if ($balance <= 0) continue;
      ?>
        <div class="pay-card p-6">
          <div class="flex items-start justify-between gap-4 mb-4">
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Bill #<?= intval($b['id']) ?></p>
              <p class="text-base font-semibold text-slate-900 mt-0.5"><?= htmlspecialchars($b['bill_month'] ?? '—') ?></p>
              <?php if (!empty($b['date'])): ?>
                <p class="text-xs text-slate-400 mt-0.5"><?= htmlspecialchars($b['date']) ?></p>
              <?php endif; ?>
            </div>
            <div class="text-right">
              <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Balance</p>
              <p class="text-xl font-bold text-rose-600 tabular-nums mt-0.5">
                KES <?= number_format($balance, 2) ?>
              </p>
            </div>
          </div>

          <div class="mb-4">
            <label class="block text-xs font-medium text-slate-600 mb-1.5">Amount to pay (KES)</label>
            <input type="number" min="1" step="0.01" max="<?= $balance ?>"
                   value="<?= number_format($balance, 2, '.', '') ?>"
                   data-bill-id="<?= intval($b['id']) ?>"
                   data-max="<?= $balance ?>"
                   class="pay-amount-input w-full px-4 py-2.5 rounded-xl bg-white text-slate-900
                          border border-slate-200 outline-none transition
                          focus:border-teal-600 focus:ring-3 focus:ring-teal-600/15">
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <button type="button"
                    class="pay-card-btn inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-white
                           bg-gradient-to-b from-slate-800 to-slate-900
                           shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all
                           disabled:opacity-50 disabled:cursor-not-allowed"
                    data-bill-id="<?= intval($b['id']) ?>"
                    <?= empty($pk) ? 'disabled' : '' ?>>
              <i data-lucide="credit-card" class="w-4 h-4"></i>
              Pay with card
            </button>

            <button type="button"
                    class="pay-mpesa-btn inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-white
                           bg-gradient-to-b from-emerald-600 to-emerald-700
                           shadow-lg shadow-emerald-700/25 hover:-translate-y-0.5 transition-all
                           disabled:opacity-50 disabled:cursor-not-allowed"
                    data-bill-id="<?= intval($b['id']) ?>"
                    <?= $mpesaReady ? '' : 'disabled' ?>>
              <i data-lucide="smartphone" class="w-4 h-4"></i>
              Pay with M-Pesa
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  <?php endif; ?>
</div>

<?php if (!empty($bills)): ?>
<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
function getAmount(billId) {
  const input = document.querySelector('.pay-amount-input[data-bill-id="' + billId + '"]');
  return parseFloat(input.value);
}
function validateAmount(billId) {
  const input = document.querySelector('.pay-amount-input[data-bill-id="' + billId + '"]');
  const amount = parseFloat(input.value);
  const max = parseFloat(input.dataset.max);
  if (!amount || amount <= 0) { alert('Please enter a valid amount.'); return false; }
  if (amount > max) { alert('Amount exceeds the outstanding balance.'); return false; }
  return true;
}

// ---- Paystack (card) ----
document.querySelectorAll('.pay-card-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const billId = btn.dataset.billId;
    if (!validateAmount(billId)) return;
    const amount = getAmount(billId);

    const handler = PaystackPop.setup({
      key: '<?= htmlspecialchars($pk, ENT_QUOTES) ?>',
      email: '<?= htmlspecialchars($_SESSION['email'] ?? 'customer@ncwsc.co.ke', ENT_QUOTES) ?>',
      amount: Math.round(amount * 100),
      currency: 'KES',
      ref: 'NCWSC-BILL' + billId + '-' + Date.now(),
      metadata: {
        custom_fields: [
          { display_name: 'Bill ID', variable_name: 'bill_id', value: billId }
        ]
      },
      callback: function (response) {
        fetch('pay_verify.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'reference=' + encodeURIComponent(response.reference)
                 + '&bill_id=' + encodeURIComponent(billId)
        })
        .then(r => r.json())
        .then(data => {
          if (data.ok) {
            window.location.href = 'pay_my_bill.php?paid=1';
          } else {
            alert('Payment was received but verification failed: ' + (data.error || 'unknown'));
          }
        })
        .catch(() => alert('Payment received. Verification error — contact support.'));
      },
      onClose: function () {}
    });
    handler.openIframe();
  });
});

// ---- Daraja (M-Pesa) ----
document.querySelectorAll('.pay-mpesa-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const billId = btn.dataset.billId;
    if (!validateAmount(billId)) return;
    const amount = getAmount(billId);

    const phone = prompt('Enter your M-Pesa phone number (e.g. 0712345678):');
    if (!phone) return;

    btn.disabled = true;
    const originalHTML = btn.innerHTML;
    btn.innerHTML = 'Sending prompt...';

    fetch('mpesa_initiate.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'bill_id=' + encodeURIComponent(billId)
             + '&amount=' + encodeURIComponent(amount)
             + '&phone=' + encodeURIComponent(phone)
    })
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        alert('STK Push sent! Check your phone and enter your M-Pesa PIN.');
        let tries = 0;
        const interval = setInterval(() => {
          tries++;
          fetch('mpesa_query.php?bill_id=' + billId)
            .then(r => r.json())
            .then(q => {
              if (q.status === 'paid') {
                clearInterval(interval);
                window.location.href = 'pay_my_bill.php?paid=1';
              } else if (tries >= 22) {
                clearInterval(interval);
                alert('Payment not confirmed yet. If you completed the payment, refresh this page in a minute.');
                btn.disabled = false;
                btn.innerHTML = originalHTML;
              }
            });
        }, 4000);
      } else {
        alert('Could not send M-Pesa prompt: ' + (data.error || 'unknown'));
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
});
</script>
<?php endif; ?>

<?php include 'footer.php'; ?>