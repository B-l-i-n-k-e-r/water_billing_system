<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
include_once 'db.php';
include 'header.php';

// Fetch this user's applications for the dropdown
$user_id = intval($_SESSION['id']);
$apps = [];

$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'applications'");
if ($tableCheck && mysqli_num_rows($tableCheck) > 0) {
    $stmt = mysqli_prepare($conn,
        "SELECT application_number FROM applications WHERE user_id = ? ORDER BY created_at DESC"
    );
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($res)) {
            $apps[] = $row['application_number'];
        }
        mysqli_stmt_close($stmt);
    }
}

// If user clicked "Upload More Documents" from view_application.php
$preselect = $_GET['app'] ?? '';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <h1 class="text-3xl font-bold text-gray-800 mb-6">Upload Application Documents</h1>

    <!-- Instructions Box -->
    <div class="bg-blue-50 border-l-4 border-ncwsc-blue p-5 mb-6 rounded-md">
        <div class="flex items-center gap-2 mb-3">
            <i data-lucide="info" class="w-5 h-5 text-ncwsc-blue"></i>
            <h2 class="font-bold text-gray-800">Instructions</h2>
        </div>
        <p class="text-red-600 font-semibold text-sm mb-3">READ BEFORE YOU PROCEED</p>
        <ol class="list-decimal list-inside text-sm text-gray-700 space-y-1 leading-relaxed">
            <li>All documents must be in <strong>PDF</strong> format.</li>
            <li>Document names must contain only <strong>alphanumeric characters</strong> (no special chars like <code>#*%\^</code>).</li>
            <li>Maximum upload size is <strong>2MB</strong> per file.</li>
            <li>Upload documents to the <strong>correct fields</strong> to avoid rejection.</li>
            <li>This process is <strong class="text-red-600">irreversible</strong> — ensure all documents are correct before submitting.</li>
            <li><strong>Track</strong> your application from the <a href="track_application.php" class="text-ncwsc-blue underline">Track Application</a> tab.</li>
        </ol>
    </div>

    <!-- Required Documents Box -->
    <div class="border-l-4 border-ncwsc-blue bg-white p-5 mb-6 rounded-md shadow-sm">
        <div class="flex items-center gap-2 mb-4">
            <i data-lucide="clipboard-list" class="w-5 h-5 text-ncwsc-blue"></i>
            <h2 class="font-bold text-gray-800">Required Documents</h2>
        </div>

        <h3 class="font-semibold text-gray-800 text-sm mb-2">Individuals</h3>
        <ol class="list-decimal list-inside text-sm text-gray-700 space-y-1 mb-4">
            <li>Photocopy of applicant's ID and KRA PIN</li>
            <li>Property location sketch map</li>
            <li>One passport size photograph</li>
            <li>Copy of Lease/Tenancy Agreement/Title Deed/Rates Demand Note</li>
            <li>Copy of landlord's ID &amp; KRA PIN (tenant applications)</li>
            <li><span class="text-red-600 font-semibold">Passport photo is mandatory</span> — failure to upload will cause rejection</li>
        </ol>

        <h3 class="font-semibold text-gray-800 text-sm mb-2">Companies</h3>
        <ol class="list-decimal list-inside text-sm text-gray-700 space-y-1 mb-4">
            <li>Copy of PIN and Certificate of Incorporation</li>
            <li>Property location sketch map</li>
            <li>Copy of Lease/Tenancy Agreement/Title Deed/Rates Demand Note</li>
            <li>Copy of landlord's ID &amp; KRA PIN (tenant applications)</li>
        </ol>

        <h3 class="font-semibold text-gray-800 text-sm mb-2">Sewer Applications</h3>
        <p class="text-sm text-gray-700 mb-2">Drawing showing plan, ground levels and invert levels of the proposed drain.</p>
        <p class="text-sm text-gray-700 mb-4">Borehole applicants: attach WRA approvals + NCWSC Letter of No Objection.</p>

        <div class="text-xs text-gray-600 flex flex-wrap gap-4 pt-2 border-t border-gray-200">
            <span class="flex items-center gap-1"><i data-lucide="phone" class="w-3 h-3"></i> +254703080000 / +254703080598</span>
            <span class="flex items-center gap-1"><i data-lucide="mail" class="w-3 h-3"></i> info@nairobiwater.co.ke</span>
        </div>
    </div>

    <!-- Application Number + Upload Form -->
    <div class="border-l-4 border-ncwsc-blue bg-white p-5 rounded-md shadow-sm">
        <div class="flex items-center gap-2 mb-4">
            <i data-lucide="hash" class="w-5 h-5 text-ncwsc-blue"></i>
            <h2 class="font-bold text-gray-800">Select Application</h2>
        </div>

        <form action="upload_application_process.php" method="POST" enctype="multipart/form-data">
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Application Number <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded ml-1">Mandatory</span>
                </label>
                <select name="application_number" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                    <option value="">-- Choose Application Number --</option>
                    <?php foreach ($apps as $a): ?>
                        <option value="<?php echo htmlspecialchars($a); ?>"
                            <?php echo ($preselect === $a) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($a); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($apps)): ?>
                    <p class="text-xs text-gray-500 mt-2">
                        You have no applications yet.
                        <a href="apply.php" class="text-ncwsc-blue underline">Start one here.</a>
                    </p>
                <?php endif; ?>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-blue text-white font-semibold py-2 px-6 rounded-md transition">
                    Continue
                </button>
            </div>
        </form>
    </div>

</div>

<?php include 'footer.php'; ?>