<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
checkLevel([1, 2]);
include_once 'db.php';
include 'admin_header.php';

$edit_id = intval($_GET['edit'] ?? 0);
$client = [
    'id' => 0,
    'fname' => '',
    'lname' => '',
    'mi' => '',
    'address' => '',
    'contact' => '',
    'email' => '',
];

if ($edit_id > 0) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM owners WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $edit_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($res)) {
        $client = $row;
    }
    mysqli_stmt_close($stmt);
}

$isEdit = $client['id'] > 0;
?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            <?php echo $isEdit ? 'Edit Client' : 'Add New Client'; ?>
        </h1>
        <a href="clients.php" class="text-sm text-gray-500 hover:text-ncwsc-blue">&larr; Back to Clients</a>
    </div>

    <form action="addclient1.php" method="POST" class="bg-white p-8 rounded-lg shadow-md border border-gray-100 space-y-5">

        <input type="hidden" name="id" value="<?php echo intval($client['id']); ?>">

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">First Name:<span class="text-red-500">*</span></label>
                <input type="text" name="fname" required value="<?php echo htmlspecialchars($client['fname']); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Middle Initial:</label>
                <input type="text" name="mi" maxlength="5" value="<?php echo htmlspecialchars($client['mi']); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name:<span class="text-red-500">*</span></label>
                <input type="text" name="lname" required value="<?php echo htmlspecialchars($client['lname']); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Address:<span class="text-red-500">*</span></label>
            <input type="text" name="address" required value="<?php echo htmlspecialchars($client['address']); ?>"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number:<span class="text-red-500">*</span></label>
                <input type="text" name="contact" required value="<?php echo htmlspecialchars($client['contact']); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($client['email']); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <a href="clients.php" class="text-gray-500 hover:text-gray-700 font-semibold py-2 px-4">Cancel</a>
            <button type="submit" class="btn-green text-white font-semibold py-2 px-6 rounded-md transition">
                <?php echo $isEdit ? 'Update Client' : 'Save Client'; ?>
            </button>
        </div>

    </form>
</div>

<?php include 'footer.php'; ?>