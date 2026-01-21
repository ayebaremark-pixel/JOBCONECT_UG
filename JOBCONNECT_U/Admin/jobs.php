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

$pageTitle = 'Manage Jobs';

// Handle job actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['job_id'])) {
        $job_id = (int)$_POST['job_id'];
        $action = $_POST['action'];
        
        try {
            switch ($action) {
                case 'activate':
                    $stmt = $pdo->prepare("UPDATE jobs SET is_active = 1 WHERE job_id = ?");
                    $stmt->execute([$job_id]);
                    setMessage('success', 'Job activated successfully.');
                    break;
                    
                case 'deactivate':
                    $stmt = $pdo->prepare("UPDATE jobs SET is_active = 0 WHERE job_id = ?");
                    $stmt->execute([$job_id]);
                    setMessage('success', 'Job deactivated successfully.');
                    break;
                    
                case 'delete':
                    // Delete related applications first (or use CASCADE)
                    $stmt = $pdo->prepare("DELETE FROM applications WHERE job_id = ?");
                    $stmt->execute([$job_id]);
                    
                    $stmt = $pdo->prepare("DELETE FROM jobs WHERE job_id = ?");
                    $stmt->execute([$job_id]);
                    setMessage('success', 'Job deleted successfully.');
                    break;
                    
                case 'feature':
                    $stmt = $pdo->prepare("UPDATE jobs SET is_featured = 1 WHERE job_id = ?");
                    $stmt->execute([$job_id]);
                    setMessage('success', 'Job featured successfully.');
                    break;
                    
                case 'unfeature':
                    $stmt = $pdo->prepare("UPDATE jobs SET is_featured = 0 WHERE job_id = ?");
                    $stmt->execute([$job_id]);
                    setMessage('success', 'Job unfeatured successfully.');
                    break;
            }
        } catch (Exception $e) {
            setMessage('error', 'Action failed: ' . $e->getMessage());
        }
        
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }
}

// Get filters
$search = sanitize($_GET['search'] ?? '');
$job_type = sanitize($_GET['job_type'] ?? '');
$status = sanitize($_GET['status'] ?? '');
$location = sanitize($_GET['location'] ?? '');

// Build query
$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(j.title LIKE ? OR e.company_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($job_type)) {
    $where[] = "j.job_type = ?";
    $params[] = $job_type;
}

if (!empty($status)) {
    $where[] = "j.is_active = ?";
    $params[] = $status === 'active' ? 1 : 0;
}

