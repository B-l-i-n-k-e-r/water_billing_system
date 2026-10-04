<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
include_once 'db.php';
include 'header.php';

$user_id = intval($_SESSION['id']);
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'my_accounts';

// Fetch user accounts (used by My Accounts tab)
$accounts = [];
$stmt = mysqli_prepare($conn, "SELECT * FROM customer_accounts WHERE user_id = ? ORDER BY date_added DESC");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) {
    $accounts[] = $row;
}
mysqli_stmt_close($stmt);

// Flash messages
$flash = $_GET['success'] ?? null;
$err   = $_GET['err'] ?? null;
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Tabs -->
    <div class="flex border-b border-gray-200 mb-6 overflow-x-auto">
        <a href="?tab=my_accounts"
           class="py-3 px-6 text-sm font-medium whitespace-nowrap
                  <?php echo $activeTab == 'my_accounts' ? 'border-b-2 border-ncwsc-blue text-ncwsc-blue' : 'text-gray-500 hover:text-gray-700'; ?>">
            MY ACCOUNTS
        </a>
        <a href="?tab=add_account"
           class="py-3 px-6 text-sm font-medium whitespace-nowrap
                  <?php echo $activeTab == 'add_account' ? 'border-b-2 border-ncwsc-blue text-ncwsc-blue' : 'text-gray-500 hover:text-gray-700'; ?>">
            ADD ACCOUNT
        </a>
        <a href="?tab=get_statement"
           class="py-3 px-6 text-sm font-medium whitespace-nowrap
                  <?php echo $activeTab == 'get_statement' ? 'border-b-2 border-ncwsc-blue text-ncwsc-blue' : 'text-gray-500 hover:text-gray-700'; ?>">
            GET STATEMENT
        </a>
    </div>

    <!-- Flash messages -->
    <?php if ($flash === '1'): ?>
        <div class="mb-4 p-3 rounded-md bg-green-50 border border-green-200 text-green-700 text-sm">
            Action completed successfully.
        </div>
    <?php endif; ?>
    <?php if ($err): ?>
        <div class="mb-4 p-3 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm">
            <?php
            $errMap = [
                'empty' => 'Please fill all required fields.',
                'db'    => 'Database error. Please try again.',
            ];
            echo htmlspecialchars($errMap[$err] ?? 'Something went wrong.');
            ?>
        </div>
    <?php endif; ?>

    <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100">

        <?php if ($activeTab == 'my_accounts'): ?>
            <h2 class="text-xl font-bold text-gray-800 mb-4 text-center">MY ACCOUNTS</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-600 text-white">
                        <tr>
                            <th class="p-3">ACCOUNT NUMBER</th>
                            <th class="p-3">DATE ADDED</th>
                            <th class="p-3">CHECK BALANCE</th>
                            <th class="p-3">LATEST E-BILL</th>
                            <th class="p-3">REMOVE ACCOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($accounts)): ?>
                            <tr>
                                <td colspan="5" class="p-4 text-center text-gray-500">
                                    You have no accounts yet. Add one using the
                                    <a href="?tab=add_account" class="text-ncwsc-blue underline">Add Account</a> tab.
                                </td>
                            </tr>
                        <?php else: foreach ($accounts as $acc): ?>
                            <tr class="border-b border-gray-200">
                                <td class="p-3 font-semibold text-gray-800">
                                    <?php echo htmlspecialchars($acc['account_number']); ?>
                                </td>
                                <td class="p-3"><?php echo htmlspecialchars($acc['date_added']); ?></td>
                                <td class="p-3">
                                    <a href="check_balance.php?account=<?php echo urlencode($acc['account_number']); ?>"
                                       class="text-ncwsc-blue hover:underline">
                                        Check (<?php echo number_format((float)$acc['balance'], 2); ?>)
                                    </a>
                                </td>
                                <td class="p-3">
                                    <a href="view_ebill.php?account=<?php echo urlencode($acc['account_number']); ?>"
                                       class="text-ncwsc-blue hover:underline">
                                        View (<?php echo number_format((float)$acc['latest_ebill'], 2); ?>)
                                    </a>
                                </td>
                                <td class="p-3">
                                    <a href="remove_account.php?id=<?php echo intval($acc['id']); ?>"
                                       class="text-red-500 hover:underline"
                                       onclick="return confirm('Remove this account?');">
                                        Remove
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($activeTab == 'add_account'): ?>
            <h2 class="text-xl font-bold text-gray-800 mb-4 text-center">ADD ACCOUNT NUMBER</h2>
            <form action="add_account_process.php" method="POST" class="max-w-md mx-auto">
                <div class="mb-4">
                    <label class="block text-sm text-gray-600 mb-1">Account Number:</label>
                    <input type="text" name="account_number" required placeholder="1234567"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                </div>
                <button type="submit" class="w-full btn-green text-white font-semibold py-2 rounded-md">
                    ADD ACCOUNT
                </button>
            </form>

        <?php elseif ($activeTab == 'get_statement'): ?>
            <h2 class="text-xl font-bold text-gray-800 mb-4 text-center">FILL DETAILS BELOW</h2>

            <?php if (!empty($_SESSION['statement'])): ?>
                <!-- Statement result -->
                <div class="mb-6 border border-gray-200 rounded-md overflow-hidden">
                    <div class="bg-gray-100 px-4 py-3 border-b border-gray-200 text-sm">
                        <p><strong>Account:</strong> <?php echo htmlspecialchars($_SESSION['statement']['account']); ?></p>
                        <p><strong>Period:</strong> <?php echo htmlspecialchars($_SESSION['statement']['from']); ?>
                           to <?php echo htmlspecialchars($_SESSION['statement']['to']); ?></p>
                    </div>
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-600 text-white">
                            <tr>
                                <th class="p-3">DATE</th>
                                <th class="p-3">DESCRIPTION</th>
                                <th class="p-3">AMOUNT</th>
                                <th class="p-3">BALANCE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($_SESSION['statement']['rows'])): ?>
                                <tr><td colspan="4" class="p-4 text-center text-gray-500">
                                    No transactions found for this period.
                                </td></tr>
                            <?php else: foreach ($_SESSION['statement']['rows'] as $row): ?>
                                <tr class="border-b border-gray-200">
                                    <td class="p-3"><?php echo htmlspecialchars($row['transaction_date']); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($row['description']); ?></td>
                                    <td class="p-3"><?php echo number_format((float)$row['amount'], 2); ?></td>
                                    <td class="p-3"><?php echo number_format((float)$row['balance_after'], 2); ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php unset($_SESSION['statement']); ?>
            <?php endif; ?>

            <form action="get_statement_process.php" method="POST"
                  class="max-w-2xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Date From:</label>
                    <input type="date" name="date_from" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Date To:</label>
                    <input type="date" name="date_to" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Account Number:</label>
                    <input type="text" name="account_number" required placeholder="1234567"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Mobile Number:</label>
                    <input type="text" name="mobile_number" required placeholder="0700000000"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                </div>
                <div class="md:col-span-2 text-center mt-4">
                    <button type="submit" class="btn-green text-white font-semibold py-2 px-8 rounded-md">
                        SUBMIT
                    </button>
                </div>
            </form>
        <?php endif; ?>

    </div>
</div>

<?php include 'footer.php'; ?>