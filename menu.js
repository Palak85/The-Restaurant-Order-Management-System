// DOM elements
const scrollableContent = document.querySelector('.scrollable-content');
const categoryNav = document.getElementById('category-nav');
const cartButton = document.getElementById('cart-button');
const cartModal = document.getElementById('cart-modal');
const cartItemsList = document.getElementById('cart-items-list');
const closeCartButton = document.getElementById('close-cart-button');
const placeOrderButton = document.getElementById('place-order-button');
const tableBookingCard = document.getElementById('table-booking-card');
const closeModalButtons = document.querySelectorAll('.close-modal');
const payButton = document.getElementById('pay-button');
const searchBar = document.getElementById('search-bar');
const searchButton = document.getElementById('search-button');
const customerNameInput = document.getElementById('customer-name');
const userInfoElement = document.getElementById('user-info');
const loginLink = document.getElementById('login-link');
const logoutLink = document.getElementById('logout-link');

// Menu items data
let menuItems = [];

// Cart functionality - using localStorage for temporary storage
let cart = {
    items: [],
    addItem: function(item, quantity = 1) {
        // Check if item already exists in cart
        const existingItem = this.items.find(i => i.menu_id === item.id);
        
        if (existingItem) {
            // Update quantity if item exists
            existingItem.quantity += quantity;
        } else {
            // Add new item to cart
            this.items.push({
                menu_id: item.id,
                name: item.name,
                price: parseFloat(item.price),
                quantity: quantity,
                category: item.category,
                image_url: item.image
            });
        }
        
        // Save to localStorage
        this.saveToLocalStorage();
        
        // Update display
        this.updateCartDisplay();
        
        // Show success message
        showToast('Item added to cart');
    },
    removeItem: function(itemId) {
        // Remove item from array
        this.items = this.items.filter(item => item.menu_id !== itemId);
        
        // Save to localStorage
        this.saveToLocalStorage();
        
        // Update display
        this.updateCartDisplay();
        
        // Show success message
        showToast('Item removed from cart');
    },
    updateQuantity: function(itemId, quantity) {
        if (quantity <= 0) {
            this.removeItem(itemId);
            return;
        }
        
        // Find and update item
        const item = this.items.find(i => i.menu_id === itemId);
        if (item) {
            item.quantity = quantity;
            
            // Save to localStorage
            this.saveToLocalStorage();
            
            // Update display
            this.updateCartDisplay();
        }
    },
    getTotalItems: function() {
        return this.items.reduce((total, item) => total + (parseInt(item.quantity) || 0), 0);
    },
    getTotalPrice: function() {
        return this.items.reduce((total, item) => {
            const price = parseFloat(item.price) || 0;
            const quantity = parseInt(item.quantity) || 0;
            return total + (price * quantity);
        }, 0);
    },
    clearCart: function() {
        // Clear items array
        this.items = [];
        
        // Clear localStorage
        this.saveToLocalStorage();
        
        // Update display
        this.updateCartDisplay();
        
        // Show success message
        showToast('Cart cleared successfully');
    },
    loadFromLocalStorage: function() {
        // Get cart data from localStorage
        const cartData = localStorage.getItem('restaurantCart');
        if (cartData) {
            try {
                this.items = JSON.parse(cartData);
                this.updateCartDisplay();
            } catch (error) {
                console.error('Error parsing cart data from localStorage:', error);
                this.items = [];
            }
        }
    },
    saveToLocalStorage: function() {
        // Save cart data to localStorage
        localStorage.setItem('restaurantCart', JSON.stringify(this.items));
    },
    updateCartDisplay: function() {
        // Update cart count in header
        document.getElementById('cart-count').textContent = this.getTotalItems();
        document.getElementById('item-count').textContent = this.getTotalItems();
        document.getElementById('total-price').textContent = this.getTotalPrice().toFixed(2);
        
        // Update all add-to-cart buttons in menu
        document.querySelectorAll('.add-to-cart').forEach(button => {
            const itemId = parseInt(button.dataset.id);
            const inCart = this.items.some(item => item.menu_id === itemId);
            if (inCart) {
                button.innerHTML = '<i class="fas fa-check"></i> Added';
                button.classList.add('added');
            } else {
                button.innerHTML = '<i class="fas fa-plus"></i> Add';
                button.classList.remove('added');
            }
        });
    }
};

