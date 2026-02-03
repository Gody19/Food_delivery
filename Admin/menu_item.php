<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Items Management</title>
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
                <h1 class="h3 mb-0 d-none">Menu Items Management</h1>
            </div>
            <!-- Menu Items Section -->

            <div class="content-container section-content" id="menu-section">
                <div class="page-header">
                    <div class="page-title">
                        <h1>Menu Items Management</h1>
                        <p>Add, edit or remove menu items from restaurants</p>
                    </div>
                    <button class="btn btn-admin btn-admin-success" data-bs-toggle="modal" data-bs-target="#addMenuItemModal">
                        <i class="fas fa-plus me-1"></i> Add Menu Item
                    </button>
                </div>

                <!-- Menu Items Table -->
                <div class="dashboard-card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Item Name</th>
                                        <th>Restaurant</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="menuItemsTable">
                                    <!-- Menu items will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Add Menu Item Modal -->
            <div class="modal fade admin-modal" id="addMenuItemModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add New Menu Item</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="addMenuItemForm">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Item Name *</label>
                                        <input type="text" class="form-control form-control-admin" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Restaurant *</label>
                                        <select class="form-control form-control-admin" required>
                                            <option value="">Select Restaurant</option>
                                            <option value="1">Mama Ntilie Restaurant</option>
                                            <option value="2">Burger King Dar</option>
                                            <option value="3">Tokyo Sushi Lounge</option>
                                            <option value="4">Mexican Grill Arusha</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Category *</label>
                                        <select class="form-control form-control-admin" required>
                                            <option value="">Select Category</option>
                                            <option value="appetizer">Appetizer</option>
                                            <option value="main-course">Main Course</option>
                                            <option value="dessert">Dessert</option>
                                            <option value="beverage">Beverage</option>
                                            <option value="traditional">Traditional</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Price (TZS) *</label>
                                        <input type="number" class="form-control form-control-admin" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control form-control-admin" rows="3"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Item Image</label>
                                    <input type="file" class="form-control form-control-admin" accept="image/*">
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Preparation Time (minutes)</label>
                                        <input type="number" class="form-control form-control-admin" value="15">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Status</label>
                                        <select class="form-control form-control-admin">
                                            <option value="available">Available</option>
                                            <option value="unavailable">Unavailable</option>
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-admin btn-admin-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-admin btn-admin-success">Add Menu Item</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../Assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>