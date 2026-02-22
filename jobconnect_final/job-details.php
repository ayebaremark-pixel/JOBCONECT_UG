<?php
require_once 'includes/config.php';

if (!isset($_GET['id'])) {
    header('Location: ' . BASE_URL . '/jobs.php');
    exit();
}

$job_id = (int)$_GET['id'];
$job = getJobDetails($pdo, $job_id);

if (!$job) {
    setMessage('error', 'Job not found');
    header('Location: ' . BASE_URL . '/jobs.php');
    exit();
}

$pageTitle = $job['title'];

// Increment job views
$stmt = $pdo->prepare("UPDATE jobs SET views = views + 1 WHERE job_id = ?");
$stmt->execute([$job_id]);

// Check if job is saved by current user
$isSaved = false;
if (isLoggedIn() && isJobSeeker()) {
    $isSaved = isJobSaved($pdo, $job_id, $_SESSION['user_id']);
}

// Handle save/unsave job
if (isset($_POST['toggle_save']) && isLoggedIn() && isJobSeeker()) {
    if ($isSaved) {
        $stmt = $pdo->prepare("DELETE FROM saved_jobs WHERE job_id = ? AND user_id = ?");
        $stmt->execute([$job_id, $_SESSION['user_id']]);
        $isSaved = false;
        setMessage('success', 'Job removed from saved jobs');
    } else {
        $stmt = $pdo->prepare("INSERT INTO saved_jobs (job_id, user_id) VALUES (?, ?)");
        $stmt->execute([$job_id, $_SESSION['user_id']]);
        $isSaved = true;
        setMessage('success', 'Job saved successfully');
    }
}

// Handle job application
if (isset($_POST['apply_job']) && isLoggedIn() && isJobSeeker()) {
    $cover_letter = sanitize($_POST['cover_letter']);
    $resume_file = '';
    
    // Check if user already applied
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE job_id = ? AND user_id = ?");
    $stmt->execute([$job_id, $_SESSION['user_id']]);
    $alreadyApplied = $stmt->fetchColumn() > 0;
    
    if ($alreadyApplied) {
        setMessage('error', 'You have already applied for this job');
    } else {
        // Check if resume is uploaded
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = handleFileUpload($_FILES['resume'], UPLOAD_RESUME_PATH);
            if ($uploadResult['success']) {
                $resume_file = $uploadResult['filename'];
            } else {
                setMessage('error', $uploadResult['error']);
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            }
        } else {
            // Use existing resume if available
            $profile = getJobSeekerProfile($pdo, $_SESSION['user_id']);
            $resume_file = $profile['resume_file'] ?? '';
            
            if (empty($resume_file)) {
                setMessage('error', 'Please upload your resume');
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            }
        }
        
        // Insert application
        $stmt = $pdo->prepare("INSERT INTO applications (job_id, user_id, cover_letter, resume_file) VALUES (?, ?, ?, ?)");
        $stmt->execute([$job_id, $_SESSION['user_id'], $cover_letter, $resume_file]);
        
        // Log the application
        logAction($_SESSION['user_id'], 'job_apply', "Applied for job: {$job['title']}", $pdo);
        
        setMessage('success', 'Application submitted successfully!');
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
}

require_once 'includes/header.php';
?>

