</main>

    <footer class="bg-white border-t border-gray-100 mt-12">
      <div class="container mx-auto px-4 py-10">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div>
            <a href="<?php echo BASE_URL; ?>/index.php" class="flex items-center gap-3 mb-3">
              <svg class="w-10 h-10 text-blue-700" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" role="img" aria-label="JobConnectUganda">
                <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M9 6V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v1m4 4H5m14 0v8a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-8m14 0H5" />
                </svg>
              <span class="text-lg font-bold text-blue-700">JobConnectUganda</span>
            </a>
            <p class="text-gray-600 text-sm">Connecting job seekers and employers across Uganda with a modern, easy-to-use platform.</p>
          </div>

          <div>
            <h4 class="text-gray-900 font-semibold mb-3">Explore</h4>
            <ul class="text-sm text-gray-600 space-y-2">
              <li><a href="<?php echo BASE_URL; ?>/jobs.php" class="hover:text-blue-700">Browse Jobs</a></li>
              <li><a href="<?php echo BASE_URL; ?>/register.php" class="hover:text-blue-700">Create Account</a></li>
              <li><a href="<?php echo BASE_URL; ?>/about.php" class="hover:text-blue-700">About Us</a></li>
            </ul>
          </div>

          <div>
            <h4 class="text-gray-900 font-semibold mb-3">For Employers</h4>
            <ul class="text-sm text-gray-600 space-y-2">
              <li><a href="<?php echo BASE_URL; ?>/employer/jobs.php" class="hover:text-blue-700">Post a Job</a></li>
              <li><a href="<?php echo BASE_URL; ?>/employer/dashboard.php" class="hover:text-blue-700">Manage Jobs</a></li>
            </ul>
          </div>

          <div>
            <h4 class="text-gray-900 font-semibold mb-3">Contact</h4>
            <p class="text-sm text-gray-600">support@jobconnectuganda.ug</p>
            <p class="text-sm text-gray-600">+256 700 000 000</p>
            <div class="mt-4 flex items-center gap-3">
              <a href="#" class="w-8 h-8 bg-blue-50 text-blue-600 rounded-md flex items-center justify-center">F</a>
              <a href="#" class="w-8 h-8 bg-blue-50 text-blue-600 rounded-md flex items-center justify-center">T</a>
              <a href="#" class="w-8 h-8 bg-blue-50 text-blue-600 rounded-md flex items-center justify-center">L</a>
            </div>
          </div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-100 text-sm text-gray-500 flex flex-col md:flex-row md:items-center md:justify-between">
          <div>© <?php echo date('Y'); ?> JobConnectUganda. All rights reserved.</div>
          <div class="mt-3 md:mt-0">
            <a href="<?php echo BASE_URL; ?>/privacy.php" class="mr-4 hover:text-blue-700">Privacy</a>
            <a href="<?php echo BASE_URL; ?>/terms.php" class="hover:text-blue-700">Terms</a>
          </div>
        </div>
      </div>
    </footer>

    <script src="<?php echo BASE_URL; ?>/Assets/Javascript/main.js"></script>
    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
    </script>
    <script>
      // small accessibility: focus outlines for keyboard users
      document.addEventListener('keyup', function(e){
        if (e.key === 'Tab') document.documentElement.classList.add('user-tabbing');
      });
    </script>
    <?php
    // Section-specific JS
    $path = $_SERVER['SCRIPT_NAME'];
    if (strpos($path, '/admin/') !== false) {
        echo '<script src="' . BASE_URL . '/Assets/Javascript/admin.js" defer></script>';
    } elseif (strpos($path, '/employer/') !== false) {
        echo '<script src="' . BASE_URL . '/Assets/Javascript/employer.js" defer></script>';
    } elseif (strpos($path, '/job-seeker/') !== false) {
        echo '<script src="' . BASE_URL . '/Assets/Javascript/job-seeker.js" defer></script>';
    }
    ?>
</body>
</html>