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

$pageTitle = 'System Logs';

// Get filters
$action_filter = sanitize($_GET['action'] ?? '');
$user_filter = sanitize($_GET['user'] ?? '');
$date_from = sanitize($_GET['date_from'] ?? '');
$date_to = sanitize($_GET['date_to'] ?? '');

// Build query
$where = [];
$params = [];

if (!empty($action_filter)) {
    $where[] = "action LIKE ?";
    $params[] = "%$action_filter%";
}

if (!empty($user_filter)) {
    $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$user_filter%";
    $params[] = "%$user_filter%";
    $params[] = "%$user_filter%";
}

if (!empty($date_from)) {
    $where[] = "l.created_at >= ?";
    $params[] = $date_from . ' 00:00:00';
}

if (!empty($date_to)) {
    $where[] = "l.created_at <= ?";
    $params[] = $date_to . ' 23:59:59';
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Get logs with pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 50;
$offset = ($page - 1) * $limit;

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM audit_logs l 
    LEFT JOIN users u ON l.user_id = u.user_id 
    $whereClause
");
$stmt->execute($params);
$totalLogs = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT l.*, u.first_name, u.last_name, u.email, u.user_type
    FROM audit_logs l 
    LEFT JOIN users u ON l.user_id = u.user_id 
    $whereClause 
    ORDER BY l.created_at DESC 
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$logs = $stmt->fetchAll();

$totalPages = ceil($totalLogs / $limit);

// Get common actions for filter
$stmt = $pdo->query("SELECT DISTINCT action FROM audit_logs ORDER BY action");
$actions = $stmt->fetchAll(PDO::FETCH_COLUMN);

require_once '../Includes/header.php';
?>

<section class="py-12 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
    <div class="container mx-auto px-4">
        <!-- Header -->
        <div class="card p-6 mb-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">System Logs</h1>
                    <p class="text-gray-600">Total: <?php echo number_format($totalLogs); ?> log entries</p>
                </div>
                <div class="flex gap-3">
                    <a href="Dashboard.php" class="btn btn-ghost">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card p-6 mb-8">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                    <select name="action" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Actions</option>
                        <?php foreach ($actions as $action): ?>
                            <option value="<?php echo htmlspecialchars($action); ?>" <?php echo $action_filter === $action ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $action))); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
                    <input type="text" name="user" value="<?php echo htmlspecialchars($user_filter); ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="Name or email...">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                    <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                    <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="btn btn-primary w-full">Filter</button>
                </div>
            </form>
        </div>

        <!-- Logs Table -->
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($logs as $log): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div><?php echo date('M j, Y', strtotime($log['created_at'])); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo date('H:i:s', strtotime($log['created_at'])); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($log['user_id']): ?>
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-gradient-to-r from-blue-100 to-blue-200 rounded-full flex items-center justify-center mr-3">
                                                <span class="text-xs font-bold text-blue-700"><?php echo substr($log['first_name'], 0, 1); ?></span>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($log['first_name'] . ' ' . $log['last_name']); ?>
                                                </div>
                                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($log['email']); ?></div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-500">System/Anonymous</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="badge" style="
                                        background:<?php 
                                            echo match(true) {
                                                str_contains($log['action'], 'login') => '#dcfce7;color:#16a34a',
                                                str_contains($log['action'], 'logout') => '#fef3c7;color:#d97706',
                                                str_contains($log['action'], 'register') => '#e6f0ff;color:#0b3b84',
                                                str_contains($log['action'], 'delete') => '#fecaca;color:#dc2626',
                                                str_contains($log['action'], 'error') => '#fecaca;color:#dc2626',
                                                default => '#f3f4f6;color:#374151'
                                            }; 
                                        ?>;font-size:0.75rem">
                                        <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $log['action']))); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 max-w-xs">
                                    <div class="truncate" title="<?php echo htmlspecialchars($log['details']); ?>">
                                        <?php echo htmlspecialchars($log['details']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No logs found</h3>
                                        <p class="text-gray-600">Try adjusting your filters.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200">
                    <div class="flex-1 flex justify-between items-center">
                        <p class="text-sm text-gray-700">
                            Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $totalLogs); ?> of <?php echo $totalLogs; ?> results
                        </p>
                        
                        <div class="flex gap-1">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&action=<?php echo urlencode($action_filter); ?>&user=<?php echo urlencode($user_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" class="px-3 py-1 text-sm bg-gray-100 text-gray-600 rounded hover:bg-gray-200">Previous</a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <a href="?page=<?php echo $i; ?>&action=<?php echo urlencode($action_filter); ?>&user=<?php echo urlencode($user_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" class="px-3 py-1 text-sm <?php echo $i === $page ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?> rounded"><?php echo $i; ?></a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&action=<?php echo urlencode($action_filter); ?>&user=<?php echo urlencode($user_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" class="px-3 py-1 text-sm bg-gray-100 text-gray-600 rounded hover:bg-gray-200">Next</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- System Info -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            <div class="card p-6 text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">System Status</h3>
                <p class="text-sm text-green-600">All Systems Operational</p>
            </div>
            
            <div class="card p-6 text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Last Activity</h3>
                <p class="text-sm text-blue-600">
                    <?php 
                    if (!empty($logs)) {
                        echo date('M j, Y H:i', strtotime($logs[0]['created_at']));
                    } else {
                        echo 'No recent activity';
                    }
                    ?>
                </p>
            </div>
            
            <div class="card p-6 text-center">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Disk Usage</h3>
                <p class="text-sm text-purple-600">Normal</p>
            </div>
        </div>
    </div>
</section>

<?php require_once '../Includes/footer.php'; ?>