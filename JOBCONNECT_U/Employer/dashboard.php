<?php
require_once '../Includes/config.php';

// Add authentication checks for employer dashboard
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

if (!isEmployer()) {
    header('Location: ' . BASE_URL . '/profile.php');
    exit();
}

$pageTitle = 'Employer Dashboard';
$user_id = $_SESSION['user_id'];

// Get employer profile and ensure employer record exists
$stmt = $pdo->prepare("SELECT * FROM employers WHERE employer_id = ? OR user_id = ?");
$stmt->execute([$user_id, $user_id]);
$employer = $stmt->fetch();

if (!$employer) {
    // Create employer record if it doesn't exist
    $stmt = $pdo->prepare("INSERT INTO employers (employer_id, user_id, company_name, created_at) VALUES (?, ?, ?, datetime('now'))");
    $stmt->execute([$user_id, $user_id, $_SESSION['first_name'] . ' ' . $_SESSION['last_name'] . ' Company']);
    
    // Fetch the newly created employer record
    $stmt = $pdo->prepare("SELECT * FROM employers WHERE employer_id = ?");
    $stmt->execute([$user_id]);
    $employer = $stmt->fetch();
}

// Use the correct employer_id
$employer_id = $employer['employer_id'];

// Final fallback for company name display
if (empty($employer['company_name'])) {
    $stmt = $pdo->prepare("SELECT company_name FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $userCompany = $stmt->fetch();
    
    $employer['company_name'] = $userCompany['company_name'] ?? ($_SESSION['company_name'] ?? ($_SESSION['first_name'] . ' ' . $_SESSION['last_name'] . ' Company'));
}

// Get employer stats
$stats = [];

// Jobs posted count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id = ?");
$stmt->execute([$employer_id]);
$stats['jobs_posted'] = $stmt->fetchColumn();

// Active jobs count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id = ? AND is_active = TRUE");
$stmt->execute([$employer_id]);
$stats['active_jobs'] = $stmt->fetchColumn();

// Total applications received
$stmt = $pdo->prepare("SELECT COUNT(*) FROM applications a JOIN jobs j ON a.job_id = j.job_id WHERE j.employer_id = ?");
$stmt->execute([$employer_id]);
$stats['total_applications'] = $stmt->fetchColumn();

// Recent applications (last 30 days)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM applications a JOIN jobs j ON a.job_id = j.job_id WHERE j.employer_id = ? AND a.applied_at > datetime('now', '-30 days')");
$stmt->execute([$employer_id]);
$stats['recent_applications'] = $stmt->fetchColumn();

// Get recent jobs posted by employer
$stmt = $pdo->prepare("
    SELECT j.*, COUNT(a.application_id) as application_count
    FROM jobs j 
    LEFT JOIN applications a ON j.job_id = a.job_id 
    WHERE j.employer_id = ? 
    GROUP BY j.job_id 
    ORDER BY j.posted_at DESC 
    LIMIT 5
");
$stmt->execute([$employer_id]);
$jobs = $stmt->fetchAll();

// Get recent applications for employer's jobs
$stmt = $pdo->prepare("
    SELECT a.*, j.title, u.first_name, u.last_name
    FROM applications a 
    JOIN jobs j ON a.job_id = j.job_id 
    JOIN users u ON a.user_id = u.user_id
    WHERE j.employer_id = ? 
    ORDER BY a.applied_at DESC 
    LIMIT 10
");
$stmt->execute([$employer_id]);
$applications = $stmt->fetchAll() ?: [];

// Get pending applications count
$pendingCount = count(array_filter($applications, function($app) { 
    return ($app['status'] ?? 'pending') === 'pending'; 
}));

require_once '../Includes/header.php';
?>

<style>
/* Employer dashboard animations */
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

.slide-in-right {
  opacity: 0;
  transform: translateX(30px);
  transition: opacity 0.5s ease-out, transform 0.5s ease-out;
}

.slide-in-right.revealed {
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

/* Stat cards hover effect */
.stat-card {
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Job card hover effect */
.job-card {
  transition: all 0.3s ease;
}

.job-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const revealElements = document.querySelectorAll('.fade-up, .slide-in-left, .slide-in-right, .scale-up');
  
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
        <!-- Employer Header -->
        <div class="card p-8 mb-8 slide-in-left">
            <div class="flex flex-col md:flex-row md:items-center gap-6">
                <div class="flex items-center gap-4">
                    <?php if (!empty($employer['logo'])): ?>
                        <div class="w-20 h-20 rounded-xl bg-white shadow-sm p-3 flex items-center justify-center">
                            <img src="<?php echo BASE_URL; ?>/Assets/uploads/logos/<?php echo $employer['logo']; ?>" alt="<?php echo htmlspecialchars($employer['company_name']); ?>" class="w-full h-full object-contain">
                        </div>
                    <?php else: ?>
                        <div class="w-20 h-20 bg-gradient-to-r from-blue-100 to-blue-200 rounded-xl flex items-center justify-center">
                            <span class="text-2xl font-bold text-blue-700"><?php echo substr($employer['company_name'] ?? 'C', 0, 1); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($employer['company_name'] ?? 'Your Company'); ?></h1>
                        <p class="text-xl text-gray-600"><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></p>
                        <div class="flex flex-wrap gap-3 mt-3">
                            <span class="badge" style="background:#e6f0ff;color:#0b3b84;padding:8px 16px">Employer</span>
                            <span class="badge" style="background:#dcfce7;color:#16a34a;padding:8px 16px">Active</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-col gap-3">
                    <a href="profile.php" class="btn btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Profile
                    </a>
                    <a href="jobs.php?new=1" class="btn btn-ghost">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Post New Job
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="card p-6 text-center stat-card scale-up" style="transition-delay: 0.1s">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 mb-2"><?php echo number_format($stats['jobs_posted']); ?></h3>
                <p class="text-gray-600 font-medium">Total Jobs Posted</p>
                <p class="text-sm text-blue-600 mt-1"><?php echo $stats['active_jobs']; ?> active</p>
            </div>

            <div class="card p-6 text-center stat-card scale-up" style="transition-delay: 0.2s">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 mb-2"><?php echo number_format($stats['total_applications']); ?></h3>
                <p class="text-gray-600 font-medium">Total Applications</p>
                <p class="text-sm text-green-600 mt-1"><?php echo $stats['recent_applications']; ?> this month</p>
            </div>

            <div class="card p-6 text-center stat-card scale-up" style="transition-delay: 0.3s">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 mb-2"><?php echo number_format($pendingCount); ?></h3>
                <p class="text-gray-600 font-medium">Pending Reviews</p>
                <p class="text-sm text-yellow-600 mt-1">Awaiting action</p>
            </div>

            <div class="card p-6 text-center stat-card scale-up" style="transition-delay: 0.4s">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 mb-2"><?php echo $stats['active_jobs'] > 0 ? 'High' : 'Low'; ?></h3>
                <p class="text-gray-600 font-medium">Activity Level</p>
                <p class="text-sm text-purple-600 mt-1">Job posting activity</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Recent Jobs -->
            <div class="lg:col-span-2">
                <div class="card p-8 slide-in-right" style="transition-delay: 0.5s">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                            <svg class="w-6 h-6 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Recent Job Postings
                        </h2>
                        <a href="jobs.php" class="text-blue-600 hover:text-blue-800 font-medium">View All</a>
                    </div>

                    <?php if (empty($jobs)): ?>
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">No jobs posted yet</h3>
                            <p class="text-gray-600 mb-4">Start posting jobs to attract talented candidates.</p>
                            <a href="jobs.php?new=1" class="btn btn-primary">Post Your First Job</a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($jobs as $index => $job): ?>
                                <article class="job-card p-4 border border-gray-100 rounded-lg hover:border-blue-200 transition-all" style="animation-delay: <?php echo $index * 0.1; ?>s">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900 mb-1"><?php echo htmlspecialchars($job['title']); ?></h3>
                                            <p class="text-gray-600 text-sm mb-2"><?php echo htmlspecialchars($job['location']); ?> • Posted <?php echo date('M j, Y', strtotime($job['posted_at'])); ?></p>
                                            
                                            <div class="flex flex-wrap gap-2 mb-2">
                                                <span class="badge" style="background:#e6f0ff;color:#0b3b84;font-size:0.75rem">
                                                    <?php echo ucfirst(str_replace('_', ' ', $job['job_type'])); ?>
                                                </span>
                                                <?php if ($job['is_active']): ?>
                                                    <span class="badge" style="background:#dcfce7;color:#16a34a;font-size:0.75rem">Active</span>
                                                <?php else: ?>
                                                    <span class="badge" style="background:#fef3c7;color:#d97706;font-size:0.75rem">Inactive</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="flex flex-col items-end gap-2">
                                            <span class="bg-blue-600 text-white text-sm px-3 py-1 rounded-full">
                                                <?php echo $job['application_count']; ?> applications
                                            </span>
                                            <div class="flex gap-2">
                                                <a href="<?php echo BASE_URL; ?>/job-details.php?id=<?php echo $job['job_id']; ?>" class="text-blue-600 hover:text-blue-800 text-sm">View</a>
                                                <a href="jobs.php?edit=<?php echo $job['job_id']; ?>" class="text-gray-600 hover:text-gray-800 text-sm">Edit</a>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Applications Sidebar -->
            <div class="lg:col-span-1">
                <div class="card p-6 fade-up" style="transition-delay: 0.6s">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Recent Applications
                    </h3>

                    <?php if (empty($applications)): ?>
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <p class="text-gray-600 text-sm">No applications yet</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach (array_slice($applications, 0, 5) as $app): ?>
                                <div class="p-3 bg-gray-50 rounded-lg">
                                    <h4 class="font-medium text-gray-900 text-sm mb-1"><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></h4>
                                    <p class="text-gray-600 text-xs mb-1"><?php echo htmlspecialchars($app['title']); ?></p>
                                    <div class="flex items-center justify-between">
                                        <p class="text-gray-500 text-xs">Applied <?php echo date('M j', strtotime($app['applied_at'])); ?></p>
                                        <span class="px-2 py-1 text-xs rounded-full <?php echo ($app['status'] ?? 'pending') === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800'; ?>">
                                            <?php echo ucfirst($app['status'] ?? 'pending'); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-4 text-center">
                            <a href="applications.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All Applications</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="card p-6 mt-6 fade-up" style="transition-delay: 0.7s">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="jobs.php?new=1" class="btn btn-ghost w-full justify-start">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Post New Job
                        </a>
                        <a href="applications.php" class="btn btn-ghost w-full justify-start">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
                            Manage Applications
                        </a>
                        <a href="profile.php" class="btn btn-ghost w-full justify-start">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Company Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../Includes/footer.php'; ?>