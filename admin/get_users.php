<?php
require_once __DIR__ . '/../config.php';

// Check if user is admin
require_once __DIR__ . '/check_admin.php';

// Query to get users
$query = "SELECT id, name, email, created_at AS registered_date FROM users ORDER BY id DESC";
$result = $conn->query($query);

if (!$result) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch users: ' . $conn->error
    ]);
    exit;
}

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

header('Content-Type: application/json');
echo json_encode(['users' => $users]);
