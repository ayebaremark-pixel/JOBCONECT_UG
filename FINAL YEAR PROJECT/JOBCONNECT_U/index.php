<?php
require_once 'includes/config.php'; // $pdo is now available
$pageTitle = 'Home';

// Get featured jobs
$stmt = $pdo->query("SELECT j.*, e.company_name, e.logo 
                     FROM jobs j 
                     JOIN employers e ON j.employer_id = e.employer_id 
                     WHERE j.is_active = TRUE 
                     ORDER BY j.posted_at DESC 
                     LIMIT 6");
$featuredJobs = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<!-- Add CSS and JS links here if not already in header.php -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/styles.css">
<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>

<section class="hero bg-gradient-to-r from-blue-700 via-blue-500 to-blue-400 text-white py-16" role="banner" aria-label="Homepage Hero">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold mb-6">Find Your Dream Job in Uganda</h1>
        <p class="text-xl mb-8 max-w-2xl mx-auto">Connect with top employers and discover opportunities that match your skills and aspirations.</p>
        <div class="flex flex-col md:flex-row justify-center gap-4">
            <a href="<?php echo BASE_URL; ?>/register.php?type=job_seeker" class="bg-white text-blue-700 px-6 py-3 rounded-md font-medium hover:bg-blue-100 transition duration-300 shadow">I'm Looking for a Job</a>
            <a href="<?php echo BASE_URL; ?>/register.php?type=employer" class="bg-blue-900 text-white px-6 py-3 rounded-md font-medium hover:bg-blue-800 transition duration-300 shadow">I'm Hiring Talent</a>
        </div>
    </div>
</section>

<section class="py-12 bg-gradient-to-br from-gray-50 via-blue-50 to-white" role="region" aria-labelledby="featured-jobs-heading">
    <div class="container mx-auto px-4">
        <h2 id="featured-jobs-heading" class="text-3xl font-bold text-center mb-12 text-blue-700">Featured Jobs</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" role="list">
            <?php if (!empty($featuredJobs)): ?>
                <?php foreach ($featuredJobs as $job): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition duration-300 border border-blue-100" role="listitem" aria-label="<?php echo htmlspecialchars($job['title'] . ' at ' . $job['company_name']); ?>">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <?php if (!empty($job['logo'])): ?>
                                    <img src="<?php echo BASE_URL; ?>/assets/uploads/logos/<?php echo htmlspecialchars($job['logo']); ?>" alt="<?php echo htmlspecialchars($job['company_name']); ?>" class="w-12 h-12 object-contain mr-4 border border-blue-200 rounded-full bg-white">
                                <?php else: ?>
                                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-700 font-bold mr-4 border border-blue-200">
                                        <?php echo htmlspecialchars(substr($job['company_name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h3 class="font-bold text-lg text-blue-700"><?php echo htmlspecialchars($job['title']); ?></h3>
                                    <p class="text-gray-600"><?php echo htmlspecialchars($job['company_name']); ?></p>
                                </div>
                            </div>
                            <div class="mb-4">
                                <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-md mr-2"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $job['job_type']))); ?></span>
                                <span class="inline-block bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-md"><?php echo htmlspecialchars($job['location']); ?></span>
                            </div>
                            <p class="text-gray-700 mb-4 line-clamp-2"><?php echo htmlspecialchars($job['description']); ?></p>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Posted <?php echo date('M j, Y', strtotime($job['posted_at'])); ?></span>
                                <a href="<?php echo BASE_URL; ?>/job-details.php?id=<?php echo urlencode($job['job_id']); ?>" class="text-blue-600 hover:text-blue-800 font-medium">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-gray-500">No featured jobs available at the moment.</p>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-8">
            <a href="<?php echo BASE_URL; ?>/jobs.php" class="inline-block bg-blue-700 text-white px-6 py-3 rounded-md font-medium hover:bg-blue-800 transition duration-300 shadow">Browse All Jobs</a>
        </div>
    </div>
</section>

<section class="bg-gradient-to-r from-blue-50 via-white to-blue-100 py-12" role="region" aria-labelledby="how-it-works-heading">
    <div class="container mx-auto px-4">
        <h2 id="how-it-works-heading" class="text-3xl font-bold text-center mb-12 text-blue-700">How It Works</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8" role="list">
            <div class="bg-white p-6 rounded-lg shadow-md text-center border border-blue-100" role="listitem" aria-label="Create Your Profile">
                <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2 text-blue-700">Create Your Profile</h3>
                <p class="text-gray-600">Register as a job seeker and build your professional profile to showcase your skills and experience.</p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md text-center border border-blue-100" role="listitem" aria-label="Find Jobs">
                <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2 text-blue-700">Find Jobs</h3>
                <p class="text-gray-600">Search and apply for jobs that match your qualifications and career goals.</p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md text-center border border-blue-100" role="listitem" aria-label="Get Hired">
                <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2 text-blue-700">Get Hired</h3>
                <p class="text-gray-600">Connect with employers and land your dream job in Uganda's growing job market.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>