// Fetch menu items from backend
async function fetchMenuItems() {
    try {
        const response = await fetch('get_menu.php');
        const data = await response.json();
        
        if (data.status === 'success') {
            menuItems = data.menu_items;
            initializeMenu();
        } else {
            showToast('Failed to load menu items');
            console.error('Error loading menu items:', data.message);
            
            // Fall back to static menu data in case of failure
            initializeMenuWithFallbackData();
        }
    } catch (error) {
        showToast('Failed to load menu items');
        console.error('Error loading menu items:', error);
        
        // Fall back to static menu data in case of failure
        initializeMenuWithFallbackData();
    }
}

// Fallback menu data
function initializeMenuWithFallbackData() {
    menuItems = [
        { id: 1, category: "Starters", name: "Chicken Wings", price: 100.00, image: "img1.jpg" },
        { id: 2, category: "Starters", name: "Garlic Bread", price: 80.00, image: "img2.jpg" },
        { id: 3, category: "Main Course", name: "Chicken Biryani", price: 180.00, image: "img4.jpg" },
        { id: 4, category: "Main Course", name: "Paneer Butter Masala", price: 160.00, image: "img5.jpg" },
        { id: 5, category: "Desserts", name: "Chocolate Brownie", price: 120.00, image: "img6.jpg" }
    ];
    initializeMenu();
}

// Initialize the menu with items grouped by category
function initializeMenu() {
    scrollableContent.innerHTML = ''; // Clear existing content
    categoryNav.innerHTML = ''; // Clear existing category navigation
    
    // Group items by category
    const categoryGroups = {};
    menuItems.forEach(item => {
        if (!categoryGroups[item.category]) {
            categoryGroups[item.category] = [];
        }
        categoryGroups[item.category].push(item);
    });
    
    // Create category navigation
    const allCategoriesButton = document.createElement('button');
    allCategoriesButton.textContent = 'All';
    allCategoriesButton.classList.add('category-button', 'active');
    allCategoriesButton.addEventListener('click', () => {
        showAllCategories();
        setActiveCategoryButton(allCategoriesButton);
    });
    categoryNav.appendChild(allCategoriesButton);
    
    // Add category buttons
    Object.keys(categoryGroups).forEach(category => {
        const categoryButton = document.createElement('button');
        categoryButton.textContent = category;
        categoryButton.classList.add('category-button');
        categoryButton.addEventListener('click', () => {
            showCategory(category);
            setActiveCategoryButton(categoryButton);
        });
        categoryNav.appendChild(categoryButton);
    });
    
    // Display all categories initially
    showAllCategories();
    
    // Update cart display
    cart.updateCartDisplay();
}

function setActiveCategoryButton(activeButton) {
    document.querySelectorAll('.category-button').forEach(button => {
        button.classList.remove('active');
    });
    activeButton.classList.add('active');
}

// Show all categories
function showAllCategories() {
    scrollableContent.innerHTML = '';
    
    // Group items by category
    const categories = [...new Set(menuItems.map(item => item.category))];
    
    categories.forEach(category => {
        // Add category heading
        const categoryHeading = document.createElement('h3');
        categoryHeading.textContent = category;
        categoryHeading.id = `category-${category.replace(/\s+/g, '-').toLowerCase()}`;
        scrollableContent.appendChild(categoryHeading);
        
        // Add items for this category
        const categoryItems = menuItems.filter(item => item.category === category);
        displayMenuItems(categoryItems);
    });
}

// Show specific category
function showCategory(category) {
    scrollableContent.innerHTML = '';
    
    // Add category heading
    const categoryHeading = document.createElement('h3');
    categoryHeading.textContent = category;
    scrollableContent.appendChild(categoryHeading);
    
    // Add items for this category
    const categoryItems = menuItems.filter(item => item.category === category);
    displayMenuItems(categoryItems);
}