if (!empty($location)) {
    $where[] = "j.location LIKE ?";
    $params[] = "%$location%";
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Get jobs with pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM jobs j 
    LEFT JOIN employers e ON j.employer_id = e.employer_id 
    $whereClause
");
$stmt->execute($params);
$totalJobs = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT j.*, e.company_name, e.logo,
           (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.job_id) as application_count
    FROM jobs j 
    LEFT JOIN employers e ON j.employer_id = e.employer_id 
    $whereClause 
    ORDER BY j.posted_at DESC 
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$jobs = $stmt->fetchAll();

$totalPages = ceil($totalJobs / $limit);

require_once '../Includes/header.php';
?>

<section class="py-12 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
    <div class="container mx-auto px-4">
        <!-- Header -->
        <div class="card p-6 mb-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Manage Jobs</h1>
                    <p class="text-gray-600">Total: <?php echo number_format($totalJobs); ?> job postings</p>
                </div>
                <a href="Dashboard.php" class="btn btn-ghost">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="card p-6 mb-8">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="Job title or company...">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Job Type</label>
                    <select name="job_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Types</option>
                        <option value="full_time" <?php echo $job_type === 'full_time' ? 'selected' : ''; ?>>Full Time</option>
                        <option value="part_time" <?php echo $job_type === 'part_time' ? 'selected' : ''; ?>>Part Time</option>
                        <option value="contract" <?php echo $job_type === 'contract' ? 'selected' : ''; ?>>Contract</option>
                        <option value="internship" <?php echo $job_type === 'internship' ? 'selected' : ''; ?>>Internship</option>
                        <option value="temporary" <?php echo $job_type === 'temporary' ? 'selected' : ''; ?>>Temporary</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($location); ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="City or region...">
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="btn btn-primary w-full">Filter</button>
                </div>
            </form>
        </div>

        <!-- Jobs Grid -->
        <div class="grid grid-cols-1 gap-6">
            <?php foreach ($jobs as $job): ?>
                <div class="card p-6 hover:shadow-lg transition-shadow">
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
                        <div class="flex items-start gap-4 flex-1">
                            <?php if ($job['logo']): ?>
                                <div class="w-16 h-16 rounded-lg bg-white shadow-sm p-2 flex items-center justify-center">
                                    <img src="<?php echo BASE_URL; ?>/Assets/uploads/logos/<?php echo $job['logo']; ?>" alt="<?php echo htmlspecialchars($job['company_name']); ?>" class="w-full h-full object-contain">
                                </div>
                            <?php else: ?>
                                <div class="w-16 h-16 bg-gradient-to-r from-blue-100 to-blue-200 rounded-lg flex items-center justify-center">
                                    <span class="text-lg font-bold text-blue-700"><?php echo substr($job['company_name'] ?? 'C', 0, 1); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($job['title']); ?></h3>
                                <p class="text-gray-600 mb-3"><?php echo htmlspecialchars($job['company_name'] ?? 'Unknown Company'); ?> • <?php echo htmlspecialchars($job['location']); ?></p>
                                
                                <div class="flex flex-wrap gap-2 mb-3">
                                    <span class="badge" style="background:#e6f0ff;color:#0b3b84;font-size:0.75rem">
                                        <?php echo ucfirst(str_replace('_', ' ', $job['job_type'])); ?>
                                    </span>
                                    
                                    <?php if ($job['is_active']): ?>
                                        <span class="badge" style="background:#dcfce7;color:#16a34a;font-size:0.75rem">Active</span>
                                    <?php else: ?>
                                        <span class="badge" style="background:#fef3c7;color:#d97706;font-size:0.75rem">Inactive</span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($job['is_featured'])): ?>
                                        <span class="badge" style="background:#fef3c7;color:#d97706;font-size:0.75rem">Featured</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($job['salary_range']): ?>
                                        <span class="badge" style="background:#dcfce7;color:#16a34a;font-size:0.75rem">
                                            <?php echo htmlspecialchars($job['salary_range']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="text-sm text-gray-600 mb-2">Posted: <?php echo date('M j, Y', strtotime($job['posted_at'])); ?></p>
                                <p class="text-sm text-gray-600"><?php echo $job['application_count']; ?> applications</p>
                            </div>
                        </div>
                        
                        <div class="flex flex-col gap-2 min-w-fit">
                            <a href="<?php echo BASE_URL; ?>/job-details.php?id=<?php echo $job['job_id']; ?>" class="btn btn-ghost text-sm" target="_blank">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View
                            </a>
                            
                            <div class="flex gap-1">
                                <?php if ($job['is_active']): ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                                        <button type="submit" name="action" value="deactivate" class="text-xs px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200" onclick="return confirm('Deactivate this job?')">Deactivate</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                                        <button type="submit" name="action" value="activate" class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200">Activate</button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if (empty($job['is_featured'])): ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                                        <button type="submit" name="action" value="feature" class="text-xs px-2 py-1 bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200">Feature</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                                        <button type="submit" name="action" value="unfeature" class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">Unfeature</button>
                                    </form>
                                <?php endif; ?>
                                
                                <form method="POST" class="inline">
                                    <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                                    <button type="submit" name="action" value="delete" class="text-xs px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200" onclick="return confirm('Delete this job? This action cannot be undone.')">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($jobs)): ?>
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No jobs found</h3>
                    <p class="text-gray-600">Try adjusting your filters or check back later.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="card p-4 mt-8">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-700">
                        Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $totalJobs); ?> of <?php echo $totalJobs; ?> results
                    </p>
                    
                    <div class="flex gap-1">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&job_type=<?php echo urlencode($job_type); ?>&status=<?php echo urlencode($status); ?>&location=<?php echo urlencode($location); ?>" class="px-3 py-1 text-sm bg-gray-100 text-gray-600 rounded hover:bg-gray-200">Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&job_type=<?php echo urlencode($job_type); ?>&status=<?php echo urlencode($status); ?>&location=<?php echo urlencode($location); ?>" class="px-3 py-1 text-sm <?php echo $i === $page ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?> rounded"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&job_type=<?php echo urlencode($job_type); ?>&status=<?php echo urlencode($status); ?>&location=<?php echo urlencode($location); ?>" class="px-3 py-1 text-sm bg-gray-100 text-gray-600 rounded hover:bg-gray-200">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once '../Includes/footer.php'; ?>