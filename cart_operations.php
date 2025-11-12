<?php
// Include database configuration
require_once 'config.php';

// Set response to JSON
header('Content-Type: application/json');

// Start session to get user information
session_start();

// Check if user is logged in, otherwise use session ID
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$session_id = session_id();

// Get request data
$request_data = json_decode(file_get_contents('php://input'), true);
$action = isset($request_data['action']) ? $request_data['action'] : '';

try {
    // Check if we have the necessary data
    if (empty($action)) {
        throw new Exception('Action is required', 400);
    }

    // Initialize response
    $response = [
        'status' => 'success',
        'message' => '',
        'data' => []
    ];

    // Connect to database
    if (!$conn) {
        throw new Exception('Database connection failed', 500);
    }

    // Determine the action to take
    switch ($action) {
        case 'add_to_cart':
            addToCart($conn, $request_data, $user_id, $session_id);
            break;
            
        case 'remove_from_cart':
            removeFromCart($conn, $request_data, $user_id, $session_id);
            break;
            
        case 'update_quantity':
            updateQuantity($conn, $request_data, $user_id, $session_id);
            break;
            
        case 'get_cart':
            getCart($conn, $user_id, $session_id);
            break;
            
        case 'clear_cart':
            clearCart($conn, $user_id, $session_id);
            break;
            
        default:
            throw new Exception('Invalid action', 400);
    }

    // Return success response (already modified by the action functions)
    echo json_encode($response);
    exit;
    
} catch (Exception $e) {
    http_response_code($e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit;
}

/**
 * Find or create a cart (pending order) for the user or session
 */
function getOrCreateCart($conn, $user_id, $session_id) {
    // Try to find an existing cart (order with status 'cart')
    $cart_query = "SELECT id FROM orders WHERE ";
    $params = [];
    
    if ($user_id) {
        $cart_query .= "user_id = ? AND status = 'cart'";
        $params[] = $user_id;
    } else {
        $cart_query .= "session_id = ? AND status = 'cart' AND user_id IS NULL";
        $params[] = $session_id;
    }
    
    $stmt = $conn->prepare($cart_query);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error, 500);
    }
    
    if (!empty($params)) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute query: ' . $stmt->error, 500);
    }
    
    $result = $stmt->get_result();
    
    // If cart exists, return its ID
    if ($result && $result->num_rows > 0) {
        $cart = $result->fetch_assoc();
        return $cart['id'];
    }
    
    // Otherwise create a new cart
    $insert_query = "INSERT INTO orders ";
    $insert_fields = [];
    $insert_values = [];
    $bind_types = '';
    $params = [];
    
    if ($user_id) {
        $insert_fields[] = 'user_id';
        $insert_values[] = '?';
        $bind_types .= 'i';
        $params[] = $user_id;
    } else {
        $insert_fields[] = 'session_id';
        $insert_values[] = '?';
        $bind_types .= 's';
        $params[] = $session_id;
    }
    
    $insert_fields[] = 'status';
    $insert_values[] = '?';
    $bind_types .= 's';
    $params[] = 'cart';
    
    $insert_fields[] = 'total';
    $insert_values[] = '?';
    $bind_types .= 'd';
    $params[] = 0.00;
    
    $insert_query .= '(' . implode(', ', $insert_fields) . ') VALUES (' . implode(', ', $insert_values) . ')';
    
    $stmt = $conn->prepare($insert_query);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error, 500);
    }
    
    $stmt->bind_param($bind_types, ...$params);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create cart: ' . $stmt->error, 500);
    }
    
    return $conn->insert_id;
}

/**
 * Add an item to the cart
 */
function addToCart($conn, $data, $user_id, $session_id) {
    global $response;
    
    // Validate required fields
    if (!isset($data['menu_id']) || !isset($data['quantity']) || !isset($data['price'])) {
        throw new Exception('Menu ID, quantity, and price are required', 400);
    }
    
    // Get or create cart
    $order_id = getOrCreateCart($conn, $user_id, $session_id);
    
    // Check if item already exists in cart
    $check_query = "SELECT id, quantity FROM order_items WHERE order_id = ? AND menu_id = ?";
    $stmt = $conn->prepare($check_query);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error, 500);
    }
    
    $stmt->bind_param('ii', $order_id, $data['menu_id']);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to check cart item: ' . $stmt->error, 500);
    }
    
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        // Item already exists, update quantity
        $item = $result->fetch_assoc();
        $new_quantity = $item['quantity'] + $data['quantity'];
        
        $update_query = "UPDATE order_items SET quantity = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $conn->error, 500);
        }
        
        $stmt->bind_param('ii', $new_quantity, $item['id']);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update cart item: ' . $stmt->error, 500);
        }
        
        $response['message'] = 'Item quantity updated in cart';
    } else {
        // Item doesn't exist, add it
        $insert_query = "INSERT INTO order_items (order_id, menu_id, quantity, price_at_order) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $conn->error, 500);
        }
        
        $stmt->bind_param('iiid', $order_id, $data['menu_id'], $data['quantity'], $data['price']);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to add item to cart: ' . $stmt->error, 500);
        }
        
        $response['message'] = 'Item added to cart';
    }
    
    // Update order total
    updateOrderTotal($conn, $order_id);
    
    // Get updated cart
    $cart = getCartItems($conn, $order_id);
    $response['data'] = $cart;
}

/**
 * Remove an item from the cart
 */
