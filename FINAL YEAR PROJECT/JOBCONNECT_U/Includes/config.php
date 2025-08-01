<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'jobconnectuganda');
define('DB_USER', 'root'); // Change if your MySQL user is different
define('DB_PASS', '');     // Add your MySQL password if set

// Base URL for your project (adjust if needed)
define('BASE_URL', 'http://localhost/FINAL%20YEAR%20PROJECT/JOBCONNECT_U');

// File upload paths
const UPLOAD_RESUME_PATH = __DIR__ . '/../assets/uploads/resumes/';
const UPLOAD_LOGO_PATH = __DIR__ . '/../assets/uploads/logos/';

// Start session
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include helper files
require_once 'functions.php';
require_once 'auth.php';
require_once 'includes/config.php';

// Autoload classes
spl_autoload_register(function($class) {
    require_once __DIR__ . '/../classes/' . $class . '.php';
});

// PDO connection
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// File upload settings
const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
const ALLOWED_RESUME_TYPES = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
const ALLOWED_LOGO_TYPES = ['image/jpeg', 'image/png', 'image/gif'];

// Create upload directories if they don't exist
if (!file_exists(UPLOAD_RESUME_PATH)) {
    mkdir(UPLOAD_RESUME_PATH, 0755, true);
}
if (!file_exists(UPLOAD_LOGO_PATH)) {
    mkdir(UPLOAD_LOGO_PATH, 0755, true);
}

$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll();
print_r($tables);