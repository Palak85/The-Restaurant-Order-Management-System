<?php
// Start session
session_start();

// Function to check if user is logged in as admin
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// If this file is accessed directly, return JSON response
if (basename($_SERVER['PHP_SELF']) == 'check_admin.php') {
    header('Content-Type: application/json');
    echo json_encode([
        'is_admin' => is_admin_logged_in(),
        'admin_username' => is_admin_logged_in() ? $_SESSION['admin_username'] : null
    ]);
    exit;
}

// For other files requiring this check
if (!is_admin_logged_in()) {
    // If AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Admin authentication required']);
        exit;
    }
    
    // If regular request, redirect to login
    header('Location: login.html');
    exit;
}
?>