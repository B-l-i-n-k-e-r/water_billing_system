</main>

<!-- Light Footer — meta links -->
<footer class="bg-white border-t border-slate-200 py-6 mt-12 no-print">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-center text-center md:text-left gap-4">
            <div class="text-sm text-slate-600 flex flex-wrap justify-center md:justify-start items-center gap-x-2 gap-y-1">
                <a href="#" class="hover:text-teal-700 transition">MATERIALS INFORMATION</a>
                <span class="text-slate-300">|</span>
                <a href="#" class="hover:text-teal-700 transition">APPLICATION INFORMATION</a>
                <span class="text-slate-300">|</span>
                <a href="#" class="hover:text-teal-700 transition">FEES AND CHARGES</a>
                <span class="text-slate-300">|</span>
                <a href="#" class="hover:text-teal-700 transition">PRIVACY POLICY</a>
                <span class="text-slate-300">|</span>
                <a href="#" class="hover:text-teal-700 transition">TERMS &amp; CONDITIONS</a>
            </div>
            <div class="text-sm text-slate-600">
                Copyright &copy; 2020&ndash;2026. All rights reserved.
            </div>
        </div>
    </div>
</footer>

<!-- Main Footer — deep navy -->
<footer class="bg-slate-900 text-slate-100 py-12 no-print">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            <div>
                <h3 class="text-sm font-bold tracking-widest text-teal-300 uppercase mb-4">Customer Service</h3>
                <ul class="space-y-2 text-sm text-slate-300">
                    <li><a href="#" class="hover:text-white transition">Pay Points</a></li>
                    <li><a href="#" class="hover:text-white transition">New Water Connections</a></li>
                    <li><a href="#" class="hover:text-white transition">FAQs</a></li>
                    <li><a href="#" class="hover:text-white transition">Customer Charter</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-bold tracking-widest text-teal-300 uppercase mb-4">Quick Links</h3>
                <ul class="space-y-2 text-sm text-slate-300">
                    <li><a href="#" class="hover:text-white transition">Work with Us</a></li>
                    <li><a href="#" class="hover:text-white transition">Contact Us</a></li>
                    <li><a href="#" class="hover:text-white transition">Tenders</a></li>
                    <li><a href="#" class="hover:text-white transition">News Watch</a></li>
                    <li><a href="#" class="hover:text-white transition">Interruption Notices</a></li>
                    <li><a href="#" class="hover:text-white transition">Staff Mail</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-bold tracking-widest text-teal-300 uppercase mb-4">Contact Us</h3>

                <div class="flex flex-wrap gap-4 text-sm text-slate-300 mb-4">
                    <span class="flex items-center gap-1"><i data-lucide="phone" class="w-4 h-4"></i> Call</span>
                    <span class="flex items-center gap-1"><i data-lucide="mail" class="w-4 h-4"></i> Email</span>
                    <span class="flex items-center gap-1"><i data-lucide="map-pin" class="w-4 h-4"></i> Locations</span>
                </div>

                <p class="text-sm text-slate-300">Working Hours:</p>
                <p class="text-sm text-white font-semibold mb-4">Mon &ndash; Fri: 8.00 a.m &ndash; 5.00 p.m</p>

                <p class="text-sm text-slate-300 underline mb-1">Call center</p>
                <p class="text-sm text-slate-300">Mon &ndash; Fri: 7.30 a.m &ndash; 9.30 p.m</p>

                <p class="text-sm text-slate-300 mt-3">+254 702 206 418</p>
                <p class="text-sm text-slate-300">+254 702 206 328</p>
                <p class="text-sm text-slate-300">+254 702 206 459</p>
                <p class="text-sm text-slate-300">+254 702 205 964</p>
            </div>
        </div>

        <div class="mt-12 pt-8 border-t border-slate-700/60 text-center">
            <div class="flex justify-center gap-6 mb-4">
                <a href="#" aria-label="Facebook" class="text-slate-400 hover:text-teal-300 transition"><i data-lucide="facebook" class="w-5 h-5"></i></a>
                <a href="#" aria-label="Twitter" class="text-slate-400 hover:text-teal-300 transition"><i data-lucide="twitter" class="w-5 h-5"></i></a>
                <a href="#" aria-label="YouTube" class="text-slate-400 hover:text-teal-300 transition"><i data-lucide="youtube" class="w-5 h-5"></i></a>
            </div>
            <p class="text-sm text-slate-400">
                <a href="#" class="hover:text-teal-300 transition">Privacy Policy</a>
            </p>
            <p class="text-sm text-slate-400 mt-1">
                &copy; <?= date('Y') ?> Nairobi City Water and Sewerage Company
            </p>
        </div>
    </div>
</footer>

<script>
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
</body>
</html>