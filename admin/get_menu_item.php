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
        'message' => 'Invalid menu item ID'
    ]);
    exit;
}

$id = intval($_GET['id']);

// Get menu item from the database
$query = "SELECT * FROM menu_items WHERE id = $id";
$result = $conn->query($query);

if (!$result) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch menu item: ' . $conn->error
    ]);
    exit;
}

if ($result->num_rows === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Menu item not found'
    ]);
    exit;
}

$item = $result->fetch_assoc();

echo json_encode([
    'status' => 'success',
    'item' => [
        'id' => $item['id'],
        'name' => $item['name'],
        'description' => $item['description'],
        'price' => $item['price'],
        'category' => $item['category'],
        'image' => $item['image_url']
    ]
]);
?>