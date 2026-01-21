<?php
require_once '../Includes/config.php';

// Admin authentication
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit();
}

if (!isAdmin()) {
    header('Location: ' . BASE_URL . '/');
    exit();
}

$pageTitle = 'Admin Dashboard';

// Get admin stats
$stats = [];

// Total users count
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$stats['total_users'] = $stmt->fetchColumn();

// Users by type
$stmt = $pdo->query("SELECT user_type, COUNT(*) as count FROM users GROUP BY user_type");
$userTypes = $stmt->fetchAll();
foreach ($userTypes as $type) {
    $stats[$type['user_type'] . '_users'] = $type['count'];
}

// Total jobs
$stmt = $pdo->query("SELECT COUNT(*) FROM jobs");
$stats['total_jobs'] = $stmt->fetchColumn();

// Active jobs
$stmt = $pdo->query("SELECT COUNT(*) FROM jobs WHERE is_active = TRUE");
$stats['active_jobs'] = $stmt->fetchColumn();

// Total applications
$stmt = $pdo->query("SELECT COUNT(*) FROM applications");
$stats['total_applications'] = $stmt->fetchColumn();

// Recent applications (last 7 days)
$stmt = $pdo->query("SELECT COUNT(*) FROM applications WHERE applied_at > datetime('now', '-7 days')");
$stats['recent_applications'] = $stmt->fetchColumn();

// Get recent users (last 10)
$stmt = $pdo->query("SELECT user_id, first_name, last_name, email, user_type, created_at FROM users ORDER BY created_at DESC LIMIT 10");
$recentUsers = $stmt->fetchAll();

// Get recent jobs (last 10)
$stmt = $pdo->prepare("
    SELECT j.*, e.company_name, 
           (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.job_id) as application_count
    FROM jobs j 
    LEFT JOIN employers e ON j.employer_id = e.employer_id 
    ORDER BY j.posted_at DESC 
    LIMIT 10
");
$stmt->execute();
$recentJobs = $stmt->fetchAll();

require_once '../Includes/header.php';
?>

<style>
/* Admin dashboard animations */
.fade-up {
  opacity: 0;
  transform: translateY(30px);
  transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}

.fade-up.revealed {
  opacity: 1;
  transform: translateY(0);
}

.slide-in-left {
  opacity: 0;
  transform: translateX(-30px);
  transition: opacity 0.5s ease-out, transform 0.5s ease-out;
}

.slide-in-left.revealed {
  opacity: 1;
  transform: translateX(0);
}

.scale-up {
  opacity: 0;
  transform: scale(0.95);
  transition: opacity 0.4s ease-out, transform 0.4s ease-out;
}

.scale-up.revealed {
  opacity: 1;
  transform: scale(1);
}

.stat-card {
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const revealElements = document.querySelectorAll('.fade-up, .slide-in-left, .scale-up');
  
  function reveal() {
    revealElements.forEach(element => {
      const elementTop = element.getBoundingClientRect().top;
      const elementVisible = 150;
      
      if (elementTop < window.innerHeight - elementVisible) {
        element.classList.add('revealed');
      }
    });
  }
  
  window.addEventListener('scroll', reveal);
  reveal(); // Initial check
});
</script>

