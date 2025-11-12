<?php
// Include admin authentication check
require_once 'check_admin.php';
require_once '../config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Sample recent activity data
// In a real application, you would query your database for actual activity
$activities = [
    [
        'type' => 'Order',
        'description' => 'New order #1024 placed',
        'time' => '10 minutes ago'
    ],
    [
        'type' => 'Reservation',
        'description' => 'Table #5 reserved for tonight',
        'time' => '30 minutes ago'
    ],
    [
        'type' => 'Menu',
        'description' => 'New menu item "Spicy Chicken Burger" added',
        'time' => '1 hour ago'
    ],
    [
        'type' => 'User',
        'description' => 'New user registration: john.doe@example.com',
        'time' => '3 hours ago'
    ],
    [
        'type' => 'System',
        'description' => 'Daily backup completed successfully',
        'time' => '6 hours ago'
    ]
];

// Return the activities as JSON
echo json_encode(['status' => 'success', 'activities' => $activities]);
?>