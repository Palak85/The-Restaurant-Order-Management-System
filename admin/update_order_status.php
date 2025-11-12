<?php
// Include database configuration
require_once '../config.php';

// Check if admin is logged in
require_once 'check_admin.php';

// Set header to JSON
header('Content-Type: application/json');

try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Method not allowed", 405);
    }
    
    // Check if required parameters are provided
    if (!isset($_POST['id']) || !isset($_POST['status'])) {
        throw new Exception("Missing required parameters", 400);
    }
    
    $order_id = intval($_POST['id']);
    $status = $conn->real_escape_string($_POST['status']);
    
    // Validate status
    $allowed_statuses = ['pending', 'processing', 'completed', 'cancelled'];
    if (!in_array($status, $allowed_statuses)) {
        throw new Exception("Invalid status value", 400);
    }
    
    // Update order status
    $query = "UPDATE orders SET status = '$status' WHERE id = $order_id";
    
    if (!$conn->query($query)) {
        throw new Exception("Failed to update order status: " . $conn->error, 500);
    }
    
    // Check if any rows were affected
    if ($conn->affected_rows === 0) {
        throw new Exception("Order not found or status unchanged", 404);
    }
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Order status updated successfully',
        'order_id' => $order_id,
        'new_status' => $status
    ]);
    
} catch (Exception $e) {
    // Return error message
    http_response_code($e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>