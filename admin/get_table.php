<?php
// Include admin authentication check
require_once 'check_admin.php';

// Include database configuration
require_once '../config.php';

// Set response type to JSON
header('Content-Type: application/json');

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid table ID'
    ]);
    exit;
}

$id = intval($_GET['id']);

try {
    // Get table from the database
    $query = "SELECT id, table_number, capacity, is_available FROM restaurant_tables WHERE id = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param('i', $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Error executing query: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Table not found'
        ]);
        exit;
    }
    
    $table = $result->fetch_assoc();
    
    // Return table data as JSON
    echo json_encode([
        'status' => 'success',
        'table' => $table
    ]);
} catch (Exception $e) {
    // Log the error
    error_log("Error in get_table.php: " . $e->getMessage());
    
    // Return error message
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>