// Display menu items
function displayMenuItems(items) {
    items.forEach(item => {
        const itemElement = document.createElement('div');
        itemElement.className = 'menu-item';
        itemElement.dataset.id = item.id;
        
        // Determine the image path - improved fallback handling
        let imagePath;
        if (item.image) {
            // If it's a full URL or already includes a path
            if (item.image.includes('://') || item.image.includes('/')) {
                imagePath = item.image;
            } else {
                // If it's just a filename, add the proper directory
                imagePath = `uploads/menu/${item.image}`;
            }
        } else {
            // Default placeholder image that we know exists
            imagePath = 'img1.jpg';
        }
        
        itemElement.innerHTML = `
            <img src="${imagePath}" alt="${item.name}" onerror="this.src='img1.jpg'">
            <div class="details">
                <h3>${item.name}</h3>
                <p>&#8377; ${parseFloat(item.price).toFixed(2)}</p>
            </div>
            <div class="actions">
                <button class="add-to-cart" data-id="${item.id}" data-name="${item.name}" data-price="${item.price}" data-category="${item.category}" data-image="${imagePath}">
                    <i class="fas fa-plus"></i> Add
                </button>
            </div>
        `;
        scrollableContent.appendChild(itemElement);
        
        // Add event listener to the add to cart button
        const addToCartButton = itemElement.querySelector('.add-to-cart');
        addToCartButton.addEventListener('click', (e) => {
            const button = e.target.closest('.add-to-cart');
            const itemId = parseInt(button.dataset.id);
            
            if (cart.items.some(i => i.menu_id === itemId)) {
                // Item already in cart - remove it
                cart.removeItem(itemId);
                button.innerHTML = '<i class="fas fa-plus"></i> Add';
                button.classList.remove('added');
                showToast(`${button.dataset.name} removed from cart!`);
            } else {
                // Create item object directly from button data attributes
                const itemToAdd = {
                    id: itemId,
                    name: button.dataset.name,
                    price: parseFloat(button.dataset.price),
                    category: button.dataset.category,
                    image: button.dataset.image
                };
                
                // Add item to cart
                cart.addItem(itemToAdd);
                button.innerHTML = '<i class="fas fa-check"></i> Added';
                button.classList.add('added');
                showToast(`${button.dataset.name} added to cart!`);
            }
        });
    });
}

