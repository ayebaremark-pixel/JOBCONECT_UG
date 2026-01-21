<?php
require_once 'Includes/config.php';

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

require_once 'Includes/header.php';
?>

<style>
/* Job details page animations */
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

<section class="py-12 bg-gradient-to-br from-gray-50 to-blue-50">
    <div class="container mx-auto px-4">
        <!-- Breadcrumb -->
        <nav class="mb-6 fade-up" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 text-sm text-gray-600">
                <li><a href="<?php echo BASE_URL; ?>" class="hover:text-blue-600">Home</a></li>
                <li><span class="mx-2">/</span></li>
                <li><a href="<?php echo BASE_URL; ?>/jobs.php" class="hover:text-blue-600">Jobs</a></li>
                <li><span class="mx-2">/</span></li>
                <li class="text-gray-900"><?php echo htmlspecialchars($job['title']); ?></li>
            </ol>
        </nav>

        <!-- Job Header Card -->
        <div class="card p-8 mb-8 slide-in-left">
            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
                <div class="flex items-start gap-6">
                    <?php if ($job['logo']): ?>
                        <div class="w-20 h-20 rounded-xl bg-white shadow-sm p-3 flex items-center justify-center">
                            <img src="<?php echo BASE_URL; ?>/Assets/uploads/logos/<?php echo $job['logo']; ?>" alt="<?php echo $job['company_name']; ?>" class="w-full h-full object-contain">
                        </div>
                    <?php else: ?>
                        <div class="w-20 h-20 bg-gradient-to-r from-blue-100 to-blue-200 rounded-xl flex items-center justify-center">
                            <span class="text-2xl font-bold text-blue-700"><?php echo substr($job['company_name'], 0, 1); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($job['title']); ?></h1>
                        <p class="text-xl text-gray-600 mb-4"><?php echo htmlspecialchars($job['company_name']); ?></p>
                        
                        <div class="flex flex-wrap gap-3">
                            <span class="badge" style="background:#e6f0ff;color:#0b3b84;padding:8px 16px;font-size:0.875rem">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2h8z"></path>
                                </svg>
                                <?php echo ucfirst(str_replace('_', ' ', $job['job_type'])); ?>
                            </span>
                            
                            <span class="badge" style="background:#f3f4f6;color:#374151;padding:8px 16px;font-size:0.875rem">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <?php echo htmlspecialchars($job['location']); ?>
                            </span>
                            
                            <?php if ($job['salary_range']): ?>
                                <span class="badge" style="background:#dcfce7;color:#16a34a;padding:8px 16px;font-size:0.875rem">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                    <?php echo htmlspecialchars($job['salary_range']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-col items-end gap-3">
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Posted <?php echo date('M j, Y', strtotime($job['posted_at'])); ?></p>
                        <p class="text-sm text-gray-500"><?php echo number_format($job['views']); ?> views</p>
                    </div>
                    
                    <?php if (isLoggedIn() && isJobSeeker()): ?>
                        <form method="POST" class="inline-block">
                            <button type="submit" name="toggle_save" class="flex items-center gap-2 px-4 py-2 rounded-lg border transition-colors <?php echo $isSaved ? 'bg-blue-50 border-blue-200 text-blue-600' : 'bg-white border-gray-200 text-gray-600 hover:text-blue-600'; ?>">
                                <svg class="w-5 h-5" fill="<?php echo $isSaved ? 'currentColor' : 'none'; ?>" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                </svg>
                                <?php echo $isSaved ? 'Saved' : 'Save Job'; ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Job Description -->
                <div class="card p-8 fade-up">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Job Description
                    </h2>
                    <div class="prose max-w-none text-gray-700 leading-relaxed">
                        <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                    </div>
                </div>

                <!-- Requirements -->
                <div class="card p-8 fade-up" style="transition-delay: 0.1s">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                        Requirements
                    </h2>
                    <div class="prose max-w-none text-gray-700 leading-relaxed">
                        <?php echo nl2br(htmlspecialchars($job['requirements'])); ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Job Details Card -->
                <div class="card p-6 fade-up" style="transition-delay: 0.2s">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Job Details</h3>
                    <div class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Experience Level</dt>
                            <dd class="text-gray-900"><?php echo $job['experience_level'] ?: 'Not specified'; ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Application Deadline</dt>
                            <dd class="text-gray-900"><?php echo $job['deadline'] ? date('M j, Y', strtotime($job['deadline'])) : 'Not specified'; ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Job ID</dt>
                            <dd class="text-gray-900">#<?php echo $job['job_id']; ?></dd>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <?php if (isLoggedIn() && isJobSeeker()): ?>
                    <div class="card p-6 scale-up" style="transition-delay: 0.3s">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <a href="#apply" class="btn btn-primary w-full justify-center">Apply for This Job</a>
                            <button onclick="window.print()" class="btn btn-ghost w-full justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H3a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                Print Job
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (isLoggedIn()): ?>
            <?php if (isJobSeeker()): ?>
                <!-- Application form for job seekers -->
                <div id="apply" class="card p-8 mt-8 fade-up" style="transition-delay: 0.4s">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Apply for This Position
                    </h2>
                    
                    <?php
                    // Check if already applied
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE job_id = ? AND user_id = ?");
                    $stmt->execute([$job_id, $_SESSION['user_id']]);
                    $alreadyApplied = $stmt->fetchColumn() > 0;
                    
                    if ($alreadyApplied): ?>
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-6 rounded-r-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">Application Submitted</h3>
                                    <p class="text-sm text-blue-700 mt-1">
                                        You've already applied for this position. The employer will review your application and contact you if you're shortlisted.
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <form method="POST" enctype="multipart/form-data" class="space-y-6">
                            <div>
                                <label for="cover_letter" class="block text-sm font-medium text-gray-700 mb-2">Cover Letter *</label>
                                <textarea id="cover_letter" name="cover_letter" rows="6" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Explain why you're a good fit for this position..." required></textarea>
                                <p class="text-sm text-gray-500 mt-1">Share your relevant experience and why you're interested in this role.</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Resume</label>
                                <?php
                                $profile = getJobSeekerProfile($pdo, $_SESSION['user_id']);
                                if (!empty($profile['resume_file'])): ?>
                                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <span class="text-green-800 font-medium"><?php echo $profile['resume_file']; ?></span>
                                        </div>
                                        <p class="text-sm text-green-700 mt-1">Your current resume will be used for this application.</p>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors">
                                    <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx" class="sr-only">
                                    <label for="resume" class="cursor-pointer">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="mt-4">
                                            <p class="text-sm text-gray-600">
                                                <span class="font-medium text-blue-600 hover:text-blue-500">Upload a new resume</span>
                                                or drag and drop
                                            </p>
                                            <p class="text-xs text-gray-500">PDF, DOC, DOCX up to 5MB</p>
                                        </div>
                                    </label>
                                    <p class="text-sm text-gray-500 mt-2" id="file-name"></p>
                                </div>
                            </div>
                            
                            <button type="submit" name="apply_job" class="btn btn-primary w-full justify-center text-lg py-3">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                                Submit Application
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php elseif (isEmployer()): ?>
                <!-- Check if this employer owns this job -->
                <?php
                $stmt = $pdo->prepare("SELECT employer_id FROM jobs WHERE job_id = ?");
                $stmt->execute([$job_id]);
                $jobEmployer = $stmt->fetch();
                $isOwner = false;
                
                if ($jobEmployer) {
                    // Check if current user is the employer of this job
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM employers WHERE (employer_id = ? OR user_id = ?) AND employer_id = ?");
                    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $jobEmployer['employer_id']]);
                    $isOwner = $stmt->fetchColumn() > 0;
                }
                
                if ($isOwner): ?>
                    <!-- Employer actions for their own job postings -->
                <div class="card p-8 mt-8 fade-up" style="transition-delay: 0.4s">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Manage This Job Posting
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <a href="<?php echo BASE_URL; ?>/Employer/applications.php?job_id=<?php echo $job_id; ?>" class="card p-6 text-center hover:shadow-md transition-all group">
                            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-blue-200 transition-colors">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <h3 class="font-semibold text-gray-900 mb-2">View Applications</h3>
                            <p class="text-sm text-gray-600"><?php echo getApplicationsCount($pdo, $job_id); ?> applicants</p>
                        </a>
                        
                        <a href="<?php echo BASE_URL; ?>/Employer/jobs.php?edit=<?php echo $job_id; ?>" class="card p-6 text-center hover:shadow-md transition-all group">
                            <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-yellow-200 transition-colors">
                                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </div>
                            <h3 class="font-semibold text-gray-900 mb-2">Edit Job</h3>
                            <p class="text-sm text-gray-600">Update job details</p>
                        </a>
                        
                        <a href="<?php echo BASE_URL; ?>/Employer/jobs.php?delete=<?php echo $job_id; ?>" class="card p-6 text-center hover:shadow-md transition-all group" onclick="return confirm('Are you sure you want to delete this job posting?');">
                            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-red-200 transition-colors">
                                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </div>
                            <h3 class="font-semibold text-gray-900 mb-2">Delete Job</h3>
                            <p class="text-sm text-gray-600">Remove this posting</p>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php else: ?>
            <!-- Call to action for non-logged in users -->
            <div class="card p-8 mt-8 text-center fade-up" style="transition-delay: 0.4s">
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Interested in this position?</h2>
                <p class="text-gray-600 mb-6 max-w-md mx-auto">Sign in to your account or create a new one to apply for this job and connect with top employers.</p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="<?php echo BASE_URL; ?>/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        Login to Apply
                    </a>
                    <a href="<?php echo BASE_URL; ?>/register.php?type=job_seeker" class="btn btn-ghost">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        Create Account
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
    // Display selected file name
    document.getElementById('resume')?.addEventListener('change', function(e) {
        var fileName = e.target.files[0] ? e.target.files[0].name : 'No file chosen';
        var fileNameElement = document.getElementById('file-name');
        if (fileNameElement) {
            fileNameElement.textContent = fileName;
        }
    });
</script>

<?php require_once 'Includes/footer.php'; ?>