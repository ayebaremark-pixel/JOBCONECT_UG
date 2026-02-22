<?php
require_once '../includes/config.php';
requireLogin();

if (!isEmployer()) {
    header('Location: ' . BASE_URL . '/');
    exit();
}

$pageTitle = 'Company Profile';
$user_id = $_SESSION['user_id'];

// Initialize FileUploader for logos
$logoUploader = new FileUploader(
    $pdo,
    UPLOAD_LOGO_PATH,
    ALLOWED_LOGO_TYPES,
    MAX_FILE_SIZE
);

// Get employer profile
$employer = new Employer($pdo);
$employer->loadByUserId($user_id);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'company_name' => sanitize($_POST['company_name'] ?? ''),
            'company_description' => sanitize($_POST['company_description'] ?? ''),
            'industry' => sanitize($_POST['industry'] ?? ''),
            'website' => sanitize($_POST['website'] ?? ''),
            'location' => sanitize($_POST['location'] ?? '')
        ];

        // Handle logo upload if provided
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            // Delete old logo if exists
            if (!empty($employer->getLogo())) {
                $logoUploader->delete($employer->getLogo());
            }
            
            // Upload new logo
            $data['logo'] = $logoUploader->upload($_FILES['logo'], $user_id);
        }

        // Update employer profile
        $employer->update($data);

        setMessage('success', 'Company profile updated successfully!');
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    } catch (Exception $e) {
        setMessage('error', $e->getMessage());
    }
}

// Handle logo deletion
if (isset($_GET['delete_logo'])) {
    try {
        if (!empty($employer->getLogo())) {
            $logoUploader->delete($employer->getLogo());
            $employer->update(['logo' => null]);
            setMessage('success', 'Logo deleted successfully!');
            header('Location: profile.php');
            exit();
        }
    } catch (Exception $e) {
        setMessage('error', $e->getMessage());
    }
}

require_once '../includes/header.php';
?>

<!-- Company profile form with logo upload -->
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-blue-600 p-4 text-white">
            <h2 class="text-xl font-semibold">Company Profile</h2>
        </div>
        <div class="p-6">
            <form method="POST" enctype="multipart/form-data">
                <!-- Company info fields -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Company Logo</label>
                    <?php if (!empty($employer->getLogo())): ?>
                        <div class="mb-4 flex items-center">
                            <img src="<?php echo BASE_URL . '/assets/uploads/logos/' . $employer->getLogo(); ?>" 
                                 alt="<?php echo $employer->getCompanyName(); ?>" 
                                 class="w-16 h-16 object-contain mr-4">
                            <a href="?delete_logo=1" class="text-red-600 hover:underline" 
                               onclick="return confirm('Are you sure you want to delete your company logo?')">
                                Delete Logo
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="flex items-center">
                        <input type="file" id="logo" name="logo" 
                               accept="image/jpeg,image/png,image/gif" 
                               class="sr-only">
                        <label for="logo" class="bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 cursor-pointer">
                            Choose file
                        </label>
                        <span class="ml-2 text-sm text-gray-500" id="logo-filename">
                            <?php echo empty($employer->getLogo()) ? 'No file chosen' : 'Upload new logo'; ?>
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">JPEG, PNG, GIF (Max 5MB)</p>
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
    document.getElementById('logo').addEventListener('change', function(e) {
        const fileName = e.target.files[0] ? e.target.files[0].name : 'No file chosen';
        document.getElementById('logo-filename').textContent = fileName;
    });
</script>

<?php require_once '../includes/footer.php'; ?>