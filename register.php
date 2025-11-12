<?php
// Temporarily enable error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once 'config.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// For debugging - log the POST data
error_log("Registration attempt with data: " . print_r($_POST, true));

try {
    // Get and sanitize user input
    $name = isset($_POST['name']) ? $conn->real_escape_string($_POST['name']) : '';
    $email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validate input
    if (empty($name) || empty($email) || empty($password)) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
        exit;
    }

    // Check if email already exists
    $checkQuery = "SELECT id FROM users WHERE email = '$email'";
    $checkResult = $conn->query($checkQuery);

    if ($checkResult === false) {
        throw new Exception("Database query error: " . $conn->error);
    }

    if ($checkResult->num_rows > 0) {
        header('Content-Type: application/json');
        http_response_code(409); // Conflict
        echo json_encode(['status' => 'error', 'message' => 'Email already registered']);
        exit;
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $insertQuery = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashedPassword')";
    
    if ($conn->query($insertQuery) === TRUE) {
        // Get the new user ID
        $user_id = $conn->insert_id;
        
        // Start session and set user data
        session_start();
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Registration successful',
            'user' => [
                'id' => $user_id,
                'name' => $name,
                'email' => $email
            ]
        ]);
    } else {
        throw new Exception("Registration failed: " . $conn->error);
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
    error_log('Registration error: ' . $e->getMessage());
}
?>
