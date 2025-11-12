<?php
// Include admin authentication check
require_once 'check_admin.php';

// Include database configuration
require_once '../config.php';

// Set response type to JSON
header('Content-Type: application/json');

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Check if all required fields are set
if (!isset($_POST['id']) || !is_numeric($_POST['id']) || 
    !isset($_POST['name']) || empty(trim($_POST['name'])) ||
    !isset($_POST['price']) || !is_numeric($_POST['price']) ||
    !isset($_POST['category']) || empty(trim($_POST['category']))) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required fields'
    ]);
    exit;
}

// Get and sanitize input
$id = intval($_POST['id']);
$name = $conn->real_escape_string(trim($_POST['name']));
$description = isset($_POST['description']) ? $conn->real_escape_string(trim($_POST['description'])) : '';
$price = floatval($_POST['price']);
$category = $conn->real_escape_string(trim($_POST['category']));

// Verify menu item exists
$checkQuery = "SELECT image_url FROM menu_items WHERE id = $id";
$checkResult = $conn->query($checkQuery);

if (!$checkResult || $checkResult->num_rows === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Menu item not found'
    ]);
    exit;
}

$currentItem = $checkResult->fetch_assoc();
$currentImage = $currentItem['image_url'];
$image_url = $currentImage; // Default to current image if no new one is uploaded

// Handle image upload if a new image is provided
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../uploads/menu/';
    
    // Create the upload directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generate a unique filename
    $file_name = time() . '_' . basename($_FILES['image']['name']);
    $upload_path = $upload_dir . $file_name;
    
    // Move the uploaded file
    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
        $image_url = 'uploads/menu/' . $file_name;
        
        // Delete the old image file if it exists and is not the default
        if ($currentImage && $currentImage !== 'img1.jpg' && $currentImage !== 'img2.jpg' && $currentImage !== 'img3.jpg') {
            $old_image_path = '../' . $currentImage;
            if (file_exists($old_image_path)) {
                unlink($old_image_path);
            }
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to upload image'
        ]);
        exit;
    }
}

// Update the menu item
$updateQuery = "UPDATE menu_items 
                SET name = '$name', 
                    description = '$description', 
                    price = $price, 
                    category = '$category', 
                    image_url = '$image_url' 
                WHERE id = $id";

if ($conn->query($updateQuery)) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Menu item updated successfully',
        'item_id' => $id
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error updating menu item: ' . $conn->error
    ]);
}
?>