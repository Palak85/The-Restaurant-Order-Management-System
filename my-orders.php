<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch orders for the current user
$query = "SELECT o.*, GROUP_CONCAT(CONCAT(mi.name, ' x', oi.quantity, ' ($', oi.price, ')') SEPARATOR ', ') as items
          FROM orders o
          LEFT JOIN order_items oi ON o.id = oi.order_id
          LEFT JOIN menu_items mi ON oi.menu_id = mi.id
          WHERE o.user_id = ?
          GROUP BY o.id
          ORDER BY o.id DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Akay Cafe</title>
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
        <h1 class="text-3xl font-bold mb-8 text-center text-slate-700">My Orders</h1>
        
        <!-- Orders List -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <?php if ($result->num_rows > 0): ?>
                <div class="space-y-6">
                    <?php while ($order = $result->fetch_assoc()): ?>
                        <div class="border-b pb-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-semibold">Order #<?php echo $order['id']; ?></h3>
                                    <p class="text-gray-600">Placed on: <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-sm font-semibold 
                                    <?php
                                    switch($order['status']) {
                                        case 'completed':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'ordered':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'canceled':
                                            echo 'bg-red-100 text-red-800';
                                            break;
                                        default:
                                            echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                            <div class="space-y-2">
                                <?php
                                $items = explode(', ', $order['items']);
                                foreach ($items as $item) {
                                    echo "<div class='flex justify-between'><span>$item</span></div>";
                                }
                                ?>
                            </div>
                            <div class="mt-4 pt-4 border-t">
                                <div class="flex justify-between font-semibold">
                                    <span>Total</span>
                                    <span>$<?php echo number_format($order['total'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <p class="text-gray-600 text-lg">You haven't placed any orders yet.</p>
                    <a href="menu.html" class="mt-4 inline-block bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 transition-colors">
                        Browse Menu
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