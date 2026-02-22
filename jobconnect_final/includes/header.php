<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JobConnectUganda - <?php echo $pageTitle ?? 'Find Your Dream Job'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/styles.css">
    <script src="<?php echo BASE_URL; ?>/assets/javascript/main.js" defer></script>
</head>
<body class="bg-gray-100">
    <header class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <a href="<?php echo BASE_URL; ?>" class="flex items-center">
                        <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                            <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"></path>
                        </svg>
                        <span class="ml-2 text-xl font-bold text-blue-600">JobConnectUganda</span>
                    </a>
                </div>
                
                <nav class="hidden md:flex space-x-8">
                    <a href="<?php echo BASE_URL; ?>/jobs.php" class="text-gray-700 hover:text-blue-600 font-medium">Browse Jobs</a>
                    <?php if (isLoggedIn()): ?>
                        <?php if (isJobSeeker()): ?>
                            <a href="<?php echo BASE_URL; ?>/job-seeker/dashboard.php" class="text-gray-700 hover:text-blue-600 font-medium">Dashboard</a>
                        <?php elseif (isEmployer()): ?>
                            <a href="<?php echo BASE_URL; ?>/employer/dashboard.php" class="text-gray-700 hover:text-blue-600 font-medium">Dashboard</a>
                        <?php elseif (isAdmin()): ?>
                            <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="text-gray-700 hover:text-blue-600 font-medium">Admin</a>
                        <?php endif; ?>
                        <a href="<?php echo BASE_URL; ?>/logout.php" class="text-gray-700 hover:text-blue-600 font-medium">Logout</a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/login.php" class="text-gray-700 hover:text-blue-600 font-medium">Login</a>
                        <a href="<?php echo BASE_URL; ?>/register.php" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-300">Register</a>
                    <?php endif; ?>
                </nav>
                
                <!-- Mobile menu button -->
                <button class="md:hidden focus:outline-none" id="mobile-menu-button">
                    <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Mobile menu -->
            <div class="md:hidden hidden mt-4" id="mobile-menu">
                <div class="flex flex-col space-y-3">
                    <a href="<?php echo BASE_URL; ?>/jobs.php" class="text-gray-700 hover:text-blue-600 font-medium">Browse Jobs</a>
                    <?php if (isLoggedIn()): ?>
                        <?php if (isJobSeeker()): ?>
                            <a href="<?php echo BASE_URL; ?>/job-seeker/dashboard.php" class="text-gray-700 hover:text-blue-600 font-medium">Dashboard</a>
                        <?php elseif (isEmployer()): ?>
                            <a href="<?php echo BASE_URL; ?>/employer/dashboard.php" class="text-gray-700 hover:text-blue-600 font-medium">Dashboard</a>
                        <?php elseif (isAdmin()): ?>
                            <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="text-gray-700 hover:text-blue-600 font-medium">Admin</a>
                        <?php endif; ?>
                        <a href="<?php echo BASE_URL; ?>/logout.php" class="text-gray-700 hover:text-blue-600 font-medium">Logout</a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/login.php" class="text-gray-700 hover:text-blue-600 font-medium">Login</a>
                        <a href="<?php echo BASE_URL; ?>/register.php" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-300 text-center">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <?php displayMessage(); ?>