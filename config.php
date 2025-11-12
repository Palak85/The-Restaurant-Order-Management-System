<?php
// DB config
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'akay_restaurant';

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    // Only set header if we're in a direct access context, not when included
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
    }
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit;
}
