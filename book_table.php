<?php
// Turn off PHP error output to prevent HTML in JSON response
ini_set('display_errors', 1); // Temporarily enable display errors for debugging
error_reporting(E_ALL);

// Set up error logging to a file
ini_set('log_errors', 1);
ini_set('error_log', 'c:/xampp/htdocs/rrr/reservation_errors.log');
error_log("Reservation attempt started: " . date('Y-m-d H:i:s'));

// Start output buffering to catch any unexpected output
ob_start();

try {
    // Include database configuration with absolute path
    $config_path = __DIR__ . '/config.php';
    if (!file_exists($config_path)) {
        throw new Exception("Config file not found at: " . $config_path);
    }
    require_once $config_path;
    
    error_log("Database connection established");
    
    // Start session to get user information
    session_start();
    
    // Log session data for debugging
    error_log("Session data: " . print_r($_SESSION, true));
    error_log("POST data: " . print_r($_POST, true));
    
    // Set response to JSON
    header('Content-Type: application/json');
    
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Please login to make a reservation', 401);
    }
    
    // Get user ID from session
    $user_id = $_SESSION['user_id'];
    
    // Get name and email from session
    if (!isset($_SESSION['user_name']) || !isset($_SESSION['user_email'])) {
        throw new Exception('User information not found in session', 400);
    }
    
    $name = $conn->real_escape_string($_SESSION['user_name']);
    $email = $conn->real_escape_string($_SESSION['user_email']);
    
    // Get and validate booking information
    $date = isset($_POST['date']) ? $conn->real_escape_string($_POST['date']) : '';
    $time = isset($_POST['time']) ? $conn->real_escape_string($_POST['time']) : '';
    $guests = isset($_POST['guests']) ? intval($_POST['guests']) : 0;
    $requests = isset($_POST['requests']) ? $conn->real_escape_string($_POST['requests']) : '';
    
    error_log("Reservation data: date=$date, time=$time, guests=$guests");
    
    // Validate input
    if (empty($date) || empty($time) || $guests <= 0) {
        throw new Exception('All fields are required', 400);
    }
    
    // Validate date (must be current or future date)
    $currentDate = date('Y-m-d');
    if ($date < $currentDate) {
        throw new Exception('Please select a current or future date', 400);
    }
    
    // --- RESERVATION PROCESSING ---
    // Ensure the tables exist
    $tables_exist = $conn->query("SHOW TABLES LIKE 'restaurant_tables'")->num_rows > 0;
    $reservations_exist = $conn->query("SHOW TABLES LIKE 'reservations'")->num_rows > 0;
    
    if (!$tables_exist || !$reservations_exist) {
        // Create tables if they don't exist
        if (!$tables_exist) {
            $conn->query("CREATE TABLE IF NOT EXISTS `restaurant_tables` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(50) NOT NULL,
                `capacity` int(11) NOT NULL,
                `is_available` tinyint(1) NOT NULL DEFAULT 1,
                `location` varchar(50) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            
            // Insert some default tables
            $conn->query("INSERT INTO `restaurant_tables` (`name`, `capacity`, `is_available`, `location`) VALUES
                ('Table 1', 2, 1, 'Window'),
                ('Table 2', 2, 1, 'Window'),
                ('Table 3', 4, 1, 'Center'),
                ('Table 4', 4, 1, 'Center'),
                ('Table 5', 6, 1, 'Corner'),
                ('Table 6', 8, 1, 'Private Room');");
        }
        
        if (!$reservations_exist) {
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
        }
    }
    
    // Check if a table exists that can accommodate the group
    $tableQuery = "SELECT id, capacity FROM restaurant_tables WHERE capacity >= $guests AND is_available = 1 ORDER BY capacity ASC LIMIT 1";
    error_log("Executing query: $tableQuery");
    $tableResult = $conn->query($tableQuery);
    
    if (!$tableResult) {
        error_log("Database error: " . $conn->error);
        throw new Exception('Database error: ' . $conn->error, 500);
    }
    
    if ($tableResult->num_rows === 0) {
        // No table available for this number of guests
        error_log("No table available for $guests guests");
        throw new Exception('Sorry, we don\'t have a table available for ' . $guests . ' guests. Please try a different time or contact us directly.', 404);
    }
    
    $table = $tableResult->fetch_assoc();
    $table_id = $table['id'];
    error_log("Found table ID: $table_id with capacity: " . $table['capacity']);
    
    // Check if table is already booked for the requested time
    $bookingCheckQuery = "SELECT id FROM reservations WHERE table_id = $table_id AND reservation_date = '$date' AND reservation_time = '$time'";
    error_log("Checking existing reservations: $bookingCheckQuery");
    $bookingCheckResult = $conn->query($bookingCheckQuery);
    
    if (!$bookingCheckResult) {
        error_log("Database error checking reservations: " . $conn->error);
        throw new Exception('Database error checking reservations: ' . $conn->error, 500);
    }
    
    if ($bookingCheckResult->num_rows > 0) {
        error_log("Timeslot already booked");
        throw new Exception('Sorry, this time slot is already booked. Please select another time.', 409);
    }
    
    // Create reservation
    $reservationQuery = "INSERT INTO reservations (user_id, table_id, reservation_date, reservation_time, guests, special_requests) 
                      VALUES ($user_id, $table_id, '$date', '$time', $guests, '$requests')";
    error_log("Creating reservation: $reservationQuery");
    
    // Start a transaction to ensure both operations complete
    $conn->begin_transaction();
    
    try {
        // Insert the reservation
        if (!$conn->query($reservationQuery)) {
            throw new Exception("Error creating reservation: " . $conn->error);
        }
        $reservation_id = $conn->insert_id;
        
        // Update table availability
        $updateTableQuery = "UPDATE restaurant_tables SET is_available = 0 WHERE id = $table_id";
        error_log("Updating table availability: $updateTableQuery");
        if (!$conn->query($updateTableQuery)) {
            throw new Exception("Error updating table availability: " . $conn->error);
        }
        
        // If both operations succeed, commit the transaction
        $conn->commit();
        
        error_log("Reservation created successfully with ID: $reservation_id");
        $response = [
            'status' => 'success',
            'message' => 'Your table has been reserved successfully! Confirmation #' . $reservation_id,
            'reservation' => [
                'id' => $reservation_id,
                'date' => $date,
                'time' => $time,
                'guests' => $guests,
                'table_id' => $table_id
            ]
        ];
    } catch (Exception $e) {
        // If an error occurs, roll back the transaction
        $conn->rollback();
        error_log("Transaction rolled back: " . $e->getMessage());
        throw new Exception('Error booking table: ' . $e->getMessage(), 500);
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
