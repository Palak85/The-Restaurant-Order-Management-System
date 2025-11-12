<?php
// Include database configuration
require_once 'config.php';

// Set response to JSON
header('Content-Type: application/json');

// Get date and time parameters if provided for filtering available tables
$date = isset($_GET['date']) ? $conn->real_escape_string($_GET['date']) : date('Y-m-d');
$time = isset($_GET['time']) ? $conn->real_escape_string($_GET['time']) : date('H:i:s');
$guests = isset($_GET['guests']) ? intval($_GET['guests']) : 1;

// Query to get all tables with their reservation status
// Only count reservations that are confirmed (not cancelled, completed, or no-show)
$query = "SELECT t.id, t.table_number, t.capacity, t.is_available,
         (SELECT COUNT(*) FROM reservations r 
          WHERE r.table_id = t.id 
          AND r.reservation_date = '$date' 
          AND r.status = 'confirmed') AS has_reservation
         FROM restaurant_tables t
         ORDER BY t.table_number";

$result = $conn->query($query);

if (!$result) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching tables: ' . $conn->error
    ]);
    exit;
}

$tables = [];
while ($row = $result->fetch_assoc()) {
    // Determine if the table is available based on is_available flag and reservations
    $isAvailable = ($row['is_available'] == 1 && $row['has_reservation'] == 0);
    
    $tables[] = [
        'id' => $row['id'],
        'table_number' => $row['table_number'],
        'capacity' => $row['capacity'],
        'is_available' => $isAvailable
    ];
}

echo json_encode([
    'status' => 'success',
    'tables' => $tables
]);
?>