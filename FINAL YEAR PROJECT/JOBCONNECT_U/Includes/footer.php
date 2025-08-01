</main>

    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">JobConnectUganda</h3>
                    <p class="text-gray-300">Connecting talented professionals with top employers in Uganda.</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="<?php echo BASE_URL; ?>/jobs.php" class="text-gray-300 hover:text-white">Browse Jobs</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white">About Us</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white">Contact</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white">Privacy Policy</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contact Us</h4>
                    <address class="text-gray-300 not-italic">
                        <p>Kampala, Uganda</p>
                        <p>Email: info@jobconnect.ug</p>
                        <p>Phone: +256 700 000000</p>
                    </address>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-6 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> JobConnectUganda. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
    </script>
    <?php
    // Section-specific JS
    $path = $_SERVER['SCRIPT_NAME'];
    if (strpos($path, '/admin/') !== false) {
        echo '<script src="' . BASE_URL . '/assets/javascript/admin.js" defer></script>';
    } elseif (strpos($path, '/employer/') !== false) {
        echo '<script src="' . BASE_URL . '/assets/javascript/employer.js" defer></script>';
    } elseif (strpos($path, '/job-seeker/') !== false) {
        echo '<script src="' . BASE_URL . '/assets/javascript/job-seeker.js" defer></script>';
    }
    ?>
</body>
</html>