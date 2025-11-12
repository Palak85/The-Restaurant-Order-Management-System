<?php
session_start();
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'orders' => []
];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Please log in to view your orders';
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch orders for the current user with their items
    $query = "SELECT o.*, 
                     GROUP_CONCAT(
                         JSON_OBJECT(
                             'name', mi.name,
                             'quantity', oi.quantity,
                             'price', oi.price
                         )
                     ) as items_json,
                     r.reservation_date,
                     r.reservation_time
              FROM orders o
              LEFT JOIN order_items oi ON o.id = oi.order_id
              LEFT JOIN menu_items mi ON oi.menu_id = mi.id
              LEFT JOIN reservations r ON o.table_id = r.table_id
              WHERE o.user_id = ?
              GROUP BY o.id
              ORDER BY o.id DESC";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to fetch orders: " . $stmt->error);
    }

    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($order = $result->fetch_assoc()) {
            // Parse the items JSON
            $items = json_decode('[' . $order['items_json'] . ']', true);
            $order['items'] = $items;
            unset($order['items_json']); // Remove the raw JSON string
            
            // Format the date
            if (isset($order['reservation_date']) && isset($order['reservation_time'])) {
                $order['formatted_date'] = $order['reservation_date'] . ' ' . $order['reservation_time'];
            } else {
                // If no reservation date is found, use a default date
                $order['formatted_date'] = date('Y-m-d H:i:s');
            }
            
            $response['orders'][] = $order;
        }
        $response['success'] = true;
    } else {
        $response['success'] = true;
        $response['message'] = 'No orders found';
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 