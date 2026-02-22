<?php
require_once 'includes/config.php';

// Clear all session data
session_destroy();

// Clear any cookies if they exist
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Clear any remember me cookies if they exist
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Set a success message for the next session
session_start();
setMessage('success', 'You have been logged out successfully.');

// Redirect to home page
header('Location: ' . BASE_URL . '/index.php');
exit();
?>