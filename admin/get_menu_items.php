<?php
// Include admin authentication check
require_once 'check_admin.php';

// Include database configuration
require_once '../config.php';

// Set response type to JSON
header('Content-Type: application/json');

// Get menu items from the database
$query = "SELECT * FROM menu_items ORDER BY category, name";
$result = $conn->query($query);

if (!$result) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch menu items: ' . $conn->error
    ]);
    exit;
}

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'description' => $row['description'],
        'price' => $row['price'],
        'category' => $row['category'],
        'image' => $row['image_url']
    ];
}

echo json_encode([
    'status' => 'success',
    'items' => $items
]);
?>