<?php
require_once '../includes/config.php';
requireLogin();

if (!isJobSeeker()) {
    header('Location: ' . BASE_URL . '/');
    exit();
}

$pageTitle = 'My Profile';
$user_id = $_SESSION['user_id'];

// Initialize FileUploader for resumes
$resumeUploader = new FileUploader(
    $pdo,
    UPLOAD_RESUME_PATH,
    ALLOWED_RESUME_TYPES,
    MAX_FILE_SIZE
);

// Get current profile
$profile = getJobSeekerProfile($pdo, $user_id);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'headline' => sanitize($_POST['headline'] ?? ''),
            'bio' => sanitize($_POST['bio'] ?? ''),
            'skills' => sanitize($_POST['skills'] ?? ''),
            'education' => sanitize($_POST['education'] ?? ''),
            'experience' => sanitize($_POST['experience'] ?? ''),
            'location' => sanitize($_POST['location'] ?? '')
        ];

        // Handle resume upload if provided
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
            // Delete old resume if exists
            if (!empty($profile['resume_file'])) {
                $resumeUploader->delete($profile['resume_file']);
            }
            
            // Upload new resume
            $data['resume_file'] = $resumeUploader->upload($_FILES['resume'], $user_id);
        }

        // Update profile
        $stmt = $pdo->prepare("UPDATE job_seeker_profiles SET headline = ?, bio = ?, skills = ?, education = ?, experience = ?, location = ?, resume_file = ? WHERE user_id = ?");
        $stmt->execute([
            $data['headline'],
            $data['bio'],
            $data['skills'],
            $data['education'],
            $data['experience'],
            $data['location'],
            $data['resume_file'] ?? $profile['resume_file'],
            $user_id
        ]);

        setMessage('success', 'Profile updated successfully!');
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    } catch (Exception $e) {
        setMessage('error', $e->getMessage());
    }
}

require_once '../includes/header.php';
?>

<!-- Profile form with resume upload -->
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-blue-600 p-4 text-white">
            <h2 class="text-xl font-semibold">My Profile</h2>
        </div>
        <div class="p-6">
            <form method="POST" enctype="multipart/form-data">
                <!-- Other profile fields -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Resume</label>
                    <?php if (!empty($profile['resume_file'])): ?>
                        <div class="mb-2 flex items-center">
                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-gray-600"><?php echo $profile['resume_file']; ?></span>
                            <a href="<?php echo BASE_URL . '/assets/uploads/resumes/' . $profile['resume_file']; ?>" 
                               class="ml-2 text-blue-600 hover:underline" 
                               target="_blank">View</a>
                            <a href="?delete_resume=1" class="ml-2 text-red-600 hover:underline" 
                               onclick="return confirm('Are you sure you want to delete your resume?')">Delete</a>
                        </div>
                    <?php endif; ?>
                    <div class="flex items-center">
                        <input type="file" id="resume" name="resume" 
                               accept=".pdf,.doc,.docx" 
                               class="sr-only">
                        <label for="resume" class="bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 cursor-pointer">
                            Choose file
                        </label>
                        <span class="ml-2 text-sm text-gray-500" id="resume-filename">
                            <?php echo empty($profile['resume_file']) ? 'No file chosen' : 'Upload new file'; ?>
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">PDF, DOC, DOCX (Max 5MB)</p>
                </div>
                
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-300">
                    Save Profile
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Update filename display when file is selected
    document.getElementById('resume').addEventListener('change', function(e) {
        const fileName = e.target.files[0] ? e.target.files[0].name : 'No file chosen';
        document.getElementById('resume-filename').textContent = fileName;
    });
</script>

<?php require_once '../includes/footer.php'; ?>