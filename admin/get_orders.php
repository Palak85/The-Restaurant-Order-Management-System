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

    // Check if orders table exists
    $tableCheckQuery = "SHOW TABLES LIKE 'orders'";
    $tableExists = $conn->query($tableCheckQuery);
    
    if (!$tableExists || $tableExists->num_rows == 0) {
        // Return empty orders array if the table doesn't exist yet
        echo json_encode([
            'status' => 'success',
            'orders' => []
        ]);
        exit;
    }

    // Get structure of orders table to check available columns
    $orderStructureQuery = "DESCRIBE orders";
    $structureResult = $conn->query($orderStructureQuery);
    $hasOrderDate = false;
    $hasTotalAmount = false;
    
    if ($structureResult) {
        while ($column = $structureResult->fetch_assoc()) {
            if ($column['Field'] === 'order_date') {
                $hasOrderDate = true;
            }
            if ($column['Field'] === 'total_amount') {
                $hasTotalAmount = true;
            }
        }
    }
    
    // Adjust query based on available columns
    $dateColumn = $hasOrderDate ? 'o.order_date' : 'NOW() as order_date';
    $totalColumn = $hasTotalAmount ? 'o.total_amount' : 'o.total as total_amount';
    
    // Get orders from the database with adjusted columns
    $query = "SELECT o.id, o.user_id, $dateColumn, $totalColumn, o.status, o.table_id,
            u.name as customer_name, u.email as customer_email
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            ORDER BY o.id DESC";

    $result = $conn->query($query);

    if (!$result) {
        throw new Exception('Failed to fetch orders: ' . $conn->error);
    }

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        // Get items for this order
        $itemsQuery = "SELECT oi.menu_id, oi.quantity, oi.price, m.name as item_name 
                      FROM order_items oi 
                      LEFT JOIN menu_items m ON oi.menu_id = m.id
                      WHERE oi.order_id = {$row['id']}";
        
        $itemsResult = $conn->query($itemsQuery);
        $items = [];
        
        if ($itemsResult) {
            while ($item = $itemsResult->fetch_assoc()) {
                $items[] = $item;
            }
        }
        
        // Format date properly if it exists
        $date = isset($row['order_date']) ? date('Y-m-d H:i', strtotime($row['order_date'])) : date('Y-m-d H:i');
        
        $orders[] = [
            'id' => $row['id'],
            'customer_name' => $row['customer_name'] ?: 'Guest',
            'customer_email' => $row['customer_email'] ?: 'No email',
            'date' => $date,
            'total' => number_format($row['total_amount'], 2),
            'status' => $row['status'] ?: 'created',
            'table_id' => $row['table_id'],
            'items' => $items
        ];
    }

    $response = [
        'status' => 'success',
        'orders' => $orders
    ];
    
    // Log what we're sending back for debugging
    error_log('Orders found: ' . count($orders));
    
} catch (Exception $e) {
    // Log the error
    error_log('Error in get_orders.php: ' . $e->getMessage());
    
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