// Check authentication status
function checkAuthStatus() {
    fetch('check_auth.php')
        .then(response => response.json())
        .then(data => {
            if (data.authenticated) {
                // User is logged in
                userInfoElement.textContent = `Welcome, ${data.user_name}`;
                loginLink.style.display = 'none';
                logoutLink.style.display = 'block';
            } else {
                // User is not logged in
                userInfoElement.textContent = '';
                loginLink.style.display = 'block';
                logoutLink.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error checking authentication status:', error);
        });
}

// Show toast notification
function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

// Initialize table selection by fetching from backend
function initializeTableSelection() {
    const tableSelection = document.querySelector('.table-selection');
    tableSelection.innerHTML = '<p>Loading available tables...</p>'; // Loading indicator
    
    // Add cache-busting parameter to prevent caching
    const timestamp = new Date().getTime();
    
    // Fetch tables from the backend
    fetch(`get_tables.php?_=${timestamp}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.tables.length > 0) {
                tableSelection.innerHTML = ''; // Clear loading message
                
                data.tables.forEach(table => {
                    const tableButton = document.createElement('button');
                    tableButton.className = 'table-button';
                    tableButton.dataset.table = table.table_number;
                    tableButton.dataset.capacity = table.capacity;
                    tableButton.textContent = `Table ${table.table_number} (${table.capacity})`;
                    
                    // Disable if table is not available
                    if (!table.is_available) {
                        tableButton.disabled = true;
                        tableButton.classList.add('not-available');
                        tableButton.setAttribute('title', 'This table is not available');
                    }
                    
                    tableSelection.appendChild(tableButton);
                    
                    // Add click handler for available tables
                    if (table.is_available) {
                        tableButton.addEventListener('click', () => {
                            document.querySelectorAll('.table-button').forEach(btn => {
                                btn.classList.remove('selected');
                            });
                            tableButton.classList.add('selected');
                        });
                    }
                });
            } else {
                tableSelection.innerHTML = '<p>No tables available. Please try again later.</p>';
            }
        })
        .catch(error => {
            console.error('Error fetching tables:', error);
            tableSelection.innerHTML = '<p>Error loading tables. Please try again.</p>';
        });
}

// Open cart modal
cartButton.addEventListener('click', () => {
    updateCartModalContent();
    cartModal.classList.remove('hidden');
});

// Close modals
closeModalButtons.forEach(button => {
    button.addEventListener('click', () => {
        cartModal.classList.add('hidden');
        tableBookingCard.classList.add('hidden');
    });
});

// Update cart modal content
function updateCartModalContent() {
    cartItemsList.innerHTML = '';
    
    if (cart.items.length === 0) {
        cartItemsList.innerHTML = '<li class="empty-cart">Your cart is empty</li>';
        document.getElementById('close-cart-button').disabled = true;
    } else {
        document.getElementById('close-cart-button').disabled = false;
        
        cart.items.forEach(item => {
            const li = document.createElement('li');
            li.className = 'cart-item';
            li.dataset.id = item.menu_id;
            li.innerHTML = `
                <div class="cart-item-info">
                    <span class="item-name">${item.name}</span>
                    <span class="item-price">&#8377; ${(item.price * item.quantity).toFixed(2)}</span>
                </div>
                <div class="quantity-controls">
                    <button class="quantity-btn minus"><i class="fas fa-minus"></i></button>
                    <span class="quantity">${item.quantity}</span>
                    <button class="quantity-btn plus"><i class="fas fa-plus"></i></button>
                    <button class="remove-btn"><i class="fas fa-times"></i></button>
                </div>
            `;
            cartItemsList.appendChild(li);
        });
    }
    
    document.getElementById('cart-total-price').textContent = cart.getTotalPrice().toFixed(2);
}

// Handle quantity changes and removal in cart modal
cartItemsList.addEventListener('click', (e) => {
    const listItem = e.target.closest('.cart-item');
    if (!listItem) return;
    
    const itemId = parseInt(listItem.dataset.id);
    const item = cart.items.find(i => i.menu_id === itemId);
    if (!item) return;
    
    if (e.target.classList.contains('minus') || e.target.closest('.minus')) {
        // Decrease quantity
        cart.updateQuantity(itemId, item.quantity - 1);
        
        // Update the quantity display in the cart modal
        const quantityDisplay = listItem.querySelector('.quantity');
        if (quantityDisplay && item.quantity > 0) {
            quantityDisplay.textContent = item.quantity;
        }
    } else if (e.target.classList.contains('plus') || e.target.closest('.plus')) {
        // Increase quantity
        cart.updateQuantity(itemId, item.quantity + 1);
        
        // Update the quantity display in the cart modal
        const quantityDisplay = listItem.querySelector('.quantity');
        if (quantityDisplay) {
            quantityDisplay.textContent = item.quantity;
        }
    } else if (e.target.classList.contains('remove-btn') || e.target.closest('.remove-btn')) {
        // Remove item completely
        cart.removeItem(itemId);
        updateCartModalContent();
    }
    
    // Update cart display
    document.getElementById('cart-total-price').textContent = cart.getTotalPrice().toFixed(2);
    
    // If cart is empty, update modal content
    if (cart.items.length === 0) {
        updateCartModalContent();
    }
});

// Place order button (footer)
placeOrderButton.addEventListener('click', () => {
    if (cart.getTotalItems() === 0) {
        showToast('Your cart is empty!');
        return;
    }
    
    // Check if user is logged in
    fetch('check_auth.php')
        .then(response => response.json())
        .then(data => {
            if (data.authenticated) {
                updateCartModalContent();
                cartModal.classList.remove('hidden');
            } else {
                // Redirect to login page
                window.location.href = 'login.html?redirect=menu.html';
            }
        })
        .catch(error => {
            console.error('Error checking authentication:', error);
            showToast('Error checking authentication status');
        });
});

// Close cart button (proceed to table booking)
closeCartButton.addEventListener('click', () => {
    if (cart.getTotalItems() === 0) {
        showToast('Your cart is empty!');
        return;
    }
    cartModal.classList.add('hidden'); // Close the cart modal
    
    // Set customer name if user is logged in
    fetch('check_auth.php')
        .then(response => response.json())
        .then(data => {
            if (data.authenticated && data.user_name) {
                customerNameInput.value = data.user_name;
            }
        })
        .catch(error => console.error('Error:', error));
    
    tableBookingCard.classList.remove('hidden'); // Open the table booking card
    initializeTableSelection(); // Initialize table selection
});

// Pay button (submit order and table booking)
payButton.addEventListener('click', () => {
    const selectedTable = document.querySelector('.table-button.selected');
    const customerName = customerNameInput.value.trim();
    
    if (!selectedTable) {
        showToast('Please select a table');
        return;
    }
    
    if (!customerName) {
        showToast('Please enter your name');
        return;
    }
    
    const tableNumber = selectedTable.dataset.table;
    const orderItems = cart.items.map(item => ({
        menu_item_id: item.menu_id,
        quantity: item.quantity,
        price: item.price
    }));
    console.log(tableNumber, customerName, orderItems)
    // Submit order to backend
    submitOrderToBackend(tableNumber, customerName, orderItems);
});

// Submit order to backend
function submitOrderToBackend(tableNumber, customerName, orderItems) {
    const orderData = {
        table_id: tableNumber,
        customer_name: customerName,
        items: cart.items.map(item => ({
            menu_item_id: item.menu_id,
            quantity: item.quantity,
            price: item.price
        })),
        total: cart.getTotalPrice()
    };

    // Store the total amount before clearing cart
    const totalAmount = cart.getTotalPrice().toFixed(2);
    
    fetch('place_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Store the order ID for reference
            const orderId = data.order_id;
            
            showToast('Order placed successfully!');
            
            // Clear the cart after storing necessary data
            cart.clearCart();
            tableBookingCard.classList.add('hidden');
            
            // Redirect to payment page with order information
            window.location.href = `qr.html?order_id=${orderId}&total=${totalAmount}&table=${tableNumber}`;
        } else {
            showToast(`Error: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Error placing order:', error);
        showToast('Failed to place order. Please try again.');
    });
}

