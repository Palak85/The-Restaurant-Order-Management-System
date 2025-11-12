<?php
// Include admin authentication check
require_once 'check_admin.php';

// Get admin username
$admin_username = $_SESSION['admin_username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Akay Cafe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: #333;
            color: #fff;
            padding: 20px 0;
        }
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid #444;
            text-align: center;
        }
        .sidebar-header h2 {
            margin: 0;
            color: #a96700;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }
        .sidebar li {
            padding: 10px 20px;
            border-bottom: 1px solid #444;
        }
        .sidebar li.active {
            background-color: #a96700;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            display: block;
        }
        .main-content {
            flex: 1;
            padding: 20px;
        }
        .header {
            background-color: #fff;
            padding: 15px 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .user-info {
            display: flex;
            align-items: center;
        }
        .user-info span {
            margin-right: 15px;
        }
        .logout-btn {
            background-color: #a96700;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 3px;
            cursor: pointer;
        }
        .logout-btn:hover {
            background-color: #8a5300;
        }
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .card {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            text-align: center;
        }
        .card h3 {
            margin-top: 0;
            color: #333;
        }
        .card p {
            font-size: 24px;
            font-weight: bold;
            color: #a96700;
        }
        .card-icon {
            font-size: 48px;
            color: #a96700;
        }
        iframe {
            display: none;
            width: 100%;
            height: calc(100vh - 100px);
            border: none;
        }
        iframe.active {
            display: block;
        }
        .content-area {
            margin-top: 20px;
            background-color: #fff;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .content-area h2 {
            margin-top: 0;
            color: #333;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Akay Cafe</h2>
                <p>Admin Panel</p>
            </div>
            <ul>
                <li class="active"><a href="#" data-section="dashboard">Dashboard</a></li>
                <li><a href="#" data-section="menu">Manage Menu</a></li>
                <li><a href="#" data-section="orders">Manage Orders</a></li>
                <li><a href="#" data-section="reservations">Reservations</a></li>
                <li><a href="#" data-section="tables">Manage Tables</a></li>
                <li><a href="#" data-section="users">User Management</a></li>
                <li><a href="#" data-section="settings">Settings</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header">
                <h1>Admin Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($admin_username); ?></span>
                    <button class="logout-btn" id="logoutBtn">Logout</button>
                </div>
            </div>
            
            <!-- Dashboard Section -->
            <div id="dashboard" class="content-section active">
                <div class="dashboard-cards">
                    <div class="card">
                        <div class="card-icon">📋</div>
                        <h3>Menu Items</h3>
                        <p id="menuCount">0</p>
                    </div>
                    <div class="card">
                        <div class="card-icon">🍽️</div>
                        <h3>Active Orders</h3>
                        <p id="orderCount">0</p>
                    </div>
                    <div class="card">
                        <div class="card-icon">📅</div>
                        <h3>Today's Reservations</h3>
                        <p id="reservationCount">0</p>
                    </div>
                    <div class="card">
                        <div class="card-icon">👥</div>
                        <h3>Registered Users</h3>
                        <p id="userCount">0</p>
                    </div>
                </div>
                <div class="content-area">
                    <h2>Recent Activity</h2>
                    <div id="recentActivity">
                        <p>Loading recent activity...</p>
                    </div>
                </div>
            </div>
            
            <!-- Menu Management Section -->
            <div id="menu" class="content-section" style="display:none;">
                <div class="content-area">
                    <h2>Menu Management</h2>
                    <button id="addMenuItemBtn" style="background-color: #a96700; color: white; border: none; padding: 8px 15px; border-radius: 3px; cursor: pointer; margin-bottom: 20px;">Add New Menu Item</button>
                    <div id="menuItemsList">
                        <p>Loading menu items...</p>
                    </div>
                </div>
            </div>
            
            <!-- Order Management Section -->
            <div id="orders" class="content-section" style="display:none;">
                <div class="content-area">
                    <h2>Order Management</h2>
                    <div id="ordersList">
                        <p>Loading orders...</p>
                    </div>
                </div>
            </div>
            
            <!-- Reservations Section -->
            <div id="reservations" class="content-section" style="display:none;">
                <div class="content-area">
                    <h2>Reservation Management</h2>
                    <div id="reservationsList">
                        <p>Loading reservations...</p>
                    </div>
                </div>
            </div>
            
            <!-- Users Section -->
            <div id="users" class="content-section" style="display:none;">
                <div class="content-area">
                    <h2>User Management</h2>
                    <div id="usersList">
                        <p>Loading users...</p>
                    </div>
                </div>
            </div>
            
            <!-- Settings Section -->
            <div id="settings" class="content-section" style="display:none;">
                <div class="content-area">
                    <h2>System Settings</h2>
                    <p>Coming soon...</p>
                </div>
            </div>

            <!-- Tables Section -->
            <div id="tables" class="content-section" style="display:none;">
                <div class="content-area">
                    <h2>Table Management</h2>
                    <button id="addTableBtn" style="background-color: #a96700; color: white; border: none; padding: 8px 15px; border-radius: 3px; cursor: pointer; margin-bottom: 20px;">Add New Table</button>
                    <div id="tablesList">
                        <p>Loading tables...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add/Edit Menu Modal -->
    <div id="addMenuModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
        <div class="modal-content" style="background-color: #fefefe; margin: 10% auto; padding: 20px; border: 1px solid #888; width: 60%; max-width: 700px; border-radius: 5px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            <span class="close" id="closeAddMenuModal" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
            <h2 id="menuModalTitle" style="color: #a96700; margin-top: 0;">Add New Menu Item</h2>
            
            <form id="addMenuForm" enctype="multipart/form-data">
                <input type="hidden" id="menuItemId" name="id" value="0">
                <input type="hidden" id="menuAction" name="action" value="add">
                
                <div style="margin-bottom: 15px;">
                    <label for="menuName" style="display: block; margin-bottom: 5px; font-weight: bold;">Name:</label>
                    <input type="text" id="menuName" name="name" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label for="menuDescription" style="display: block; margin-bottom: 5px; font-weight: bold;">Description:</label>
                    <textarea id="menuDescription" name="description" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box;"></textarea>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label for="menuPrice" style="display: block; margin-bottom: 5px; font-weight: bold;">Price ($):</label>
                    <input type="number" id="menuPrice" name="price" step="0.01" min="0" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label for="menuCategory" style="display: block; margin-bottom: 5px; font-weight: bold;">Category:</label>
                    <select id="menuCategory" name="category" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box;">
                        <option value="">-- Select Category --</option>
                        <option value="Starters">Starters</option>
                        <option value="Main Course">Main Course</option>
                        <option value="Desserts">Desserts</option>
                        <option value="Drinks">Drinks</option>
                        <option value="Specials">Specials</option>
                    </select>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label for="menuImage" style="display: block; margin-bottom: 5px; font-weight: bold;">Image:</label>
                    <input type="file" id="menuImage" name="image" accept="image/*" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box;">
                    <small style="color: #666; margin-top: 5px; display: block;">Recommended size: 400x300 pixels</small>
                    <div id="currentImageContainer" style="display:none; margin-top: 10px;">
                        <p>Current Image:</p>
                        <img id="currentImage" src="" alt="Current menu item image" style="max-width: 100px; max-height: 100px; object-fit: cover;">
                    </div>
                </div>
                
                <div style="margin-top: 20px;">
                    <button type="submit" id="menuSubmitButton" style="background-color: #a96700; color: white; padding: 10px 15px; border: none; border-radius: 3px; cursor: pointer;">Add Item</button>
                    <button type="button" id="cancelAddMenu" style="background-color: #ccc; color: #333; padding: 10px 15px; border: none; border-radius: 3px; margin-left: 10px; cursor: pointer;">Cancel</button>
                </div>
            </form>
            
            <div id="menuAddStatus" style="margin-top: 15px; padding: 10px; border-radius: 3px; display: none;"></div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Navigation
            const navLinks = document.querySelectorAll('.sidebar a');
            const contentSections = document.querySelectorAll('.content-section');
            
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Update active link
                    navLinks.forEach(link => link.parentElement.classList.remove('active'));
                    this.parentElement.classList.add('active');
                    
                    // Show selected section
                    const targetSection = this.getAttribute('data-section');
                    contentSections.forEach(section => {
                        section.style.display = section.id === targetSection ? 'block' : 'none';
                    });
                    
                    // Load content based on section
                    loadSectionContent(targetSection);
                });
            });
            
            // Logout button
            document.getElementById('logoutBtn').addEventListener('click', function() {
                fetch('admin_logout.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            window.location.href = 'login.html';
                        }
                    });
            });
            
            // Initial load of dashboard data
            loadDashboardData();
        });
        
        function loadDashboardData() {
            // Load counts for dashboard cards
            fetch('get_dashboard_data.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('menuCount').textContent = data.menu_count;
                    document.getElementById('orderCount').textContent = data.order_count;
                    document.getElementById('reservationCount').textContent = data.reservation_count;
                    document.getElementById('userCount').textContent = data.user_count;
                })
                .catch(error => {
                    console.error('Error loading dashboard data:', error);
                });
                
            // Load recent activity
            fetch('get_recent_activity.php')
                .then(response => response.json())
                .then(data => {
                    const activityList = document.getElementById('recentActivity');
                    if (data.activities && data.activities.length > 0) {
                        let html = '<ul style="list-style: none; padding: 0;">';
                        data.activities.forEach(activity => {
                            html += `<li style="padding: 10px; border-bottom: 1px solid #eee;">
                                <strong>${activity.type}</strong> - ${activity.description}
                                <span style="float: right; color: #888;">${activity.time}</span>
                            </li>`;
                        });
                        html += '</ul>';
                        activityList.innerHTML = html;
                    } else {
                        activityList.innerHTML = '<p>No recent activity found.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error loading recent activity:', error);
                    document.getElementById('recentActivity').innerHTML = '<p>Error loading activity data.</p>';
                });
        }
        
        function loadSectionContent(section) {
            switch (section) {
                case 'menu':
                    loadMenuItems();
                    break;
                case 'orders':
                    loadOrders();
                    break;
                case 'reservations':
                    loadReservations();
                    break;
                case 'users':
                    loadUsers();
                    break;
                case 'tables':
                    loadTables();
                    break;
            }
        }
        
        function loadMenuItems() {
            // Directly fetch menu items without running setup script first
            fetch('get_menu_items.php')
                .then(response => response.json())
                .then(data => {
                    const menuList = document.getElementById('menuItemsList');
                    if (data.items && data.items.length > 0) {
                        let html = '<table style="width:100%; border-collapse: collapse;">';
                        html += `<tr style="background-color: #f2f2f2;">
                            <th style="text-align: left; padding: 12px;">Image</th>
                            <th style="text-align: left; padding: 12px;">Name</th>
                            <th style="text-align: left; padding: 12px;">Category</th>
                            <th style="text-align: left; padding: 12px;">Price</th>
                            <th style="text-align: left; padding: 12px;">Actions</th>
                        </tr>`;
                        
                        data.items.forEach(item => {
                            html += `<tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 12px;"><img src="../${item.image}" style="width: 50px; height: 50px; object-fit: cover;"></td>
                                <td style="padding: 12px;">${item.name}</td>
                                <td style="padding: 12px;">${item.category}</td>
                                <td style="padding: 12px;">$${item.price}</td>
                                <td style="padding: 12px;">
                                    <button onclick="editMenuItem(${item.id})" style="background-color: #4CAF50; color: white; border: none; padding: 5px 10px; margin-right: 5px; cursor: pointer;">Edit</button>
                                    <button onclick="deleteMenuItem(${item.id})" style="background-color: #f44336; color: white; border: none; padding: 5px 10px; cursor: pointer;">Delete</button>
                                </td>
                            </tr>`;
                        });
                        
                        html += '</table>';
                        menuList.innerHTML = html;
                    } else {
                        menuList.innerHTML = '<p>No menu items found. Click "Add New Menu Item" to add your first menu item.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error loading menu items:', error);
                    document.getElementById('menuItemsList').innerHTML = '<p>Error loading menu data. Please try refreshing the page.</p>';
                });
        }
        
        function loadOrders() {
            fetch('get_orders.php')
                .then(response => response.json())
                .then(data => {
                    const ordersList = document.getElementById('ordersList');
                    if (data.orders && data.orders.length > 0) {
                        let html = '<table style="width:100%; border-collapse: collapse;">';
                        html += `<tr style="background-color: #f2f2f2;">
                            <th style="text-align: left; padding: 12px;">Order ID</th>
                            <th style="text-align: left; padding: 12px;">Customer</th>
                            <th style="text-align: left; padding: 12px;">Date</th>
                            <th style="text-align: left; padding: 12px;">Total</th>
                            <th style="text-align: left; padding: 12px;">Status</th>
                            <th style="text-align: left; padding: 12px;">Actions</th>
                        </tr>`;
                        
                        data.orders.forEach(order => {
                            html += `<tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 12px;">#${order.id}</td>
                                <td style="padding: 12px;">${order.customer_name}</td>
                                <td style="padding: 12px;">${order.date}</td>
                                <td style="padding: 12px;">$${order.total}</td>
                                <td style="padding: 12px;">
                                    <span style="padding: 5px 10px; border-radius: 3px; background-color: ${getStatusColor(order.status)}; color: white;">
                                        ${order.status}
                                    </span>
                                </td>
                                <td style="padding: 12px;">
                                    <button onclick="viewOrder(${order.id})" style="background-color: #2196F3; color: white; border: none; padding: 5px 10px; margin-right: 5px; cursor: pointer;">View</button>
                                    <button onclick="updateOrderStatus(${order.id})" style="background-color: #4CAF50; color: white; border: none; padding: 5px 10px; cursor: pointer;">Update Status</button>
                                </td>
                            </tr>`;
                        });
                        
                        html += '</table>';
                        ordersList.innerHTML = html;
                    } else {
                        ordersList.innerHTML = '<p>No orders found.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error loading orders:', error);
                    document.getElementById('ordersList').innerHTML = '<p>Error loading orders data.</p>';
                });
        }
        
        function loadReservations() {
            fetch('get_reservations.php')
                .then(response => response.json())
                .then(data => {
                    const reservationsList = document.getElementById('reservationsList');
                    if (data.reservations && data.reservations.length > 0) {
                        let html = '<table style="width:100%; border-collapse: collapse;">';
                        html += `<tr style="background-color: #f2f2f2;">
                            <th style="text-align: left; padding: 12px;">ID</th>
                            <th style="text-align: left; padding: 12px;">Customer</th>
                            <th style="text-align: left; padding: 12px;">Table</th>
                            <th style="text-align: left; padding: 12px;">Date</th>
                            <th style="text-align: left; padding: 12px;">Time</th>
                            <th style="text-align: left; padding: 12px;">Guests</th>
                            <th style="text-align: left; padding: 12px;">Status</th>
                            <th style="text-align: left; padding: 12px;">Actions</th>
                        </tr>`;
                        
                        data.reservations.forEach(reservation => {
                            html += `<tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 12px;">#${reservation.id}</td>
                                <td style="padding: 12px;">${reservation.user_name}</td>
                                <td style="padding: 12px;">${reservation.table_number}</td>
                                <td style="padding: 12px;">${reservation.date}</td>
                                <td style="padding: 12px;">${reservation.time}</td>
                                <td style="padding: 12px;">${reservation.guests}</td>
                                <td style="padding: 12px;">
                                    <span style="padding: 5px 10px; border-radius: 3px; background-color: ${getReservationStatusColor(reservation.status)}; color: white;">
                                        ${reservation.status}
                                    </span>
                                </td>
                                <td style="padding: 12px;">
                                    <button onclick="viewReservationDetails(${reservation.id})" style="background-color: #2196F3; color: white; border: none; padding: 5px 10px; margin-right: 5px; cursor: pointer;">View</button>
                                    <button onclick="updateReservationStatus(${reservation.id})" style="background-color: #4CAF50; color: white; border: none; padding: 5px 10px; cursor: pointer;">Update</button>
                                </td>
                            </tr>`;
                        });
                        
                        html += '</table>';
                        reservationsList.innerHTML = html;
                    } else {
                        reservationsList.innerHTML = '<p>No upcoming reservations found.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error loading reservations:', error);
                    document.getElementById('reservationsList').innerHTML = '<p>Error loading reservations data.</p>';
                });
        }
        
        function loadUsers() {
            fetch('get_users.php')
                .then(response => response.json())
                .then(data => {
                    const usersList = document.getElementById('usersList');
                    if (data.users && data.users.length > 0) {
                        let html = '<table style="width:100%; border-collapse: collapse;">';
                        html += `<tr style="background-color: #f2f2f2;">
                            <th style="text-align: left; padding: 12px;">ID</th>
                            <th style="text-align: left; padding: 12px;">Name</th>
                            <th style="text-align: left; padding: 12px;">Email</th>
                            <th style="text-align: left; padding: 12px;">Registered</th>
                            <th style="text-align: left; padding: 12px;">Actions</th>
                        </tr>`;
                        
                        data.users.forEach(user => {
                            html += `<tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 12px;">#${user.id}</td>
                                <td style="padding: 12px;">${user.name}</td>
                                <td style="padding: 12px;">${user.email}</td>
                                <td style="padding: 12px;">${user.registered_date}</td>
                                <td style="padding: 12px;">
                                    <button onclick="viewUser(${user.id})" style="background-color: #2196F3; color: white; border: none; padding: 5px 10px; margin-right: 5px; cursor: pointer;">View</button>
                                    <button onclick="deleteUser(${user.id})" style="background-color: #f44336; color: white; border: none; padding: 5px 10px; cursor: pointer;">Delete</button>
                                </td>
                            </tr>`;
                        });
                        
                        html += '</table>';
                        usersList.innerHTML = html;
                    } else {
                        usersList.innerHTML = '<p>No users found.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error loading users:', error);
                    document.getElementById('usersList').innerHTML = '<p>Error loading users data.</p>';
                });
        }
        
        // Helper functions
        function getStatusColor(status) {
            switch(status.toLowerCase()) {
                case 'pending': return '#ff9800';
                case 'processing': return '#2196F3';
                case 'completed': return '#4CAF50';
                case 'cancelled': return '#f44336';
                default: return '#607D8B';
            }
        }
        
        function getReservationStatusColor(status) {
            switch(status.toLowerCase()) {
                case 'confirmed': return '#4CAF50';
                case 'pending': return '#ff9800';
                case 'cancelled': return '#f44336';
                default: return '#607D8B';
            }
        }
        
        // Menu management functions
        function editMenuItem(id) {
            // Reset form and prepare for editing
            document.getElementById('addMenuForm').reset();
            document.getElementById('menuModalTitle').textContent = 'Edit Menu Item';
            document.getElementById('menuSubmitButton').textContent = 'Update Item';
            document.getElementById('menuItemId').value = id;
            document.getElementById('menuAction').value = 'edit';
            
            // Show loading status
            const statusDiv = document.getElementById('menuAddStatus');
            statusDiv.style.display = 'block';
            statusDiv.style.backgroundColor = '#f8f9fa';
            statusDiv.style.color = '#666';
            statusDiv.textContent = 'Loading menu item data...';
            
            // Fetch the menu item data
            fetch('get_menu_item.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Populate form with menu item data
                        document.getElementById('menuName').value = data.item.name;
                        document.getElementById('menuDescription').value = data.item.description;
                        document.getElementById('menuPrice').value = data.item.price;
                        document.getElementById('menuCategory').value = data.item.category;
                        
                        // Show current image if available
                        if (data.item.image) {
                            document.getElementById('currentImageContainer').style.display = 'block';
                            document.getElementById('currentImage').src = '../' + data.item.image;
                        } else {
                            document.getElementById('currentImageContainer').style.display = 'none';
                        }
                        
                        // Hide status and show modal
                        statusDiv.style.display = 'none';
                        document.getElementById('addMenuModal').style.display = 'block';
                    } else {
                        // Show error
                        statusDiv.style.backgroundColor = '#f8d7da';
                        statusDiv.style.color = '#721c24';
                        statusDiv.textContent = data.message || 'Error fetching menu item data';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    statusDiv.style.backgroundColor = '#f8d7da';
                    statusDiv.style.color = '#721c24';
                    statusDiv.textContent = 'An error occurred while fetching menu item data.';
                });
        }
        
        function deleteMenuItem(id) {
            if (confirm("Are you sure you want to delete this menu item?")) {
                const formData = new FormData();
                formData.append('id', id);
                
                fetch('delete_menu_item.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    return response.text(); // Get the raw text first
                })
                .then(text => {
                    let data;
                    try {
                        data = JSON.parse(text); // Try to parse as JSON
                        if (data.status === 'success') {
                            alert('Menu item deleted successfully');
                            loadMenuItems(); // Reload the menu items list
                        } else {
                            alert('Error: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Error parsing JSON:', error);
                        console.error('Received response:', text);
                        // Show a more user-friendly message
                        alert('An error occurred while deleting the menu item. Please check the server response.');
                    }
                })
                .catch(error => {
                    console.error('Network Error:', error);
                    alert('An error occurred while deleting the menu item.');
                });
            }
        }
        
        // Add Menu Modal Functions
        document.getElementById('addMenuItemBtn').addEventListener('click', function() {
            // Reset form for adding new item
            document.getElementById('addMenuForm').reset();
            document.getElementById('menuModalTitle').textContent = 'Add New Menu Item';
            document.getElementById('menuSubmitButton').textContent = 'Add Item';
            document.getElementById('menuItemId').value = '0';
            document.getElementById('menuAction').value = 'add';
            document.getElementById('currentImageContainer').style.display = 'none';
            document.getElementById('menuAddStatus').style.display = 'none';
            
            // Show the modal
            document.getElementById('addMenuModal').style.display = 'block';
        });
        
        document.getElementById('closeAddMenuModal').addEventListener('click', function() {
            document.getElementById('addMenuModal').style.display = 'none';
        });
        
        document.getElementById('cancelAddMenu').addEventListener('click', function() {
            document.getElementById('addMenuModal').style.display = 'none';
        });
        
        // Close modal if user clicks outside the content
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('addMenuModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
        
        // Handle form submission
        document.getElementById('addMenuForm').addEventListener('submit', function(event) {
            event.preventDefault();
            
            const formData = new FormData(this);
            const statusDiv = document.getElementById('menuAddStatus');
            const action = document.getElementById('menuAction').value;
            
            // Determine endpoint based on action
            const endpoint = action === 'add' ? 'add_menu_item.php' : 'update_menu_item.php';
            
            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                statusDiv.style.display = 'block';
                
                if (data.status === 'success') {
                    statusDiv.style.backgroundColor = '#d4edda';
                    statusDiv.style.color = '#155724';
                    statusDiv.textContent = data.message;
                    
                    // Reset form and close modal after a delay
                    setTimeout(() => {
                        document.getElementById('addMenuModal').style.display = 'none';
                        document.getElementById('addMenuForm').reset();
                        loadMenuItems(); // Reload menu items
                    }, 1500);
                } else {
                    statusDiv.style.backgroundColor = '#f8d7da';
                    statusDiv.style.color = '#721c24';
                    statusDiv.textContent = data.message || 'Error processing menu item';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                statusDiv.style.display = 'block';
                statusDiv.style.backgroundColor = '#f8d7da';
                statusDiv.style.color = '#721c24';
                statusDiv.textContent = 'An error occurred while processing the menu item.';
            });
        });
        
        // Tables management functions
        function loadTables() {
            fetch('get_tables_admin.php')
                .then(response => response.json())
                .then(data => {
                    const tablesList = document.getElementById('tablesList');
                    if (data.tables && data.tables.length > 0) {
                        let html = '<table style="width:100%; border-collapse: collapse;">';
                        html += `<tr style="background-color: #f2f2f2;">
                            <th style="text-align: left; padding: 12px;">Table #</th>
                            <th style="text-align: left; padding: 12px;">Capacity</th>
                            <th style="text-align: left; padding: 12px;">Status</th>
                            <th style="text-align: left; padding: 12px;">Actions</th>
                        </tr>`;
                        
                        data.tables.forEach(table => {
                            html += `<tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 12px;">${table.table_number}</td>
                                <td style="padding: 12px;">${table.capacity} people</td>
                                <td style="padding: 12px;">
                                    <span style="padding: 5px 10px; border-radius: 3px; background-color: ${table.is_available==1 ? '#4CAF50' : '#f44336'}; color: white;">
                                        ${table.is_available==1 ? 'Available' : 'Occupied'}
                                    </span>
                                </td>
                                <td style="padding: 12px;">
                                    <button onclick="editTable(${table.id})" style="background-color: #4CAF50; color: white; border: none; padding: 5px 10px; margin-right: 5px; cursor: pointer;">Edit</button>
                                    <button onclick="deleteTable(${table.id})" style="background-color: #f44336; color: white; border: none; padding: 5px 10px; cursor: pointer;">Delete</button>
                                </td>
                            </tr>`;
                        });
                        
                        html += '</table>';
                        tablesList.innerHTML = html;
                    } else {
                        tablesList.innerHTML = '<p>No tables found. Click "Add New Table" to add your first table.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error loading tables:', error);
                    document.getElementById('tablesList').innerHTML = '<p>Error loading tables data. Please try refreshing the page.</p>';
                });
        }

        function editTable(id) {
            showTableModal('edit', id);
        }

        function deleteTable(id) {
            if (confirm("Are you sure you want to delete this table? This action cannot be undone and may affect existing reservations.")) {
                fetch('delete_table.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + id
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Table deleted successfully');
                        loadTables(); // Reload the tables list
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the table.');
                });
            }
        }

        // Add button event listener
        document.addEventListener('DOMContentLoaded', function() {
            const addTableBtn = document.getElementById('addTableBtn');
            if (addTableBtn) {
                addTableBtn.addEventListener('click', function() {
                    showTableModal('add');
                });
            }
        });

        function showTableModal(action, tableId = null) {
            // Create a modal for adding/editing tables
            const modal = document.createElement('div');
            modal.className = 'table-modal';
            modal.style.position = 'fixed';
            modal.style.zIndex = '1000';
            modal.style.left = '0';
            modal.style.top = '0';
            modal.style.width = '100%';
            modal.style.height = '100%';
            modal.style.overflow = 'auto';
            modal.style.backgroundColor = 'rgba(0,0,0,0.4)';
            
            // Create modal content
            const modalContent = document.createElement('div');
            modalContent.style.backgroundColor = '#fefefe';
            modalContent.style.margin = '15% auto';
            modalContent.style.padding = '20px';
            modalContent.style.border = '1px solid #888';
            modalContent.style.width = '40%';
            modalContent.style.maxWidth = '500px';
            modalContent.style.borderRadius = '5px';
            modalContent.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
            
            // Add a close button
            const closeBtn = document.createElement('span');
            closeBtn.innerHTML = '&times;';
            closeBtn.style.color = '#aaa';
            closeBtn.style.float = 'right';
            closeBtn.style.fontSize = '28px';
            closeBtn.style.fontWeight = 'bold';
            closeBtn.onclick = function() {
                document.body.removeChild(modal);
            };
            
            // Add a title
            const title = document.createElement('h2');
            title.textContent = action === 'add' ? 'Add New Table' : 'Edit Table';
            title.style.color = '#a96700';
            title.style.marginTop = '0';
            
            // Add form content
            const form = document.createElement('form');
            form.id = 'tableForm';
            form.innerHTML = `
                <input type="hidden" id="tableId" name="id" value="${tableId || ''}">
                <input type="hidden" id="tableAction" name="action" value="${action}">
                
                <div style="margin-bottom: 15px;">
                    <label for="tableNumber" style="display: block; margin-bottom: 5px; font-weight: bold;">Table Number:</label>
                    <input type="number" id="tableNumber" name="table_number" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box;" min="1">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label for="tableCapacity" style="display: block; margin-bottom: 5px; font-weight: bold;">Capacity (people):</label>
                    <input type="number" id="tableCapacity" name="capacity" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box;" min="1">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Availability:</label>
                    <label style="margin-right: 15px;">
                        <input type="radio" name="is_available" value="1" checked> Available
                    </label>
                    <label>
                        <input type="radio" name="is_available" value="0"> Occupied
                    </label>
                </div>
                
                <div style="margin-top: 20px; text-align: right;">
                    <button type="button" id="cancelTableAction" style="background-color: #ccc; color: #333; border: none; padding: 8px 15px; border-radius: 3px; cursor: pointer; margin-right: 10px;">Cancel</button>
                    <button type="submit" id="submitTable" style="background-color: #4CAF50; color: white; border: none; padding: 8px 15px; border-radius: 3px; cursor: pointer;">${action === 'add' ? 'Add Table' : 'Update Table'}</button>
                </div>
                
                <div id="tableFormStatus" style="margin-top: 15px; padding: 10px; border-radius: 3px; display: none;"></div>
            `;
            
            // Assemble modal
            modalContent.appendChild(closeBtn);
            modalContent.appendChild(title);
            modalContent.appendChild(form);
            modal.appendChild(modalContent);
            
            // Add modal to body
            document.body.appendChild(modal);
            
            // Close modal if user clicks outside content
            modal.onclick = function(event) {
                if (event.target === modal) {
                    document.body.removeChild(modal);
                }
            };
            
            // Handle cancel button
            document.getElementById('cancelTableAction').onclick = function() {
                document.body.removeChild(modal);
            };
            
            // If editing, fetch the table data
            if (action === 'edit' && tableId) {
                fetch(`get_table.php?id=${tableId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Populate form with table data
                            document.getElementById('tableNumber').value = data.table.table_number;
                            document.getElementById('tableCapacity').value = data.table.capacity;
                            
                            // Set availability radio button
                            const availableRadios = document.getElementsByName('is_available');
                            for (let radio of availableRadios) {
                                if (radio.value == data.table.is_available) {
                                    radio.checked = true;
                                }
                            }
                        } else {
                            const statusDiv = document.getElementById('tableFormStatus');
                            statusDiv.style.display = 'block';
                            statusDiv.style.backgroundColor = '#f8d7da';
                            statusDiv.style.color = '#721c24';
                            statusDiv.textContent = data.message || 'Error loading table data';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        const statusDiv = document.getElementById('tableFormStatus');
                        statusDiv.style.display = 'block';
                        statusDiv.style.backgroundColor = '#f8d7da';
                        statusDiv.style.color = '#721c24';
                        statusDiv.textContent = 'An error occurred while loading table data.';
                    });
            }
            
            // Handle form submission
            document.getElementById('tableForm').addEventListener('submit', function(event) {
                event.preventDefault();
                
                const formData = new FormData(this);
                const statusDiv = document.getElementById('tableFormStatus');
                const submitAction = action === 'add' ? 'add_table.php' : 'update_table.php';
                
                // Disable the buttons while processing
                document.getElementById('cancelTableAction').disabled = true;
                document.getElementById('submitTable').disabled = true;
                
                // Show status message
                statusDiv.style.display = 'block';
                statusDiv.style.backgroundColor = '#f8f9fa';
                statusDiv.style.color = '#666';
                statusDiv.textContent = action === 'add' ? 'Adding table...' : 'Updating table...';
                
                fetch(submitAction, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        statusDiv.style.backgroundColor = '#d4edda';
                        statusDiv.style.color = '#155724';
                        statusDiv.textContent = data.message;
                        
                        // Close modal and reload tables after a delay
                        setTimeout(() => {
                            document.body.removeChild(modal);
                            loadTables(); // Reload the tables list
                        }, 1500);
                    } else {
                        statusDiv.style.backgroundColor = '#f8d7da';
                        statusDiv.style.color = '#721c24';
                        statusDiv.textContent = data.message || 'Error processing table data';
                        
                        // Re-enable buttons
                        document.getElementById('cancelTableAction').disabled = false;
                        document.getElementById('submitTable').disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    statusDiv.style.backgroundColor = '#f8d7da';
                    statusDiv.style.color = '#721c24';
                    statusDiv.textContent = 'An error occurred while processing the table.';
                    
                    // Re-enable buttons
                    document.getElementById('cancelTableAction').disabled = false;
                    document.getElementById('submitTable').disabled = false;
                });
            });
        }

        // Order management functions
        function viewOrder(id) {
            // Create a modal to show order details
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.style.position = 'fixed';
            modal.style.zIndex = '1000';
            modal.style.left = '0';
            modal.style.top = '0';
            modal.style.width = '100%';
            modal.style.height = '100%';
            modal.style.overflow = 'auto';
            modal.style.backgroundColor = 'rgba(0,0,0,0.4)';
            
            // Create modal content
            const modalContent = document.createElement('div');
            modalContent.style.backgroundColor = '#fefefe';
            modalContent.style.margin = '10% auto';
            modalContent.style.padding = '20px';
            modalContent.style.border = '1px solid #888';
            modalContent.style.width = '60%';
            modalContent.style.maxWidth = '700px';
            modalContent.style.borderRadius = '5px';
            modalContent.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
            
            // Add a close button
            const closeBtn = document.createElement('span');
            closeBtn.innerHTML = '&times;';
            closeBtn.className = 'close-modal-btn';
            closeBtn.style.color = '#aaa';
            closeBtn.style.float = 'right';
            closeBtn.style.fontSize = '28px';
            closeBtn.style.fontWeight = 'bold';
            closeBtn.style.cursor = 'pointer';
            
            // Add title
            const title = document.createElement('h2');
            title.textContent = 'Order Details';
            title.style.color = '#a96700';
            title.style.marginTop = '0';
            
            // Add loading message
            const loadingMsg = document.createElement('p');
            loadingMsg.textContent = 'Loading order details...';
            
            // Assemble modal
            modalContent.appendChild(closeBtn);
            modalContent.appendChild(title);
            modalContent.appendChild(loadingMsg);
            modal.appendChild(modalContent);
            
            // Add modal to body
            document.body.appendChild(modal);
            
            // Close modal when clicking on close button
            closeBtn.onclick = function() {
                document.body.removeChild(modal);
            };
            
            // Close modal if user clicks outside content
            modal.onclick = function(event) {
                if (event.target === modal) {
                    document.body.removeChild(modal);
                }
            };
            
            // Fetch order details
            fetch(`get_order_details.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const order = data.order;
                        
                        let orderItems = '<h3>Items:</h3><ul style="list-style: none; padding: 0;">';
                        if (order.items && order.items.length > 0) {
                            order.items.forEach(item => {
                                orderItems += `<li style="padding: 8px 0; border-bottom: 1px solid #eee;">
                                    ${item.quantity}x ${item.item_name} - $${parseFloat(item.price).toFixed(2)}
                                </li>`;
                            });
                        } else {
                            orderItems = '<p>No items found in this order.</p>';
                        }
                        orderItems += '</ul>';
                        
                        let tableInfo = '';
                        if (order.table_id) {
                            tableInfo = `<p><strong>Table:</strong> Table #${order.table_id}</p>`;
                        }
                        
                        let html = `
                            <div style="margin-bottom: 20px;">
                                <p><strong>Order ID:</strong> #${order.id}</p>
                                <p><strong>Date:</strong> ${order.date}</p>
                                <p><strong>Customer:</strong> ${order.customer_name || 'Guest'}</p>
                                ${tableInfo}
                                <p><strong>Status:</strong> 
                                    <span style="padding: 5px 10px; border-radius: 3px; background-color: ${getStatusColor(order.status)}; color: white;">
                                        ${order.status}
                                    </span>
                                </p>
                                ${orderItems}
                                <p><strong>Total Amount:</strong> $${order.total}</p>
                            </div>
                            <div style="margin-top: 20px; text-align: right;">
                                <button onclick="updateOrderStatus(${order.id}, '${order.status}')" style="background-color: #4CAF50; color: white; border: none; padding: 8px 15px; border-radius: 3px; cursor: pointer; margin-right: 10px;">Update Status</button>
                                <button class="close-modal-btn" style="background-color: #ccc; color: #333; border: none; padding: 8px 15px; border-radius: 3px; cursor: pointer;">Close</button>
                            </div>
                        `;
                        
                        // Update modal content
                        modalContent.removeChild(loadingMsg);
                        const contentDiv = document.createElement('div');
                        contentDiv.innerHTML = html;
                        modalContent.appendChild(contentDiv);
                        
                        // Add event listeners to any new close buttons
                        document.querySelectorAll('.close-modal-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                document.body.removeChild(modal);
                            });
                        });
                    } else {
                        modalContent.removeChild(loadingMsg);
                        const errorMsg = document.createElement('p');
                        errorMsg.style.color = '#f44336';
                        errorMsg.textContent = data.message || 'Failed to load order details.';
                        modalContent.appendChild(errorMsg);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalContent.removeChild(loadingMsg);
                    const errorMsg = document.createElement('p');
                    errorMsg.style.color = '#f44336';
                    errorMsg.textContent = 'An error occurred while fetching order details.';
                    modalContent.appendChild(errorMsg);
                });
        }
        
        function updateOrderStatus(id, currentStatus = '') {
            // Create a modal for updating order status
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.style.position = 'fixed';
            modal.style.zIndex = '1000';
            modal.style.left = '0';
            modal.style.top = '0';
            modal.style.width = '100%';
            modal.style.height = '100%';
            modal.style.overflow = 'auto';
            modal.style.backgroundColor = 'rgba(0,0,0,0.4)';
            
            // Create modal content
            const modalContent = document.createElement('div');
            modalContent.style.backgroundColor = '#fefefe';
            modalContent.style.margin = '15% auto';
            modalContent.style.padding = '20px';
            modalContent.style.border = '1px solid #888';
            modalContent.style.width = '40%';
            modalContent.style.maxWidth = '500px';
            modalContent.style.borderRadius = '5px';
            modalContent.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
            
            // Add content to modal
            modalContent.innerHTML = `
                <span class="close-modal-btn" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
                <h2 style="color: #a96700; margin-top: 0;">Update Order Status</h2>
                <p>Order #${id}</p>
                <div style="margin-bottom: 15px;">
                    <label for="orderStatus" style="display: block; margin-bottom: 5px; font-weight: bold;">Status:</label>
                    <select id="orderStatus" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box;">
                        <option value="pending" ${currentStatus === 'pending' ? 'selected' : ''}>Pending</option>
                        <option value="processing" ${currentStatus === 'processing' ? 'selected' : ''}>Processing</option>
                        <option value="completed" ${currentStatus === 'completed' ? 'selected' : ''}>Completed</option>
                        <option value="cancelled" ${currentStatus === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                    </select>
                </div>
                <div style="margin-top: 20px; text-align: right;">
                    <button id="cancelOrderUpdate" style="background-color: #ccc; color: #333; border: none; padding: 8px 15px; border-radius: 3px; cursor: pointer; margin-right: 10px;">Cancel</button>
                    <button id="submitOrderUpdate" style="background-color: #4CAF50; color: white; border: none; padding: 8px 15px; border-radius: 3px; cursor: pointer;">Update</button>
                </div>
                
                <div id="orderUpdateStatus" style="margin-top: 15px; padding: 10px; border-radius: 3px; display: none;"></div>
            `;
            
            // Add modal to body
            modal.appendChild(modalContent);
            document.body.appendChild(modal);
            
            // Close modal when clicking on close button
            const closeBtn = modalContent.querySelector('.close-modal-btn');
            closeBtn.onclick = function() {
                document.body.removeChild(modal);
            };
            
            // Close modal if user clicks outside content
            modal.onclick = function(event) {
                if (event.target === modal) {
                    document.body.removeChild(modal);
                }
            };
            
            // Handle cancel button
            document.getElementById('cancelOrderUpdate').onclick = function() {
                document.body.removeChild(modal);
            };
            
            // Handle update button
            document.getElementById('submitOrderUpdate').onclick = function() {
                const status = document.getElementById('orderStatus').value;
                const statusDiv = document.getElementById('orderUpdateStatus');
                
                // Disable buttons while processing
                document.getElementById('cancelOrderUpdate').disabled = true;
                document.getElementById('submitOrderUpdate').disabled = true;
                
                // Show status message
                statusDiv.style.display = 'block';
                statusDiv.style.backgroundColor = '#f8f9fa';
                statusDiv.style.color = '#666';
                statusDiv.textContent = 'Updating order status...';
                
                // Send update request
                const formData = new FormData();
                formData.append('id', id);
                formData.append('status', status);
                
                fetch('update_order_status.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        statusDiv.style.backgroundColor = '#d4edda';
                        statusDiv.style.color = '#155724';
                        statusDiv.textContent = data.message || 'Order status updated successfully';
                        
                        // Close modal and reload orders after a delay
                        setTimeout(() => {
                            document.body.removeChild(modal);
                            loadOrders(); // Reload the orders list
                        }, 1500);
                    } else {
                        statusDiv.style.backgroundColor = '#f8d7da';
                        statusDiv.style.color = '#721c24';
                        statusDiv.textContent = data.message || 'Error updating order status';
                        
                        // Re-enable buttons
                        document.getElementById('cancelOrderUpdate').disabled = false;
                        document.getElementById('submitOrderUpdate').disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    statusDiv.style.backgroundColor = '#f8d7da';
                    statusDiv.style.color = '#721c24';
                    statusDiv.textContent = 'An error occurred while updating order status.';
                    
                    // Re-enable buttons
                    document.getElementById('cancelOrderUpdate').disabled = false;
                    document.getElementById('submitOrderUpdate').disabled = false;
                });
            };
        }

        // Reservation management functions
        function viewReservationDetails(id) {
            // Create a modal to show reservation details
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.style.position = 'fixed';
            modal.style.zIndex = '1000';
            modal.style.left = '0';
            modal.style.top = '0';
            modal.style.width = '100%';
            modal.style.height = '100%';
            modal.style.overflow = 'auto';
            modal.style.backgroundColor = 'rgba(0,0,0,0.4)';
            
            // Create modal content
            const modalContent = document.createElement('div');
            modalContent.style.backgroundColor = '#fefefe';
            modalContent.style.margin = '10% auto';
            modalContent.style.padding = '20px';
            modalContent.style.border = '1px solid #888';
            modalContent.style.width = '60%';
            modalContent.style.maxWidth = '700px';
            modalContent.style.borderRadius = '5px';
            modalContent.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
            
            // Add a close button
            const closeBtn = document.createElement('span');
            closeBtn.innerHTML = '&times;';
            closeBtn.className = 'close-modal-btn';
            closeBtn.style.color = '#aaa';
            closeBtn.style.float = 'right';
            closeBtn.style.fontSize = '28px';
            closeBtn.style.fontWeight = 'bold';
            closeBtn.style.cursor = 'pointer';
            
            // Add title
            const title = document.createElement('h2');
            title.textContent = 'Reservation Details';
            title.style.color = '#a96700';
            title.style.marginTop = '0';
            
            // Add loading message
            const loadingMsg = document.createElement('p');
            loadingMsg.textContent = 'Loading reservation details...';
            
            // Assemble modal
            modalContent.appendChild(closeBtn);
            modalContent.appendChild(title);
            modalContent.appendChild(loadingMsg);
            modal.appendChild(modalContent);
            
            // Add modal to body
            document.body.appendChild(modal);
            
            // Close modal when clicking on close button
            closeBtn.onclick = function() {
                document.body.removeChild(modal);
            };
            
            // Close modal if user clicks outside content
            modal.onclick = function(event) {
                if (event.target === modal) {
                    document.body.removeChild(modal);
                }
            };
            
            // Fetch reservation details
            fetch(`get_reservation_details.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const res = data.reservation;
                        
                        let html = `
                            <div style="display: flex; flex-wrap: wrap; margin: 0 -10px;">
                                <div style="flex: 1; padding: 0 10px; min-width: 250px;">
                                    <h3>Reservation Information</h3>
                                    <p><strong>ID:</strong> #${res.id}</p>
                                    <p><strong>Date:</strong> ${res.date}</p>
                                    <p><strong>Time:</strong> ${res.time}</p>
                                    <p><strong>Guests:</strong> ${res.guests}</p>
                                    <p><strong>Status:</strong> 
                                        <span style="padding: 5px 10px; border-radius: 3px; background-color: ${getReservationStatusColor(res.status)}; color: white;">
                                            ${res.status}
                                        </span>
                                    </p>
                                    <p><strong>Created:</strong> ${res.created_at}</p>
                                    ${res.special_requests ? `<p><strong>Special Requests:</strong> ${res.special_requests}</p>` : ''}
                                </div>
                                <div style="flex: 1; padding: 0 10px; min-width: 250px;">
                                    <h3>Customer Information</h3>
                                    <p><strong>Name:</strong> ${res.user_name}</p>
                                    <p><strong>Email:</strong> ${res.user_email}</p>
                                    <p><strong>User ID:</strong> ${res.user_id}</p>
                                    
                                    <h3>Table Information</h3>
                                    <p><strong>Table Number:</strong> ${res.table_number}</p>
                                    <p><strong>Capacity:</strong> ${res.capacity} people</p>
                                </div>
                            </div>
                            <div style="margin-top: 20px; text-align: right;">
                                <button onclick="updateReservationStatus(${res.id}, '${res.status}')" style="background-color: #4CAF50; color: white; border: none; padding: 8px 15px; border-radius: 3px; cursor: pointer; margin-right: 10px;">Update Status</button>
                                <button class="close-modal-btn" style="background-color: #ccc; color: #333; border: none; padding: 8px 15px; border-radius: 3px; cursor: pointer;">Close</button>
                            </div>
                        `;
                        
                        // Update modal content
                        modalContent.removeChild(loadingMsg);
                        const contentDiv = document.createElement('div');
                        contentDiv.innerHTML = html;
                        modalContent.appendChild(contentDiv);
                        
                        // Add event listeners to any new close buttons
                        document.querySelectorAll('.close-modal-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                document.body.removeChild(modal);
                            });
                        });
                    } else {
                        modalContent.removeChild(loadingMsg);
                        const errorMsg = document.createElement('p');
                        errorMsg.style.color = '#f44336';
                        errorMsg.textContent = data.message || 'Failed to load reservation details.';
                        modalContent.appendChild(errorMsg);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalContent.removeChild(loadingMsg);
                    const errorMsg = document.createElement('p');
                    errorMsg.style.color = '#f44336';
                    errorMsg.textContent = 'An error occurred while fetching reservation details.';
                    modalContent.appendChild(errorMsg);
                });
        }
        
        function updateReservationStatus(id, currentStatus = '') {
            // Create a modal for updating reservation status
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.style.position = 'fixed';
            modal.style.zIndex = '1000';
            modal.style.left = '0';
            modal.style.top = '0';
            modal.style.width = '100%';
            modal.style.height = '100%';
            modal.style.overflow = 'auto';
            modal.style.backgroundColor = 'rgba(0,0,0,0.4)';
            
            // Create modal content
            const modalContent = document.createElement('div');
            modalContent.style.backgroundColor = '#fefefe';
            modalContent.style.margin = '15% auto';
            modalContent.style.padding = '20px';
            modalContent.style.border = '1px solid #888';
            modalContent.style.width = '40%';
            modalContent.style.maxWidth = '500px';
            modalContent.style.borderRadius = '5px';
            modalContent.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
            
            // Add content to modal
            modalContent.innerHTML = `
                <span class="close-modal-btn" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
                <h2 style="color: #a96700; margin-top: 0;">Update Reservation Status</h2>
                <p>Reservation #${id}</p>
                <div style="margin-bottom: 15px;">
                    <label for="reservationStatus" style="display: block; margin-bottom: 5px; font-weight: bold;">Status:</label>
                    <select id="reservationStatus" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box;">
                        <option value="pending" ${currentStatus === 'pending' ? 'selected' : ''}>Pending</option>
                        <option value="confirmed" ${currentStatus === 'confirmed' ? 'selected' : ''}>Confirmed</option>
                        <option value="completed" ${currentStatus === 'completed' ? 'selected' : ''}>Completed</option>
                        <option value="cancelled" ${currentStatus === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                        <option value="no-show" ${currentStatus === 'no-show' ? 'selected' : ''}>No-show</option>
                    </select>
                </div>
                <div style="margin-top: 20px; text-align: right;">
                    <button id="cancelReservationUpdate" style="background-color: #ccc; color: #333; border: none; padding: 8px 15px; border-radius: 3px; cursor: pointer; margin-right: 10px;">Cancel</button>
                    <button id="submitReservationUpdate" style="background-color: #4CAF50; color: white; border: none; padding: 8px 15px; border-radius: 3px; cursor: pointer;">Update</button>
                </div>
                
                <div id="reservationUpdateStatus" style="margin-top: 15px; padding: 10px; border-radius: 3px; display: none;"></div>
            `;
            
            // Add modal to body
            modal.appendChild(modalContent);
            document.body.appendChild(modal);
            
            // Close modal when clicking on close button
            const closeBtn = modalContent.querySelector('.close-modal-btn');
            closeBtn.onclick = function() {
                document.body.removeChild(modal);
            };
            
            // Close modal if user clicks outside content
            modal.onclick = function(event) {
                if (event.target === modal) {
                    document.body.removeChild(modal);
                }
            };
            
            // Handle cancel button
            document.getElementById('cancelReservationUpdate').onclick = function() {
                document.body.removeChild(modal);
            };
            
            // Handle update button
            document.getElementById('submitReservationUpdate').onclick = function() {
                const status = document.getElementById('reservationStatus').value;
                const statusDiv = document.getElementById('reservationUpdateStatus');
                
                // Disable buttons while processing
                document.getElementById('cancelReservationUpdate').disabled = true;
                document.getElementById('submitReservationUpdate').disabled = true;
                
                // Show status message
                statusDiv.style.display = 'block';
                statusDiv.style.backgroundColor = '#f8f9fa';
                statusDiv.style.color = '#666';
                statusDiv.textContent = 'Updating reservation status...';
                
                // Send update request
                const formData = new FormData();
                formData.append('id', id);
                formData.append('status', status);
                
                fetch('update_reservation_status.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        statusDiv.style.backgroundColor = '#d4edda';
                        statusDiv.style.color = '#155724';
                        statusDiv.textContent = data.message || 'Reservation status updated successfully';
                        
                        // Close modal and reload reservations after a delay
                        setTimeout(() => {
                            document.body.removeChild(modal);
                            loadReservations(); // Reload the reservations list
                        }, 1500);
                    } else {
                        statusDiv.style.backgroundColor = '#f8d7da';
                        statusDiv.style.color = '#721c24';
                        statusDiv.textContent = data.message || 'Error updating reservation status';
                        
                        // Re-enable buttons
                        document.getElementById('cancelReservationUpdate').disabled = false;
                        document.getElementById('submitReservationUpdate').disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    statusDiv.style.backgroundColor = '#f8d7da';
                    statusDiv.style.color = '#721c24';
                    statusDiv.textContent = 'An error occurred while updating reservation status.';
                    
                    // Re-enable buttons
                    document.getElementById('cancelReservationUpdate').disabled = false;
                    document.getElementById('submitReservationUpdate').disabled = false;
                });
            };
        }
    </script>
</body>
</html>
