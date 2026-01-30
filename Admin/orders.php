<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders management</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="../Assets/bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../Assets/fontawesome/css/all.min.css">
</head>

<body>
    <?php include 'include/aside.php'; ?>
    <div id="content">
        <?php include 'include/header.php'; ?>
        <div class="main-content">
            <div class="page-header">
                <h1 class="h3 mb-0">Orders Management</h1>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="dashboard-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Recent Orders</span>
                            <a href="#orders" class="btn btn-admin btn-admin-primary btn-sm view-all-orders">
                                View All
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Customer</th>
                                            <th>Restaurant</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recentOrdersTable">
                                        <!-- Recent orders will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Orders Section -->
        <div class="content-container section-content" id="orders-section">
            <div class="page-header">
                <div class="page-title">
                    <h1>Order Management</h1>
                    <p>View and manage all customer orders</p>
                </div>
                <div class="search-box" style="width: 300px;">
                    <input type="text" class="form-control form-control-admin" placeholder="Search orders...">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            
            <!-- Order Tabs -->
            <div class="admin-tabs">
                <button class="admin-tab active" data-status="all">All Orders</button>
                <button class="admin-tab" data-status="pending">Pending</button>
                <button class="admin-tab" data-status="confirmed">Confirmed</button>
                <button class="admin-tab" data-status="preparing">Preparing</button>
                <button class="admin-tab" data-status="delivered">Delivered</button>
                <button class="admin-tab" data-status="cancelled">Cancelled</button>
            </div>
            
            <!-- Orders Table -->
            <div class="dashboard-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Restaurant</th>
                                    <th>Items</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Order Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="ordersTable">
                                <!-- Orders will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>Showing 1 to 10 of 1248 orders</div>
                        <nav>
                            <ul class="pagination mb-0">
                                <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item"><a class="page-link" href="#">Next</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../Assets/jquery/jquery.min.js"></script>
</body>

</html>