<?php
// Include database configuration
require_once '../config.php';

// Check if admin is logged in
require_once 'check_admin.php';

// Set header to JSON
header('Content-Type: application/json');

try {
    // Get current date and time
    $currentDate = date('Y-m-d');
    $currentTime = date('H:i:s');
    
    // Query to get all upcoming reservations with user and table information
    // Shows reservations that are today with time in the future or future dates
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
            (r.reservation_date > '$currentDate') OR 
            (r.reservation_date = '$currentDate' AND r.reservation_time > '$currentTime')
        ORDER BY 
            r.reservation_date ASC, r.reservation_time ASC
    ";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Database query error: " . $conn->error);
    }
    
    $reservations = [];
    
    while ($row = $result->fetch_assoc()) {
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
        
        $reservations[] = $reservation;
    }
    
    // Return reservations as JSON
    echo json_encode([
        'status' => 'success',
        'reservations' => $reservations
    ]);
    
} catch (Exception $e) {
    // Return error message
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>