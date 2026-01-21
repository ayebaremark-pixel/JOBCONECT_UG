<?php
require_once '../Includes/config.php';

header('Content-Type: application/json');

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
        
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful! Welcome to JobConnectUganda'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'errors' => $result['errors'],
            'message' => 'Registration failed. Please check the errors.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>