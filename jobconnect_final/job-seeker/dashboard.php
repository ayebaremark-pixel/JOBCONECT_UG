<?php
require_once '../includes/config.php';
requireLogin();

if (!isJobSeeker()) {
    header('Location: ' . BASE_URL . '/');
    exit();
}

$pageTitle = 'Dashboard';
$user_id = $_SESSION['user_id'];

// Get job seeker profile
$profile = getJobSeekerProfile($pdo, $user_id);

// Get recent applications
$applications = getUserApplications($pdo, $user_id);

// Get saved jobs
$savedJobs = getSavedJobs($pdo, $user_id);

require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar -->
        <div class="md:w-1/4">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-blue-600 p-4 text-white">
                    <h2 class="text-xl font-semibold">My Profile</h2>
                </div>
                <div class="p-4">
                    <div class="flex items-center mb-4">
                        <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 font-bold text-2xl mr-4">
                            <?php echo substr($_SESSION['first_name'], 0, 1) . substr($_SESSION['last_name'], 0, 1); ?>
                        </div>
                        <div>
                            <h3 class="font-bold"><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></h3>
                            <p class="text-gray-600 text-sm"><?php echo $profile['headline'] ?? 'Job Seeker'; ?></p>
                        </div>
                    </div>
                    <ul class="space-y-2">
                        <li>
                            <a href="profile.php" class="flex items-center text-gray-700 hover:text-blue-600">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                My Profile
                            </a>
                        </li>
                        <li>
                            <a href="applications.php" class="flex items-center text-gray-700 hover:text-blue-600">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                My Applications
                            </a>
                        </li>
                        <li>
                            <a href="jobs.php?filter=saved" class="flex items-center text-gray-700 hover:text-blue-600">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                </svg>
                                Saved Jobs
                            </a>
                        </li>
                        <li>
                            <a href="../logout.php" class="flex items-center text-gray-700 hover:text-blue-600">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Profile completeness -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mt-6">
                <div class="bg-blue-600 p-4 text-white">
                    <h2 class="text-xl font-semibold">Profile Strength</h2>
                </div>
                <div class="p-4">
                    <?php
                    $completeness = 0;
                    if (!empty($profile['headline'])) $completeness += 20;
                    if (!empty($profile['bio'])) $completeness += 20;
                    if (!empty($profile['skills'])) $completeness += 20;
                    if (!empty($profile['education'])) $completeness += 20;
                    if (!empty($profile['resume_file'])) $completeness += 20;
                    ?>
                    <div class="mb-2">
                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                            <span>Complete</span>
                            <span><?php echo $completeness; ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?php echo $completeness; ?>%"></div>
                        </div>
                    </div>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li class="flex items-center <?php echo empty($profile['headline']) ? 'text-gray-400' : 'text-green-600'; ?>">
                            <?php if (!empty($profile['headline'])): ?>
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            <?php else: ?>
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            <?php endif; ?>
                            Professional Headline
                        </li>
                        <li class="flex items-center <?php echo empty($profile['bio']) ? 'text-gray-400' : 'text-green-600'; ?>">
                            <?php if (!empty($profile['bio'])): ?>
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            <?php else: ?>
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            <?php endif; ?>
                            Bio/Summary
                        </li>
                        <li class="flex items-center <?php echo empty($profile['skills']) ? 'text-gray-400' : 'text-green-600'; ?>">
                            <?php if (!empty($profile['skills'])): ?>
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            <?php else: ?>
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            <?php endif; ?>
                            Skills
                        </li>
                        <li class="flex items-center <?php echo empty($profile['education']) ? 'text-gray-400' : 'text-green-600'; ?>">
                            <?php if (!empty($profile['education'])): ?>
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            <?php else: ?>
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            <?php endif; ?>
                            Education
                        </li>
                        <li class="flex items-center <?php echo empty($profile['resume_file']) ? 'text-gray-400' : 'text-green-600'; ?>">
                            <?php if (!empty($profile['resume_file'])): ?>
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            <?php else: ?>
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            <?php endif; ?>
                            Resume Uploaded
                        </li>
                    </ul>
                    <a href="profile.php" class="mt-4 inline-block text-blue-600 hover:underline text-sm">Complete your profile</a>
                </div>
            </div>
        </div>
        
        <!-- Main content -->
        <div class="md:w-3/4">
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
                <div class="bg-blue-600 p-4 text-white">
                    <h2 class="text-xl font-semibold">Welcome, <?php echo $_SESSION['first_name']; ?></h2>
                </div>
                <div class="p-6">
                    <p class="text-gray-600 mb-4">Here's what's happening with your job search:</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-blue-50 border border-blue-200 rounded-md p-4 text-center">
                            <h3 class="text-2xl font-bold text-blue-600 mb-1"><?php echo count($applications); ?></h3>
                            <p class="text-gray-600">Applications</p>
                        </div>
                        <div class="bg-green-50 border border-green-200 rounded-md p-4 text-center">
                            <h3 class="text-2xl font-bold text-green-600 mb-1">
                                <?php echo count(array_filter($applications, function($app) { return $app['status'] === 'accepted'; })); ?>
                            </h3>
                            <p class="text-gray-600">Accepted</p>
                        </div>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 text-center">
                            <h3 class="text-2xl font-bold text-yellow-600 mb-1"><?php echo count($savedJobs); ?></h3>
                            <p class="text-gray-600">Saved Jobs</p>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <h3 class="font-medium text-gray-900 mb-2">Recommended Jobs</h3>
                        <?php
                        // Simple recommendation based on skills (for demo purposes)
                        if (!empty($profile['skills'])) {
                            $skills = explode(',', $profile['skills']);
                            $skillQuery = '%' . trim($skills[0]) . '%';
                            
                            $stmt = $pdo->prepare("SELECT j.job_id, j.title, e.company_name 
                                                  FROM jobs j 
                                                  JOIN employers e ON j.employer_id = e.employer_id 
                                                  WHERE j.description LIKE ? OR j.requirements LIKE ? 
                                                  ORDER BY j.posted_at DESC 
                                                  LIMIT 3");
                            $stmt->execute([$skillQuery, $skillQuery]);
                            $recommendedJobs = $stmt->fetchAll();
                            
                            if (!empty($recommendedJobs)) {
                                echo '<ul class="space-y-2">';
                                foreach ($recommendedJobs as $job) {
                                    echo '<li class="flex justify-between items-center py-2 border-b border-gray-100">';
                                    echo '<div>';
                                    echo '<a href="' . BASE_URL . '/job-details.php?id=' . $job['job_id'] . '" class="text-blue-600 hover:underline">' . $job['title'] . '</a>';
                                    echo '<p class="text-sm text-gray-600">' . $job['company_name'] . '</p>';
                                    echo '</div>';
                                    echo '<a href="' . BASE_URL . '/job-details.php?id=' . $job['job_id'] . '" class="text-sm text-blue-600 hover:text-blue-800">View</a>';
                                    echo '</li>';
                                }
                                echo '</ul>';
                                echo '<a href="' . BASE_URL . '/jobs.php?search=' . urlencode(trim($skills[0])) . '" class="mt-2 inline-block text-blue-600 hover:underline text-sm">View all matching jobs</a>';
                            } else {
                                echo '<p class="text-gray-600">No recommended jobs found based on your skills.</p>';
                            }
                        } else {
                            echo '<p class="text-gray-600">Add skills to your profile to get job recommendations.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <!-- Recent Applications -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-blue-600 p-4 text-white">
                    <h2 class="text-xl font-semibold">Recent Applications</h2>
                </div>
                <div class="p-6">
                    <?php if (empty($applications)): ?>
                        <p class="text-gray-600">You haven't applied to any jobs yet.</p>
                        <a href="<?php echo BASE_URL; ?>/jobs.php" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-300">Browse Jobs</a>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Title</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach (array_slice($applications, 0, 5) as $application): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900"><?php echo $application['title']; ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-600"><?php echo $application['company_name']; ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?php echo $application['status'] === 'accepted' ? 'bg-green-100 text-green-800' : 
                                                          ($application['status'] === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                                    <?php echo ucfirst($application['status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                <?php echo date('M j, Y', strtotime($application['applied_at'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="<?php echo BASE_URL; ?>/job-details.php?id=<?php echo $application['job_id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            <a href="applications.php" class="text-blue-600 hover:underline">View all applications</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>