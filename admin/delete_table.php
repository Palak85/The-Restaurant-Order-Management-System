<?php
// Include admin authentication check
require_once 'check_admin.php';

// Include database configuration
require_once '../config.php';

// Set response type to JSON
header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Check if ID is provided
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid table ID'
    ]);
    exit;
}

$tableId = intval($_POST['id']);

try {
    // Check if the table has active reservations (those that should not be deleted)
    $reservationCheck = "SELECT COUNT(*) as count FROM reservations WHERE table_id = ? AND status IN ('confirmed', 'pending')";
    $stmt = $conn->prepare($reservationCheck);
    
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param('i', $tableId);
    
    if (!$stmt->execute()) {
        throw new Exception("Error checking reservations: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        throw new Exception("Cannot delete table - it has active reservations");
    }
    
    // Get the table number for confirmation in response
    $tableQuery = "SELECT table_number FROM restaurant_tables WHERE id = ?";
    $stmt = $conn->prepare($tableQuery);
    
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param('i', $tableId);
    
    if (!$stmt->execute()) {
        throw new Exception("Error retrieving table: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Table not found");
    }
    
    $tableData = $result->fetch_assoc();
    $tableNumber = $tableData['table_number'];
    
    // Start a transaction to ensure all operations succeed or fail together
    $conn->begin_transaction();
    
    try {
        // Delete any non-active reservations (completed, cancelled, no-show) first to handle foreign key constraint
        $deleteReservationsQuery = "DELETE FROM reservations WHERE table_id = ? AND status NOT IN ('confirmed', 'pending')";
        $stmt = $conn->prepare($deleteReservationsQuery);
        
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $stmt->bind_param('i', $tableId);
        
        if (!$stmt->execute()) {
            throw new Exception("Error deleting related reservations: " . $stmt->error);
        }
        
        // Delete the table
        $deleteQuery = "DELETE FROM restaurant_tables WHERE id = ?";
        $stmt = $conn->prepare($deleteQuery);
        
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $stmt->bind_param('i', $tableId);
        
        if (!$stmt->execute()) {
            throw new Exception("Error deleting table: " . $stmt->error);
        }
        
        if ($stmt->affected_rows === 0) {
            throw new Exception("Table not found or already deleted");
        }
        
        // Commit the transaction if all operations succeed
        $conn->commit();
        
        // Return success response
        echo json_encode([
            'status' => 'success',
            'message' => "Table #$tableNumber has been deleted successfully"
        ]);
        
    } catch (Exception $e) {
        // Roll back the transaction if any operation fails
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    // Log the error
    error_log("Error in delete_table.php: " . $e->getMessage());
    
    // Return error message
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>