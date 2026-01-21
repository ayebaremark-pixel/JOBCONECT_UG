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

$pageTitle = 'Manage Job Postings';
$user_id = $_SESSION['user_id'];

// Ensure employer record exists
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

// Handle create/edit/delete
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $location = sanitize($_POST['location'] ?? '');
    $job_type = sanitize($_POST['job_type'] ?? '');
    $salary_range = sanitize($_POST['salary_range'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $requirements = sanitize($_POST['requirements'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $job_id = $_POST['job_id'] ?? null;

    if (empty($title)) $errors[] = 'Title is required.';
    if (empty($location)) $errors[] = 'Location is required.';

    if (empty($errors)) {
        if ($job_id) {
            // update
            $stmt = $pdo->prepare("UPDATE jobs SET title=?, location=?, job_type=?, salary_range=?, description=?, requirements=?, is_active=? WHERE job_id=? AND employer_id=?");
            $stmt->execute([$title, $location, $job_type, $salary_range, $description, $requirements, $is_active, $job_id, $employer_id]);
            setMessage('success', 'Job updated successfully.');
        } else {
            // create
            $stmt = $pdo->prepare("INSERT INTO jobs (title, location, job_type, salary_range, description, requirements, employer_id, posted_at, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now'), ?)");
            $stmt->execute([$title, $location, $job_type, $salary_range, $description, $requirements, $employer_id, $is_active]);
            setMessage('success', 'Job posted successfully.');
        }
        header('Location: ' . BASE_URL . '/Employer/jobs.php');
        exit();
    } else {
        setMessage('error', implode(' ', $errors));
    }
}

// Delete
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM jobs WHERE job_id = ? AND employer_id = ?");
    $stmt->execute([$delId, $employer_id]);
    setMessage('success', 'Job deleted.');
    header('Location: ' . BASE_URL . '/Employer/jobs.php');
    exit();
}

// Get job for editing
$editingJob = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE job_id = ? AND employer_id = ?");
    $stmt->execute([$editId, $employer_id]);
    $editingJob = $stmt->fetch();
}

// List jobs with application counts
$stmt = $pdo->prepare("SELECT j.*, (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.job_id) AS application_count FROM jobs j WHERE j.employer_id = ? ORDER BY j.posted_at DESC");
$stmt->execute([$employer_id]);
$jobs = $stmt->fetchAll();

require_once '../Includes/header.php';
?>

<section class="py-12 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
  <div class="container mx-auto px-4">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold">Your Job Postings</h2>
            <a href="?new=1" class="text-sm bg-blue-600 text-white px-3 py-1 rounded-md">Post New Job</a>
          </div>

          <?php if (empty($jobs)): ?>
            <div class="text-center py-12">
              <p class="text-gray-600">You have not posted any jobs yet.</p>
            </div>
          <?php else: ?>
            <div class="space-y-4">
              <?php foreach ($jobs as $job): ?>
                <div class="border border-gray-100 rounded-md p-4 hover:border-blue-200 transition">
                  <div class="flex justify-between items-start">
                    <div>
                      <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($job['title']); ?></h3>
                      <p class="text-sm text-gray-600"><?php echo htmlspecialchars($job['location']); ?> • <?php echo htmlspecialchars($job['job_type']); ?></p>
                    </div>
                    <div class="text-right">
                      <div class="text-sm text-gray-500 mb-2"><?php echo $job['application_count']; ?> applications</div>
                      <div class="flex gap-2 justify-end">
                        <a href="?edit=<?php echo $job['job_id']; ?>" class="text-sm text-gray-600 hover:underline">Edit</a>
                        <a href="?delete=<?php echo $job['job_id']; ?>" onclick="return confirm('Delete this job?')" class="text-sm text-red-600 hover:underline">Delete</a>
                        <a href="<?php echo BASE_URL; ?>/Employer/applications.php?job_id=<?php echo $job['job_id']; ?>" class="text-sm text-blue-600 hover:underline">View Applications</a>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div>
        <div class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-lg font-semibold mb-4"><?php echo $editingJob ? 'Edit Job' : 'Post a Job'; ?></h3>

          <form method="POST">
            <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($editingJob['job_id'] ?? ''); ?>">
            <div class="mb-3">
              <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
              <input name="title" required class="w-full px-3 py-2 border border-gray-300 rounded-md" value="<?php echo htmlspecialchars($editingJob['title'] ?? ''); ?>">
            </div>
            <div class="mb-3">
              <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
              <input name="location" required class="w-full px-3 py-2 border border-gray-300 rounded-md" value="<?php echo htmlspecialchars($editingJob['location'] ?? ''); ?>">
            </div>
            <div class="mb-3">
              <label class="block text-sm font-medium text-gray-700 mb-1">Job Type</label>
              <select name="job_type" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <option value="">Select type</option>
                <?php $types = ['full_time','part_time','contract','internship','temporary']; foreach($types as $t): ?>
                  <option value="<?php echo $t; ?>" <?php echo (($editingJob['job_type'] ?? '') === $t) ? 'selected' : ''; ?>><?php echo ucfirst(str_replace('_',' ',$t)); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="block text-sm font-medium text-gray-700 mb-1">Salary Range</label>
              <input name="salary_range" class="w-full px-3 py-2 border border-gray-300 rounded-md" value="<?php echo htmlspecialchars($editingJob['salary_range'] ?? ''); ?>">
            </div>
            <div class="mb-3">
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea name="description" rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-md"><?php echo htmlspecialchars($editingJob['description'] ?? ''); ?></textarea>
            </div>
            <div class="mb-3">
              <label class="block text-sm font-medium text-gray-700 mb-1">Requirements</label>
              <textarea name="requirements" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md"><?php echo htmlspecialchars($editingJob['requirements'] ?? ''); ?></textarea>
            </div>
            <div class="mb-3">
              <label class="inline-flex items-center"><input type="checkbox" name="is_active" value="1" <?php echo (isset($editingJob['is_active']) && $editingJob['is_active']) ? 'checked' : ''; ?> class="mr-2"> Active</label>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-md"><?php echo $editingJob ? 'Update Job' : 'Post Job'; ?></button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once '../Includes/footer.php'; ?>