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
        <!-- Order Details Modal -->
        <div class="modal fade admin-modal" id="orderDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Order Details - #ORD-<span id="orderIdDisplay"></span></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>Customer Information</h6>
                                <p class="mb-1"><strong>Name:</strong> <span id="customerName">John Doe</span></p>
                                <p class="mb-1"><strong>Phone:</strong> <span id="customerPhone">+255 712 345 678</span></p>
                                <p class="mb-1"><strong>Email:</strong> <span id="customerEmail">john@example.com</span></p>
                                <p class="mb-0"><strong>Address:</strong> <span id="customerAddress">123 Street, Dar es Salaam</span></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Order Information</h6>
                                <p class="mb-1"><strong>Order Time:</strong> <span id="orderTime">Today, 10:30 AM</span></p>
                                <p class="mb-1"><strong>Restaurant:</strong> <span id="orderRestaurant">Mama Ntilie Restaurant</span></p>
                                <p class="mb-1"><strong>Payment Method:</strong> <span id="paymentMethod">M-Pesa</span></p>
                                <p class="mb-0"><strong>Status:</strong> <span id="orderStatus" class="status-badge status-pending">Pending</span></p>
                            </div>
                        </div>
                        
                        <div class="order-details-card">
                            <h6>Order Items</h6>
                            <div class="order-item">
                                <div>Nyama Choma Special x2</div>
                                <div>TZS 36,000</div>
                            </div>
                            <div class="order-item">
                                <div>Wali na Maharage x1</div>
                                <div>TZS 8,000</div>
                            </div>
                            <div class="order-item">
                                <div>Delivery Fee</div>
                                <div>TZS 2,500</div>
                            </div>
                            <div class="order-item">
                                <div>Tax (18%)</div>
                                <div>TZS 8,370</div>
                            </div>
                            <div class="order-item">
                                <div><strong>Total Amount</strong></div>
                                <div><strong>TZS 54,870</strong></div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Update Order Status</label>
                            <select class="form-control form-control-admin" id="updateOrderStatus">
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="preparing">Preparing</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea class="form-control form-control-admin" rows="2" placeholder="Add notes about this order..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-admin btn-admin-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-admin btn-admin-primary">Update Order</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../Assets/jquery/jquery.min.js"></script>
</body>

</html>