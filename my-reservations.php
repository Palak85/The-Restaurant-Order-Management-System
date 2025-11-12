<?php
session_start();
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

$user_id = $_SESSION['user_id'];

// Debug information
echo "<!-- Debug: User ID: " . $user_id . " -->";

// Handle cancellation
if (isset($_POST['cancel_reservation'])) {
    $reservation_id = $_POST['reservation_id'];
    $update_query = "UPDATE reservations SET status = 'cancelled' WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ii", $reservation_id, $user_id);
    $stmt->execute();
}

// Fetch reservations for the current user
$query = "SELECT r.*, rt.table_number, rt.capacity 
          FROM reservations r
          LEFT JOIN restaurant_tables rt ON r.table_id = rt.id
          WHERE r.user_id = ?
          ORDER BY r.reservation_date DESC, r.reservation_time DESC";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo "<!-- Debug: Prepare failed: " . $conn->error . " -->";
}

$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    echo "<!-- Debug: Execute failed: " . $stmt->error . " -->";
}

$result = $stmt->get_result();

// Debug information
echo "<!-- Debug: Number of reservations found: " . $result->num_rows . " -->";

// Check if there are any reservations
if ($result->num_rows > 0) {
    // Debug: Print first reservation
    $first_reservation = $result->fetch_assoc();
    echo "<!-- Debug: First reservation: " . print_r($first_reservation, true) . " -->";
    // Reset the result pointer
    $result->data_seek(0);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reservations - Akay Cafe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css?family=Cabin|Herr+Von+Muellerhoff|Source+Sans+Pro" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css">
    <link rel="stylesheet" href="main.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navigation Bar -->
    <nav class="bg-gray-800 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.html" class="text-3xl font-bold">Akay Cafe</a>
            <div class="space-x-4">
                <a href="index.html" class="hover:text-gray-300">Home</a>
                <a href="menu.html" class="hover:text-gray-300">Menu</a>
                <a href="reservation.html" class="hover:text-gray-300">Reservations</a>
                <a href="contact.html" class="hover:text-gray-300">Contact</a>
                <a href="logout.php" class="hover:text-gray-300">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8 flex-grow">
        <h1 class="text-3xl font-bold mb-8 text-center text-slate-700">My Reservations</h1>
        
        <!-- Debug Information -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="text-center mb-4">
                <p class="text-gray-600">Logged in as User ID: <?php echo $_SESSION['user_id']; ?></p>
            </div>
        <?php endif; ?>
        
        <!-- Reservations List -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <?php if ($result->num_rows > 0): ?>
                <div class="space-y-6">
                    <?php while ($reservation = $result->fetch_assoc()): ?>
                        <div class="border-b pb-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-semibold">Reservation #<?php echo $reservation['id']; ?></h3>
                                    <p class="text-gray-600">Date: <?php echo date('F j, Y', strtotime($reservation['reservation_date'])); ?></p>
                                    <p class="text-gray-600">Time: <?php echo date('g:i A', strtotime($reservation['reservation_time'])); ?></p>
                                    <p class="text-gray-600">Table: <?php echo $reservation['table_number']; ?> (Capacity: <?php echo $reservation['capacity']; ?>)</p>
                                    <p class="text-gray-600">Guests: <?php echo $reservation['guests']; ?></p>
                                    <?php if (!empty($reservation['special_requests'])): ?>
                                        <p class="text-gray-600 mt-2">
                                            <span class="font-semibold">Special Requests:</span><br>
                                            <?php echo htmlspecialchars($reservation['special_requests']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <span class="px-3 py-1 rounded-full text-sm font-semibold 
                                    <?php
                                    switch($reservation['status']) {
                                        case 'confirmed':
                                            echo 'bg-blue-100 text-blue-800';
                                            break;
                                        case 'completed':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'cancelled':
                                            echo 'bg-red-100 text-red-800';
                                            break;
                                        default:
                                            echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php echo ucfirst($reservation['status']); ?>
                                </span>
                            </div>
                            <?php if ($reservation['status'] == 'confirmed'): ?>
                                <div class="mt-4">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                        <button type="submit" name="cancel_reservation" 
                                                class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition-colors"
                                                onclick="return confirm('Are you sure you want to cancel this reservation?')">
                                            Cancel Reservation
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <p class="text-gray-600 text-lg">You haven't made any reservations yet.</p>
                    <a href="reservation.html" class="mt-4 inline-block bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 transition-colors">
                        Make a Reservation
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-auto py-12">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; 2024 Akay Cafe. All rights reserved.</p>
        </div>
    </footer>
</body>
</html> 