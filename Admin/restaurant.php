<?php
include '../config/connection.php';

// Initialize message variables
$success = false;
$message = '';

function sanitizeInput($data, $type)
{
    if ($data === null) return false;

    switch ($type) {

        case 'string':
            $data = trim($data);
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
            break;

        case 'email':
            $data = filter_var(trim($data), FILTER_SANITIZE_EMAIL);
            if (!filter_var($data, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
            break;

        case 'int':
            $data = filter_var($data, FILTER_SANITIZE_NUMBER_INT);
            if (!filter_var($data, FILTER_VALIDATE_INT)) {
                return false;
            }
            break;

        case 'float':
            $data = filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            if (!filter_var($data, FILTER_VALIDATE_FLOAT)) {
                return false;
            }
            break;

        case 'url':
            $data = filter_var($data, FILTER_SANITIZE_URL);
            if (!filter_var($data, FILTER_VALIDATE_URL)) {
                return false;
            }
            break;

        case 'image':

            if (!isset($data['tmp_name']) || $data['error'] !== UPLOAD_ERR_OK) {
                return false;
            }

            $allowedMime = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            $uploadDir = '../Assets/img/';

            // Validate size
            if ($data['size'] > $maxSize) {
                return false;
            }

            // Validate real MIME type (secure)
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $data['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $allowedMime)) {
                return false;
            }

            // Generate safe filename
            $ext = image_type_to_extension(exif_imagetype($data['tmp_name']));
            $filename = uniqid('img_', true) . $ext;

            if (!move_uploaded_file($data['tmp_name'], $uploadDir . $filename)) {
                return false;
            }

            $data = $filename;
            break;

        default:
            return false;
    }

    return $data;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $restaurantName = sanitizeInput($_POST['restaurant_name'] ?? '', 'string');
    $ownerName = sanitizeInput($_POST['owner_name'] ?? '', 'string');
    $cuisineType = sanitizeInput($_POST['cuisine_type'] ?? '', 'string');
    $phoneNumber = sanitizeInput($_POST['phone_number'] ?? '', 'string');
    $address = sanitizeInput($_POST['address'] ?? '', 'string');
    $commissionRate = sanitizeInput($_POST['commission_rate'] ?? '', 'float');
    $status = sanitizeInput($_POST['status'] ?? 'active', 'string');
    $restaurantImage = !empty($_FILES['restaurant_image']['name']) ? sanitizeInput($_FILES['restaurant_image'], 'image') : '';

    if (!$restaurantName || !$ownerName || !$cuisineType || !$phoneNumber || !$address || $commissionRate === false) {
        $message = 'Invalid or missing required fields.';
    } elseif (!isset($conn) || $conn->connect_error) {
        $message = 'Database connection error.';
    } else {
        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO restaurants (name, owner_name, cuisine_type, phone_number, address, commission_rate, status, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            $message = 'Prepare failed: ' . $conn->error;
        } else {
            $stmt->bind_param('sssssdss', $restaurantName, $ownerName, $cuisineType, $phoneNumber, $address, $commissionRate, $status, $restaurantImage);
            if ($stmt->execute()) {
                $success = true;
                $message = 'Restaurant added successfully!';
            } else {
                $message = 'Error: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch all restaurants from database
$restaurants = [];
if (isset($conn) && !$conn->connect_error) {
    $query = "SELECT id, restaurant_name, restaurant_owner, cuisine_type, phone_number, address, commission_rate, status, restaurant_image FROM restaurants ORDER BY id DESC";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $restaurants[] = $row;
        }
    }
}

?>

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
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="../Assets/sweetalert2/sweetalert2.min.css">
</head>

<body>
    <!-- Display restaurant registration message from backend using SweetAlert -->
    <?php if (!empty($message)): ?>
        <script src="../Assets/sweetalert2/sweetalert2.all.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: <?php echo $success ? "'success'" : "'error'"; ?>,
                    title: <?php echo $success ? "'Success!'" : "'Error'"; ?>,
                    text: <?php echo json_encode($message); ?>,
                    confirmButtonColor: <?php echo $success ? "'#28a745'" : "'#dc3545'"; ?>
                }).then(() => {
                    <?php if ($success): ?>
                        window.location.href = 'restaurant.php';
                    <?php endif; ?>
                });
            });
        </script>
    <?php endif; ?>

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

                <!-- Debug Info (remove later) -->
                <div class="mt-3 p-3 bg-light rounded" style="display: none;">
                    <small class="text-muted">
                        <strong>Debug:</strong> Total Restaurants: <?php echo count($restaurants); ?><br>
                        <?php if (count($restaurants) > 0) {
                            echo "First restaurant: " . json_encode($restaurants[0]);
                        } ?>
                    </small>
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
                    <form id="addRestaurantForm" method="post" action="" enctype="multipart/form-data">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Restaurant Name *</label>
                                <input type="text" class="form-control form-control-admin" name="restaurant_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Owner Name *</label>
                                <input type="text" class="form-control form-control-admin" name="owner_name" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Cuisine Type *</label>
                                <select class="form-control form-control-admin" name="cuisine_type" required>
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
                                <input type="tel" name="phone_number" class="form-control form-control-admin" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Address *</label>
                                <textarea class="form-control form-control-admin" name="address" rows="2" required></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Commission Rate (%) *</label>
                                <input type="number" name="commission_rate" class="form-control form-control-admin" value="15" min="5" max="30" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-control form-control-admin" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Restaurant Image</label>
                            <input type="file" class="form-control form-control-admin" accept="image/*" name="restaurant_image">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-admin btn-admin-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="addRestaurantSubmit" class="btn btn-admin btn-admin-success">Add Restaurant</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../Assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../Assets/fontawesome/js/all.min.js"></script>
    <script src="../Assets/sweetalert2/sweetalert2.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('addRestaurantForm');
            const submitBtn = document.getElementById('addRestaurantSubmit');

            function clearValidationErrors() {
                const errorElements = form.querySelectorAll('.error-message');
                errorElements.forEach(el => el.remove());
                const invalids = form.querySelectorAll('.is-invalid');
                invalids.forEach(f => f.classList.remove('is-invalid'));
            }

            function showValidationError(field, message) {
                field.classList.add('is-invalid');
                let err = document.createElement('div');
                err.className = 'error-message text-danger small mt-1';
                err.textContent = message;
                if (field.parentNode) field.parentNode.appendChild(err);
            }

            function validateForm() {
                clearValidationErrors();
                let valid = true;

                const restaurantName = form.querySelector('[name="restaurant_name"]');
                const ownerName = form.querySelector('[name="owner_name"]');
                const cuisine = form.querySelector('[name="cuisine_type"]');
                const phone = form.querySelector('[name="phone_number"]');
                const address = form.querySelector('[name="address"]');
                const commission = form.querySelector('[name="commission_rate"]');

                if (!restaurantName.value.trim()) {
                    showValidationError(restaurantName, 'Restaurant name is required');
                    valid = false;
                }
                if (!ownerName.value.trim()) {
                    showValidationError(ownerName, 'Owner name is required');
                    valid = false;
                }
                if (!cuisine.value) {
                    showValidationError(cuisine, 'Please select a cuisine');
                    valid = false;
                }
                if (!/^\d{9}$/.test(phone.value.trim())) {
                    showValidationError(phone, 'Phone number must be 9 digits');
                    valid = false;
                }
                if (!address.value.trim()) {
                    showValidationError(address, 'Address is required');
                    valid = false;
                }
                const rate = parseFloat(commission.value);
                if (isNaN(rate) || rate < 5 || rate > 30) {
                    showValidationError(commission, 'Commission rate must be between 5 and 30');
                    valid = false;
                }

                // Optional: validate image file type/size
                const imageInput = form.querySelector('[name="restaurant_image"]');
                if (imageInput && imageInput.files && imageInput.files.length > 0) {
                    const file = imageInput.files[0];
                    const allowed = ['image/jpeg', 'image/png', 'image/gif'];
                    if (!allowed.includes(file.type)) {
                        showValidationError(imageInput, 'Allowed image types: jpg, png, gif');
                        valid = false;
                    }
                    const maxSize = 5 * 1024 * 1024;
                    if (file.size > maxSize) {
                        showValidationError(imageInput, 'Image must be smaller than 5MB');
                        valid = false;
                    }
                }

                return valid;
            }

            // Handle submit button click (modal footer button)
            submitBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (validateForm()) {
                    form.submit();
                }
            });

            // Also validate on form submit (in case of native submit)
            form.addEventListener('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                }
            });
        });

        // Display restaurants in grid
        document.addEventListener('DOMContentLoaded', function() {
            const restaurants = <?php echo json_encode($restaurants); ?>;
            const grid = document.getElementById('restaurantsGrid');
            
            console.log('Total restaurants fetched:', restaurants.length);
            console.log('Full restaurants data:', restaurants);
            console.log('Grid element:', grid);

            if (!restaurants || restaurants.length === 0) {
                const emptyMsg = '<div class="col-12 text-center py-5"><i class="fas fa-store fa-3x text-muted mb-3" style="display: block;"></i><p class="text-muted">No restaurants found. Add one to get started.</p></div>';
                grid.innerHTML = emptyMsg;
                console.log('No restaurants to display');
                return;
            }

            // Clear any existing content
            grid.innerHTML = '';

            restaurants.forEach((restaurant, index) => {
                console.log(`Rendering restaurant ${index + 1}:`, restaurant);
                const restaurantCard = document.createElement('div');
                restaurantCard.className = 'col-xl-4 col-md-6 mb-4';
                restaurantCard.innerHTML = `
                    <div class="dashboard-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-1">${escapeHtml(restaurant.restaurant_name)}</h5>
                                    <p class="text-muted mb-0" style="font-size: 0.85rem;">
                                        <strong>Owner:</strong> ${escapeHtml(restaurant.restaurant_owner)}<br>
                                        <strong>Cuisine:</strong> ${escapeHtml(restaurant.cuisine_type)}
                                    </p>
                                </div>
                                <span class="badge ${restaurant.status === 'active' ? 'bg-success' : restaurant.status === 'inactive' ? 'bg-warning' : 'bg-danger'}" style="white-space: nowrap;">
                                    ${escapeHtml(restaurant.status)}
                                </span>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="text-muted small">Phone</div>
                                    <div class="fw-bold small">+255${escapeHtml(restaurant.phone_number)}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small">Commission</div>
                                    <div class="fw-bold small">${restaurant.commission_rate}%</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="text-muted small">Address</div>
                                <p class="small mb-0">${escapeHtml(restaurant.address)}</p>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                <button class="btn btn-admin btn-admin-primary btn-sm flex-fill" onclick="viewRestaurant(${restaurant.id})" title="View Details">
                                    <i class="fas fa-eye me-1"></i><span class="d-none d-md-inline">View</span>
                                </button>
                                <button class="btn btn-admin btn-admin-info btn-sm flex-fill" onclick="editRestaurant(${restaurant.id})" title="Edit">
                                    <i class="fas fa-edit me-1"></i><span class="d-none d-md-inline">Edit</span>
                                </button>
                                <button class="btn btn-admin ${restaurant.status === 'active' ? 'btn-admin-warning' : 'btn-admin-success'} btn-sm flex-fill" onclick="toggleRestaurantStatus(${restaurant.id}, '${restaurant.status}')" title="${restaurant.status === 'active' ? 'Deactivate' : 'Activate'}">
                                    <i class="fas ${restaurant.status === 'active' ? 'fa-pause' : 'fa-play'} me-1"></i><span class="d-none d-md-inline">${restaurant.status === 'active' ? 'Pause' : 'Active'}</span>
                                </button>
                                <button class="btn btn-admin btn-admin-danger btn-sm flex-fill" onclick="deleteRestaurant(${restaurant.id})" title="Delete">
                                    <i class="fas fa-trash me-1"></i><span class="d-none d-md-inline">Delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                grid.appendChild(restaurantCard);
            });

            console.log(`Successfully rendered ${restaurants.length} restaurants`);
        });

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }

        function viewRestaurant(id) {
            Swal.fire({
                icon: 'info',
                title: 'Restaurant Details',
                text: 'View details for restaurant ID: ' + id,
                confirmButtonText: 'OK'
            });
        }

        function editRestaurant(id) {
            Swal.fire({
                icon: 'info',
                title: 'Edit Restaurant',
                text: 'Edit functionality for restaurant ID: ' + id,
                confirmButtonText: 'OK'
            });
        }

        function toggleRestaurantStatus(id, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            Swal.fire({
                icon: 'question',
                title: 'Confirm',
                text: `Change status to ${newStatus}?`,
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated',
                        text: 'Restaurant status changed to ' + newStatus,
                        timer: 1500
                    });
                }
            });
        }

        function deleteRestaurant(id) {
            Swal.fire({
                icon: 'warning',
                title: 'Delete Restaurant',
                text: 'Are you sure? This action cannot be undone.',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                confirmButtonColor: '#dc3545',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted',
                        text: 'Restaurant deleted successfully',
                        timer: 1500
                    });
                }
            });
        }
    </script>
</body>

</html>