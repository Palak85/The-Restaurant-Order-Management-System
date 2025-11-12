<?php
// Include admin authentication check
require_once 'check_admin.php';

// Include database configuration
require_once '../config.php';

// Set response type to JSON
header('Content-Type: application/json');

try {
    // Check if the restaurant_tables table exists
    $tableCheckQuery = "SHOW TABLES LIKE 'restaurant_tables'";
    $tableExists = $conn->query($tableCheckQuery);
    
    if (!$tableExists || $tableExists->num_rows == 0) {
        // Return empty tables array if the table doesn't exist yet
        echo json_encode([
            'status' => 'success',
            'tables' => []
        ]);
        exit;
    }

    // Get all tables from the database
    $query = "SELECT id, table_number, capacity, is_available FROM restaurant_tables ORDER BY table_number ASC";
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $tables = [];
    while ($row = $result->fetch_assoc()) {
        $tables[] = $row;
    }
    
    // Return tables as JSON
    echo json_encode([
        'status' => 'success',
        'tables' => $tables
    ]);
} catch (Exception $e) {
    // Log the error
    error_log("Error in get_tables_admin.php: " . $e->getMessage());
    
    // Return error message
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>