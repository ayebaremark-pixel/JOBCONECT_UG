<?php
require_once 'Includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit();
}

// Check if user is a job seeker
if (!isJobSeeker()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit();
}

$pageTitle = 'Profile & Stats';
$user_id = $_SESSION['user_id'];

// Get user profile data
$profile = getJobSeekerProfile($pdo, $user_id);

// If profile doesn't exist or is incomplete, get basic user data
if (!$profile || empty($profile['first_name'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $userData = $stmt->fetch();
    
    // Merge user data with empty profile structure
    $profile = array_merge([
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'phone' => '',
        'created_at' => date('Y-m-d H:i:s'),
        'profile_views' => 0
    ], $userData ?: [], $profile ?: []);
}

// Get user stats
$stats = [];

// Applications count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE user_id = ?");
$stmt->execute([$user_id]);
$stats['applications'] = $stmt->fetchColumn();

// Saved jobs count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM saved_jobs WHERE user_id = ?");
$stmt->execute([$user_id]);
$stats['saved_jobs'] = $stmt->fetchColumn();

// Profile views (if you track this)
$stats['profile_views'] = $profile['profile_views'] ?? 0;

// Recent activity count (last 30 days) - SQLite compatible
$stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE user_id = ? AND applied_at > datetime('now', '-30 days')");
$stmt->execute([$user_id]);
$stats['recent_applications'] = $stmt->fetchColumn();

// Get saved jobs with details
$stmt = $pdo->prepare("
    SELECT j.*, e.company_name, e.logo, sj.saved_at
    FROM saved_jobs sj 
    JOIN jobs j ON sj.job_id = j.job_id 
    JOIN employers e ON j.employer_id = e.employer_id 
    WHERE sj.user_id = ? AND j.is_active = TRUE
    ORDER BY sj.saved_at DESC 
    LIMIT 10
");
$stmt->execute([$user_id]);
$savedJobs = $stmt->fetchAll();

// Get recent applications
$stmt = $pdo->prepare("
    SELECT a.*, j.title, j.location, e.company_name, e.logo
    FROM applications a 
    JOIN jobs j ON a.job_id = j.job_id 
    JOIN employers e ON j.employer_id = e.employer_id 
    WHERE a.user_id = ? 
    ORDER BY a.applied_at DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$recentApplications = $stmt->fetchAll();

require_once 'Includes/header.php';
?>

<style>
/* Profile page animations */
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
        <!-- Profile Header -->
        <div class="card p-8 mb-8 slide-in-left">
            <div class="flex flex-col md:flex-row md:items-center gap-6">
                <div class="w-24 h-24 bg-gradient-to-r from-blue-100 to-blue-200 rounded-full flex items-center justify-center">
                    <span class="text-3xl font-bold text-blue-700"><?php echo strtoupper(substr($profile['first_name'] ?? 'U', 0, 1)); ?></span>
                </div>
                
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">
                        <?php echo htmlspecialchars(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? '')); ?>
                        <?php if (empty(trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? '')))): ?>
                            <span class="text-gray-500">Your Name</span>
                        <?php endif; ?>
                    </h1>
                    <p class="text-xl text-gray-600 mb-3"><?php echo htmlspecialchars($profile['email'] ?? $_SESSION['email'] ?? 'No email'); ?></p>
                    <?php if (!empty($profile['phone'])): ?>
                        <p class="text-gray-600 mb-3">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <?php echo htmlspecialchars($profile['phone']); ?>
                        </p>
                    <?php endif; ?>
                    <div class="flex flex-wrap gap-3">
                        <span class="badge" style="background:#e6f0ff;color:#0b3b84;padding:8px 16px">Job Seeker</span>
                        <span class="badge" style="background:#dcfce7;color:#16a34a;padding:8px 16px">Active</span>
                        <span class="badge" style="background:#f3f4f6;color:#374151;padding:8px 16px">
                            Member since <?php echo date('M Y', strtotime($profile['created_at'])); ?>
                        </span>
                    </div>
                </div>
                
                <div class="flex flex-col gap-3">
                    <a href="<?php echo BASE_URL; ?>/job-seeker/dashboard.php" class="btn btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Profile
                    </a>
                    <button onclick="window.print()" class="btn btn-ghost">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H3a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print Profile
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="card p-6 text-center stat-card scale-up" style="transition-delay: 0.1s">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 mb-2"><?php echo number_format($stats['applications']); ?></h3>
                <p class="text-gray-600 font-medium">Total Applications</p>
                <p class="text-sm text-blue-600 mt-1"><?php echo $stats['recent_applications']; ?> this month</p>
            </div>

            <div class="card p-6 text-center stat-card scale-up" style="transition-delay: 0.2s">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                    </svg>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 mb-2"><?php echo number_format($stats['saved_jobs']); ?></h3>
                <p class="text-gray-600 font-medium">Saved Jobs</p>
                <p class="text-sm text-green-600 mt-1">Ready to apply</p>
            </div>

            <div class="card p-6 text-center stat-card scale-up" style="transition-delay: 0.3s">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 mb-2"><?php echo number_format($stats['profile_views']); ?></h3>
                <p class="text-gray-600 font-medium">Profile Views</p>
                <p class="text-sm text-purple-600 mt-1">By employers</p>
            </div>

            <div class="card p-6 text-center stat-card scale-up" style="transition-delay: 0.4s">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 mb-2"><?php echo $stats['recent_applications'] > 0 ? 'High' : 'Low'; ?></h3>
                <p class="text-gray-600 font-medium">Activity Level</p>
                <p class="text-sm text-yellow-600 mt-1">Last 30 days</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Saved Jobs -->
            <div class="lg:col-span-2">
                <div class="card p-8 slide-in-right" style="transition-delay: 0.5s">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                            <svg class="w-6 h-6 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                            </svg>
                            Saved Jobs
                        </h2>
                        <?php if (count($savedJobs) > 0): ?>
                            <a href="<?php echo BASE_URL; ?>/saved.php" class="text-blue-600 hover:text-blue-800 font-medium">View All</a>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($savedJobs)): ?>
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">No saved jobs yet</h3>
                            <p class="text-gray-600 mb-4">Start saving jobs you're interested in to keep track of them.</p>
                            <a href="<?php echo BASE_URL; ?>/jobs.php" class="btn btn-primary">Browse Jobs</a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($savedJobs as $index => $job): ?>
                                <article class="job-card p-4 border border-gray-100 rounded-lg hover:border-blue-200 transition-all" style="animation-delay: <?php echo $index * 0.1; ?>s">
                                    <div class="flex items-start gap-4">
                                        <?php if ($job['logo']): ?>
                                            <img src="<?php echo BASE_URL; ?>/Assets/uploads/logos/<?php echo $job['logo']; ?>" alt="<?php echo $job['company_name']; ?>" class="w-12 h-12 object-contain rounded-lg">
                                        <?php else: ?>
                                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                                <span class="text-blue-700 font-bold"><?php echo substr($job['company_name'], 0, 1); ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900 mb-1"><?php echo htmlspecialchars($job['title']); ?></h3>
                                            <p class="text-gray-600 text-sm mb-2"><?php echo htmlspecialchars($job['company_name']); ?> • <?php echo htmlspecialchars($job['location']); ?></p>
                                            
                                            <div class="flex flex-wrap gap-2 mb-2">
                                                <span class="badge" style="background:#e6f0ff;color:#0b3b84;font-size:0.75rem">
                                                    <?php echo ucfirst(str_replace('_', ' ', $job['job_type'])); ?>
                                                </span>
                                                <?php if ($job['salary_range']): ?>
                                                    <span class="badge" style="background:#dcfce7;color:#16a34a;font-size:0.75rem">
                                                        <?php echo htmlspecialchars($job['salary_range']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <p class="text-sm text-gray-500">Saved <?php echo date('M j, Y', strtotime($job['saved_at'])); ?></p>
                                        </div>

                                        <div class="flex flex-col gap-2">
                                            <a href="<?php echo BASE_URL; ?>/job-details.php?id=<?php echo $job['job_id']; ?>" class="btn btn-primary text-sm">
                                                View Job
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Activity Sidebar -->
            <div class="lg:col-span-1">
                <div class="card p-6 fade-up" style="transition-delay: 0.6s">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Recent Applications
                    </h3>

                    <?php if (empty($recentApplications)): ?>
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <p class="text-gray-600 text-sm">No applications yet</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($recentApplications as $app): ?>
                                <div class="p-3 bg-gray-50 rounded-lg">
                                    <h4 class="font-medium text-gray-900 text-sm mb-1"><?php echo htmlspecialchars($app['title']); ?></h4>
                                    <p class="text-gray-600 text-xs mb-1"><?php echo htmlspecialchars($app['company_name']); ?></p>
                                    <p class="text-gray-500 text-xs">Applied <?php echo date('M j', strtotime($app['applied_at'])); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="card p-6 mt-6 fade-up" style="transition-delay: 0.7s">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="<?php echo BASE_URL; ?>/jobs.php" class="btn btn-ghost w-full justify-start">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Search Jobs
                        </a>
                        <a href="<?php echo BASE_URL; ?>/job-seeker/dashboard.php" class="btn btn-ghost w-full justify-start">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Update Profile
                        </a>
                        <a href="<?php echo BASE_URL; ?>/saved.php" class="btn btn-ghost w-full justify-start">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                            </svg>
                            View All Saved Jobs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'Includes/footer.php'; ?>