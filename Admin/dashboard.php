<?php
include 'include/check_login.php';
include '../config/connection.php';
// Today revenue
$q_today = $conn->query("
    SELECT IFNULL(SUM(total_amount),0) AS today_revenue
    FROM orders
    WHERE DATE(inserted_at) = CURDATE()
");
$today = $q_today->fetch_assoc();

// This month revenue
$q_this = $conn->query("
    SELECT IFNULL(SUM(total_amount),0) AS total
    FROM orders
    WHERE MONTH(inserted_at)=MONTH(CURDATE())
    AND YEAR(inserted_at)=YEAR(CURDATE())
");
$this_month = $q_this->fetch_assoc()['total'];

// Last month revenue
$q_last = $conn->query("
    SELECT IFNULL(SUM(total_amount),0) AS total
    FROM orders
    WHERE MONTH(inserted_at)=MONTH(CURDATE()-INTERVAL 1 MONTH)
    AND YEAR(inserted_at)=YEAR(CURDATE()-INTERVAL 1 MONTH)
");
$last_month = $q_last->fetch_assoc()['total'];

// Growth %
$growth = ($last_month > 0)
    ? (($this_month - $last_month) / $last_month) * 100
    : 0;

// Top restaurant
/* $topRestaurant = $conn->query("
SELECT r.restaurant_name,
SUM(oi.quantity * oi.price) revenue
FROM order_items oi
JOIN menu_items m ON oi.menu_item_id=m.id
JOIN restaurants r ON m.restaurant_id=r.id
GROUP BY r.id
ORDER BY revenue DESC
LIMIT 1
")->fetch_assoc();
 */
// Best selling item
$q_best_item = $conn->query("
    SELECT m.item_name, SUM(oi.quantity) AS sold
    FROM order_items oi
    JOIN menu_items m ON oi.menu_item_id = m.id
    GROUP BY m.id
    ORDER BY sold DESC
    LIMIT 1
");
$best_item = $q_best_item->fetch_assoc();

// Total orders
$total_orders = $conn->query("SELECT COUNT(*) c FROM orders")->fetch_assoc()['c'];

// Restaurants
$total_restaurants = $conn->query("SELECT COUNT(*) c FROM restaurants")->fetch_assoc()['c'];

// Pending orders
$pending_orders = $conn->query("SELECT COUNT(*) c FROM orders WHERE status='pending'")->fetch_assoc()['c'];

function lastUpdated()
{
    date_default_timezone_set('Africa/Dar_es_Salaam'); // Tanzania timezone
    return "Today " . date("l, d M Y h:i A");
}

?>

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
    <style>
        .live-clock {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 8px 16px;
            background: linear-gradient(135deg, #2d3748, #4a5568);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            min-width: 180px;
            font-family: 'Segoe UI', -apple-system, system-ui, sans-serif;
        }

        .clock-time {
            font-size: 1.5rem;
            font-weight: 600;
            color: #ffffff;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
        }

        #clockHours,
        #clockMinutes,
        #clockSeconds {
            font-family: 'Courier New', monospace;
            background: rgba(0, 0, 0, 0.3);
            padding: 2px 6px;
            border-radius: 4px;
            min-width: 32px;
            text-align: center;
            display: inline-block;
        }

        #clockSeconds {
            color: #68d391;
            font-weight: 700;
            animation: pulse 1s infinite;
        }

        .clock-colon {
            margin: 0 2px;
            color: #a0aec0;
            font-weight: 400;
        }

        .clock-day {
            font-size: 0.875rem;
            color: #cbd5e0;
            margin-top: 2px;
            display: flex;
            align-items: center;
        }

        #clockDay {
            font-weight: 500;
        }

        .clock-date {
            color: #a0aec0;
            margin-left: 4px;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.8;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .live-clock {
                min-width: 160px;
                padding: 6px 12px;
            }

            .clock-time {
                font-size: 1.25rem;
            }

            #clockHours,
            #clockMinutes,
            #clockSeconds {
                min-width: 28px;
                padding: 1px 4px;
            }
        }
    </style>
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

                <div class="d-flex align-items-center gap-3">
                    <!-- Live Clock -->
                    <div id="liveClock" class="live-clock">
                        <div class="clock-time">
                            <span id="clockHours">00</span>
                            <span class="clock-colon">:</span>
                            <span id="clockMinutes">00</span>
                            <span class="clock-colon">:</span>
                            <span id="clockSeconds">00</span>
                        </div>
                        <div class="clock-day">
                            <span id="clockDay">Monday</span>
                            <span class="clock-date">, <span id="clockDate">01 Jan</span></span>
                        </div>
                    </div>

                </div>

            </div>

            <div class="dashboard-intro mb-4">
                <p>Here's a quick overview of your restaurant's performance. Use the sidebar to navigate through different sections and manage your business effectively.</p>
            </div>

        </div>

        <!-- Stats Cards -->
        <div class="row">

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="stats-card primary">
                    <div class="stats-title">TODAY REVENUE</div>
                    <div class="stats-value">
                        TZS <?= number_format($today['today_revenue']) ?>
                    </div>
                    <div class="stats-change">
                        <i class="fas fa-calendar-day"></i> Today
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="stats-card success">
                    <div class="stats-title">MONTHLY GROWTH</div>
                    <div class="stats-value"><?= round($growth, 1) ?>%</div>
                    <div class="stats-change">
                        Compared to last month
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4 d-none">
                <div class="stats-card warning">
                    <div class="stats-title">TOP RESTAURANT</div>
                    <div class="stats-value">
                        <?= $top_restaurant['restaurant_name'] ?? 'N/A' ?>
                    </div>
                    <div class="stats-change">Highest revenue</div>
                    <div class="stats-icon">
                        <i class="fas fa-crown"></i>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="stats-card danger">
                    <div class="stats-title">BEST SELLING ITEM</div>
                    <div class="stats-value">
                        <?= $best_item['item_name'] ?? 'N/A' ?>
                    </div>
                    <div class="stats-change">Most ordered</div>
                    <div class="stats-icon">
                        <i class="fas fa-fire"></i>
                    </div>
                </div>
            </div>

        </div>


        <div class="row">

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="stats-card primary">
                    <div class="stats-title">TOTAL ORDERS</div>
                    <div class="stats-value"><?= number_format($total_orders) ?></div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="stats-card success">
                    <div class="stats-title">RESTAURANTS</div>
                    <div class="stats-value"><?= number_format($total_restaurants) ?></div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="stats-card danger">
                    <div class="stats-title">PENDING ORDERS</div>
                    <div class="stats-value"><?= number_format($pending_orders) ?></div>
                </div>
            </div>

        </div>

        <!-- Charts and Recent Orders -->
        <div class="row d-none">
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
    <?php include 'include/footer.php'; ?>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="../Assets/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script>
        // Initialize clock immediately
        function updateClock() {
            const now = new Date();

            // Get time components
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');

            // Update time display
            document.getElementById('clockHours').textContent = hours;
            document.getElementById('clockMinutes').textContent = minutes;
            document.getElementById('clockSeconds').textContent = seconds;

            // Get day and date
            const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            const dayName = days[now.getDay()];
            const date = now.getDate();
            const monthName = months[now.getMonth()];

            // Update day and date display
            document.getElementById('clockDay').textContent = dayName;
            document.getElementById('clockDate').textContent = `${date} ${monthName}`;

            // Add subtle visual feedback on seconds change
            const secondsElement = document.getElementById('clockSeconds');
            secondsElement.style.transform = 'scale(1.1)';
            setTimeout(() => {
                secondsElement.style.transform = 'scale(1)';
            }, 100);
        }

        // Set initial time
        updateClock();

        // Update every second
        setInterval(updateClock, 1000);

        // Optional: Add smooth fade-in
        document.addEventListener('DOMContentLoaded', function() {
            const clock = document.getElementById('liveClock');
            clock.style.opacity = '0';
            clock.style.transition = 'opacity 0.5s ease';

            setTimeout(() => {
                clock.style.opacity = '1';
            }, 50);
        });
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
                return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                });
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