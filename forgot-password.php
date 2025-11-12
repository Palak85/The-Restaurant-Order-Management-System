<?php
session_start();
require_once 'config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
        
        if (empty($email)) {
            throw new Exception("Please enter your email address.");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please enter a valid email address.");
        }

        // Check if email exists in database
        $query = "SELECT id, name FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Generate a unique token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Check if reset_token column exists
            $check_column = "SHOW COLUMNS FROM users LIKE 'reset_token'";
            $column_result = $conn->query($check_column);
            
            if ($column_result->num_rows == 0) {
                // Add reset_token column if it doesn't exist
                $alter_table = "ALTER TABLE users 
                               ADD COLUMN reset_token VARCHAR(64) DEFAULT NULL,
                               ADD COLUMN reset_token_expiry DATETIME DEFAULT NULL";
                if (!$conn->query($alter_table)) {
                    throw new Exception("Failed to add reset token columns: " . $conn->error);
                }
            }
            
            // Store token in database
            $update_query = "UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?";
            $stmt = $conn->prepare($update_query);
            if (!$stmt) {
                throw new Exception("Database error: " . $conn->error);
            }
            
            $stmt->bind_param("sss", $token, $expiry, $email);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update reset token: " . $stmt->error);
            }
            
            // Send reset email
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password.php?token=" . $token;
            $to = $email;
            $subject = "Password Reset Request - Akay Cafe";
            $message = "Dear " . $user['name'] . ",\n\n";
            $message .= "You have requested to reset your password. Click the link below to reset your password:\n\n";
            $message .= $reset_link . "\n\n";
            $message .= "This link will expire in 1 hour.\n\n";
            $message .= "If you did not request this password reset, please ignore this email.\n\n";
            $message .= "Best regards,\nAkay Cafe Team";
            
            $headers = "From: noreply@akaycafe.com\r\n";
            $headers .= "Reply-To: noreply@akaycafe.com\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();
            
            if (mail($to, $subject, $message, $headers)) {
                $response['success'] = true;
                $response['message'] = "Password reset instructions have been sent to your email address.";
            } else {
                throw new Exception("Failed to send reset email. Please try again later.");
            }
        } else {
            throw new Exception("No account found with that email address.");
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
}

// Clear any previous output
ob_clean();

// Set headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Send JSON response
echo json_encode($response);
exit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Akay Cafe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css?family=Cabin|Herr+Von+Muellerhoff|Source+Sans+Pro" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css">
    <link rel="stylesheet" href="main.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navigation Bar -->
    <nav class="bg-gray-800 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.html" class="text-3xl font-bold">Akay Cafe</a>
            <div class="space-x-4">
                <a href="index.html" class="hover:text-gray-300">Home</a>
                <a href="menu.html" class="hover:text-gray-300">Menu</a>
                <a href="reservation.html" class="hover:text-gray-300">Reservations</a>
                <a href="contact.html" class="hover:text-gray-300">Contact</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8 flex-grow flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-md p-8 max-w-md w-full">
            <h1 class="text-3xl font-bold mb-6 text-center text-slate-700">Forgot Password</h1>
            
            <?php if ($response['message']): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $response['message']; ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input type="email" id="email" name="email" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Send Reset Link
                    </button>
                </div>
                
                <div class="text-center">
                    <a href="login.html" class="text-sm text-blue-600 hover:text-blue-500">
                        Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-auto py-12">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; 2024 Akay Cafe. All rights reserved.</p>
        </div>
    </footer>
</body>
</html> 