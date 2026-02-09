<?php
include 'include/check_login.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management</title>
    <!-- Bootstrap CSS -->
    <link href="../Assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../Assets/fontawesome/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-dark: #3a56d4;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --border-color: #dee2e6;
            --shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            --radius: 12px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fb;
            color: #333;
            margin: 0;
            padding: 0;
        }

        #content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        @media (max-width: 992px) {
            #content {
                margin-left: 0;
                padding: 15px;
            }
        }

        .page-header {
            background: white;
            border-radius: var(--radius);
            padding: 20px 25px;
            margin-bottom: 25px;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary-color);
        }

        .page-header h1 {
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 5px;
            font-size: 28px;
        }

        .dashboard-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 25px;
            overflow: hidden;
            border: none;
        }

        .dashboard-card .card-header {
            background: var(--light-color);
            border-bottom: 1px solid var(--border-color);
            padding: 18px 25px;
            font-weight: 600;
            font-size: 16px;
            color: var(--dark-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dashboard-card .card-body {
            padding: 0;
        }

        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: var(--light-color);
            border-bottom: 2px solid var(--border-color);
            padding: 16px 20px;
            font-weight: 600;
            color: var(--dark-color);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            padding: 16px 20px;
            vertical-align: middle;
            border-bottom: 1px solid #eee;
            color: #555;
            font-size: 14px;
        }

        .table tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .status-badge {
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 500;
            border-radius: 20px;
            text-transform: uppercase;
            display: inline-block;
        }

        .status-pending { background-color: var(--warning-color); color: #212529; }
        .status-confirmed { background-color: var(--info-color); color: white; }
        .status-preparing { background-color: #4361ee; color: white; }
        .status-delivered { background-color: var(--success-color); color: white; }
        .status-cancelled { background-color: var(--danger-color); color: white; }

        .btn-admin {
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-admin-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-admin-primary:hover {
            background: var(--primary-dark);
        }

        .btn-sm {
            padding: 5px 15px;
            font-size: 13px;
            border-radius: 6px;
        }

        .admin-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 25px;
            flex-wrap: wrap;
            background: white;
            padding: 15px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .admin-tab {
            padding: 10px 20px;
            background: var(--light-color);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--secondary-color);
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .admin-tab:hover {
            background: #e9ecef;
        }

        .admin-tab.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .search-box {
            position: relative;
            width: 300px;
        }

        .search-box input {
            padding-left: 40px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            height: 40px;
            width: 100%;
        }

        .admin-modal .modal-content {
            border-radius: var(--radius);
            border: none;
        }

        .admin-modal .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            border-bottom: none;
            padding: 20px 30px;
        }

        .admin-modal .modal-body {
            padding: 30px;
        }

        .order-details-card {
            background: var(--light-color);
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .form-control-admin {
            border-radius: 8px;
            border: 1px solid var(--border-color);
            padding: 10px 15px;
            font-size: 14px;
            width: 100%;
        }

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-left: 4px solid var(--primary-color);
        }

        .toast-success {
            border-left-color: var(--success-color);
        }

        .toast-error {
            border-left-color: var(--danger-color);
        }
    </style>
</head>
<body>
    <?php include 'include/header.php';?>
    <?php include 'include/aside.php'; ?>
    <div id="content">
        <!-- Recent Orders Card -->
        <div class="dashboard-card">
            <div class="card-header">
                <span>Recent Orders</span>
                <a href="#orders-section" class="btn-admin btn-admin-primary btn-sm view-all-orders">
                    View All
                </a>
            </div>
            <?php
            include '../config/connection.php';
            
            $sql = "SELECT  o.id, o.total_amount, o.status, o.inserted_at, u.username, r.restaurant_name
            FROM orders o JOIN users u ON o.user_id = u.id JOIN order_items oi ON o.id = oi.order_id JOIN menu_items m ON oi.menu_item_id = m.id JOIN restaurants r ON m.restaurant_id = r.id
            GROUP BY o.id ORDER BY o.inserted_at DESC LIMIT 5";
            
            $result = $conn->query($sql);
            ?>
            
            <div class="card-body">
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
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $statusClass = 'status-badge ';
                                    switch(strtolower($row['status'])) {
                                        case 'pending': $statusClass .= 'status-pending'; break;
                                        case 'confirmed': $statusClass .= 'status-confirmed'; break;
                                        case 'preparing': $statusClass .= 'status-preparing'; break;
                                        case 'delivered': $statusClass .= 'status-delivered'; break;
                                        case 'cancelled': $statusClass .= 'status-cancelled'; break;
                                        default: $statusClass .= 'bg-secondary';
                                    }
                            ?>
                            <tr>
                                <td><strong>#ORD-<?php echo $row['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo isset($row['restaurant_name']) ? htmlspecialchars($row['restaurant_name']) : 'N/A'; ?></td>
                                <td>TZS <?php echo number_format($row['total_amount']); ?></td>
                                <td>
                                    <span class="<?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($row['inserted_at'])); ?></td>
                                <td>
                                    <button class="btn-admin btn-admin-primary btn-sm view-order-btn" 
                                            data-order-id="<?php echo $row['id']; ?>"
                                            data-order-data='<?php echo json_encode($row); ?>'>
                                        View
                                    </button>
                                </td>
                            </tr>
                            <?php
                                }
                            } else {
                                echo '<tr><td colspan="7" class="text-center">No recent orders found</td></tr>';
                            }
                            $conn->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Toast Container for Notifications -->
        <div class="toast-container"></div>

        <!-- Order Details Modal -->
        <div class="modal fade admin-modal" id="orderDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Order Details - <span id="modalOrderId">#ORD-0000</span></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="updateOrderForm">
                        <div class="modal-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6>Customer Information</h6>
                                    <p class="mb-1"><strong>Name:</strong> <span id="customerName">Loading...</span></p>
                                    <p class="mb-1"><strong>Phone:</strong> <span id="customerPhone">Loading...</span></p>
                                    <p class="mb-1"><strong>Email:</strong> <span id="customerEmail">Loading...</span></p>
                                    <p class="mb-0"><strong>Address:</strong> <span id="customerAddress">Loading...</span></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Order Information</h6>
                                    <p class="mb-1"><strong>Order Time:</strong> <span id="orderTime">Loading...</span></p>
                                    <p class="mb-1"><strong>Restaurant:</strong> <span id="orderRestaurant">Loading...</span></p>
                                    <p class="mb-1"><strong>Payment Method:</strong> <span id="paymentMethod">Loading...</span></p>
                                    <p class="mb-0"><strong>Status:</strong> 
                                        <span id="currentStatus" class="status-badge">Loading</span>
                                    </p>
                                </div>
                            </div>

                            <div class="order-details-card">
                                <h6>Order Items</h6>
                                <div id="orderItemsContainer">
                                    <!-- Order items will be loaded here -->
                                    <div class="text-center py-3">
                                        <div class="spinner-border spinner-border-sm" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 mb-0">Loading order items...</p>
                                    </div>
                                </div>
                                <div class="order-item">
                                    <div><strong>Total Amount</strong></div>
                                    <div><strong id="orderTotalAmount">TZS 0</strong></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><strong>Update Order Status</strong></label>
                                <div class="row">
                                    <div class="col-md-8">
                                        <select class="form-control-admin" id="updateOrderStatus" name="status" required>
                                            <option value="">Select Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="confirmed">Confirmed</option>
                                            <option value="preparing">Preparing</option>
                                            <option value="delivered">Delivered</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-control-admin" id="statusReason" name="status_reason" style="display: none;">
                                            <option value="">Reason (Optional)</option>
                                            <option value="out_of_stock">Out of Stock</option>
                                            <option value="customer_request">Customer Request</option>
                                            <option value="payment_issue">Payment Issue</option>
                                            <option value="delivery_delay">Delivery Delay</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <small class="text-muted">Select the new status for this order</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><strong>Admin Notes</strong></label>
                                <textarea class="form-control-admin" id="adminNotes" name="admin_notes" rows="3" placeholder="Add internal notes about this status change..."></textarea>
                                <small class="text-muted">These notes are for internal use only</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><strong>Notify Customer</strong></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="notifyCustomer" name="notify_customer" checked>
                                    <label class="form-check-label" for="notifyCustomer">
                                        Send email/SMS notification to customer about status change
                                    </label>
                                </div>
                            </div>

                            <!-- Hidden Fields -->
                            <input type="hidden" id="orderId" name="order_id" value="">
                            <input type="hidden" id="currentOrderStatus" name="current_status" value="">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-admin btn-admin-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn-admin btn-admin-primary" id="updateOrderBtn">
                                <span class="spinner-border spinner-border-sm" role="status" style="display: none;"></span>
                                Update Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
   <script src="../Assets/bootstrap/js/bootstrap.bundle.min.js"></script>

<script>
    // Initialize modal
    let orderModal;
    
    // Store current order data
    let currentOrderData = null;

    document.addEventListener('DOMContentLoaded', function() {
        orderModal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
        
        // View order button click event
        document.querySelectorAll('.view-order-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const orderId = this.getAttribute('data-order-id');
                const orderData = JSON.parse(this.getAttribute('data-order-data'));
                loadOrderDetails(orderId, orderData);
            });
        });

        // Status select change event
        document.getElementById('updateOrderStatus').addEventListener('change', function() {
            const statusReason = document.getElementById('statusReason');
            if (this.value === 'cancelled') {
                statusReason.style.display = 'block';
                statusReason.setAttribute('required', 'required');
            } else {
                statusReason.style.display = 'none';
                statusReason.removeAttribute('required');
                statusReason.value = '';
            }
        });

        // Form submit event
        document.getElementById('updateOrderForm').addEventListener('submit', function(e) {
            e.preventDefault();
            updateOrderStatus();
        });
        
        // Add click event to table rows for quick view
        document.querySelectorAll('#recentOrdersTable tr').forEach(row => {
            row.addEventListener('click', function(e) {
                if (!e.target.closest('button') && !e.target.closest('a') && !e.target.closest('input') && !e.target.closest('select')) {
                    const viewBtn = this.querySelector('.view-order-btn');
                    if (viewBtn) {
                        const orderId = viewBtn.getAttribute('data-order-id');
                        const orderData = JSON.parse(viewBtn.getAttribute('data-order-data'));
                        loadOrderDetails(orderId, orderData);
                    }
                }
            });
        });
    });

    function loadOrderDetails(orderId, orderData) {
        currentOrderData = orderData;
        
        // Clear any existing alerts
        const existingAlert = document.querySelector('#orderDetailsModal .alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        // Set basic order info from the row data
        document.getElementById('modalOrderId').textContent = `#ORD-${orderId}`;
        document.getElementById('orderId').value = orderId;
        document.getElementById('currentOrderStatus').value = orderData.status;
        document.getElementById('orderRestaurant').textContent = orderData.restaurant_name || 'N/A';
        document.getElementById('customerName').textContent = orderData.username;
        document.getElementById('orderTotalAmount').textContent = `TZS ${parseInt(orderData.total_amount).toLocaleString()}`;
        
        // Set current status badge
        updateStatusBadge(orderData.status);
        
        // Set the select dropdown to current status
        document.getElementById('updateOrderStatus').value = orderData.status;
        
        // Reset form
        document.getElementById('adminNotes').value = '';
        document.getElementById('notifyCustomer').checked = true;
        document.getElementById('statusReason').style.display = 'none';
        document.getElementById('statusReason').removeAttribute('required');
        document.getElementById('statusReason').value = '';
        
        // Show loading state for items
        document.getElementById('orderItemsContainer').innerHTML = `
            <div class="text-center py-3">
                <div class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 mb-0">Loading order details...</p>
            </div>
        `;
        
        // Load additional order details via AJAX
        fetchOrderDetails(orderId);
        
        // Show modal
        orderModal.show();
    }

    function fetchOrderDetails(orderId) {
        fetch('fetch_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'order_id=' + encodeURIComponent(orderId)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) { 
                showToast(data.msg || 'Failed to load order details', 'error'); 
                return; 
            }
            
            const order = data.order;
            const items = data.items;
            
            // Update customer details
            document.getElementById('customerName').textContent = order.username || 'N/A';
            document.getElementById('customerPhone').textContent = order.phone || 'N/A';
            document.getElementById('customerEmail').textContent = order.user_email || 'N/A';
            document.getElementById('customerAddress').textContent = order.delivery_address || 'N/A';
            document.getElementById('orderTime').textContent = new Date(order.inserted_at).toLocaleString('en-US', {
                weekday: 'short',
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            document.getElementById('paymentMethod').textContent = order.payment_method || 'Not specified';
            document.getElementById('orderRestaurant').textContent = order.restaurant_name || 'N/A';
            
            // Update order items
            let html = '';
            if (items && items.length > 0) {
                items.forEach(item => {
                    html += `
                        <div class="order-item">
                            <div>
                                <strong>${item.item_name}</strong><br>
                                <small class="text-muted">Quantity: ${item.quantity} × TZS ${parseInt(item.price || item.unit_price || 0).toLocaleString()}</small>
                            </div>
                            <div>TZS ${parseInt(item.total || (item.quantity * (item.price || item.unit_price || 0))).toLocaleString()}</div>
                        </div>
                    `;
                });
                
                // Add delivery fee if exists
                if (data.delivery_fee && data.delivery_fee > 0) {
                    html += `<div class="order-item"><div>Delivery Fee</div><div>TZS ${parseInt(data.delivery_fee).toLocaleString()}</div></div>`;
                }
                
                // Add tax if exists
                if (data.tax && data.tax > 0) {
                    html += `<div class="order-item"><div>Tax (18%)</div><div>TZS ${parseInt(data.tax).toLocaleString()}</div></div>`;
                }
                
                // Add status history if available
                if (data.status_history && data.status_history.length > 0) {
                    html += `
                        <div class="mt-4">
                            <h6>Status History</h6>
                            <div class="timeline">
                    `;
                    
                    data.status_history.forEach(history => {
                        html += `
                            <div class="timeline-item mb-2">
                                <small class="text-muted">${new Date(history.changed_at || history.timestamp).toLocaleString()}</small><br>
                                <span class="badge bg-secondary">${history.old_status}</span> 
                                <i class="fas fa-arrow-right mx-2 text-muted"></i>
                                <span class="badge ${getStatusClass(history.new_status)}">${history.new_status}</span>
                                ${history.reason ? `<br><small class="text-muted">Reason: ${history.reason}</small>` : ''}
                            </div>
                        `;
                    });
                    
                    html += `</div></div>`;
                }
                
            } else {
                html = '<div class="text-center py-3"><p class="text-muted">No items found</p></div>';
            }
            
            document.getElementById('orderItemsContainer').innerHTML = html;
            
            // Update total amount
            document.getElementById('orderTotalAmount').textContent = 'TZS ' + (parseInt(data.total_amount || order.total_amount || 0)).toLocaleString();
            
            // Update status badge and hidden fields
            updateStatusBadge(order.status);
            document.getElementById('orderId').value = order.id;
            document.getElementById('currentOrderStatus').value = order.status;
            
        })
        .catch(error => {
            console.error('Error fetching order details:', error);
            document.getElementById('orderItemsContainer').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> Error loading order details. Please try again.
                </div>
            `;
            showToast('Error loading order details. Please check your connection.', 'error');
        });
    }

    function updateStatusBadge(status) {
        const badge = document.getElementById('currentStatus');
        badge.className = 'status-badge ';
        
        switch((status || '').toLowerCase()) {
            case 'pending':
                badge.classList.add('status-pending');
                break;
            case 'confirmed':
                badge.classList.add('status-confirmed');
                break;
            case 'preparing':
                badge.classList.add('status-preparing');
                break;
            case 'delivered':
                badge.classList.add('status-delivered');
                break;
            case 'cancelled':
                badge.classList.add('status-cancelled');
                break;
            default:
                badge.classList.add('bg-secondary');
        }
        
        badge.textContent = (status || '').charAt(0).toUpperCase() + (status || '').slice(1);
    }

    // Helper function to get status class for badges
    function getStatusClass(status) {
        switch((status || '').toLowerCase()) {
            case 'pending': return 'bg-warning';
            case 'confirmed': return 'bg-info';
            case 'preparing': return 'bg-primary';
            case 'delivered': return 'bg-success';
            case 'cancelled': return 'bg-danger';
            default: return 'bg-secondary';
        }
    }

    function updateOrderStatus() {
        const form = document.getElementById('updateOrderForm');
        const formData = new FormData(form);
        const btn = document.getElementById('updateOrderBtn');
        const spinner = btn.querySelector('.spinner-border');
        const newStatus = document.getElementById('updateOrderStatus').value;
        const currentStatus = document.getElementById('currentOrderStatus').value;
        
        if (!newStatus) { 
            showToast('Select a status', 'error'); 
            return; 
        }
        
        // Check if status is actually changing
        if (newStatus === currentStatus) {
            showToast('Order status is already set to ' + newStatus, 'warning');
            return;
        }
        
        // Special validation for cancellation
        if (newStatus === 'cancelled') {
            const reason = document.getElementById('statusReason').value;
            if (!reason) {
                showToast('Please provide a reason for cancellation', 'error');
                return;
            }
        }
        
        btn.disabled = true;
        spinner.style.display = 'inline-block';
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Updating...';
        
        fetch('update_order_status.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            btn.disabled = false; 
            spinner.style.display = 'none';
            btn.innerHTML = 'Update Order';
            
            if (data.success) {
                // Update the status badge in modal
                updateStatusBadge(newStatus);
                
                // Update the current status in hidden field
                document.getElementById('currentOrderStatus').value = newStatus;
                
                // Update the table row in the main view
                updateTableRow(formData.get('order_id'), newStatus);
                
                // Show success message
                showToast(data.msg || 'Order status updated successfully', 'success');
                
                // Refresh order details to show updated history
                setTimeout(() => {
                    fetchOrderDetails(formData.get('order_id'));
                }, 500);
                
                // Show success alert in modal
                const successAlert = document.createElement('div');
                successAlert.className = 'alert alert-success alert-dismissible fade show';
                successAlert.innerHTML = `
                    <i class="fas fa-check-circle"></i> 
                    <strong>Success!</strong> ${data.msg || 'Order status updated successfully'}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                const modalBody = document.querySelector('#orderDetailsModal .modal-body');
                modalBody.insertBefore(successAlert, modalBody.firstChild);
                
                // Auto-remove alert after 5 seconds
                setTimeout(() => {
                    if (successAlert.parentNode) {
                        successAlert.remove();
                    }
                }, 5000);
                
            } else {
                showToast(data.msg || 'Failed to update order status', 'error');
            }
        })
        .catch(error => {
            console.error('Error updating order status:', error);
            btn.disabled = false; 
            spinner.style.display = 'none';
            btn.innerHTML = 'Update Order';
            showToast('Network error. Please try again.', 'error');
        });
    }

    function updateTableRow(orderId, newStatus) {
        // Find and update the table row in recent orders table
        document.querySelectorAll('#recentOrdersTable tr').forEach(row => {
            const orderIdCell = row.querySelector('td:first-child');
            if (orderIdCell && orderIdCell.textContent.includes(`#ORD-${orderId}`)) {
                // Update status badge
                const statusBadge = row.querySelector('.status-badge');
                if (statusBadge) {
                    statusBadge.className = 'status-badge ';
                    
                    switch(newStatus.toLowerCase()) {
                        case 'pending':
                            statusBadge.classList.add('status-pending');
                            break;
                        case 'confirmed':
                            statusBadge.classList.add('status-confirmed');
                            break;
                        case 'preparing':
                            statusBadge.classList.add('status-preparing');
                            break;
                        case 'delivered':
                            statusBadge.classList.add('status-delivered');
                            break;
                        case 'cancelled':
                            statusBadge.classList.add('status-cancelled');
                            break;
                    }
                    
                    statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                    
                    // Update the button data
                    const viewBtn = row.querySelector('.view-order-btn');
                    if (viewBtn) {
                        try {
                            const orderData = JSON.parse(viewBtn.getAttribute('data-order-data'));
                            orderData.status = newStatus;
                            viewBtn.setAttribute('data-order-data', JSON.stringify(orderData));
                        } catch (e) {
                            console.error('Error updating button data:', e);
                        }
                    }
                }
            }
        });
        
        // Also update any other tables that might have this order
        updateAllOrderTables(orderId, newStatus);
    }

    function updateAllOrderTables(orderId, newStatus) {
        // Update orders in the main orders table if it exists
        const mainOrdersTable = document.getElementById('ordersTable');
        if (mainOrdersTable) {
            mainOrdersTable.querySelectorAll('tr').forEach(row => {
                const orderIdCell = row.querySelector('td:first-child');
                if (orderIdCell && orderIdCell.textContent.includes(`#ORD-${orderId}`)) {
                    const statusBadge = row.querySelector('.status-badge, .badge');
                    if (statusBadge) {
                        statusBadge.className = 'badge ' + getStatusClass(newStatus);
                        statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                    }
                }
            });
        }
    }

    function showToast(message, type = 'success') {
        // Create or get toast container
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        
        const toastId = 'toast-' + Date.now();
        const toast = document.createElement('div');
        toast.className = `toast ${type === 'success' ? 'bg-success text-white' : 'bg-danger text-white'}`;
        toast.id = toastId;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="toast-header ${type === 'success' ? 'bg-success text-white' : 'bg-danger text-white'}">
                <strong class="me-auto">${type === 'success' ? 'Success' : 'Error'}</strong>
                <small>Just now</small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: 3000
        });
        
        bsToast.show();
        
        // Remove toast from DOM after it's hidden
        toast.addEventListener('hidden.bs.toast', function() {
            toast.remove();
        });
    }

    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl + S to save/update order status when modal is open
        if ((e.ctrlKey || e.metaKey) && e.key === 's' && document.getElementById('orderDetailsModal').classList.contains('show')) {
            e.preventDefault();
            document.getElementById('updateOrderForm').dispatchEvent(new Event('submit'));
        }
        
        // Escape to close modal
        if (e.key === 'Escape' && document.getElementById('orderDetailsModal').classList.contains('show')) {
            orderModal.hide();
        }
    });

    // For demo: Show a toast on page load
    setTimeout(() => {
        showToast('Orders management system loaded successfully!', 'success');
    }, 1000);
</script>
</body>
</html>