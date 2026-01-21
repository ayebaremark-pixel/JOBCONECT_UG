<?php
// Sanitize input data
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Display success/error messages
function displayMessage() {
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message']['type'];
        $text = $_SESSION['message']['text'];
        echo "<div class='alert alert-$type'>$text</div>";
        unset($_SESSION['message']);
    }
}

// Set flash message
function setMessage($type, $text) {
    $_SESSION['message'] = [
        'type' => $type,
        'text' => $text
    ];
}

// Get user's full name
function getFullName($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    return $user ? $user['first_name'] . ' ' . $user['last_name'] : '';
}

// Get job details
function getJobDetails($pdo, $job_id) {
    $stmt = $pdo->prepare("SELECT j.*, e.company_name, e.logo FROM jobs j JOIN employers e ON j.employer_id = e.employer_id WHERE j.job_id = ?");
    $stmt->execute([$job_id]);
    return $stmt->fetch();
}

// Get employer details
function getEmployerDetails($pdo, $employer_id) {
    $stmt = $pdo->prepare("SELECT e.*, u.email, u.phone FROM employers e JOIN users u ON e.user_id = u.user_id WHERE e.employer_id = ?");
    $stmt->execute([$employer_id]);
    return $stmt->fetch();
}

// Get job seeker profile
function getJobSeekerProfile($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT p.*, u.email, u.phone FROM job_seeker_profiles p JOIN users u ON p.user_id = u.user_id WHERE p.user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// Check if job is saved by user
function isJobSaved($pdo, $job_id, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM saved_jobs WHERE job_id = ? AND user_id = ?");
    $stmt->execute([$job_id, $user_id]);
    return $stmt->fetchColumn() > 0;
}

// Handle file upload
function handleFileUpload($file, $targetDir, $allowedTypes = ['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg']) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload error'];
    }
    
    // Check file size (max 5MB)
    if ($file['size'] > 5000000) {
        return ['success' => false, 'error' => 'File too large (max 5MB)'];
    }
    
    // Get file extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Check file type
    if (!in_array($ext, $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }
    
    // Generate unique filename
    $filename = uniqid() . '.' . $ext;
    $targetPath = $targetDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'error' => 'Failed to move uploaded file'];
    }
}

// Get job applications count
function getApplicationsCount($pdo, $job_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE job_id = ?");
    $stmt->execute([$job_id]);
    return $stmt->fetchColumn();
}

// Get user applications
function getUserApplications($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT a.*, j.title, e.company_name 
                          FROM applications a 
                          JOIN jobs j ON a.job_id = j.job_id 
                          JOIN employers e ON j.employer_id = e.employer_id 
                          WHERE a.user_id = ?
                          ORDER BY a.applied_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Get job applications for employer
function getJobApplications($pdo, $job_id) {
    $stmt = $pdo->prepare("SELECT a.*, u.first_name, u.last_name, p.headline 
                          FROM applications a 
                          JOIN users u ON a.user_id = u.user_id 
                          JOIN job_seeker_profiles p ON u.user_id = p.user_id 
                          WHERE a.job_id = ?
                          ORDER BY a.applied_at DESC");
    $stmt->execute([$job_id]);
    return $stmt->fetchAll();
}

// Get all jobs with filters
function getJobs($pdo, $filters = []) {
    $query = "SELECT j.*, e.company_name, e.logo FROM jobs j JOIN employers e ON j.employer_id = e.employer_id WHERE j.is_active = TRUE";
    $params = [];
    
    if (!empty($filters['search'])) {
        $query .= " AND (j.title LIKE ? OR j.description LIKE ? OR e.company_name LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
    }
    
    if (!empty($filters['location'])) {
        $query .= " AND j.location LIKE ?";
        $params[] = '%' . $filters['location'] . '%';
    }
    
    if (!empty($filters['job_type'])) {
        $query .= " AND j.job_type = ?";
        $params[] = $filters['job_type'];
    }
    
    if (!empty($filters['experience_level'])) {
        $query .= " AND j.experience_level = ?";
        $params[] = $filters['experience_level'];
    }
    
    if (!empty($filters['industry'])) {
        $query .= " AND e.industry = ?";
        $params[] = $filters['industry'];
    }
    
    $query .= " ORDER BY j.posted_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Get saved jobs for user
function getSavedJobs($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT j.*, e.company_name, e.logo 
                          FROM saved_jobs s 
                          JOIN jobs j ON s.job_id = j.job_id 
                          JOIN employers e ON j.employer_id = e.employer_id 
                          WHERE s.user_id = ?
                          ORDER BY s.saved_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}
?>