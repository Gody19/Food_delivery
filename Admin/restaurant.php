<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurants management</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="../Assets/bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../Assets/fontawesome/css/all.min.css">
</head>

<body>
    <!-- Restaurants Section -->
    <?php include 'include/aside.php'; ?>
    <div id="content">
        <?php include 'include/header.php'; ?>
        <div class="main-content">
            <div class="page-header">
                <h1 class="h3 mb-0">Restaurant Management</h1>
            </div>
            <div class="content-container section-content" id="restaurants-section">
                <div class="page-header">
                    <div class="page-title">
                        <h1>Restaurant Management</h1>
                        <p>Manage restaurants in your delivery network</p>
                    </div>
                    <button class="btn btn-admin btn-admin-success" data-bs-toggle="modal" data-bs-target="#addRestaurantModal">
                        <i class="fas fa-plus me-1"></i> Add Restaurant
                    </button>
                </div>

                <!-- Restaurants Grid -->
                <div class="row" id="restaurantsGrid">
                    <!-- Restaurants will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>
    <!-- Add Restaurant Modal -->
        <div class="modal fade admin-modal" id="addRestaurantModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Restaurant</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addRestaurantForm">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Restaurant Name *</label>
                                    <input type="text" class="form-control form-control-admin" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Owner Name *</label>
                                    <input type="text" class="form-control form-control-admin" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Cuisine Type *</label>
                                    <select class="form-control form-control-admin" required>
                                        <option value="">Select Cuisine</option>
                                        <option value="tanzanian">Tanzanian</option>
                                        <option value="indian">Indian</option>
                                        <option value="chinese">Chinese</option>
                                        <option value="italian">Italian</option>
                                        <option value="fast-food">Fast Food</option>
                                        <option value="seafood">Seafood</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control form-control-admin" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Address *</label>
                                    <textarea class="form-control form-control-admin" rows="2" required></textarea>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Commission Rate (%) *</label>
                                    <input type="number" class="form-control form-control-admin" value="15" min="5" max="30" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <select class="form-control form-control-admin">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="suspended">Suspended</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Restaurant Image</label>
                                <input type="file" class="form-control form-control-admin" accept="image/*">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-admin btn-admin-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-admin btn-admin-success">Add Restaurant</button>
                    </div>
                </div>
            </div>
        </div>
    <script src="../Assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>