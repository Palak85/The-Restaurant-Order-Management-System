<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the raw input for debugging
$rawInput = file_get_contents('php://input');
error_log("Raw input: " . $rawInput);

// Get POST data
$data = json_decode($rawInput, true);

// Log the decoded data
error_log("Decoded data: " . print_r($data, true));

if (!$data) {
    error_log("JSON decode error: " . json_last_error_msg());
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data: ' . json_last_error_msg()
    ]);
    exit;
}

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

// Log the extracted values
error_log("Email: " . $email);
error_log("Password length: " . strlen($password));

// Validate input
if (empty($email) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Email and password are required'
    ]);
    exit;
}

// Database connection
require_once 'config.php';

try {
    // Log the database connection status
    error_log("Database connection established");
    
    // Prepare statement
    $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Log the query result
    error_log("Query result: " . ($result->num_rows > 0 ? "User found" : "User not found"));

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Log password verification
        error_log("Stored password hash: " . $user['password']);
        $passwordVerified = password_verify($password, $user['password']);
        error_log("Password verification result: " . ($passwordVerified ? "Success" : "Failed"));
        
        // Verify password
        if ($passwordVerified) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email']
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid password'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
    }
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();
?>