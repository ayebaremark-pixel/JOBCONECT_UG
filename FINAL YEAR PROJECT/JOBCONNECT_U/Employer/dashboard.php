<?php
require_once '../includes/config.php';
requireLogin();

if (!isEmployer()) {
    header('Location: ' . BASE_URL . '/');
    exit();
}

$pageTitle = 'Employer Dashboard';
$user_id = $_SESSION['user_id'];

// Get employer details
$stmt = $pdo->prepare("SELECT e.* FROM employers e JOIN users u ON e.user_id = u.user_id WHERE e.user_id = ?");
$stmt->execute([$user_id]);
$employer = $stmt->fetch();

// Get job postings
$stmt = $pdo->prepare("SELECT j.*, COUNT(a.application_id) as application_count 
                      FROM jobs j 
                      LEFT JOIN applications a ON j.job_id = a.job_id 
                      WHERE j.employer_id = ? 
                      GROUP BY j.job_id 
                      ORDER BY j.posted_at DESC");
$stmt->execute([$employer['employer_id']]);
$jobs = $stmt->fetchAll();

// Get recent applications
$stmt = $pdo->prepare("SELECT a.*, j.title, u.first_name, u.last_name 
                      FROM applications a 
                      JOIN jobs j ON a.job_id = j.job_id 
                      JOIN users u ON a.user_id = u.user_id 
                      WHERE j.employer_id = ? 
                      ORDER BY a.applied_at DESC 
                      LIMIT 5");
$stmt->execute([$employer['employer_id']]);
$applications = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar -->
        <div class="md:w-1/4">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-blue-600 p-4 text-white">
                    <h2 class="text-xl font-semibold">Company Profile</h2>
                </div>
                <div class="p-4">
                    <div class="flex items-center mb-4">
                        <?php if ($employer['logo']): ?>
                            <img src="<?php echo BASE_URL; ?>/assets/uploads/logos/<?php echo $employer['logo']; ?>" alt="<?php echo $employer['company_name']; ?>" class="w-16 h-16 object-contain mr-4">
                        <?php else: ?>
                            <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 font-bold text-2xl mr-4">
                                <?php echo substr($employer['company_name'], 0, 1); ?>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h3 class="font-bold"><?php echo $employer['company_name']; ?></h3>
                            <p class="text-gray-600 text-sm"><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></p>
                        </div>
                    </div>
                    <ul class="space-y-2">
                        <li>
                            <a href="profile.php" class="flex items-center text-gray-700 hover:text-blue-600">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Company Profile
                            </a>
                        </li>
                        <li>
                            <a href="jobs.php" class="flex items-center text-gray-700 hover:text-blue-600">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                Job Postings
                            </a>
                        </li>
                        <li>
                            <a href="applications.php" class="flex items-center text-gray-700 hover:text-blue-600">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                Applications
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
            
            <!-- Quick Stats -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mt-6">
                <div class="bg-blue-600 p-4 text-white">
                    <h2 class="text-xl font-semibold">Quick Stats</h2>
                </div>
                <div class="p-4">
                    <div class="mb-4">
                        <h3 class="font-medium text-gray-900 mb-1">Active Job Postings</h3>
                        <p class="text-2xl font-bold text-blue-600">
                            <?php echo count(array_filter($jobs, function($job) { return $job['is_active']; })); ?>
                        </p>
                    </div>
                    <div class="mb-4">
                        <h3 class="font-medium text-gray-900 mb-1">Total Applications</h3>
                        <p class="text-2xl font-bold text-blue-600">
                            <?php echo array_sum(array_column($jobs, 'application_count')); ?>
                        </p>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900 mb-1">Pending Reviews</h3>
                        <p class="text-2xl font-bold text-blue-600">
                            <?php echo count(array_filter($applications, function($app) { return $app['status'] === 'pending'; })); ?>
                        </p>
                    </div>
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
                    <p class="text-gray-600 mb-6">Here's an overview of your job postings and applications.</p>
                    
                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Recent Job Postings</h3>
                            <a href="jobs.php?new=1" class="text-sm bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700 transition duration-300">Post New Job</a>
                        </div>
                        
                        <?php if (empty($jobs)): ?>
                            <p class="text-gray-600">You haven't posted any jobs yet.</p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach (array_slice($jobs, 0, 3) as $job): ?>
                                    <div class="border border-gray-200 rounded-md p-4 hover:border-blue-300 transition duration-300">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="font-bold"><?php echo $job['title']; ?></h4>
                                                <p class="text-sm text-gray-600 mb-2">Posted <?php echo date('M j, Y', strtotime($job['posted_at'])); ?></p>
                                                <div class="flex flex-wrap gap-2 mb-2">
                                                    <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-md"><?php echo ucfirst(str_replace('_', ' ', $job['job_type'])); ?></span>
                                                    <span class="inline-block bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-md"><?php echo $job['location']; ?></span>
                                                    <?php if ($job['is_active']): ?>
                                                        <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-md">Active</span>
                                                    <?php else: ?>
                                                        <span class="inline-block bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-md">Inactive</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="inline-block bg-blue-600 text-white text-sm px-2 py-1 rounded-md"><?php echo $job['application_count']; ?> applications</span>
                                            </div>
                                        </div>
                                        <div class="flex justify-end mt-2 space-x-2">
                                            <a href="<?php echo BASE_URL; ?>/job-details.php?id=<?php echo $job['job_id']; ?>" class="text-sm text-blue-600 hover:underline">View</a>
                                            <a href="jobs.php?edit=<?php echo $job['job_id']; ?>" class="text-sm text-gray-600 hover:underline">Edit</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="mt-4">
                                <a href="jobs.php" class="text-blue-600 hover:underline">View all job postings</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Recent Applications</h3>
                        
                        <?php if (empty($applications)): ?>
                            <p class="text-gray-600">No applications received yet.</p>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Candidate</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Title</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($applications as $application): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900"><?php echo $application['first_name'] . ' ' . $application['last_name']; ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-600"><?php echo $application['title']; ?></div>
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
                                                    <a href="applications.php?view=<?php echo $application['application_id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
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
</div>

<?php require_once '../includes/footer.php'; ?>