<?php
session_start();
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'reservations' => []
];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Please log in to view your reservations';
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch reservations for the current user
    $query = "SELECT r.*, rt.table_number, rt.capacity 
              FROM reservations r
              LEFT JOIN restaurant_tables rt ON r.table_id = rt.id
              WHERE r.user_id = ?
              ORDER BY r.reservation_date DESC, r.reservation_time DESC";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to fetch reservations: " . $stmt->error);
    }

    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($reservation = $result->fetch_assoc()) {
            $response['reservations'][] = $reservation;
        }
        $response['success'] = true;
    } else {
        $response['success'] = true;
        $response['message'] = 'No reservations found';
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 