<?php
// Start session
session_start();

// Set response to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'authenticated' => true,
        'user_id' => $_SESSION['user_id'],
        'user_name' => $_SESSION['user_name'],
        'user_email' => $_SESSION['user_email']
    ]);
} else {
    echo json_encode([
        'authenticated' => false
    ]);
}
?>