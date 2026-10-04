<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';
include_once 'tariff.php';
include 'admin_header.php';

$owners = [];
$res = mysqli_query($conn, "SELECT id, fname, lname, contact FROM owners ORDER BY lname, fname");
while ($row = mysqli_fetch_assoc($res)) $owners[] = $row;

$preselect = intval($_GET['owners_id'] ?? 0);
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Create New Bill</h1>
        <a href="billing.php" class="text-sm text-gray-500 hover:text-ncwsc-blue">&larr; Back to Billing</a>
    </div>

    <div class="bg-blue-50 border-l-4 border-ncwsc-blue p-4 mb-6 text-sm text-gray-700">
        <i data-lucide="info" class="w-4 h-4 inline mr-2"></i>
        Enter the meter readings. The system will calculate the bill using the current tariff.
    </div>

    <form action="addbillexec.php" method="POST" class="bg-white p-8 rounded-lg shadow-md border border-gray-100 space-y-5">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Customer:<span class="text-red-500">*</span></label>
            <select name="owners_id" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                <option value="">-- Select Customer --</option>
                <?php foreach ($owners as $o): ?>
                    <option value="<?php echo intval($o['id']); ?>" <?php echo ($preselect === intval($o['id'])) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($o['fname'] . ' ' . $o['lname'] . ' (' . $o['contact'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Previous Reading (m³):<span class="text-red-500">*</span></label>
                <input type="number" name="prev" step="0.01" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Present Reading (m³):<span class="text-red-500">*</span></label>
                <input type="number" name="pres" step="0.01" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bill Month:</label>
            <input type="text" name="bill_month" value="<?php echo htmlspecialchars(date('M Y')); ?>"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <a href="billing.php" class="text-gray-500 hover:text-gray-700 font-semibold py-2 px-4">Cancel</a>
            <button type="submit" class="btn-green text-white font-semibold py-2 px-6 rounded-md transition">
                Generate Bill
            </button>
        </div>

    </form>
</div>

<?php include 'footer.php'; ?>