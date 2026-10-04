<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
include 'header.php';

$step = isset($_GET['step']) ? intval($_GET['step']) : 1;

$steps = [
    1  => 'Application Type',
    2  => 'Service Type',
    3  => 'Document Upload',
    4  => 'Identification',
    5  => 'Applicant Details',
    6  => 'Contact Information',
    7  => 'Alternate Contact',
    8  => 'Supply Details',
    9  => 'Type of Supply',
    10 => 'Property Location',
    11 => 'Confirmation'
];
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col md:flex-row gap-8">

        <!-- Sidebar Stepper -->
        <div class="w-full md:w-1/3 bg-white p-6 rounded-lg shadow-md border border-gray-100">
            <h2 class="text-xl font-bold text-ncwsc-blue mb-2">Water &amp; Sewer Application</h2>
            <p class="text-sm text-gray-500 mb-6">Complete your application in a few simple steps</p>

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

        <!-- Main Content Area -->
        <div class="w-full md:w-2/3 bg-white p-8 rounded-lg shadow-md border border-gray-100">

        <?php if ($step == 1): ?>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Application Type</h2>
            <p class="text-sm text-gray-500 mb-6">Select customer type</p>

            <div class="bg-blue-50 border-l-4 border-ncwsc-blue p-4 mb-6 text-sm text-gray-700">
                <i data-lucide="info" class="w-4 h-4 inline mr-2"></i>
                Please select the type of application that best describes your situation.
            </div>

            <form action="apply_step.php" method="POST">
                <input type="hidden" name="step" value="1">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <label class="border-2 border-gray-200 rounded-lg p-6 flex flex-col items-center cursor-pointer hover:border-ncwsc-blue transition has-[:checked]:border-ncwsc-blue has-[:checked]:bg-blue-50">
                        <input type="radio" name="app_type" value="individual" class="sr-only" required>
                        <i data-lucide="user" class="w-12 h-12 text-ncwsc-blue mb-3 pointer-events-none"></i>
                        <span class="font-semibold text-gray-800 pointer-events-none">Individual</span>
                        <span class="text-xs text-gray-500 text-center mt-1 pointer-events-none">For personal water connection</span>
                    </label>
                    <label class="border-2 border-gray-200 rounded-lg p-6 flex flex-col items-center cursor-pointer hover:border-ncwsc-blue transition has-[:checked]:border-ncwsc-blue has-[:checked]:bg-blue-50">
                        <input type="radio" name="app_type" value="business" class="sr-only" required>
                        <i data-lucide="building" class="w-12 h-12 text-ncwsc-blue mb-3 pointer-events-none"></i>
                        <span class="font-semibold text-gray-800 pointer-events-none">Business</span>
                        <span class="text-xs text-gray-500 text-center mt-1 pointer-events-none">For commercial water connection</span>
                    </label>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn-blue text-white font-semibold py-2 px-6 rounded-md flex items-center gap-2">
                        Continue <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>

        <?php elseif ($step == 2): ?>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Service Type</h2>
            <p class="text-sm text-gray-500 mb-6">What kind of service do you need?</p>

            <form action="apply_step.php" method="POST">
                <input type="hidden" name="step" value="2">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <label class="border-2 border-gray-200 rounded-lg p-6 flex flex-col items-center cursor-pointer hover:border-ncwsc-blue transition has-[:checked]:border-ncwsc-blue has-[:checked]:bg-blue-50">
                        <input type="radio" name="service_type" value="water" class="sr-only" required>
                        <i data-lucide="droplet" class="w-12 h-12 text-ncwsc-blue mb-3 pointer-events-none"></i>
                        <span class="font-semibold text-gray-800 pointer-events-none">Water</span>
                        <span class="text-xs text-gray-500 text-center mt-1 pointer-events-none">New or additional water connection</span>
                    </label>
                    <label class="border-2 border-gray-200 rounded-lg p-6 flex flex-col items-center cursor-pointer hover:border-ncwsc-blue transition has-[:checked]:border-ncwsc-blue has-[:checked]:bg-blue-50">
                        <input type="radio" name="service_type" value="sewer" class="sr-only" required>
                        <i data-lucide="waves" class="w-12 h-12 text-ncwsc-blue mb-3 pointer-events-none"></i>
                        <span class="font-semibold text-gray-800 pointer-events-none">Sewer</span>
                        <span class="text-xs text-gray-500 text-center mt-1 pointer-events-none">Sewer connection application</span>
                    </label>
                    <label class="border-2 border-gray-200 rounded-lg p-6 flex flex-col items-center cursor-pointer hover:border-ncwsc-blue transition has-[:checked]:border-ncwsc-blue has-[:checked]:bg-blue-50 md:col-span-2">
                        <input type="radio" name="service_type" value="both" class="sr-only" required>
                        <i data-lucide="layers" class="w-12 h-12 text-ncwsc-blue mb-3 pointer-events-none"></i>
                        <span class="font-semibold text-gray-800 pointer-events-none">Water &amp; Sewer</span>
                        <span class="text-xs text-gray-500 text-center mt-1 pointer-events-none">Both connections</span>
                    </label>
                </div>

                <div class="flex justify-between">
                    <a href="apply.php?step=1" class="text-gray-500 hover:text-ncwsc-blue font-semibold py-2">Back</a>
                    <button type="submit" class="btn-blue text-white font-semibold py-2 px-6 rounded-md flex items-center gap-2">
                        Continue <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>

        <?php elseif ($step == 3): ?>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Upload Documents</h2>
            <p class="text-sm text-gray-500 mb-6">Upload all required supporting documents before proceeding</p>

            <div class="bg-blue-50 border-l-4 border-ncwsc-blue p-4 mb-6 text-sm text-gray-700">
                <i data-lucide="info" class="w-4 h-4 inline mr-2"></i>
                All documents must be in <strong>PDF</strong> format, maximum <strong>2 MB</strong> each. All four are mandatory.
            </div>

            <form action="apply_step.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="step" value="3">

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
                                      file:bg-ncwsc-blue file:text-white hover:file:bg-blue-700 cursor-pointer">
                        <p class="text-xs text-gray-400 mt-1">PDF only · max 2 MB</p>
                    </div>
                <?php endforeach; ?>

                <div class="flex justify-between mt-8">
                    <a href="apply.php?step=2" class="text-gray-500 hover:text-ncwsc-blue font-semibold py-2">Back</a>
                    <button type="submit" class="btn-blue text-white font-semibold py-2 px-6 rounded-md flex items-center gap-2">
                        Continue <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>

        <?php elseif ($step == 4): ?>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Identification</h2>
            <p class="text-sm text-gray-500 mb-6">Provide your identification details</p>

            <form action="apply_step.php" method="POST">
                <input type="hidden" name="step" value="4">

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
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">KRA PIN:<span class="text-red-500">*</span></label>
                        <input type="text" name="kra_pin" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                    </div>
                </div>

                <div class="flex justify-between mt-8">
                    <a href="apply.php?step=3" class="text-gray-500 hover:text-ncwsc-blue font-semibold py-2">Back</a>
                    <button type="submit" class="btn-blue text-white font-semibold py-2 px-6 rounded-md flex items-center gap-2">
                        Continue <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>

        <?php elseif ($step == 5): ?>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Applicant Details</h2>
            <p class="text-sm text-gray-500 mb-6">Tell us about the applicant</p>

            <form action="apply_step.php" method="POST">
                <input type="hidden" name="step" value="5">

                <div class="space-y-4 max-w-md">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name:<span class="text-red-500">*</span></label>
                        <input type="text" name="full_name" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth:<span class="text-red-500">*</span></label>
                        <input type="date" name="date_of_birth" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gender:<span class="text-red-500">*</span></label>
                        <select name="gender" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                            <option value="">Select</option>
                            <option>Male</option>
                            <option>Female</option>
                            <option>Prefer not to say</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-between mt-8">
                    <a href="apply.php?step=4" class="text-gray-500 hover:text-ncwsc-blue font-semibold py-2">Back</a>
                    <button type="submit" class="btn-blue text-white font-semibold py-2 px-6 rounded-md flex items-center gap-2">
                        Continue <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>

        <?php elseif ($step == 6): ?>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Contact Information</h2>
            <p class="text-sm text-gray-500 mb-6">How can we reach you?</p>

            <form action="apply_step.php" method="POST">
                <input type="hidden" name="step" value="6">

                <div class="space-y-4 max-w-md">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email:<span class="text-red-500">*</span></label>
                        <input type="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone:<span class="text-red-500">*</span></label>
                        <input type="tel" name="phone" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Postal Address:</label>
                        <input type="text" name="postal_address" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                    </div>
                </div>

                <div class="flex justify-between mt-8">
                    <a href="apply.php?step=5" class="text-gray-500 hover:text-ncwsc-blue font-semibold py-2">Back</a>
                    <button type="submit" class="btn-blue text-white font-semibold py-2 px-6 rounded-md flex items-center gap-2">
                        Continue <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>

        <?php elseif ($step == 7): ?>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Alternate Contact</h2>
            <p class="text-sm text-gray-500 mb-6">Optional alternate contact person</p>

            <form action="apply_step.php" method="POST">
                <input type="hidden" name="step" value="7">

                <div class="space-y-4 max-w-md">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alternate Name:</label>
                        <input type="text" name="alt_name" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alternate Phone:</label>
                        <input type="tel" name="alt_phone" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Relationship:</label>
                        <input type="text" name="alt_relationship" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                    </div>
                </div>

                <div class="flex justify-between mt-8">
                    <a href="apply.php?step=6" class="text-gray-500 hover:text-ncwsc-blue font-semibold py-2">Back</a>
                    <button type="submit" class="btn-blue text-white font-semibold py-2 px-6 rounded-md flex items-center gap-2">
                        Continue <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>

        <?php elseif ($step == 8): ?>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Supply Details</h2>
            <p class="text-sm text-gray-500 mb-6">Details about your supply requirements</p>

            <form action="apply_step.php" method="POST">
                <input type="hidden" name="step" value="8">

                <div class="space-y-4 max-w-md">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Premises Type:<span class="text-red-500">*</span></label>
                        <select name="premises_type" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                            <option>Residential</option>
                            <option>Commercial</option>
                            <option>Industrial</option>
                            <option>Institutional</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Number of Units:<span class="text-red-500">*</span></label>
                        <input type="number" name="units" required min="1" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estimated Daily Demand (m³):</label>
                        <input type="number" name="daily_demand" step="0.1" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                    </div>
                </div>

                <div class="flex justify-between mt-8">
                    <a href="apply.php?step=7" class="text-gray-500 hover:text-ncwsc-blue font-semibold py-2">Back</a>
                    <button type="submit" class="btn-blue text-white font-semibold py-2 px-6 rounded-md flex items-center gap-2">
                        Continue <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>

        <?php elseif ($step == 9): ?>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Type of Supply</h2>
            <p class="text-sm text-gray-500 mb-6">How will the supply be metered?</p>

            <form action="apply_step.php" method="POST">
                <input type="hidden" name="step" value="9">

                <div class="space-y-4 max-w-md">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Meter Size (inches):<span class="text-red-500">*</span></label>
                        <select name="meter_size" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                            <option>1/2"</option>
                            <option>3/4"</option>
                            <option>1"</option>
                            <option>1.5"</option>
                            <option>2"</option>
                            <option>3"</option>
                            <option>4"</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Supply Category:<span class="text-red-500">*</span></label>
                        <select name="supply_category" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                            <option>Domestic</option>
                            <option>Commercial</option>
                            <option>Industrial</option>
                            <option>Bulk</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-between mt-8">
                    <a href="apply.php?step=8" class="text-gray-500 hover:text-ncwsc-blue font-semibold py-2">Back</a>
                    <button type="submit" class="btn-blue text-white font-semibold py-2 px-6 rounded-md flex items-center gap-2">
                        Continue <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>

        <?php elseif ($step == 10): ?>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Property Location</h2>
            <p class="text-sm text-gray-500 mb-6">Where is the property located?</p>

            <form action="apply_step.php" method="POST">
                <input type="hidden" name="step" value="10">

                <div class="space-y-4 max-w-md">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">County:<span class="text-red-500">*</span></label>
                        <input type="text" name="county" value="Nairobi" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estate / Area:<span class="text-red-500">*</span></label>
                        <input type="text" name="estate" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Plot / LR Number:<span class="text-red-500">*</span></label>
                        <input type="text" name="plot_number" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Street / Road:</label>
                        <input type="text" name="street" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-ncwsc-blue outline-none">
                    </div>
                </div>

                <div class="flex justify-between mt-8">
                    <a href="apply.php?step=9" class="text-gray-500 hover:text-ncwsc-blue font-semibold py-2">Back</a>
                    <button type="submit" class="btn-blue text-white font-semibold py-2 px-6 rounded-md flex items-center gap-2">
                        Continue <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>

        <?php else: ?>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Confirmation</h2>
            <p class="text-sm text-gray-500 mb-6">Review and submit your application</p>

            <div class="bg-gray-50 border border-gray-200 rounded-md p-4 mb-6 text-sm text-gray-600">
                Please confirm all details you've provided across all steps are accurate. Once submitted, this application will be reviewed by NCWSC staff.
            </div>

            <form action="apply_step.php" method="POST">
                <input type="hidden" name="step" value="11">

                <div class="flex items-center gap-2 mb-6">
                    <input type="checkbox" name="confirm" id="confirm" required class="w-4 h-4">
                    <label for="confirm" class="text-sm text-gray-700">
                        I confirm that all the information provided is accurate.
                    </label>
                </div>

                <div class="flex justify-between">
                    <a href="apply.php?step=10" class="text-gray-500 hover:text-ncwsc-blue font-semibold py-2">Back</a>
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