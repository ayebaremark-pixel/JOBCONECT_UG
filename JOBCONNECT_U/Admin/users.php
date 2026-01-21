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

$pageTitle = 'Manage Users';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['user_id'])) {
        $user_id = (int)$_POST['user_id'];
        $action = $_POST['action'];
        
        try {
            switch ($action) {
                case 'activate':
                    $stmt = $pdo->prepare("UPDATE users SET is_active = 1 WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                    setMessage('success', 'User activated successfully.');
                    break;
                    
                case 'deactivate':
                    $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                    setMessage('success', 'User deactivated successfully.');
                    break;
                    
                case 'delete':
                    // Note: Be careful with deletions - consider soft delete instead
                    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND user_type != 'admin'");
                    $stmt->execute([$user_id]);
                    setMessage('success', 'User deleted successfully.');
                    break;
                    
                case 'make_admin':
                    $stmt = $pdo->prepare("UPDATE users SET user_type = 'admin' WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                    setMessage('success', 'User promoted to admin.');
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
$user_type = sanitize($_GET['user_type'] ?? '');
$status = sanitize($_GET['status'] ?? '');

// Build query
$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($user_type)) {
    $where[] = "user_type = ?";
    $params[] = $user_type;
}

if (!empty($status)) {
    $where[] = "is_active = ?";
    $params[] = $status === 'active' ? 1 : 0;
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Get users with pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM users $whereClause");
$stmt->execute($params);
$totalUsers = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT * FROM users $whereClause ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$users = $stmt->fetchAll();

$totalPages = ceil($totalUsers / $limit);

require_once '../Includes/header.php';
?>

<section class="py-12 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
    <div class="container mx-auto px-4">
        <!-- Header -->
        <div class="card p-6 mb-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Manage Users</h1>
                    <p class="text-gray-600">Total: <?php echo number_format($totalUsers); ?> users</p>
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
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="Name or email...">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">User Type</label>
                    <select name="user_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Types</option>
                        <option value="job_seeker" <?php echo $user_type === 'job_seeker' ? 'selected' : ''; ?>>Job Seekers</option>
                        <option value="employer" <?php echo $user_type === 'employer' ? 'selected' : ''; ?>>Employers</option>
                        <option value="admin" <?php echo $user_type === 'admin' ? 'selected' : ''; ?>>Admins</option>
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
                
                <div class="flex items-end">
                    <button type="submit" class="btn btn-primary w-full">Filter</button>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($users as $user): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-r from-blue-100 to-blue-200 rounded-full flex items-center justify-center mr-3">
                                            <span class="text-sm font-bold text-blue-700"><?php echo substr($user['first_name'], 0, 1); ?></span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="badge" style="background:<?php 
                                        echo match($user['user_type']) {
                                            'admin' => '#dc2626;color:#ffffff',
                                            'employer' => '#e6f0ff;color:#0b3b84',
                                            default => '#dcfce7;color:#16a34a'
                                        }; ?>;font-size:0.75rem">
                                        <?php echo ucfirst(str_replace('_', ' ', $user['user_type'])); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $user['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center gap-2">
                                        <?php if ($user['user_type'] !== 'admin' || $_SESSION['user_id'] !== $user['user_id']): ?>
                                            <?php if ($user['is_active']): ?>
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                    <button type="submit" name="action" value="deactivate" class="text-red-600 hover:text-red-900" onclick="return confirm('Deactivate this user?')">Deactivate</button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                    <button type="submit" name="action" value="activate" class="text-green-600 hover:text-green-900">Activate</button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <?php if ($user['user_type'] !== 'admin'): ?>
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                    <button type="submit" name="action" value="make_admin" class="text-blue-600 hover:text-blue-900" onclick="return confirm('Make this user an admin?')">Make Admin</button>
                                                </form>
                                                
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                    <button type="submit" name="action" value="delete" class="text-red-600 hover:text-red-900" onclick="return confirm('Delete this user? This action cannot be undone.')">Delete</button>
                                                </form>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-gray-400">Current Admin</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200">
                    <div class="flex-1 flex justify-between items-center">
                        <p class="text-sm text-gray-700">
                            Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $totalUsers); ?> of <?php echo $totalUsers; ?> results
                        </p>
                        
                        <div class="flex gap-1">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&user_type=<?php echo urlencode($user_type); ?>&status=<?php echo urlencode($status); ?>" class="px-3 py-1 text-sm bg-gray-100 text-gray-600 rounded hover:bg-gray-200">Previous</a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&user_type=<?php echo urlencode($user_type); ?>&status=<?php echo urlencode($status); ?>" class="px-3 py-1 text-sm <?php echo $i === $page ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?> rounded"><?php echo $i; ?></a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&user_type=<?php echo urlencode($user_type); ?>&status=<?php echo urlencode($status); ?>" class="px-3 py-1 text-sm bg-gray-100 text-gray-600 rounded hover:bg-gray-200">Next</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once '../Includes/footer.php'; ?>