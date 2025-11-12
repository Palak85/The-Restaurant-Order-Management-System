<?php
// Include admin authentication check
require_once 'check_admin.php';
require_once '../config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Initialize dashboard data
$dashboardData = [
    'status' => 'success',
    'menu_count' => 0,
    'order_count' => 0,
    'reservation_count' => 0,
    'user_count' => 0
];

// Get menu count
$menuQuery = "SELECT COUNT(*) as count FROM menu_items";
$menuResult = $conn->query($menuQuery);
if ($menuResult && $menuRow = $menuResult->fetch_assoc()) {
    $dashboardData['menu_count'] = $menuRow['count'];
}

// Get order count - assuming you have an orders table
// If you don't have this table yet, it will be 0
$orderQuery = "SHOW TABLES LIKE 'orders'";
$orderTableExists = $conn->query($orderQuery);
if ($orderTableExists && $orderTableExists->num_rows > 0) {
    $orderCountQuery = "SELECT COUNT(*) as count FROM orders";
    $orderResult = $conn->query($orderCountQuery);
    if ($orderResult && $orderRow = $orderResult->fetch_assoc()) {
        $dashboardData['order_count'] = $orderRow['count'];
    }
}

// Get reservation count - assuming you have a bookings table
// If you don't have this table yet, it will be 0
$reservationQuery = "SHOW TABLES LIKE 'bookings'";
$reservationTableExists = $conn->query($reservationQuery);
if ($reservationTableExists && $reservationTableExists->num_rows > 0) {
    $reservationCountQuery = "SELECT COUNT(*) as count FROM bookings";
    $reservationResult = $conn->query($reservationCountQuery);
    if ($reservationResult && $reservationRow = $reservationResult->fetch_assoc()) {
        $dashboardData['reservation_count'] = $reservationRow['count'];
    }
}

// Get user count - assuming you have a users table
// If you don't have this table yet, it will be 0
$userQuery = "SHOW TABLES LIKE 'users'";
$userTableExists = $conn->query($userQuery);
if ($userTableExists && $userTableExists->num_rows > 0) {
    $userCountQuery = "SELECT COUNT(*) as count FROM users";
    $userResult = $conn->query($userCountQuery);
    if ($userResult && $userRow = $userResult->fetch_assoc()) {
        $dashboardData['user_count'] = $userRow['count'];
    }
}

// Return the dashboard data as JSON
echo json_encode($dashboardData);
?>