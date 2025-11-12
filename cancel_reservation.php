<?php
session_start();
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Please log in to cancel reservations';
    echo json_encode($response);
    exit;
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);
$reservation_id = isset($data['reservation_id']) ? $data['reservation_id'] : null;

if (!$reservation_id) {
    $response['message'] = 'Invalid reservation ID';
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Update reservation status
    $update_query = "UPDATE reservations SET status = 'cancelled' WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($update_query);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $stmt->bind_param("ii", $reservation_id, $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to cancel reservation: " . $stmt->error);
    }

    if ($stmt->affected_rows > 0) {
        $response['success'] = true;
        $response['message'] = 'Reservation cancelled successfully';
    } else {
        $response['message'] = 'Reservation not found or already cancelled';
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 