<div id="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-utensils"></i> FoodChap Admin
    </div>

    <div class="sidebar-divider"></div>

    <nav class="sidebar-nav">
        <a href="dashboard.php" class="sidebar-item active" data-section="dashboard">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <?php
        include '../config/connection.php';
        $sql = "SELECT COUNT(*) AS pending_count FROM orders WHERE status='pending'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        ?>
        <a href="orders.php" class="sidebar-item" data-section="orders">
            <i class="fas fa-shopping-cart"></i> Orders
            <span class="badge bg-danger float-end mt-1" id="pendingOrdersBadge"><?php echo $row['pending_count']; ?></span>
        </a>

        <a href="restaurant.php" class="sidebar-item" data-section="restaurants">
            <i class="fas fa-store"></i> Restaurants
        </a>

        <a href="menu_item.php" class="sidebar-item" data-section="menu">
            <i class="fas fa-utensils"></i> Menu Items
        </a>

        <a href="users.php" class="sidebar-item" data-section="customers">
            <i class="fas fa-users"></i> Customers
        </a>

        <a href="#delivery" class="sidebar-item d-none" data-section="delivery">
            <i class="fas fa-motorcycle"></i> Delivery
        </a>

        <a href="messages.php" class="sidebar-item" data-section="messages">
            <i class="fas fa-envelope"></i> Messages
        </a>

        <a href="#analytics" class="sidebar-item d-none" data-section="analytics">
            <i class="fas fa-chart-line"></i> Analytics
        </a>

        <div class="sidebar-divider"></div>

        <a href="#settings " class="sidebar-item d-none" data-section="settings">
            <i class="fas fa-cog"></i> Settings
        </a>

        <a href="include/logout.php" class="sidebar-item" id="logoutBtn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>
</div>