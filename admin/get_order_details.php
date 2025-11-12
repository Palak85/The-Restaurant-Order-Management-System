<?php
// Turn off PHP error output to prevent HTML in JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set up error logging to a file
ini_set('log_errors', 1);
ini_set('error_log', '../admin_errors.log');

// Start output buffering to catch any unexpected output
ob_start();

try {
    // Include admin authentication check
    require_once 'check_admin.php';

    // Include database configuration
    require_once '../config.php';

    // Set response type to JSON
    header('Content-Type: application/json');

    // Check if order ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('Order ID is required');
    }

    // Sanitize input
    $orderId = intval($_GET['id']);
    
    // Get order details - using the actual table structure
    $query = "SELECT o.id, o.user_id, o.total, o.status, o.table_id,
              u.name AS customer_name, u.email AS customer_email
              FROM orders o
              LEFT JOIN users u ON o.user_id = u.id
              WHERE o.id = ?";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare order query: ' . $conn->error);
    }

    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result || $result->num_rows === 0) {
        throw new Exception('Order not found');
    }

    // Get order details
    $order = $result->fetch_assoc();
    
    // Format total amount properly
    $order['total'] = number_format((float)$order['total'], 2);
    
    // Add current date/time since there's no date column in the orders table
    $order['date'] = date('Y-m-d H:i');
    
    // Get items for this order
    $itemsQuery = "SELECT oi.menu_id, oi.quantity, oi.price, m.name AS item_name 
                  FROM order_items oi 
                  LEFT JOIN menu_items m ON oi.menu_id = m.id
                  WHERE oi.order_id = ?";
    
    $itemsStmt = $conn->prepare($itemsQuery);
    if (!$itemsStmt) {
        throw new Exception('Failed to prepare items query: ' . $conn->error);
    }

    $itemsStmt->bind_param('i', $orderId);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();
    $items = [];
    
    if ($itemsResult) {
        while ($item = $itemsResult->fetch_assoc()) {
            $items[] = $item;
        }
    }
    
    // Add items to order
    $order['items'] = $items;
    
    // If table_id exists, get table information - using the correct table name "restraunt_tables"
    if (!empty($order['table_id'])) {
        $tableQuery = "SELECT table_number, capacity FROM restaurant_tables WHERE id = ?";
        $tableStmt = $conn->prepare($tableQuery);
        
        if ($tableStmt) {
            $tableStmt->bind_param('i', $order['table_id']);
            $tableStmt->execute();
            $tableResult = $tableStmt->get_result();
            
            if ($tableResult && $tableResult->num_rows > 0) {
                $table = $tableResult->fetch_assoc();
                $order['table_number'] = $table['table_number'];
                $order['table_capacity'] = $table['capacity'];
            }
            
            $tableStmt->close();
        }
    }

    $response = [
        'status' => 'success',
        'order' => $order
    ];
    
    // Log success for debugging
    error_log('Order details retrieved successfully for ID: ' . $orderId);
    
} catch (Exception $e) {
    // Log the error
    error_log('Error in get_order_details.php: ' . $e->getMessage());
    
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// Clean any output buffer
ob_end_clean();

// Return JSON response
echo json_encode($response);
exit;
?>