<section class="py-8">
    <div class="container mx-auto px-4">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col md:flex-row md:items-center justify-between">
                    <div class="flex items-start mb-4 md:mb-0">
                        <?php if ($job['logo']): ?>
                            <img src="<?php echo BASE_URL; ?>/assets/uploads/logos/<?php echo $job['logo']; ?>" alt="<?php echo $job['company_name']; ?>" class="w-16 h-16 object-contain mr-4">
                        <?php else: ?>
                            <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 font-bold text-2xl mr-4">
                                <?php echo substr($job['company_name'], 0, 1); ?>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h1 class="text-2xl font-bold"><?php echo $job['title']; ?></h1>
                            <p class="text-lg text-gray-600"><?php echo $job['company_name']; ?></p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm"><?php echo ucfirst(str_replace('_', ' ', $job['job_type'])); ?></span>
                                <span class="inline-block bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm"><?php echo $job['location']; ?></span>
                                <?php if ($job['salary_range']): ?>
                                    <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm"><?php echo $job['salary_range']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="text-sm text-gray-500 mb-2">Posted <?php echo date('M j, Y', strtotime($job['posted_at'])); ?></span>
                        <span class="text-sm text-gray-500"><?php echo $job['views']; ?> views</span>
                    </div>
                </div>
            </div>
            
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-semibold mb-4">Job Description</h2>
                    <?php if (isLoggedIn() && isJobSeeker()): ?>
                        <form method="POST">
                            <button type="submit" name="toggle_save" class="flex items-center text-sm <?php echo $isSaved ? 'text-blue-600' : 'text-gray-600'; ?> hover:text-blue-800">
                                <svg class="w-5 h-5 mr-1" fill="<?php echo $isSaved ? 'currentColor' : 'none'; ?>" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                </svg>
                                <?php echo $isSaved ? 'Saved' : 'Save Job'; ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                <div class="prose max-w-none">
                    <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                </div>
            </div>
            
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold mb-4">Requirements</h2>
                <div class="prose max-w-none">
                    <?php echo nl2br(htmlspecialchars($job['requirements'])); ?>
                </div>
            </div>
            
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-4">Job Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h3 class="font-medium text-gray-900">Experience Level</h3>
                        <p class="text-gray-600"><?php echo $job['experience_level'] ?: 'Not specified'; ?></p>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">Deadline</h3>
                        <p class="text-gray-600"><?php echo $job['deadline'] ? date('M j, Y', strtotime($job['deadline'])) : 'Not specified'; ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (isLoggedIn()): ?>
            <?php if (isJobSeeker()): ?>
                <!-- Application form for job seekers -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mt-8">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold">Apply for This Position</h2>
                    </div>
                    
                    <div class="p-6">
                        <?php
                        // Check if already applied
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE job_id = ? AND user_id = ?");
                        $stmt->execute([$job_id, $_SESSION['user_id']]);
                        $alreadyApplied = $stmt->fetchColumn() > 0;
                        
                        if ($alreadyApplied): ?>
                            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            You've already applied for this position. The employer will review your application and contact you if you're shortlisted.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-4">
                                    <label for="cover_letter" class="block text-gray-700 text-sm font-medium mb-2">Cover Letter</label>
                                    <textarea id="cover_letter" name="cover_letter" rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Explain why you're a good fit for this position..."></textarea>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-medium mb-2">Resume</label>
                                    <?php
                                    $profile = getJobSeekerProfile($pdo, $_SESSION['user_id']);
                                    if (!empty($profile['resume_file'])): ?>
                                        <div class="mb-2 flex items-center">
                                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                            <span class="text-gray-600"><?php echo $profile['resume_file']; ?></span>
                                        </div>
                                        <p class="text-sm text-gray-500 mb-2">You can upload a new resume below to replace your current one.</p>
                                    <?php endif; ?>
                                    
                                    <div class="flex items-center">
                                        <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx" class="sr-only">
                                        <label for="resume" class="bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 cursor-pointer">
                                            Choose file
                                        </label>
                                        <span class="ml-2 text-sm text-gray-500" id="file-name">No file chosen</span>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">PDF, DOC, DOCX (Max 5MB)</p>
                                </div>
                                
                                <button type="submit" name="apply_job" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-300">Submit Application</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif (isEmployer() && $_SESSION['user_id'] == $job['user_id']): ?>
                <!-- Employer actions for their own job postings -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mt-8">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold">Manage This Job Posting</h2>
                    </div>
                    
                    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="<?php echo BASE_URL; ?>/employer/applications.php?job_id=<?php echo $job_id; ?>" class="bg-blue-50 border border-blue-200 rounded-md p-4 text-center hover:bg-blue-100 transition duration-300">
                            <svg class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <h3 class="font-medium text-gray-900">View Applications</h3>
                            <p class="text-sm text-gray-600"><?php echo getApplicationsCount($pdo, $job_id); ?> applicants</p>
                        </a>
                        
                        <a href="<?php echo BASE_URL; ?>/employer/jobs.php?edit=<?php echo $job_id; ?>" class="bg-yellow-50 border border-yellow-200 rounded-md p-4 text-center hover:bg-yellow-100 transition duration-300">
                            <svg class="w-8 h-8 text-yellow-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            <h3 class="font-medium text-gray-900">Edit Job</h3>
                            <p class="text-sm text-gray-600">Update job details</p>
                        </a>
                        
                        <a href="<?php echo BASE_URL; ?>/employer/jobs.php?delete=<?php echo $job_id; ?>" class="bg-red-50 border border-red-200 rounded-md p-4 text-center hover:bg-red-100 transition duration-300" onclick="return confirm('Are you sure you want to delete this job posting?');">
                            <svg class="w-8 h-8 text-red-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            <h3 class="font-medium text-gray-900">Delete Job</h3>
                            <p class="text-sm text-gray-600">Remove this posting</p>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Call to action for non-logged in users -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mt-8">
                <div class="p-6 text-center">
                    <h2 class="text-xl font-semibold mb-4">Interested in this position?</h2>
                    <p class="text-gray-600 mb-6">Sign in or create an account to apply for this job.</p>
                    <div class="flex flex-col md:flex-row justify-center gap-4">
                        <a href="<?php echo BASE_URL; ?>/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="bg-blue-600 text-white px-6 py-3 rounded-md font-medium hover:bg-blue-700 transition duration-300">Login</a>
                        <a href="<?php echo BASE_URL; ?>/register.php?type=job_seeker" class="bg-white border border-blue-600 text-blue-600 px-6 py-3 rounded-md font-medium hover:bg-blue-50 transition duration-300">Register</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
    // Display selected file name
    document.getElementById('resume').addEventListener('change', function(e) {
        var fileName = e.target.files[0] ? e.target.files[0].name : 'No file chosen';
        document.getElementById('file-name').textContent = fileName;
    });
</script>

<?php require_once 'includes/footer.php'; ?>