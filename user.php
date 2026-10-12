<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1]); // Admin only
include_once 'db.php';
$pageTitle = 'User Management';
include 'admin_header.php';

$search = trim($_GET['q'] ?? '');

$sql = "SELECT id, name, username, email, mobile, userlevel FROM user WHERE 1=1";
$params = []; $types = '';
if ($search !== '') {
    $sql .= " AND (name LIKE ? OR username LIKE ? OR email LIKE ? OR mobile LIKE ?)";
    $like = "%$search%";
    for ($i = 0; $i < 4; $i++) $params[] = $like;
    $types .= 'ssss';
}
$sql .= " ORDER BY userlevel ASC, id ASC";

$stmt = mysqli_prepare($conn, $sql);
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$users = [];
while ($row = mysqli_fetch_assoc($res)) $users[] = $row;
mysqli_stmt_close($stmt);

$flash = $_GET['success'] ?? null;
$err   = $_GET['err'] ?? null;

$levelLabel = [1 => 'Admin', 2 => 'Cashier', 3 => 'Client'];
$levelStyles = [
    1 => 'bg-purple-50 text-purple-700 ring-purple-200/70',
    2 => 'bg-blue-50 text-blue-700 ring-blue-200/70',
    3 => 'bg-slate-100 text-slate-700 ring-slate-200',
];
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between mb-8 gap-4">
    <div>
      <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">User management</h1>
      <p class="text-sm text-slate-500 mt-1">Create, edit, and manage staff and customer accounts.</p>
    </div>
    <a href="adduser.php"
       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-white
              bg-gradient-to-b from-slate-800 to-slate-900
              shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all w-max">
      <i data-lucide="user-plus" class="w-4 h-4"></i>
      Add new user
    </a>
  </div>

  <?php if ($flash === '1'): ?>
    <div class="mb-6 flex items-start gap-3 p-3.5 rounded-xl bg-emerald-50 ring-1 ring-emerald-200/70 text-emerald-800 text-sm">
      <i data-lucide="check-circle" class="w-5 h-5 mt-0.5 shrink-0"></i>
      <span>User saved successfully.</span>
    </div>
  <?php elseif ($flash === 'deleted'): ?>
    <div class="mb-6 flex items-start gap-3 p-3.5 rounded-xl bg-emerald-50 ring-1 ring-emerald-200/70 text-emerald-800 text-sm">
      <i data-lucide="trash-2" class="w-5 h-5 mt-0.5 shrink-0"></i>
      <span>User deleted.</span>
    </div>
  <?php endif; ?>

  <?php if ($err === 'self'): ?>
    <div class="mb-6 flex items-start gap-3 p-3.5 rounded-xl bg-rose-50 ring-1 ring-rose-200/70 text-rose-800 text-sm">
      <i data-lucide="alert-triangle" class="w-5 h-5 mt-0.5 shrink-0"></i>
      <span>You cannot delete your own account.</span>
    </div>
  <?php elseif ($err === 'notfound'): ?>
    <div class="mb-6 flex items-start gap-3 p-3.5 rounded-xl bg-rose-50 ring-1 ring-rose-200/70 text-rose-800 text-sm">
      <i data-lucide="alert-triangle" class="w-5 h-5 mt-0.5 shrink-0"></i>
      <span>User not found.</span>
    </div>
  <?php endif; ?>

  <!-- Search -->
  <form method="GET"
        class="bg-white rounded-2xl border border-slate-200 p-3 sm:p-4 mb-6
               flex flex-col sm:flex-row gap-3">
    <div class="relative flex-1">
      <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
             placeholder="Search by name, username, email, or mobile"
             class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white text-slate-900 placeholder-slate-400
                    border border-slate-200 outline-none transition hover:border-slate-300
                    focus:border-teal-600 focus:ring-3 focus:ring-teal-600/15">
    </div>

    <button type="submit"
            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white
                   bg-gradient-to-b from-slate-800 to-slate-900
                   shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 transition-all whitespace-nowrap">
      Search
    </button>

    <?php if ($search !== ''): ?>
      <a href="user.php"
         class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-medium text-slate-700
                bg-white border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition whitespace-nowrap">
        <i data-lucide="x" class="w-4 h-4"></i>
        Clear
      </a>
    <?php endif; ?>
  </form>

  <!-- Table -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead>
          <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
            <th class="px-5 py-3 font-semibold w-16">#</th>
            <th class="px-5 py-3 font-semibold">Name</th>
            <th class="px-5 py-3 font-semibold">Username</th>
            <th class="px-5 py-3 font-semibold">Email</th>
            <th class="px-5 py-3 font-semibold">Mobile</th>
            <th class="px-5 py-3 font-semibold">Level</th>
            <th class="px-5 py-3 font-semibold text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($users)): ?>
            <tr>
              <td colspan="7" class="px-6 py-16 text-center">
                <div class="mx-auto w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
                  <i data-lucide="users" class="w-6 h-6 text-slate-500"></i>
                </div>
                <p class="text-base font-semibold text-slate-900">No users found</p>
                <p class="text-sm text-slate-500 mt-1"><?= $search ? 'Try a different search term.' : 'Add your first staff or customer account.' ?></p>
              </td>
            </tr>
          <?php else: foreach ($users as $u):
            $lvl = intval($u['userlevel']);
            $lbl = $levelLabel[$lvl] ?? 'Unknown';
            $cls = $levelStyles[$lvl] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
            $isSelf = (intval($u['id']) === intval($_SESSION['id']));
          ?>
            <tr class="hover:bg-slate-50 transition">
              <td class="px-5 py-4 font-semibold text-slate-900 tabular-nums">#<?= intval($u['id']) ?></td>
              <td class="px-5 py-4">
                <div class="font-medium text-slate-900"><?= htmlspecialchars($u['name'] ?: '—') ?></div>
                <?php if ($isSelf): ?>
                  <span class="inline-block mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-teal-700">You</span>
                <?php endif; ?>
              </td>
              <td class="px-5 py-4 text-slate-600 font-mono text-xs"><?= htmlspecialchars($u['username']) ?></td>
              <td class="px-5 py-4 text-slate-600">
                <?php if (!empty($u['email'])): ?>
                  <a href="mailto:<?= htmlspecialchars($u['email']) ?>" class="hover:text-teal-700 transition">
                    <?= htmlspecialchars($u['email']) ?>
                  </a>
                <?php else: ?>
                  <span class="text-slate-400">—</span>
                <?php endif; ?>
              </td>
              <td class="px-5 py-4 text-slate-600 tabular-nums"><?= htmlspecialchars($u['mobile'] ?: '—') ?></td>
              <td class="px-5 py-4">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ring-1 <?= $cls ?>">
                  <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                  <?= htmlspecialchars($lbl) ?>
                </span>
              </td>
              <td class="px-5 py-4">
                <div class="flex items-center justify-end gap-3">
                  <a href="edituser.php?id=<?= intval($u['id']) ?>"
                     class="inline-flex items-center gap-1 text-sm font-medium text-teal-700 hover:text-teal-800 transition">
                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                    Edit
                  </a>
                  <?php if (!$isSelf): ?>
                    <a href="deluser.php?id=<?= intval($u['id']) ?>"
                       onclick="return confirm('Delete this user?');"
                       class="inline-flex items-center gap-1 text-sm font-medium text-rose-600 hover:text-rose-700 transition">
                      <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                      Delete
                    </a>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php include 'footer.php'; ?>