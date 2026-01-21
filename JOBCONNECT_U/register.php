<?php
require_once 'Includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectByUserType();
}

$pageTitle = 'Register';
$userType = isset($_GET['type']) && in_array($_GET['type'], ['job_seeker', 'employer']) ? $_GET['type'] : null;

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'email' => sanitize($_POST['email']),
        'password' => $_POST['password'],
        'confirm_password' => $_POST['confirm_password'],
        'first_name' => sanitize($_POST['first_name']),
        'last_name' => sanitize($_POST['last_name']),
        'phone' => sanitize($_POST['phone'] ?? ''),
        'user_type' => sanitize($_POST['user_type']),
        'company_name' => sanitize($_POST['company_name'] ?? ''),
        'location' => sanitize($_POST['location'] ?? '')
    ];
    
    $result = registerUser($data, $pdo);
    
    if ($result['success']) {
        // Auto-login after registration
        $user_id = $result['user_id'];
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        
        setMessage('success', 'Registration successful! Welcome to JobConnectUganda');
        redirectByUserType();
        exit();
    } else {
        $errors = $result['errors'];
    }
}

require_once 'Includes/header.php';
?>

<div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md overflow-hidden p-6 mt-10">
    <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Create Your Account</h2>
    
    <div class="flex border-b mb-6">
        <a href="?type=job_seeker" class="flex-1 text-center py-2 <?php echo ($userType === 'job_seeker' || !$userType) ? 'border-b-2 border-blue-600 text-blue-600 font-medium' : 'text-gray-600'; ?>">
            I'm a Job Seeker
        </a>
        <a href="?type=employer" class="flex-1 text-center py-2 <?php echo $userType === 'employer' ? 'border-b-2 border-blue-600 text-blue-600 font-medium' : 'text-gray-600'; ?>">
            I'm an Employer
        </a>
    </div>
    
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
        <input type="hidden" name="user_type" value="<?php echo $userType ?: 'job_seeker'; ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="first_name" class="block text-gray-700 text-sm font-medium mb-2">First Name*</label>
                <input type="text" id="first_name" name="first_name" required class="w-full px-3 py-2 border <?php echo isset($errors['first_name']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo $_POST['first_name'] ?? ''; ?>">
                <?php if (isset($errors['first_name'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?php echo $errors['first_name']; ?></p>
                <?php endif; ?>
            </div>
            
            <div>
                <label for="last_name" class="block text-gray-700 text-sm font-medium mb-2">Last Name*</label>
                <input type="text" id="last_name" name="last_name" required class="w-full px-3 py-2 border <?php echo isset($errors['last_name']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo $_POST['last_name'] ?? ''; ?>">
                <?php if (isset($errors['last_name'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?php echo $errors['last_name']; ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email Address*</label>
            <input type="email" id="email" name="email" required class="w-full px-3 py-2 border <?php echo isset($errors['email']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo $_POST['email'] ?? ''; ?>">
            <?php if (isset($errors['email'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['email']; ?></p>
            <?php endif; ?>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="password" class="block text-gray-700 text-sm font-medium mb-2">Password*</label>
                <input type="password" id="password" name="password" required class="w-full px-3 py-2 border <?php echo isset($errors['password']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <?php if (isset($errors['password'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?php echo $errors['password']; ?></p>
                <?php endif; ?>
            </div>
            
            <div>
                <label for="confirm_password" class="block text-gray-700 text-sm font-medium mb-2">Confirm Password*</label>
                <input type="password" id="confirm_password" name="confirm_password" required class="w-full px-3 py-2 border <?php echo isset($errors['confirm_password']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <?php if (isset($errors['confirm_password'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?php echo $errors['confirm_password']; ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mb-4">
            <label for="phone" class="block text-gray-700 text-sm font-medium mb-2">Phone Number</label>
            <input type="tel" id="phone" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo $_POST['phone'] ?? ''; ?>">
        </div>
        
        <?php if ($userType === 'employer'): ?>
            <div class="mb-4">
                <label for="company_name" class="block text-gray-700 text-sm font-medium mb-2">Company Name*</label>
                <input type="text" id="company_name" name="company_name" required class="w-full px-3 py-2 border <?php echo isset($errors['company_name']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo $_POST['company_name'] ?? ''; ?>">
                <?php if (isset($errors['company_name'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?php echo $errors['company_name']; ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="mb-6">
            <label for="location" class="block text-gray-700 text-sm font-medium mb-2">Location</label>
            <input type="text" id="location" name="location" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo $_POST['location'] ?? ''; ?>">
        </div>
        
        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            Register
        </button>
    </form>
    
    <div class="mt-6 text-center">
        <p class="text-gray-600">Already have an account? <a href="<?php echo BASE_URL; ?>/login.php" class="text-blue-600 hover:underline">Login here</a></p>
    </div>
</div>

<?php require_once 'Includes/footer.php'; ?>