<?php
// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is an admin
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_type'] === 'admin';
}

// Check if user is an employer
function isEmployer() {
    return isLoggedIn() && $_SESSION['user_type'] === 'employer';
}

// Check if user is a job seeker
function isJobSeeker() {
    return isLoggedIn() && $_SESSION['user_type'] === 'job_seeker';
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }
}


function redirectByUserType() {
    if (isLoggedIn()) {
        if (isAdmin()) {
            header('Location: ' . BASE_URL . '/admin/dashboard.php');
        } elseif (isEmployer()) {
            header('Location: ' . BASE_URL . '/employer/dashboard.php');
        } else {
            header('Location: ' . BASE_URL . '/job-seeker/dashboard.php');
        }
        exit();
    }
}

// Login user
function loginUser($email, $password, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        
        // Update last login
        $updateStmt = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?");
        $updateStmt->execute([$user['user_id']]);
        
        // Log the login
        logAction($user['user_id'], 'login', 'User logged in', $pdo);
        
        return true;
    }
    
    return false;
}

// Logout user
function logoutUser($pdo) {
    if (isLoggedIn()) {
        logAction($_SESSION['user_id'], 'logout', 'User logged out', $pdo);
    }
    
    // Unset all session variables
    $_SESSION = array();
    
    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
}

// Log actions for audit trail
function logAction($user_id, $action, $description, $pdo) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $action, $description, $ip]);
}

// Register new user
function registerUser($data, $pdo) {
   
    $errors = [];
    
    if (empty($data['email'])) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetchColumn() > 0) {
            $errors['email'] = 'Email already registered';
        }
    }
    
    if (empty($data['password'])) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($data['password']) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    }
    
    if ($data['password'] !== $data['confirm_password']) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    if (empty($data['first_name'])) {
        $errors['first_name'] = 'First name is required';
    }
    
    if (empty($data['last_name'])) {
        $errors['last_name'] = 'Last name is required';
    }
    
    if (empty($data['user_type']) || !in_array($data['user_type'], ['job_seeker', 'employer'])) {
        $errors['user_type'] = 'Invalid user type';
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    // Hash password
    $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, user_type, first_name, last_name, phone) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['email'],
        $password_hash,
        $data['user_type'],
        $data['first_name'],
        $data['last_name'],
        $data['phone'] ?? null
    ]);
    
    $user_id = $pdo->lastInsertId();
    
    // Create profile based on user type
    if ($data['user_type'] === 'job_seeker') {
        $stmt = $pdo->prepare("INSERT INTO job_seeker_profiles (user_id, location) VALUES (?, ?)");
        $stmt->execute([$user_id, $data['location'] ?? null]);
    } elseif ($data['user_type'] === 'employer') {
        $stmt = $pdo->prepare("INSERT INTO employers (user_id, company_name, location) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $data['company_name'], $data['location'] ?? null]);
    }
    
    // Log the registration
    logAction($user_id, 'register', 'New user registered', $pdo);
    
    return ['success' => true, 'user_id' => $user_id];
}
?>