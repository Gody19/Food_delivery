<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodChap Admin | Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="../Assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../Assets/fontawesome/css/all.min.css">
    
     <link rel="stylesheet" href="css/style.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
</head>
<body>
    <!-- Sidebar -->
    <?php include 'include/aside.php'; ?>
    <!-- Main Content -->
    <div id="content">
        <!-- Top Navigation -->
        <?php include 'include/header.php'; ?>

        <!-- Dashboard Content (Default) -->
        <div class="content-container section-content active" id="dashboard-section">
            <div class="page-header">
                <div class="page-title">
                    <h1>Dashboard</h1>
                    <p>Welcome back, Admin! Here's what's happening with your business today.</p>
                </div>
                <div>
                    <span class="text-muted me-2">Last updated: Today 10:30 AM</span>
                    <button class="btn btn-admin btn-admin-primary">
                        <i class="fas fa-sync-alt me-1"></i> Refresh
                    </button>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stats-card primary">
                        <div class="stats-title">TOTAL REVENUE</div>
                        <div class="stats-value">TZS 8.4M</div>
                        <div class="stats-change">
                            <i class="fas fa-arrow-up me-1"></i> 12.5% from last month
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stats-card success">
                        <div class="stats-title">TOTAL ORDERS</div>
                        <div class="stats-value">1,248</div>
                        <div class="stats-change">
                            <i class="fas fa-arrow-up me-1"></i> 8.3% from last month
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stats-card warning">
                        <div class="stats-title">ACTIVE RESTAURANTS</div>
                        <div class="stats-value">42</div>
                        <div class="stats-change">
                            <i class="fas fa-arrow-up me-1"></i> 2 new this month
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-store"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stats-card danger">
                        <div class="stats-title">PENDING ORDERS</div>
                        <div class="stats-value">18</div>
                        <div class="stats-change">
                            <i class="fas fa-clock me-1"></i> Need attention
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts and Recent Orders -->
            <div class="row">
                <div class="col-xl-8 col-lg-7 mb-4">
                    <div class="dashboard-card">
                        <div class="card-header">
                            Revenue Overview
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-4 col-lg-5 mb-4">
                    <div class="dashboard-card">
                        <div class="card-header">
                            Order Status
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="orderStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customers Section -->
        <div class="content-container section-content" id="customers-section">
            <div class="page-header">
                <div class="page-title">
                    <h1>Customer Management</h1>
                    <p>View and manage customer accounts</p>
                </div>
                <div class="search-box" style="width: 300px;">
                    <input type="text" class="form-control form-control-admin" placeholder="Search customers...">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            
            <!-- Customers Table -->
            <div class="dashboard-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Customer ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Total Orders</th>
                                    <th>Total Spent</th>
                                    <th>Joined Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="customersTable">
                                <!-- Customers will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'include/footer.php'; ?>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="../Assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Sample data
        const orders = [
            {
                id: "ORD-1001",
                customer: "John Doe",
                restaurant: "Mama Ntilie Restaurant",
                items: 3,
                amount: "TZS 54,870",
                status: "pending",
                date: "2023-11-15 10:30",
                customerPhone: "+255 712 345 678",
                customerEmail: "john@example.com",
                customerAddress: "123 Street, Dar es Salaam",
                paymentMethod: "M-Pesa"
            },
            {
                id: "ORD-1002",
                customer: "Aisha Kimaro",
                restaurant: "Burger King Dar",
                items: 2,
                amount: "TZS 24,500",
                status: "confirmed",
                date: "2023-11-15 09:15",
                customerPhone: "+255 713 987 654",
                customerEmail: "aisha@example.com",
                customerAddress: "456 Avenue, Arusha",
                paymentMethod: "Tigo Pesa"
            },
            {
                id: "ORD-1003",
                customer: "David Kato",
                restaurant: "Tokyo Sushi Lounge",
                items: 4,
                amount: "TZS 68,200",
                status: "preparing",
                date: "2023-11-15 08:45",
                customerPhone: "+255 714 567 890",
                customerEmail: "david@example.com",
                customerAddress: "789 Road, Dodoma",
                paymentMethod: "Airtel Money"
            },
            {
                id: "ORD-1004",
                customer: "Joseph Mwambene",
                restaurant: "Mexican Grill Arusha",
                items: 1,
                amount: "TZS 12,800",
                status: "delivered",
                date: "2023-11-14 19:20",
                customerPhone: "+255 715 123 456",
                customerEmail: "joseph@example.com",
                customerAddress: "321 Boulevard, Mwanza",
                paymentMethod: "CRDB Bank"
            },
            {
                id: "ORD-1005",
                customer: "Sarah Juma",
                restaurant: "Mama Ntilie Restaurant",
                items: 3,
                amount: "TZS 42,300",
                status: "cancelled",
                date: "2023-11-14 17:50",
                customerPhone: "+255 716 654 321",
                customerEmail: "sarah@example.com",
                customerAddress: "654 Lane, Zanzibar",
                paymentMethod: "M-Pesa"
            }
        ];

        const restaurants = [
            {
                id: 1,
                name: "Mama Ntilie Restaurant",
                cuisine: "Traditional Tanzanian",
                orders: 248,
                rating: 4.7,
                status: "active",
                revenue: "TZS 4.2M"
            },
            {
                id: 2,
                name: "Burger King Dar",
                cuisine: "Fast Food",
                orders: 312,
                rating: 4.5,
                status: "active",
                revenue: "TZS 5.1M"
            },
            {
                id: 3,
                name: "Tokyo Sushi Lounge",
                cuisine: "Japanese",
                orders: 189,
                rating: 4.8,
                status: "active",
                revenue: "TZS 3.8M"
            },
            {
                id: 4,
                name: "Mexican Grill Arusha",
                cuisine: "Mexican",
                orders: 156,
                rating: 4.4,
                status: "active",
                revenue: "TZS 2.9M"
            },
            {
                id: 5,
                name: "Pizza Palace",
                cuisine: "Italian",
                orders: 278,
                rating: 4.6,
                status: "inactive",
                revenue: "TZS 4.5M"
            },
            {
                id: 6,
                name: "Seafood Delight",
                cuisine: "Seafood",
                orders: 134,
                rating: 4.3,
                status: "active",
                revenue: "TZS 3.1M"
            }
        ];

        const menuItems = [
            {
                id: 101,
                name: "Nyama Choma Special",
                restaurant: "Mama Ntilie Restaurant",
                category: "Traditional",
                price: "TZS 18,000",
                status: "available"
            },
            {
                id: 102,
                name: "Cheeseburger Deluxe",
                restaurant: "Burger King Dar",
                category: "Main Course",
                price: "TZS 12,000",
                status: "available"
            },
            {
                id: 103,
                name: "California Roll",
                restaurant: "Tokyo Sushi Lounge",
                category: "Main Course",
                price: "TZS 15,000",
                status: "available"
            },
            {
                id: 104,
                name: "Chicken Tacos",
                restaurant: "Mexican Grill Arusha",
                category: "Main Course",
                price: "TZS 10,000",
                status: "available"
            },
            {
                id: 105,
                name: "Wali na Maharage",
                restaurant: "Mama Ntilie Restaurant",
                category: "Traditional",
                price: "TZS 8,000",
                status: "available"
            },
            {
                id: 106,
                name: "Pepperoni Pizza",
                restaurant: "Pizza Palace",
                category: "Main Course",
                price: "TZS 14,000",
                status: "unavailable"
            }
        ];

        const customers = [
            {
                id: "CUST-1001",
                name: "John Doe",
                email: "john@example.com",
                phone: "+255 712 345 678",
                orders: 24,
                spent: "TZS 480,500",
                joined: "2023-01-15"
            },
            {
                id: "CUST-1002",
                name: "Aisha Kimaro",
                email: "aisha@example.com",
                phone: "+255 713 987 654",
                orders: 18,
                spent: "TZS 320,800",
                joined: "2023-02-20"
            },
            {
                id: "CUST-1003",
                name: "David Kato",
                email: "david@example.com",
                phone: "+255 714 567 890",
                orders: 32,
                spent: "TZS 610,200",
                joined: "2023-01-05"
            },
            {
                id: "CUST-1004",
                name: "Joseph Mwambene",
                email: "joseph@example.com",
                phone: "+255 715 123 456",
                orders: 12,
                spent: "TZS 195,400",
                joined: "2023-03-10"
            },
            {
                id: "CUST-1005",
                name: "Sarah Juma",
                email: "sarah@example.com",
                phone: "+255 716 654 321",
                orders: 8,
                spent: "TZS 142,600",
                joined: "2023-04-15"
            }
        ];

        // DOM elements
        const sidebar = document.getElementById('sidebar');
        const content = document.getElementById('content');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebarItems = document.querySelectorAll('.sidebar-item');
        const sectionContents = document.querySelectorAll('.section-content');
        const adminTabs = document.querySelectorAll('.admin-tab');
        const pendingOrdersBadge = document.getElementById('pendingOrdersBadge');
        const recentOrdersTable = document.getElementById('recentOrdersTable');
        const ordersTable = document.getElementById('ordersTable');
        const restaurantsGrid = document.getElementById('restaurantsGrid');
        const menuItemsTable = document.getElementById('menuItemsTable');
        const customersTable = document.getElementById('customersTable');
        const viewAllOrdersBtn = document.querySelector('.view-all-orders');
        const logoutBtn = document.getElementById('logoutBtn');

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize charts
            initRevenueChart();
            initOrderStatusChart();
            
            // Load data
            loadRecentOrders();
            loadOrders();
            loadRestaurants();
            loadMenuItems();
            loadCustomers();
            
            // Update pending orders badge
            updatePendingOrdersBadge();
            
            // Set up event listeners
            setupEventListeners();
        });

        // Set up event listeners
        function setupEventListeners() {
            // Sidebar toggle
            sidebarToggle.addEventListener('click', toggleSidebar);
            
            // Sidebar navigation
            sidebarItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all items
                    sidebarItems.forEach(i => i.classList.remove('active'));
                    
                    // Add active class to clicked item
                    this.classList.add('active');
                    
                    // Show corresponding section
                    const section = this.getAttribute('data-section');
                    showSection(section);
                    
                    // Close sidebar on mobile
                    if (window.innerWidth <= 768) {
                        toggleSidebar();
                    }
                });
            });
            
            // Admin tabs
            adminTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    adminTabs.forEach(t => t.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Filter orders by status
                    const status = this.getAttribute('data-status');
                    filterOrdersByStatus(status);
                });
            });
            
            // View all orders button
            viewAllOrdersBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Switch to orders section
                sidebarItems.forEach(i => i.classList.remove('active'));
                document.querySelector('.sidebar-item[data-section="orders"]').classList.add('active');
                showSection('orders');
            });
            
            // Logout button
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to logout?')) {
                    window.location.href = 'index.html'; // Redirect to main site
                }
            });
        }

        // Toggle sidebar on mobile
        function toggleSidebar() {
            sidebar.classList.toggle('active');
            content.classList.toggle('active');
        }

        // Show section content
        function showSection(sectionId) {
            // Hide all sections
            sectionContents.forEach(section => {
                section.classList.remove('active');
            });
            
            // Show selected section
            document.getElementById(`${sectionId}-section`).classList.add('active');
            
            // Update page title if needed
            updatePageTitle(sectionId);
        }

        // Update page title
        function updatePageTitle(sectionId) {
            const titles = {
                'dashboard': 'Dashboard',
                'orders': 'Order Management',
                'restaurants': 'Restaurant Management',
                'menu': 'Menu Items Management',
                'customers': 'Customer Management',
                'delivery': 'Delivery Management',
                'payments': 'Payment Management',
                'analytics': 'Analytics',
                'settings': 'Settings'
            };
            
            const pageTitle = document.querySelector('.page-title h1');
            if (pageTitle && titles[sectionId]) {
                pageTitle.textContent = titles[sectionId];
            }
        }

        // Initialize revenue chart
        function initRevenueChart() {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Revenue (TZS)',
                        data: [5200000, 5800000, 6200000, 7100000, 6900000, 7500000, 8200000, 7800000, 8400000, 9200000, 8800000, 9500000],
                        borderColor: 'rgb(78, 115, 223)',
                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'TZS ' + (value / 1000000) + 'M';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Initialize order status chart
        function initOrderStatusChart() {
            const ctx = document.getElementById('orderStatusChart').getContext('2d');
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Delivered', 'Preparing', 'Confirmed', 'Pending', 'Cancelled'],
                    datasets: [{
                        data: [45, 20, 15, 12, 8],
                        backgroundColor: [
                            'rgb(28, 200, 138)',
                            'rgb(78, 115, 223)',
                            'rgb(54, 185, 204)',
                            'rgb(246, 194, 62)',
                            'rgb(231, 74, 59)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Load recent orders
        function loadRecentOrders() {
            recentOrdersTable.innerHTML = '';
            
            orders.slice(0, 5).forEach(order => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><strong>${order.id}</strong></td>
                    <td>${order.customer}</td>
                    <td>${order.restaurant}</td>
                    <td>${order.amount}</td>
                    <td><span class="status-badge status-${order.status}">${formatStatus(order.status)}</span></td>
                    <td>${formatDate(order.date)}</td>
                    <td>
                        <button class="btn btn-admin btn-admin-primary btn-sm view-order" data-id="${order.id}">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                `;
                recentOrdersTable.appendChild(row);
            });
            
            // Add event listeners to view order buttons
            document.querySelectorAll('.view-order').forEach(button => {
                button.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-id');
                    showOrderDetails(orderId);
                });
            });
        }

        // Load all orders
        function loadOrders() {
            ordersTable.innerHTML = '';
            
            orders.forEach(order => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><strong>${order.id}</strong></td>
                    <td>${order.customer}</td>
                    <td>${order.restaurant}</td>
                    <td>${order.items} items</td>
                    <td>${order.amount}</td>
                    <td><span class="status-badge status-${order.status}">${formatStatus(order.status)}</span></td>
                    <td>${formatDate(order.date)}</td>
                    <td>
                        <button class="btn btn-admin btn-admin-primary btn-sm view-order me-1" data-id="${order.id}">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-admin btn-admin-success btn-sm update-order" data-id="${order.id}">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                `;
                ordersTable.appendChild(row);
            });
            
            // Add event listeners
            document.querySelectorAll('.view-order').forEach(button => {
                button.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-id');
                    showOrderDetails(orderId);
                });
            });
        }

        // Filter orders by status
        function filterOrdersByStatus(status) {
            const rows = ordersTable.querySelectorAll('tr');
            
            rows.forEach(row => {
                if (status === 'all') {
                    row.style.display = '';
                } else {
                    const statusBadge = row.querySelector('.status-badge');
                    if (statusBadge && statusBadge.classList.contains(`status-${status}`)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
        }

        // Load restaurants
        function loadRestaurants() {
            restaurantsGrid.innerHTML = '';
            
            restaurants.forEach(restaurant => {
                const col = document.createElement('div');
                col.className = 'col-xl-4 col-md-6 mb-4';
                col.innerHTML = `
                    <div class="dashboard-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title mb-1">${restaurant.name}</h5>
                                    <p class="text-muted mb-0">${restaurant.cuisine}</p>
                                </div>
                                <span class="badge ${restaurant.status === 'active' ? 'bg-success' : 'bg-warning'}">${restaurant.status}</span>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="text-muted small">Total Orders</div>
                                    <div class="fw-bold">${restaurant.orders}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small">Total Revenue</div>
                                    <div class="fw-bold">${restaurant.revenue}</div>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center mb-3">
                                <div class="text-warning me-2">
                                    ${generateStarRating(restaurant.rating)}
                                </div>
                                <span class="text-muted">${restaurant.rating}/5.0</span>
                            </div>
                            
                            <div class="d-flex">
                                <button class="btn btn-admin btn-admin-primary btn-sm me-2 flex-fill">View Details</button>
                                <button class="btn btn-admin ${restaurant.status === 'active' ? 'btn-admin-warning' : 'btn-admin-success'} btn-sm flex-fill">
                                    ${restaurant.status === 'active' ? 'Deactivate' : 'Activate'}
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                restaurantsGrid.appendChild(col);
            });
        }

        // Load menu items
        function loadMenuItems() {
            menuItemsTable.innerHTML = '';
            
            menuItems.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.id}</td>
                    <td><strong>${item.name}</strong></td>
                    <td>${item.restaurant}</td>
                    <td>${item.category}</td>
                    <td>${item.price}</td>
                    <td><span class="badge ${item.status === 'available' ? 'bg-success' : 'bg-warning'}">${item.status}</span></td>
                    <td>
                        <button class="btn btn-admin btn-admin-primary btn-sm me-1">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-admin btn-admin-danger btn-sm">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                menuItemsTable.appendChild(row);
            });
        }

        // Load customers
        function loadCustomers() {
            customersTable.innerHTML = '';
            
            customers.forEach(customer => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><strong>${customer.id}</strong></td>
                    <td>${customer.name}</td>
                    <td>${customer.email}</td>
                    <td>${customer.phone}</td>
                    <td>${customer.orders}</td>
                    <td>${customer.spent}</td>
                    <td>${formatDate(customer.joined)}</td>
                    <td>
                        <button class="btn btn-admin btn-admin-primary btn-sm me-1">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-admin btn-admin-warning btn-sm">
                            <i class="fas fa-envelope"></i>
                        </button>
                    </td>
                `;
                customersTable.appendChild(row);
            });
        }

        // Update pending orders badge
        function updatePendingOrdersBadge() {
            const pendingOrders = orders.filter(order => order.status === 'pending').length;
            pendingOrdersBadge.textContent = pendingOrders;
        }

        // Show order details modal
        function showOrderDetails(orderId) {
            const order = orders.find(o => o.id === orderId);
            if (!order) return;
            
            // Populate modal with order details
            document.getElementById('orderIdDisplay').textContent = orderId.replace('ORD-', '');
            document.getElementById('customerName').textContent = order.customer;
            document.getElementById('customerPhone').textContent = order.customerPhone;
            document.getElementById('customerEmail').textContent = order.customerEmail;
            document.getElementById('customerAddress').textContent = order.customerAddress;
            document.getElementById('orderTime').textContent = formatDate(order.date, true);
            document.getElementById('orderRestaurant').textContent = order.restaurant;
            document.getElementById('paymentMethod').textContent = order.paymentMethod;
            
            // Update status badge
            const statusBadge = document.getElementById('orderStatus');
            statusBadge.className = `status-badge status-${order.status}`;
            statusBadge.textContent = formatStatus(order.status);
            
            // Set status in select
            document.getElementById('updateOrderStatus').value = order.status;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
            modal.show();
        }

        // Helper functions
        function formatStatus(status) {
            const statusMap = {
                'pending': 'Pending',
                'confirmed': 'Confirmed',
                'preparing': 'Preparing',
                'delivered': 'Delivered',
                'cancelled': 'Cancelled'
            };
            return statusMap[status] || status;
        }

        function formatDate(dateString, includeTime = false) {
            const date = new Date(dateString);
            if (includeTime) {
                return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            }
            return date.toLocaleDateString();
        }

        function generateStarRating(rating) {
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= Math.floor(rating)) {
                    stars += '<i class="fas fa-star"></i>';
                } else if (i - 0.5 <= rating) {
                    stars += '<i class="fas fa-star-half-alt"></i>';
                } else {
                    stars += '<i class="far fa-star"></i>';
                }
            }
            return stars;
        }

        // Initialize modals
        const addRestaurantModal = new bootstrap.Modal(document.getElementById('addRestaurantModal'));
        const addMenuItemModal = new bootstrap.Modal(document.getElementById('addMenuItemModal'));
        const orderDetailsModal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    </script>
</body>
</html>