// Search functionality
searchButton.addEventListener('click', performSearch);
searchBar.addEventListener('keyup', event => {
    if (event.key === 'Enter') {
        performSearch();
    }
});

function performSearch() {
    const searchTerm = searchBar.value.toLowerCase().trim();
    
    // First, set "All" category as active
    const allCategoryButton = categoryNav.querySelector('.category-button:first-child');
    setActiveCategoryButton(allCategoryButton);
    
    // Show all items when search is cleared
    if (searchTerm === '') {
        showAllCategories();
        return;
    }
    
    // Filter items based on search term
    const filteredItems = menuItems.filter(item => 
        item.name.toLowerCase().includes(searchTerm) ||
        item.category.toLowerCase().includes(searchTerm)
    );
    
    // Display search results
    scrollableContent.innerHTML = '';
    if (filteredItems.length > 0) {
        const searchHeading = document.createElement('h3');
        searchHeading.textContent = `Search Results: "${searchTerm}"`;
        scrollableContent.appendChild(searchHeading);
        
        displayMenuItems(filteredItems);
    } else {
        const noResults = document.createElement('div');
        noResults.className = 'no-results';
        noResults.textContent = `No items found matching "${searchTerm}"`;
        scrollableContent.appendChild(noResults);
    }
}

// Initialize the app
document.addEventListener('DOMContentLoaded', () => {
    checkAuthStatus();
    fetchMenuItems();
    cart.loadFromLocalStorage();
    initializeTableSelection();
});
