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

// Get menu item ID
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid menu item ID']);
    exit;
}

// Get image URL before deletion to remove the file
$query = "SELECT image_url FROM menu_items WHERE id = $id";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $image_url = $row['image_url'];
    
    try {
        // Temporarily disable foreign key checks
        $conn->query('SET foreign_key_checks = 0');
        
        // Delete menu item from database
        $deleteQuery = "DELETE FROM menu_items WHERE id = $id";
        
        if ($conn->query($deleteQuery) === TRUE) {
            // Remove image file if it exists
            if (!empty($image_url) && file_exists("../$image_url")) {
                unlink("../$image_url");
            }
            
            // Re-enable foreign key checks
            $conn->query('SET foreign_key_checks = 1');
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Menu item deleted successfully'
            ]);
        } else {
            // Re-enable foreign key checks
            $conn->query('SET foreign_key_checks = 1');
            
            http_response_code(500);
            echo json_encode([
                'status' => 'error', 
                'message' => 'Failed to delete menu item: ' . $conn->error
            ]);
        }
    } catch (Exception $e) {
        // Re-enable foreign key checks in case of exception
        $conn->query('SET foreign_key_checks = 1');
        
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error deleting menu item: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(404);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Menu item not found'
    ]);
}
?>