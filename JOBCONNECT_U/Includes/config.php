<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'jobconnectuganda');
define('DB_USER', 'root'); // Change if your MySQL user is different
define('DB_PASS', '');     // Add your MySQL password if set

// SQLite database file path
define('SQLITE_DB_PATH', __DIR__ . '/../database/jobconnectuganda.db');

// Base URL for your project (adjust if needed)
define('BASE_URL', 'http://localhost:8000');

// File upload paths
const UPLOAD_RESUME_PATH = __DIR__ . '/../Assets/uploads/resumes/';
const UPLOAD_LOGO_PATH = __DIR__ . '/../Assets/uploads/logos/';

// Start session
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include helper files
require_once 'functions.php';
require_once 'auth.php';

// Autoload classes
spl_autoload_register(function($class) {
    require_once __DIR__ . '/../Classes/' . $class . '.php';
});

// PDO connection - SQLite
try {
    // Create database directory if it doesn't exist
    $dbDir = dirname(SQLITE_DB_PATH);
    if (!file_exists($dbDir)) {
        mkdir($dbDir, 0755, true);
    }
    
    $pdo = new PDO(
        'sqlite:' . SQLITE_DB_PATH,
        null,
        null,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Enable foreign key constraints for SQLite
    $pdo->exec('PRAGMA foreign_keys = ON');
    
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

// Remove debug code - uncomment below for debugging
// $stmt = $pdo->query("SHOW TABLES");
// $tables = $stmt->fetchAll();
// print_r($tables);

// Check if redirectByUserType function exists and fix the employer path
if (!function_exists('redirectByUserType')) {
    function redirectByUserType() {
        if (isEmployer()) {
            header('Location: ' . BASE_URL . '/Employer/dashboard.php');
        } else {
            header('Location: ' . BASE_URL . '/job-seeker/dashboard.php');
        }
        exit();
    }
}