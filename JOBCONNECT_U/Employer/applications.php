<?php
require_once '../Includes/config.php';

// Auth
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit();
}
if (!isEmployer()) {
    header('Location: ' . BASE_URL . '/');
    exit();
}

$pageTitle = 'Applications';
$user_id = $_SESSION['user_id'];

// Get employer_id (same logic as other employer files)
$stmt = $pdo->prepare("SELECT employer_id FROM employers WHERE employer_id = ? OR user_id = ?");
$stmt->execute([$user_id, $user_id]);
$employer = $stmt->fetch();

if (!$employer) {
    // Create employer record if it doesn't exist
    $stmt = $pdo->prepare("INSERT INTO employers (employer_id, user_id, company_name, created_at) VALUES (?, ?, ?, datetime('now'))");
    $stmt->execute([$user_id, $user_id, $_SESSION['first_name'] . ' ' . $_SESSION['last_name'] . ' Company']);
    $employer_id = $user_id;
} else {
    $employer_id = $employer['employer_id'];
}

// Optional job_id filter
$job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : null;

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['application_id'])) {
    $action = $_POST['action'];
    $application_id = (int)$_POST['application_id'];

    // Ensure application belongs to one of employer's jobs
    $stmt = $pdo->prepare("SELECT a.* FROM applications a JOIN jobs j ON a.job_id = j.job_id WHERE a.application_id = ? AND j.employer_id = ?");
    $stmt->execute([$application_id, $employer_id]);
    $app = $stmt->fetch();

    if ($app) {
        if ($action === 'accept') {
            $stmt = $pdo->prepare("UPDATE applications SET status = 'accepted' WHERE application_id = ?");
            $stmt->execute([$application_id]);
            setMessage('success', 'Application accepted.');
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE applications SET status = 'rejected' WHERE application_id = ?");
            $stmt->execute([$application_id]);
            setMessage('success', 'Application rejected.');
        }
    } else {
        setMessage('error', 'Application not found or access denied.');
    }

    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit();
}

// If viewing a single application
$viewId = isset($_GET['view']) ? (int)$_GET['view'] : null;

if ($viewId) {
    $stmt = $pdo->prepare("SELECT a.*, j.title, j.job_id, u.first_name, u.last_name, u.email FROM applications a JOIN jobs j ON a.job_id = j.job_id JOIN users u ON a.user_id = u.user_id WHERE a.application_id = ? AND j.employer_id = ?");
    $stmt->execute([$viewId, $employer_id]);
    $application = $stmt->fetch();

    if (!$application) {
        setMessage('error', 'Application not found or access denied.');
        header('Location: ' . BASE_URL . '/Employer/applications.php');
        exit();
    }

    require_once '../Includes/header.php';
    ?>
    <section class="py-12 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
      <div class="container mx-auto px-4">
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold">Application Details</h2>
            <a href="applications.php" class="text-sm text-gray-600 hover:text-blue-600">Back to list</a>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2">
              <h3 class="text-lg font-semibold mb-2"><?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></h3>
              <p class="text-sm text-gray-600 mb-2">Applied for: <strong><?php echo htmlspecialchars($application['title']); ?></strong></p>
              <p class="text-sm text-gray-600 mb-4">Email: <?php echo htmlspecialchars($application['email']); ?></p>

              <div class="mb-4">
                <h4 class="font-medium mb-1">Cover Letter</h4>
                <div class="p-4 bg-gray-50 rounded-md text-sm text-gray-700"><?php echo nl2br(htmlspecialchars($application['cover_letter'])); ?></div>
              </div>

              <?php if (!empty($application['resume_file'])): ?>
                <div class="mb-4">
                  <h4 class="font-medium mb-1">Resume</h4>
                  <a href="<?php echo BASE_URL . '/Assets/uploads/resumes/' . $application['resume_file']; ?>" class="text-blue-600 hover:underline" target="_blank">Download Resume</a>
                </div>
              <?php endif; ?>

              <div class="flex gap-3">
                <form method="POST" class="inline">
                  <input type="hidden" name="application_id" value="<?php echo $application['application_id']; ?>">
                  <button type="submit" name="action" value="accept" class="bg-green-600 text-white px-4 py-2 rounded-md">Accept</button>
                </form>

                <form method="POST" class="inline">
                  <input type="hidden" name="application_id" value="<?php echo $application['application_id']; ?>">
                  <button type="submit" name="action" value="reject" class="bg-red-600 text-white px-4 py-2 rounded-md">Reject</button>
                </form>

                <a href="mailto:<?php echo htmlspecialchars($application['email']); ?>" class="ml-2 text-sm text-blue-600">Contact</a>
              </div>
            </div>

            <div>
              <div class="bg-gray-50 p-4 rounded-md">
                <p class="text-sm text-gray-600">Applied: <strong><?php echo date('M j, Y H:i', strtotime($application['applied_at'])); ?></strong></p>
                <p class="text-sm text-gray-600">Status: <strong><?php echo ucfirst($application['status'] ?? 'pending'); ?></strong></p>
                <p class="text-sm text-gray-600 mt-2">Job ID: <?php echo $application['job_id']; ?></p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <?php
    require_once '../Includes/footer.php';
    exit();
}

