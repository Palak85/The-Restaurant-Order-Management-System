<?php
// Include database configuration
require_once '../config.php';

// Check if admin is logged in
require_once 'check_admin.php';

// Set header to JSON
header('Content-Type: application/json');

try {
    // Check if reservation ID is provided
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception("Invalid reservation ID", 400);
    }
    
    $reservation_id = intval($_GET['id']);
    
    // Query to get reservation details with user and table information
    $query = "
        SELECT 
            r.id, 
            r.user_id, 
            r.table_id, 
            r.reservation_date, 
            r.reservation_time, 
            r.guests, 
            r.status, 
            r.special_requests, 
            r.created_at,
            u.name AS user_name, 
            u.email AS user_email,
            t.table_number,
            t.capacity
        FROM 
            reservations r
        LEFT JOIN 
            users u ON r.user_id = u.id
        LEFT JOIN 
            restaurant_tables t ON r.table_id = t.id
        WHERE 
            r.id = $reservation_id
    ";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Database query error: " . $conn->error);
    }
    
    if ($result->num_rows === 0) {
        throw new Exception("Reservation not found", 404);
    }
    
    $row = $result->fetch_assoc();
    
    // Format date and time for display
    $date = new DateTime($row['reservation_date']);
    $formattedDate = $date->format('M j, Y'); // Apr 17, 2025
    
    $time = new DateTime($row['reservation_time']);
    $formattedTime = $time->format('g:i A'); // 7:30 PM
    
    $reservation = [
        'id' => $row['id'],
        'user_id' => $row['user_id'],
        'user_name' => $row['user_name'] ?? 'Guest',
        'user_email' => $row['user_email'] ?? 'No email provided',
        'table_id' => $row['table_id'],
        'table_number' => $row['table_number'] ?? 'Unknown table',
        'table_capacity' => $row['capacity'] ?? 0,
        'date' => $formattedDate,
        'time' => $formattedTime,
        'original_date' => $row['reservation_date'],
        'original_time' => $row['reservation_time'],
        'guests' => $row['guests'],
        'status' => $row['status'] ?? 'confirmed',
        'special_requests' => $row['special_requests'] ?? '',
        'created_at' => $row['created_at']
    ];
    
    // Return reservation as JSON
    echo json_encode([
        'status' => 'success',
        'reservation' => $reservation
    ]);
    
} catch (Exception $e) {
    // Return error message
    http_response_code($e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>