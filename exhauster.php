<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
include 'header.php';

$step = isset($_GET['step']) ? intval($_GET['step']) : 1;

$steps = [
    1 => 'Documents',
    2 => 'Identification',
    3 => 'Personal Information',
    4 => 'Contact Information',
    5 => 'Alternate Contact',
    6 => 'Vehicle Details',
    7 => 'Review & Submit'
];
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-ncwsc-blue">Exhauster Permit</h1>
        <p class="text-sm text-gray-500 mt-1">Private Exhauster Permit Application</p>
    </div>

    <div class="flex flex-col md:flex-row gap-8">

        <!-- Sidebar Stepper -->
        <div class="w-full md:w-1/3 bg-white p-6 rounded-lg shadow-md border border-gray-100">
            <ul class="space-y-4">
                <?php foreach ($steps as $num => $label): ?>
                    <li class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                            <?php echo $num == $step ? 'bg-ncwsc-blue text-white' : 'bg-gray-200 text-gray-600'; ?>">
                            <?php echo $num; ?>
                        </span>
                        <span class="text-sm <?php echo $num == $step ? 'text-ncwsc-blue font-semibold' : 'text-gray-500'; ?>">
                            <?php echo $label; ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="w-full md:w-2/3 bg-white p-8 rounded-lg shadow-md border border-gray-100">

            <?php if ($step == 1): ?>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Upload Documents</h2>
                <p class="text-sm text-gray-500 mb-6">Upload all required supporting documents before proceeding</p>

                <div class="bg-blue-50 border-l-4 border-ncwsc-blue p-4 mb-6 text-sm text-gray-700">
                    <i data-lucide="info" class="w-4 h-4 inline mr-2"></i>
                    All documents must be in <strong>PDF</strong> format, maximum <strong>2 MB</strong> each. All four are mandatory.
                </div>

                <form action="exhauster_step.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="step" value="1">

                    <?php
                    $docs = [
                        'doc_kra_pin'         => 'KRA PIN CERTIFICATE',
                        'doc_nema'            => 'NEMA CERTIFICATE',
                        'doc_vehicle_logbook' => 'VEHICLE LOGBOOK',
                        'doc_vehicle_photo'   => 'MOTOR VEHICLE PHOTO'
                    ];
                    foreach ($docs as $key => $doc): ?>
                        <div class="border-l-4 border-ncwsc-blue bg-gray-50 rounded-md p-4 mb-4">
                            <div class="flex items-center justify-between mb-3">
                                <label class="font-semibold text-gray-800 text-sm"><?php echo $doc; ?></label>
                                <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded">Mandatory</span>
                            </div>
                            <input type="file" name="<?php echo $key; ?>" accept="application/pdf" required
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                                          file:rounded-md file:border-0 file:text-sm file:font-semibold
                                          file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                            <p class="text-xs text-gray-400 mt-1">PDF only · max 2 MB</p>
                        </div>
                    <?php endforeach; ?>

                    <div class="flex justify-end mt-8">
                        <button type="submit" class="btn-blue text-white font-semibold py-2 px-6 rounded-md flex items-center gap-2">
                            Continue <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>

            <?php elseif ($step == 2): ?>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Identification</h2>
                <p class="text-sm text-gray-500 mb-6">Provide your identification details</p>

                <form action="exhauster_step.php" method="POST">
                    <input type="hidden" name="step" value="2">

                    <div class="space-y-4 max-w-md">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ID Number:<span class="text-red-500">*</span></label>
                            <input type="text" name="id_number" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ID Type:<span class="text-red-500">*</span></label>
                            <select name="id_type" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                                <option>National ID</option>
                                <option>Passport</option>
                                <option>Alien ID</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-between mt-8">
                        <a href="exhauster.php?step=1" class="text-gray-500 hover:text-ncwsc-blue font-semibold py-2">Back</a>
                        <button type="submit" class="btn-blue text-white font-semibold py-2 px-6 rounded-md flex items-center gap-2">
                            Continue <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>

            <?php elseif ($step == 3): ?>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Personal Information</h2>
                <p class="text-sm text-gray-500 mb-6">Tell us about yourself</p>

                <form action="exhauster_step.php" method="POST">
                    <input type="hidden" name="step" value="3">

                    <div class="space-y-4 max-w-md">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name:<span class="text-red-500">*</span></label>
                            <input type="text" name="full_name" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth:<span class="text-red-500">*</span></label>
                            <input type="date" name="date_of_birth" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                        </div>
                    </div>

                    <div class="flex justify-between mt-8">
                        <a href="exhauster.php?step=2" class="text-gray-500 hover:text-ncwsc-blue font-semibold py-2">Back</a>
                        <button type="submit" class="btn-blue text-white font-semibold py-2 px-6 rounded-md flex items-center gap-2">
                            Continue <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>

            <?php elseif ($step == 4): ?>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Contact Information</h2>
                <p class="text-sm text-gray-500 mb-6">How can we reach you?</p>

                <form action="exhauster_step.php" method="POST">
                    <input type="hidden" name="step" value="4">

                    <div class="space-y-4 max-w-md">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email:<span class="text-red-500">*</span></label>
                            <input type="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone:<span class="text-red-500">*</span></label>
                            <input type="tel" name="phone" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                        </div>
                    </div>

                    <div class="flex justify-between mt-8">
                        <a href="exhauster.php?step=3" class="text-gray-500 hover:text-ncwsc-blue font-semibold py-2">Back</a>
                        <button type="submit" class="btn-blue text-white font-semibold py-2 px-6 rounded-md flex items-center gap-2">
                            Continue <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>

            <?php elseif ($step == 5): ?>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Alternate Contact</h2>
                <p class="text-sm text-gray-500 mb-6">Optional alternate contact person</p>

                <form action="exhauster_step.php" method="POST">
                    <input type="hidden" name="step" value="5">

                    <div class="space-y-4 max-w-md">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alternate Name:</label>
                            <input type="text" name="alt_name" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alternate Phone:</label>
                            <input type="tel" name="alt_phone" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                        </div>
                    </div>

                    <div class="flex justify-between mt-8">
                        <a href="exhauster.php?step=4" class="text-gray-500 hover:text-ncwsc-blue font-semibold py-2">Back</a>
                        <button type="submit" class="btn-blue text-white font-semibold py-2 px-6 rounded-md flex items-center gap-2">
                            Continue <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>

            <?php elseif ($step == 6): ?>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Vehicle Details</h2>
                <p class="text-sm text-gray-500 mb-6">Details of the exhauster vehicle</p>

                <form action="exhauster_step.php" method="POST">
                    <input type="hidden" name="step" value="6">

                    <div class="space-y-4 max-w-md">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Registration:<span class="text-red-500">*</span></label>
                            <input type="text" name="vehicle_registration" required placeholder="e.g. KDA 123X"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Capacity (Litres):<span class="text-red-500">*</span></label>
                            <input type="number" name="vehicle_capacity" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                        </div>
                    </div>

                    <div class="flex justify-between mt-8">
                        <a href="exhauster.php?step=5" class="text-gray-500 hover:text-ncwsc-blue font-semibold py-2">Back</a>
                        <button type="submit" class="btn-blue text-white font-semibold py-2 px-6 rounded-md flex items-center gap-2">
                            Continue <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>

            <?php else: ?>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Review &amp; Submit</h2>
                <p class="text-sm text-gray-500 mb-6">Please review your application before submitting</p>

                <?php $d = $_SESSION['exhauster_wizard'] ?? []; ?>

                <div class="bg-gray-50 border border-gray-200 rounded-md p-4 mb-6 text-sm text-gray-700 space-y-1">
                    <p><strong>ID:</strong> <?php echo htmlspecialchars($d['id_number'] ?? '—'); ?></p>
                    <p><strong>Full Name:</strong> <?php echo htmlspecialchars($d['full_name'] ?? '—'); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($d['email'] ?? '—'); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($d['phone'] ?? '—'); ?></p>
                    <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($d['vehicle_registration'] ?? '—'); ?>
                       (<?php echo htmlspecialchars($d['vehicle_capacity'] ?? '—'); ?> L)</p>
                </div>

                <form action="exhauster_step.php" method="POST">
                    <input type="hidden" name="step" value="7">

                    <div class="flex items-center gap-2 mb-6">
                        <input type="checkbox" name="confirm" id="confirm" required class="w-4 h-4">
                        <label for="confirm" class="text-sm text-gray-700">
                            I confirm that all the information provided is accurate.
                        </label>
                    </div>

                    <div class="flex justify-between">
                        <a href="exhauster.php?step=6" class="text-gray-500 hover:text-ncwsc-blue font-semibold py-2">Back</a>
                        <button type="submit" class="btn-green text-white font-semibold py-2 px-6 rounded-md transition">
                            Submit Application
                        </button>
                    </div>
                </form>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include 'footer.php'; ?>