// List view
$params = [$employer_id];
$sql = "SELECT a.*, j.title, j.job_id, u.first_name, u.last_name FROM applications a JOIN jobs j ON a.job_id = j.job_id JOIN users u ON a.user_id = u.user_id WHERE j.employer_id = ?";
if ($job_id) {
    $sql .= " AND j.job_id = ?";
    $params[] = $job_id;
}
$sql .= " ORDER BY a.applied_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll() ?: [];

require_once '../Includes/header.php';
?>

<section class="py-12 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
  <div class="container mx-auto px-4">
    <div class="bg-white rounded-lg shadow-md p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold">Applications</h2>
        <div class="flex items-center gap-2">
          <form method="GET" class="flex items-center gap-2">
            <select name="job_id" class="px-3 py-2 border border-gray-300 rounded-md">
              <option value="">All Jobs</option>
              <?php
                $jobsListStmt = $pdo->prepare("SELECT job_id, title FROM jobs WHERE employer_id = ? ORDER BY posted_at DESC");
                $jobsListStmt->execute([$employer_id]);
                $jobsList = $jobsListStmt->fetchAll();
                foreach ($jobsList as $j) {
                    $selected = ($job_id && $job_id == $j['job_id']) ? 'selected' : '';
                    echo "<option value=\"{$j['job_id']}\" $selected>" . htmlspecialchars($j['title']) . "</option>";
                }
              ?>
            </select>
            <button type="submit" class="bg-blue-600 text-white px-3 py-2 rounded-md">Filter</button>
          </form>
          <a href="<?php echo BASE_URL; ?>/Employer/jobs.php" class="text-sm text-gray-600 hover:text-blue-600">Manage Jobs</a>
        </div>
      </div>

      <?php if (empty($applications)): ?>
        <div class="text-center py-12">
          <p class="text-gray-600">No applications found.</p>
        </div>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach ($applications as $app): ?>
            <div class="p-3 bg-gray-50 rounded-md flex items-start justify-between">
              <div>
                <h4 class="font-medium"><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></h4>
                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($app['title']); ?></p>
                <p class="text-xs text-gray-500">Applied: <?php echo date('M j, Y', strtotime($app['applied_at'])); ?></p>
              </div>

              <div class="flex items-center gap-2">
                <span class="px-2 py-1 rounded-full text-xs <?php echo ($app['status'] ?? 'pending') === 'pending' ? 'bg-yellow-100 text-yellow-800' : (($app['status'] === 'accepted') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>"><?php echo ucfirst($app['status'] ?? 'pending'); ?></span>
                <a href="?view=<?php echo $app['application_id']; ?>" class="text-sm text-blue-600 hover:underline">View</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>
</section>

<?php require_once '../Includes/footer.php';
?>