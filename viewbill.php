<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';
include_once 'tariff.php';
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
    echo '<div class="max-w-3xl mx-auto px-4 py-16 text-center">';
    echo '<h1 class="text-2xl font-bold text-gray-800 mb-4">Bill not found</h1>';
    echo '<a href="billing.php" class="text-ncwsc-blue hover:underline">&larr; Back to Billing</a>';
    echo '</div>';
    include 'footer.php';
    exit();
}

$consumption = (float) ($bill['consumption'] ?? ((float)$bill['pres'] - (float)$bill['prev']));
$calc = calculateBill($consumption, $conn);

$statusClass = [
    'unpaid'  => 'bg-red-100 text-red-700',
    'partial' => 'bg-yellow-100 text-yellow-800',
    'paid'    => 'bg-green-100 text-green-700',
][$bill['status'] ?? 'unpaid'];

$created = !empty($_GET['created']);
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <?php if ($created): ?>
        <div class="mb-6 p-4 rounded-md bg-green-50 border border-green-200 text-green-700 text-sm">
            Bill <strong>#<?php echo intval($bill['id']); ?></strong> created successfully.
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md border border-gray-100 overflow-hidden">

        <div class="bg-ncwsc-blue text-white p-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Water Bill</h1>
                <p class="text-blue-100 text-sm mt-1">Bill #<?php echo intval($bill['id']); ?> · <?php echo htmlspecialchars($bill['bill_month']); ?></p>
            </div>
            <span class="<?php echo $statusClass; ?> text-xs px-3 py-1 rounded-full font-semibold uppercase">
                <?php echo htmlspecialchars($bill['status'] ?? 'unpaid'); ?>
            </span>
        </div>

        <div class="p-6 border-b border-gray-200 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Customer</p>
                <p class="font-semibold text-gray-800"><?php echo htmlspecialchars(($bill['fname'] ?? '') . ' ' . ($bill['lname'] ?? '')); ?></p>
            </div>
            <div>
                <p class="text-gray-500">Contact</p>
                <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($bill['contact'] ?? '—'); ?></p>
            </div>
            <div>
                <p class="text-gray-500">Address</p>
                <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($bill['address'] ?? '—'); ?></p>
            </div>
        </div>

        <div class="p-6 border-b border-gray-200 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Previous Reading</p>
                <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($bill['prev']); ?> m³</p>
            </div>
            <div>
                <p class="text-gray-500">Present Reading</p>
                <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($bill['pres']); ?> m³</p>
            </div>
            <div>
                <p class="text-gray-500">Consumption</p>
                <p class="font-semibold text-gray-800"><?php echo number_format($consumption, 2); ?> m³</p>
            </div>
        </div>

        <div class="p-6 border-b border-gray-200">
            <h2 class="font-bold text-gray-800 mb-3">Charge Breakdown</h2>
            <table class="w-full text-sm text-gray-700">
                <thead class="text-left text-gray-500 border-b">
                    <tr>
                        <th class="py-2">Range</th>
                        <th class="py-2 text-right">Units</th>
                        <th class="py-2 text-right">Rate</th>
                        <th class="py-2 text-right">Cost</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($calc['breakdown'] as $row): ?>
                        <tr class="border-b border-gray-100">
                            <td class="py-2"><?php echo htmlspecialchars($row['range']); ?></td>
                            <td class="py-2 text-right"><?php echo number_format($row['units'], 2); ?></td>
                            <td class="py-2 text-right"><?php echo number_format($row['rate'], 2); ?></td>
                            <td class="py-2 text-right"><?php echo number_format($row['cost'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="mt-4 space-y-1 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Water Charge</span><span class="font-semibold"><?php echo number_format($calc['water_charge'], 2); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Sewer Charge</span><span class="font-semibold"><?php echo number_format($calc['sewer_charge'], 2); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Meter Rent</span><span class="font-semibold"><?php echo number_format($calc['meter_rent'], 2); ?></span></div>
                <div class="flex justify-between border-t border-gray-200 pt-1 mt-1"><span class="text-gray-500">Subtotal</span><span class="font-semibold"><?php echo number_format($calc['subtotal'], 2); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-500">VAT (16%)</span><span class="font-semibold"><?php echo number_format($calc['vat'], 2); ?></span></div>
                <div class="flex justify-between text-lg font-bold text-ncwsc-blue border-t border-gray-200 pt-2 mt-2">
                    <span>Total Due</span><span><?php echo kes($calc['total']); ?></span>
                </div>
                <div class="flex justify-between"><span class="text-gray-500">Amount Paid</span><span class="font-semibold"><?php echo number_format((float)$bill['amount_paid'], 2); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Balance</span><span class="font-semibold"><?php echo number_format($calc['total'] - (float)$bill['amount_paid'], 2); ?></span></div>
            </div>
        </div>

        <div class="p-6 bg-gray-50 flex flex-wrap justify-end gap-3">
            <a href="billing.php" class="text-gray-600 hover:text-ncwsc-blue font-semibold py-2 px-4">Back to Billing</a>
            <button onclick="window.print()" class="border border-ncwsc-blue text-ncwsc-blue font-semibold py-2 px-4 rounded-md hover:bg-blue-50">
                Print
            </button>
            <?php if (($bill['status'] ?? 'unpaid') !== 'paid'): ?>
                <a href="paybill.php?bill_id=<?php echo intval($bill['id']); ?>" class="btn-green text-white font-semibold py-2 px-6 rounded-md">
                    Record Payment
                </a>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include 'footer.php'; ?>