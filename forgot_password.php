<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['email'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Email is required'
    ]);
    exit;
}

$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ]);
    exit;
}

// Database connection
require_once 'config.php';

try {
    // Check if email exists
    $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'No account found with this email address'
        ]);
        exit;
    }

    $user = $result->fetch_assoc();
    
    // Generate reset token
    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Store reset token in database
    $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
    $stmt->bind_param("ssi", $token, $expiry, $user['id']);
    $stmt->execute();

    // Send reset email
    $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=" . $token;
    $to = $email;
    $subject = "Password Reset Request - Akay Cafe";
    $message = "Hello " . $user['name'] . ",\n\n";
    $message .= "You have requested to reset your password. Click the link below to reset it:\n\n";
    $message .= $resetLink . "\n\n";
    $message .= "This link will expire in 1 hour.\n\n";
    $message .= "If you didn't request this, please ignore this email.\n\n";
    $message .= "Best regards,\nAkay Cafe Team";
    
    $headers = "From: noreply@akaycafe.com";

    if (mail($to, $subject, $message, $headers)) {
        echo json_encode([
            'success' => true,
            'message' => 'Password reset link has been sent to your email'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error sending email. Please try again later.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();
?> 