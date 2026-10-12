<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';
$pageTitle = 'Add Client';
include 'admin_header.php';

$edit_id = intval($_GET['edit'] ?? 0);
$client = [
    'id' => 0, 'fname' => '', 'lname' => '', 'mi' => '',
    'address' => '', 'contact' => '', 'email' => '',
];

if ($edit_id > 0) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM owners WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $edit_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($res)) $client = $row;
    mysqli_stmt_close($stmt);
}

$isEdit = $client['id'] > 0;
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

  <a href="clients.php"
     class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 transition mb-6">
    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
    Back to clients
  </a>

  <div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">
      <?= $isEdit ? 'Edit client' : 'Add new client' ?>
    </h1>
    <p class="text-sm text-slate-500 mt-1">
      <?= $isEdit ? 'Update the customer record below.' : 'Create a new customer record. You can create bills and record payments afterward.' ?>
    </p>
  </div>

  <form action="addclient1.php" method="POST"
        class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">

    <input type="hidden" name="id" value="<?= intval($client['id']) ?>">

    <!-- Name row -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
      <div>
        <label for="fname" class="wiz-label">First name</label>
        <input id="fname" type="text" name="fname" required
               value="<?= htmlspecialchars($client['fname']) ?>"
               class="wiz-input" placeholder="John">
      </div>
      <div>
        <label for="mi" class="wiz-label">Middle initial <span class="text-slate-400 font-normal">(optional)</span></label>
        <input id="mi" type="text" name="mi" maxlength="5"
               value="<?= htmlspecialchars($client['mi']) ?>"
               class="wiz-input" placeholder="M.">
      </div>
      <div>
        <label for="lname" class="wiz-label">Last name</label>
        <input id="lname" type="text" name="lname" required
               value="<?= htmlspecialchars($client['lname']) ?>"
               class="wiz-input" placeholder="Doe">
      </div>
    </div>

    <!-- Address -->
    <div class="mb-5">
      <label for="address" class="wiz-label">Address</label>
      <input id="address" type="text" name="address" required
             value="<?= htmlspecialchars($client['address']) ?>"
             class="wiz-input" placeholder="e.g. Westlands, Nairobi">
    </div>

    <!-- Contact + Email -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
      <div>
        <label for="contact" class="wiz-label">Contact number</label>
        <input id="contact" type="text" name="contact" required
               value="<?= htmlspecialchars($client['contact']) ?>"
               class="wiz-input" placeholder="0712345678" inputmode="tel">
      </div>
      <div>
        <label for="email" class="wiz-label">Email <span class="text-slate-400 font-normal">(optional)</span></label>
        <input id="email" type="email" name="email"
               value="<?= htmlspecialchars($client['email']) ?>"
               class="wiz-input" placeholder="you@example.com">
      </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-3 pt-5 border-t border-slate-100">
      <a href="clients.php"
         class="inline-flex items-center px-5 py-2.5 rounded-xl font-medium text-slate-700
                bg-white border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition">
        Cancel
      </a>
      <button type="submit"
              class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white
                     bg-gradient-to-b from-slate-800 to-slate-900
                     shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all">
        <i data-lucide="<?= $isEdit ? 'save' : 'user-plus' ?>" class="w-4 h-4"></i>
        <?= $isEdit ? 'Update client' : 'Save client' ?>
      </button>
    </div>

  </form>

</div>

<?php include 'footer.php'; ?>