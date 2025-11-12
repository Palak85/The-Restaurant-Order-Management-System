<?php
// Include admin authentication check
require_once 'check_admin.php';

// Include database configuration
require_once '../config.php';

// Set response type to JSON
header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Get and sanitize input
$name = isset($_POST['name']) ? $conn->real_escape_string($_POST['name']) : '';
$description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : '';
$price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
$category = isset($_POST['category']) ? $conn->real_escape_string($_POST['category']) : '';

// Validate required fields
if (empty($name) || empty($category) || $price <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Name, category, and valid price are required']);
    exit;
}

// Handle image upload if present
$image_url = '';
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    // Check file type
    if (!in_array($_FILES['image']['type'], $allowed_types)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed']);
        exit;
    }
    
    // Check file size
    if ($_FILES['image']['size'] > $max_size) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'File size too large. Maximum size is 5MB']);
        exit;
    }
    
    $upload_dir = '../uploads/menu/';
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error', 
                'message' => 'Failed to create upload directory. Please check permissions.',
                'debug_info' => [
                    'upload_dir' => $upload_dir,
                    'parent_dir_exists' => file_exists(dirname($upload_dir)),
                    'parent_dir_writable' => is_writable(dirname($upload_dir)),
                    'parent_dir_permissions' => substr(sprintf('%o', fileperms(dirname($upload_dir))), -4)
                ]
            ]);
            exit;
        }
        // Set directory permissions after creation
        chmod($upload_dir, 0777);
    }
    
    // Check if directory is writable
    if (!is_writable($upload_dir)) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Upload directory is not writable. Please check permissions.',
            'debug_info' => [
                'upload_dir' => $upload_dir,
                'exists' => file_exists($upload_dir),
                'is_dir' => is_dir($upload_dir),
                'is_writable' => is_writable($upload_dir),
                'permissions' => substr(sprintf('%o', fileperms($upload_dir)), -4),
                'owner' => posix_getpwuid(fileowner($upload_dir))['name'],
                'group' => posix_getgrgid(filegroup($upload_dir))['name']
            ]
        ]);
        exit;
    }
    
    // Generate unique filename
    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $file_name = uniqid() . '_' . time() . '.' . $file_extension;
    $destination = $upload_dir . $file_name;
    
    // Try to move the uploaded file
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
        $error = error_get_last();
        $error_message = isset($error['message']) ? $error['message'] : 'Unknown error';
        http_response_code(500);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Failed to upload image. Error: ' . $error_message,
            'debug_info' => [
                'tmp_name' => $_FILES['image']['tmp_name'],
                'destination' => $destination,
                'upload_dir' => $upload_dir,
                'is_writable' => is_writable($upload_dir),
                'permissions' => substr(sprintf('%o', fileperms($upload_dir)), -4),
                'owner' => posix_getpwuid(fileowner($upload_dir))['name'],
                'group' => posix_getgrgid(filegroup($upload_dir))['name']
            ]
        ]);
        exit;
    }
    
    $image_url = 'uploads/menu/' . $file_name;
}

// Insert menu item into database
$query = "INSERT INTO menu_items (name, description, price, category, image_url) 
          VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query);
$stmt->bind_param("ssdss", $name, $description, $price, $category, $image_url);

if ($stmt->execute()) {
    $item_id = $conn->insert_id;
    echo json_encode([
        'status' => 'success',
        'message' => 'Menu item added successfully',
        'item' => [
            'id' => $item_id,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'category' => $category,
            'image' => $image_url
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to add menu item: ' . $stmt->error]);
}

$stmt->close();
?>