function removeFromCart($conn, $data, $user_id, $session_id) {
    global $response;
    
    // Validate required fields
    if (!isset($data['menu_id'])) {
        throw new Exception('Menu ID is required', 400);
    }
    
    // Get cart
    $order_id = getOrCreateCart($conn, $user_id, $session_id);
    
    // Delete the item
    $delete_query = "DELETE FROM order_items WHERE order_id = ? AND menu_id = ?";
    $stmt = $conn->prepare($delete_query);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error, 500);
    }
    
    $stmt->bind_param('ii', $order_id, $data['menu_id']);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to remove item from cart: ' . $stmt->error, 500);
    }
    
    // Update order total
    updateOrderTotal($conn, $order_id);
    
    $response['message'] = 'Item removed from cart';
    
    // Get updated cart
    $cart = getCartItems($conn, $order_id);
    $response['data'] = $cart;
}

/**
 * Update item quantity in cart
 */
function updateQuantity($conn, $data, $user_id, $session_id) {
    global $response;
    
    // Validate required fields
    if (!isset($data['menu_id']) || !isset($data['quantity'])) {
        throw new Exception('Menu ID and quantity are required', 400);
    }
    
    if ($data['quantity'] <= 0) {
        // If quantity is 0 or negative, remove the item
        removeFromCart($conn, $data, $user_id, $session_id);
        return;
    }
    
    // Get cart
    $order_id = getOrCreateCart($conn, $user_id, $session_id);
    
    // Update quantity
    $update_query = "UPDATE order_items SET quantity = ? WHERE order_id = ? AND menu_id = ?";
    $stmt = $conn->prepare($update_query);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error, 500);
    }
    
    $stmt->bind_param('iii', $data['quantity'], $order_id, $data['menu_id']);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update item quantity: ' . $stmt->error, 500);
    }
    
    // Update order total
    updateOrderTotal($conn, $order_id);
    
    $response['message'] = 'Item quantity updated';
    
    // Get updated cart
    $cart = getCartItems($conn, $order_id);
    $response['data'] = $cart;
}

/**
 * Get cart contents
 */
function getCart($conn, $user_id, $session_id) {
    global $response;
    
    try {
        // Get cart
        $order_id = getOrCreateCart($conn, $user_id, $session_id);
        
        // Get cart items
        $cart = getCartItems($conn, $order_id);
        
        $response['data'] = $cart;
        $response['message'] = 'Cart retrieved successfully';
    } catch (Exception $e) {
        if ($e->getMessage() === 'No cart found') {
            // No cart exists yet, return empty array
            $response['data'] = [
                'items' => [],
                'total' => 0
            ];
            $response['message'] = 'Cart is empty';
        } else {
            throw $e;
        }
    }
}

/**
 * Clear cart contents
 */
function clearCart($conn, $user_id, $session_id) {
    global $response;
    
    try {
        // Get cart
        $order_id = getOrCreateCart($conn, $user_id, $session_id);
        
        // Delete all items
        $delete_query = "DELETE FROM order_items WHERE order_id = ?";
        $stmt = $conn->prepare($delete_query);
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $conn->error, 500);
        }
        
        $stmt->bind_param('i', $order_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to clear cart: ' . $stmt->error, 500);
        }
        
        // Update order total to 0
        updateOrderTotal($conn, $order_id);
        
        $response['message'] = 'Cart cleared successfully';
        $response['data'] = [
            'items' => [],
            'total' => 0
        ];
    } catch (Exception $e) {
        if ($e->getMessage() === 'No cart found') {
            $response['message'] = 'Cart is already empty';
            $response['data'] = [
                'items' => [],
                'total' => 0
            ];
        } else {
            throw $e;
        }
    }
}

/**
 * Get cart items
 */
function getCartItems($conn, $order_id) {
    // Query to get all items in cart with menu item details
    $query = "SELECT oi.*, mi.name, mi.description, mi.category, mi.image_url 
              FROM order_items oi
              JOIN menu_items mi ON oi.menu_id = mi.id
              WHERE oi.order_id = ?";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error, 500);
    }
    
    $stmt->bind_param('i', $order_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to get cart items: ' . $stmt->error, 500);
    }
    
    $result = $stmt->get_result();
    $items = [];
    $total = 0;
    
    while ($row = $result->fetch_assoc()) {
        $item_total = $row['quantity'] * $row['price_at_order'];
        $total += $item_total;
        
        $items[] = [
            'id' => $row['id'],
            'menu_id' => $row['menu_id'],
            'quantity' => $row['quantity'],
            'price' => $row['price_at_order'],
            'name' => $row['name'],
            'description' => $row['description'],
            'category' => $row['category'],
            'image_url' => $row['image_url'],
            'item_total' => $item_total
        ];
    }
    
    // Get order details
    $order_query = "SELECT * FROM orders WHERE id = ?";
    $stmt = $conn->prepare($order_query);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error, 500);
    }
    
    $stmt->bind_param('i', $order_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to get order details: ' . $stmt->error, 500);
    }
    
    $order_result = $stmt->get_result();
    $order = $order_result->fetch_assoc();
    
    return [
        'order_id' => $order_id,
        'status' => $order['status'],
        'total' => $total,
        'items' => $items
    ];
}

/**
 * Update order total based on order items
 */
function updateOrderTotal($conn, $order_id) {
    // Calculate total from order items
    $total_query = "SELECT SUM(quantity * price_at_order) AS total FROM order_items WHERE order_id = ?";
    $stmt = $conn->prepare($total_query);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error, 500);
    }
    
    $stmt->bind_param('i', $order_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to calculate total: ' . $stmt->error, 500);
    }
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total = $row['total'] ? $row['total'] : 0;
    
    // Update order with total
    $update_query = "UPDATE orders SET total = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error, 500);
    }
    
    $stmt->bind_param('di', $total, $order_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update order total: ' . $stmt->error, 500);
    }
    
    return $total;
}
?>