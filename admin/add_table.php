<?php
// Include admin authentication check
require_once 'check_admin.php';

// Include database configuration
require_once '../config.php';

// Set response type to JSON
header('Content-Type: application/json');

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
    exit;
}

try {
    // Check if required fields are set
    if (!isset($_POST['table_number']) || !is_numeric($_POST['table_number']) || 
        !isset($_POST['capacity']) || !is_numeric($_POST['capacity'])) {
        throw new Exception('Missing or invalid required fields');
    }

    // Get and sanitize input
    $table_number = intval($_POST['table_number']);
    $capacity = intval($_POST['capacity']);
    $is_available = isset($_POST['is_available']) ? intval($_POST['is_available']) : 1;

    // Validate input
    if ($table_number <= 0) {
        throw new Exception('Table number must be greater than zero');
    }

    if ($capacity <= 0) {
        throw new Exception('Capacity must be greater than zero');
    }

    // Check if the table number already exists
    $checkQuery = "SELECT id FROM restaurant_tables WHERE table_number = ?";
    $stmt = $conn->prepare($checkQuery);
    
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param('i', $table_number);
    
    if (!$stmt->execute()) {
        throw new Exception("Error checking table: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        throw new Exception("Table number $table_number already exists");
    }

    // Insert the new table
    $insertQuery = "INSERT INTO restaurant_tables (table_number, capacity, is_available) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param('iii', $table_number, $capacity, $is_available);
    
    if (!$stmt->execute()) {
        throw new Exception("Error adding table: " . $stmt->error);
    }
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'message' => "Table #$table_number added successfully",
        'table_id' => $conn->insert_id
    ]);
} catch (Exception $e) {
    // Log the error
    error_log("Error in add_table.php: " . $e->getMessage());
    
    // Return error message
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>