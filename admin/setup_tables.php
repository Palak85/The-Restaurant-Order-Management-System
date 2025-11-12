<?php
// Turn off PHP error output to prevent HTML in JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set up error logging to a file
ini_set('log_errors', 1);
ini_set('error_log', 'c:/xampp/htdocs/rrr/admin_errors.log');
error_log("Table setup started: " . date('Y-m-d H:i:s'));

// Include database configuration
require_once '../config.php';

header('Content-Type: application/json');

try {
    // Check if 'restaurant_tables' table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'restaurant_tables'");
    
    if ($tableCheck->num_rows == 0) {
        // Create table if it doesn't exist
        $conn->query("CREATE TABLE IF NOT EXISTS `restaurant_tables` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(50) NOT NULL,
            `capacity` int(11) NOT NULL,
            `is_available` tinyint(1) NOT NULL DEFAULT 1,
            `location` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        
        // Insert some default tables if none exist
        $conn->query("INSERT INTO `restaurant_tables` (`name`, `capacity`, `is_available`, `location`) VALUES
            ('Table 1', 2, 1, 'Window'),
            ('Table 2', 2, 1, 'Window'),
            ('Table 3', 4, 1, 'Center'),
            ('Table 4', 4, 1, 'Center'),
            ('Table 5', 6, 1, 'Corner'),
            ('Table 6', 8, 1, 'Private Room');");
            
        error_log("Created restaurant_tables table and added default tables");
    }
    
    // Check if 'reservations' table exists
    $reservationCheck = $conn->query("SHOW TABLES LIKE 'reservations'");
    
    if ($reservationCheck->num_rows == 0) {
        // Create table if it doesn't exist
        $conn->query("CREATE TABLE IF NOT EXISTS `reservations` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `table_id` int(11) NOT NULL,
            `reservation_date` date NOT NULL,
            `reservation_time` time NOT NULL,
            `guests` int(11) NOT NULL,
            `special_requests` text DEFAULT NULL,
            `status` enum('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        
        error_log("Created reservations table");
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Tables set up successfully'
    ]);
} catch (Exception $e) {
    error_log("Error setting up tables: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Error setting up tables: ' . $e->getMessage()
    ]);
}
?>