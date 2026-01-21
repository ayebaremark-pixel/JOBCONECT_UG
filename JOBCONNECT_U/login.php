<?php
require_once 'Includes/config.php'; 


if (isLoggedIn()) {
    redirectByUserType();
}

$pageTitle = 'Login';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    if (loginUser($email, $password, $pdo)) {
        // Redirect to appropriate dashboard
        if (isset($_SESSION['redirect_url'])) {
            $redirectUrl = $_SESSION['redirect_url'];
            unset($_SESSION['redirect_url']);
            header("Location: $redirectUrl");
        } else {
            redirectByUserType();
        }
        exit();
    } else {
        setMessage('error', 'Invalid email or password');
    }
}

require_once 'Includes/header.php';
?>

<div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden p-6 mt-10">
    <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Login to Your Account</h2>
    
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email Address</label>
            <input type="email" id="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div class="mb-6">
            <label for="password" class="block text-gray-700 text-sm font-medium mb-2">Password</label>
            <input type="password" id="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <div class="text-right mt-2">
                <a href="#" class="text-sm text-blue-600 hover:underline">Forgot password?</a>
            </div>
        </div>
        
        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            Login
        </button>
    </form>
    
    <div class="mt-6 text-center">
        <p class="text-gray-600">Don't have an account? <a href="<?php echo BASE_URL; ?>/register.php" class="text-blue-600 hover:underline">Register here</a></p>
    </div>
</div>

<?php require_once 'Includes/footer.php'; ?>