<?php
// Start session
session_start();

// Set response to JSON
header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Get submitted username and password
$username = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validate credentials (hardcoded admin/admin)
if ($username === 'admin' && $password === 'admin') {
    // Set admin session variables
    $_SESSION['admin_id'] = 1;
    $_SESSION['admin_username'] = 'admin';
    $_SESSION['is_admin'] = true;
    
    echo json_encode([
        'status' => 'success', 
        'message' => 'Login successful'
    ]);
} else {
    http_response_code(401);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Invalid username or password'
    ]);
}
?>