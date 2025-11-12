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
    
    $reservation_id = intval($_POST['id']);
    $status = $conn->real_escape_string($_POST['status']);
    
    // Validate status
    $allowed_statuses = ['confirmed', 'cancelled', 'completed', 'no-show'];
    if (!in_array($status, $allowed_statuses)) {
        throw new Exception("Invalid status value", 400);
    }
    
    // Start a transaction to ensure all operations succeed
    $conn->begin_transaction();
    
    try {
        // Update reservation status
        $query = "UPDATE reservations SET status = '$status' WHERE id = $reservation_id";
        
        if (!$conn->query($query)) {
            throw new Exception("Failed to update reservation status: " . $conn->error, 500);
        }
        
        // Check if any rows were affected
        if ($conn->affected_rows === 0) {
            throw new Exception("Reservation not found or status unchanged", 404);
        }
        
        // If status is "completed", "cancelled", or "no-show", make the table available again
        if (in_array($status, ['completed', 'cancelled', 'no-show'])) {
            // First, get the table_id for this reservation
            $tableQuery = "SELECT table_id FROM reservations WHERE id = $reservation_id";
            $tableResult = $conn->query($tableQuery);
            
            if (!$tableResult) {
                throw new Exception("Failed to retrieve table information: " . $conn->error, 500);
            }
            
            if ($tableResult->num_rows > 0) {
                $tableRow = $tableResult->fetch_assoc();
                $tableId = $tableRow['table_id'];
                
                // Update the table availability to make it available again
                $updateTableQuery = "UPDATE restaurant_tables SET is_available = 1 WHERE id = $tableId";
                
                if (!$conn->query($updateTableQuery)) {
                    throw new Exception("Failed to update table availability: " . $conn->error, 500);
                }
            }
        }
        
        // Commit the transaction
        $conn->commit();
        
        // Return success response
        echo json_encode([
            'status' => 'success',
            'message' => 'Reservation status updated successfully' . (in_array($status, ['completed', 'cancelled', 'no-show']) ? ' and table marked as available' : ''),
            'reservation_id' => $reservation_id,
            'new_status' => $status
        ]);
        
    } catch (Exception $e) {
        // Rollback the transaction if there was an error
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    // Return error message
    http_response_code($e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>