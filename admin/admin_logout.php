<?php
// Start session
session_start();

// Clear all admin session variables
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['is_admin']);

// Destroy the session
session_destroy();

// Send success response
header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'message' => 'Logged out successfully']);
?>