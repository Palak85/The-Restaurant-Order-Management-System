<?php
// Turn off PHP error output to prevent HTML in JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set up error logging to a file
ini_set('log_errors', 1);
ini_set('error_log', 'reservation_errors.log');
error_log("Order finalization attempt started: " . date('Y-m-d H:i:s'));

// Start output buffering to catch any unexpected output
ob_start();

try {
    // Include database configuration
    require_once 'config.php';
    
    error_log("Database connection established");
    
    // Start session to get user information
    session_start();
    
    // Log request data for debugging
    error_log("POST data: " . file_get_contents('php://input'));
    
    // Set response to JSON
    header('Content-Type: application/json');
    
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }
    
    // Get raw input data from request
    $inputData = json_decode(file_get_contents('php://input'), true);
    error_log("Parsed input data: " . print_r($inputData, true));
    
    // Validate input data
    if (!isset($inputData['table_id']) || !isset($inputData['customer_name']) || !isset($inputData['items']) || empty($inputData['items'])) {
        throw new Exception('Missing required order data', 400);
    }
    
    // Extract and sanitize data
    $tableNumber = $conn->real_escape_string($inputData['table_id']);
    $customerName = $conn->real_escape_string($inputData['customer_name']);
    $totalAmount = isset($inputData['total']) ? floatval($inputData['total']) : 0;
    $orderItems = $inputData['items'];
    
    // Get user ID if logged in, otherwise use session ID for guest orders
    $userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
    $sessionId = session_id();
    
    // Check if table exists and is available
    $tableQuery = "SELECT id, capacity, is_available FROM restaurant_tables WHERE table_number = ?";
    $stmt = $conn->prepare($tableQuery);
    
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error, 500);
    }
    
    $stmt->bind_param('s', $tableNumber);
    
    if (!$stmt->execute()) {
        throw new Exception("Error executing query: " . $stmt->error, 500);
    }
    
    $tableResult = $stmt->get_result();
    
    if (!$tableResult || $tableResult->num_rows === 0) {
        throw new Exception("Table #$tableNumber does not exist", 404);
    }
    
    $table = $tableResult->fetch_assoc();
    $tableId = $table['id'];
    
    if ($table['is_available'] != 1) {
        throw new Exception("Table #$tableNumber is already occupied", 409);
    }
    
    // Verify that we have items
    if (count($orderItems) === 0) {
        throw new Exception("Your cart is empty. Please add items before placing an order.", 400);
    }
    
    // Start a transaction to ensure all database operations succeed together
    $conn->begin_transaction();
    
    try {
        // Create reservation
        $reservationQuery = "INSERT INTO reservations (user_id, table_id, reservation_date, reservation_time, guests, status) 
                           VALUES (?, ?, CURDATE(), CURTIME(), ?, 'confirmed')";
        
        $guestsCount = isset($inputData['guests']) ? intval($inputData['guests']) : 1;
        $stmt = $conn->prepare($reservationQuery);
        
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error, 500);
        }
        
        $stmt->bind_param('iii', $userId, $tableId, $guestsCount);
        
        if (!$stmt->execute()) {
            throw new Exception("Error creating reservation: " . $stmt->error, 500);
        }
        
        $reservationId = $conn->insert_id;
        
        // Create a new order
        $createOrderQuery = "INSERT INTO orders (user_id, table_id, total, status) 
                           VALUES (?, ?, ?, ?)";
        
        $stmt = $conn->prepare($createOrderQuery);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error, 500);
        }
        
        $status = 'ordered';
        $stmt->bind_param('iids', $userId, $tableId, $totalAmount, $status);
        
        if (!$stmt->execute()) {
            throw new Exception("Error creating order: " . $stmt->error, 500);
        }
        
        $orderId = $conn->insert_id;
        
        // Insert order items
        $itemInsertQuery = "INSERT INTO order_items (order_id, menu_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($itemInsertQuery);
        
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error, 500);
        }
        
        // Insert each item from the cart
        foreach ($orderItems as $item) {
            $menuId = intval($item['menu_item_id']);
            $quantity = intval($item['quantity']);
            $price = floatval($item['price']);
            
            $stmt->bind_param('iiid', $orderId, $menuId, $quantity, $price);
            
            if (!$stmt->execute()) {
                throw new Exception("Error adding item to order: " . $stmt->error, 500);
            }
        }
        
        // Update table availability
        $updateTableQuery = "UPDATE restaurant_tables SET is_available = 0 WHERE id = ?";
        $stmt = $conn->prepare($updateTableQuery);
        
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error, 500);
        }
        
        $stmt->bind_param('i', $tableId);
        
        if (!$stmt->execute()) {
            throw new Exception("Error updating table status: " . $stmt->error, 500);
        }
        
        // If all operations succeed, commit the transaction
        $conn->commit();
        
        error_log("Order placed successfully with ID: $orderId and reservation ID: $reservationId");
        $response = [
            'status' => 'success',
            'message' => 'Order placed successfully!',
            'order_id' => $orderId,
            'reservation_id' => $reservationId,
            'table_number' => $tableNumber,
            'total' => $totalAmount
        ];
    } catch (Exception $e) {
        // If an error occurs, roll back the transaction
        $conn->rollback();
        error_log("Transaction rolled back: " . $e->getMessage());
        throw new Exception('Error placing order: ' . $e->getMessage(), 500);
    }
} catch (Exception $e) {
    // Log the exception
    error_log("Exception: " . $e->getMessage() . " (Code: " . $e->getCode() . ")");
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Clear any output that might have been generated
    ob_clean();
    
    // Set appropriate status code
    $statusCode = $e->getCode();
    if ($statusCode < 400 || $statusCode > 599) {
        $statusCode = 500; // Default to server error if code is not valid
    }
    http_response_code($statusCode);
    
    // Ensure header is JSON
    header('Content-Type: application/json');
    
    $response = [
        'status' => 'error',
        'message' => $e->getMessage(),
        'code' => $statusCode
    ];
}

// Clean any remaining buffer
while (ob_get_level() > 0) {
    ob_end_clean();
}

// Log the final response
error_log("Final response: " . json_encode($response));

// Send JSON response
echo json_encode($response);
exit;
?>