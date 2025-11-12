<?php
// Include database configuration
require_once 'config.php';

// Set response to JSON
header('Content-Type: application/json');

// Query to get all available menu items with category information
// Updated to match your actual database schema (menu_items table)
$query = "SELECT id, name, description, price, category, image_url as image 
          FROM menu_items 
          ORDER BY category, name";

$result = $conn->query($query);

if (!$result) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching menu items: ' . $conn->error
    ]);
    exit;
}

$menu_items = [];
while ($row = $result->fetch_assoc()) {
    // If category is null, assign a default category
    if (!$row['category']) {
        $row['category'] = 'Other';
    }
    
    $menu_items[] = $row;
}

echo json_encode([
    'status' => 'success',
    'menu_items' => $menu_items
]);
?>