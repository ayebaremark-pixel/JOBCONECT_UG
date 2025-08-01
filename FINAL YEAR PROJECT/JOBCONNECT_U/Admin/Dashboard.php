<?php
require_once '../includes/config.php';
requireLogin();

if (!isAdmin()) {
    header('Location: ' . BASE_URL . '/');
    exit();
}

$pageTitle = 'Admin Dashboard';

// Get stats
$usersCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$jobSeekersCount = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'job_seeker'")->fetchColumn();
$employersCount = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'employer'")->fetchColumn();
$jobsCount = $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
$applicationsCount = $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();

// Get recent users
$recentUsers = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Get recent jobs
$recentJobs = $pdo->query("SELECT j.*, e.company_name FROM jobs j JOIN employers e ON j.employer_id = e.employer_id ORDER BY j.posted_at DESC LIMIT 5")->fetchAll();

// Get recent logs
$recentLogs = $pdo->query("SELECT l.*, u.email FROM audit_logs l LEFT JOIN users u ON l.user_id = u.user_id ORDER BY l.created_at DESC LIMIT 10")->fetchAll();

require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-8">Admin Dashboard</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <h3 class="text-lg font-medium text-gray-900 mb-1">Total Users</h3>
            <p class="text-3xl font-bold text-blue-600"><?php echo $usersCount; ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <h3 class="text-lg font-medium text-gray-900 mb-1">Job Seekers</h3>
            <p class="text-3xl font-bold text-blue-600"><?php echo $jobSeekersCount; ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <h3 class="text-lg font-medium text-gray-900 mb-1">Employers</h3>
            <p class="text-3xl font-bold text-blue-600"><?php echo $employersCount; ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <h3 class="text-lg font-medium text-gray-900 mb-1">Job Postings</h3>
            <p class="text-3xl font-bold text-blue-600"><?php echo $jobsCount; ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <h3 class="text-lg font-medium text-gray-900 mb-1">Applications</h3>
            <p class="text-3xl font-bold text-blue-600"><?php echo $applicationsCount; ?></p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Users -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-blue-600 p-4 text-white">
                <h2 class="text-xl font-semibold">Recent Users</h2>
            </div>
            <div class="p-4">
                <?php if (empty($recentUsers)): ?>
                    <p class="text-gray-600">No users found.</p>
                <?php else: ?>
                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($recentUsers as $user): ?>
                            <li class="py-3">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 font-bold">
                                        <?php echo substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1); ?>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-900"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></p>
                                        <p class="text-sm text-gray-500"><?php echo $user['email']; ?></p>
                                        <div class="mt-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                <?php echo $user['user_type'] === 'admin' ? 'bg-purple-100 text-purple-800' : 
                                                      ($user['user_type'] === 'employer' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'); ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $user['user_type'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="mt-4">
                        <a href="users.php" class="text-blue-600 hover:underline">View all users</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Jobs -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-blue-600 p-4 text-white">
                <h2 class="text-xl font-semibold">Recent Job Postings</h2>
            </div>
            <div class="p-4">
                <?php if (empty($recentJobs)): ?>
                    <p class="text-gray-600">No job postings found.</p>
                <?php else: ?>
                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($recentJobs as $job): ?>
                            <li class="py-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-900"><?php echo $job['title']; ?></p>
                                    <p class="text-sm text-gray-500"><?php echo $job['company_name']; ?></p>
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <?php echo ucfirst(str_replace('_', ' ', $job['job_type'])); ?>
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <?php echo $job['location']; ?>
                                        </span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Posted <?php echo date('M j, Y', strtotime($job['posted_at'])); ?></p>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="mt-4">
                        <a href="jobs.php" class="text-blue-600 hover:underline">View all jobs</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-blue-600 p-4 text-white">
                <h2 class="text-xl font-semibold">Recent Activity</h2>
            </div>
            <div class="p-4">
                <?php if (empty($recentLogs)): ?>
                    <p class="text-gray-600">No activity logs found.</p>
                <?php else: ?>
                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($recentLogs as $log): ?>
                            <li class="py-3">
                                <div>
                                    <p class="text-sm text-gray-900">
                                        <span class="font-medium"><?php echo $log['email'] ?: 'System'; ?></span> 
                                        <?php echo $log['action']; ?>: <?php echo $log['description']; ?>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <?php echo date('M j, Y g:i A', strtotime($log['created_at'])); ?>
                                        <?php if ($log['ip_address']): ?>
                                            • <?php echo $log['ip_address']; ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="mt-4">
                        <a href="logs.php" class="text-blue-600 hover:underline">View all logs</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>