<section class="py-12 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
    <div class="container mx-auto px-4">
        <!-- Admin Header -->
        <div class="card p-8 mb-8 slide-in-left">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Admin Dashboard</h1>
                    <p class="text-xl text-gray-600">Welcome back, <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></p>
                    <div class="flex flex-wrap gap-3 mt-3">
                        <span class="badge" style="background:#dc2626;color:#ffffff;padding:8px 16px">Administrator</span>
                        <span class="badge" style="background:#dcfce7;color:#16a34a;padding:8px 16px">System Active</span>
                    </div>
                </div>
                
                <div class="flex flex-col gap-3">
                    <a href="users.php" class="btn btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                        </svg>
                        Manage Users
                    </a>
                    <a href="logs.php" class="btn btn-ghost">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        View Logs
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="card p-6 text-center stat-card scale-up" style="transition-delay: 0.1s">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                    </svg>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 mb-2"><?php echo number_format($stats['total_users']); ?></h3>
                <p class="text-gray-600 font-medium">Total Users</p>
                <p class="text-sm text-blue-600 mt-1"><?php echo $stats['job_seeker_users'] ?? 0; ?> job seekers, <?php echo $stats['employer_users'] ?? 0; ?> employers</p>
            </div>

            <div class="card p-6 text-center stat-card scale-up" style="transition-delay: 0.2s">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 mb-2"><?php echo number_format($stats['total_jobs']); ?></h3>
                <p class="text-gray-600 font-medium">Total Jobs</p>
                <p class="text-sm text-green-600 mt-1"><?php echo $stats['active_jobs']; ?> active</p>
            </div>

            <div class="card p-6 text-center stat-card scale-up" style="transition-delay: 0.3s">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 mb-2"><?php echo number_format($stats['total_applications']); ?></h3>
                <p class="text-gray-600 font-medium">Total Applications</p>
                <p class="text-sm text-yellow-600 mt-1"><?php echo $stats['recent_applications']; ?> this week</p>
            </div>

            <div class="card p-6 text-center stat-card scale-up" style="transition-delay: 0.4s">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 mb-2">High</h3>
                <p class="text-gray-600 font-medium">System Activity</p>
                <p class="text-sm text-purple-600 mt-1">Platform performance</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Users -->
            <div class="card p-8 fade-up" style="transition-delay: 0.5s">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                        </svg>
                        Recent Users
                    </h2>
                    <a href="users.php" class="text-blue-600 hover:text-blue-800 font-medium">View All</a>
                </div>

                <div class="space-y-3">
                    <?php foreach ($recentUsers as $user): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-100 to-blue-200 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-bold text-blue-700"><?php echo substr($user['first_name'], 0, 1); ?></span>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($user['email']); ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="badge" style="background:<?php echo $user['user_type'] === 'employer' ? '#e6f0ff;color:#0b3b84' : '#dcfce7;color:#16a34a'; ?>;font-size:0.75rem">
                                    <?php echo ucfirst($user['user_type']); ?>
                                </span>
                                <p class="text-xs text-gray-500 mt-1"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recent Jobs -->
            <div class="card p-8 fade-up" style="transition-delay: 0.6s">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Recent Jobs
                    </h2>
                    <a href="jobs.php" class="text-blue-600 hover:text-blue-800 font-medium">View All</a>
                </div>

                <div class="space-y-3">
                    <?php foreach ($recentJobs as $job): ?>
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-1"><?php echo htmlspecialchars($job['title']); ?></h4>
                            <p class="text-sm text-gray-600 mb-1"><?php echo htmlspecialchars($job['company_name'] ?? 'Unknown Company'); ?> • <?php echo htmlspecialchars($job['location']); ?></p>
                            <div class="flex items-center justify-between">
                                <div class="flex gap-2">
                                    <span class="badge" style="background:#e6f0ff;color:#0b3b84;font-size:0.75rem">
                                        <?php echo ucfirst(str_replace('_', ' ', $job['job_type'])); ?>
                                    </span>
                                    <?php if ($job['is_active']): ?>
                                        <span class="badge" style="background:#dcfce7;color:#16a34a;font-size:0.75rem">Active</span>
                                    <?php else: ?>
                                        <span class="badge" style="background:#fef3c7;color:#d97706;font-size:0.75rem">Inactive</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500"><?php echo $job['application_count']; ?> applications</p>
                                    <p class="text-xs text-gray-500"><?php echo date('M j', strtotime($job['posted_at'])); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card p-8 mt-8 fade-up" style="transition-delay: 0.7s">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                <svg class="w-6 h-6 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                Quick Actions
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <a href="users.php" class="group p-6 text-center border-2 border-gray-100 rounded-lg hover:border-blue-200 hover:shadow-md transition-all">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-blue-200 transition-colors">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Manage Users</h3>
                    <p class="text-sm text-gray-600">View and manage all users</p>
                </a>

                <a href="jobs.php" class="group p-6 text-center border-2 border-gray-100 rounded-lg hover:border-green-200 hover:shadow-md transition-all">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-green-200 transition-colors">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Manage Jobs</h3>
                    <p class="text-sm text-gray-600">Moderate job postings</p>
                </a>

                <a href="logs.php" class="group p-6 text-center border-2 border-gray-100 rounded-lg hover:border-yellow-200 hover:shadow-md transition-all">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-yellow-200 transition-colors">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">System Logs</h3>
                    <p class="text-sm text-gray-600">Monitor system activity</p>
                </a>

                <a href="../logout.php" class="group p-6 text-center border-2 border-gray-100 rounded-lg hover:border-red-200 hover:shadow-md transition-all">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-red-200 transition-colors">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Logout</h3>
                    <p class="text-sm text-gray-600">Exit admin panel</p>
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once '../Includes/footer.php'; ?>