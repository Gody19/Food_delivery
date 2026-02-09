<?php
include 'include/check_login.php';
include '../config/connection.php';

// Server-side handler for adding menu item
$success = false;
$message = '';

function sanitize($data, $type)
{
    if ($data === null) return false;
    $data = trim($data);
    switch ($type) {
        case 'int':
            if (!filter_var($data, FILTER_VALIDATE_INT)) return false;
            return (int)$data;
        case 'float':
            $val = filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            if ($val === '' || !filter_var($val, FILTER_VALIDATE_FLOAT)) return false;
            return (float)$val;
        case 'string':
        default:
            return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}

// Fetch restaurants for select options
$restaurants = [];
if (isset($conn) && !$conn->connect_error) {
    $q = "SELECT id, cuisine_type, restaurant_name FROM restaurants WHERE status = 'active' ORDER BY restaurant_name ASC";
    $res = $conn->query($q);
    if ($res && $res->num_rows > 0) {
        while ($r = $res->fetch_assoc()) $restaurants[] = $r;
    }
}

// Handle Update Menu Item
if (isset($_POST['update_id'])) {
    $update_id = sanitize($_POST['update_id'] ?? '', 'int');
    $item_name = sanitize($_POST['update_item_name'] ?? '', 'string');
    $restaurant_id = sanitize($_POST['update_restaurant_id'] ?? '', 'int');
    $category = sanitize($_POST['update_category'] ?? '', 'string');
    $price = sanitize($_POST['update_price'] ?? '', 'float');
    $description = sanitize($_POST['update_description'] ?? '', 'string');
    $prep_time = sanitize($_POST['update_prep_time'] ?? '', 'int');
    $status = sanitize($_POST['update_status'] ?? 'available', 'string');

    // Validate required
    if (!$update_id || !$item_name || !$restaurant_id || !$category || $price === false) {
        $message = 'Please fill all required fields correctly.';
    } else {
        // Get current item to check for existing image
        $current_sql = "SELECT item_image FROM menu_items WHERE id = ?";
        $current_stmt = $conn->prepare($current_sql);
        $current_stmt->bind_param('i', $update_id);
        $current_stmt->execute();
        $current_result = $current_stmt->get_result();
        $current_item = $current_result->fetch_assoc();
        $current_stmt->close();
        
        $imageName = $current_item['item_image'] ?? '';

        // Handle image upload if new image is provided
        if (!empty($_FILES['update_item_image']['name'])) {
            $file = $_FILES['update_item_image'];
            if ($file['error'] === UPLOAD_ERR_OK) {
                $allowed = ['image/jpeg', 'image/png', 'image/gif'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                if (!in_array($mime, $allowed)) {
                    $message = 'Invalid image type. Allowed: jpg, png, gif.';
                } elseif ($file['size'] > 5 * 1024 * 1024) {
                    $message = 'Image too large. Max 5MB.';
                } else {
                    $ext = '';
                    if ($mime === 'image/jpeg') $ext = '.jpg';
                    if ($mime === 'image/png') $ext = '.png';
                    if ($mime === 'image/gif') $ext = '.gif';
                    $newImageName = uniqid('menu_', true) . $ext;
                    $dest = __DIR__ . '/../Assets/img/' . $newImageName;
                    if (!move_uploaded_file($file['tmp_name'], $dest)) {
                        $message = 'Failed to upload new image.';
                    } else {
                        // Delete old image if it exists
                        if ($imageName && file_exists(__DIR__ . '/../Assets/img/' . $imageName)) {
                            unlink(__DIR__ . '/../Assets/img/' . $imageName);
                        }
                        $imageName = $newImageName;
                    }
                }
            } elseif ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                $message = 'Image upload error.';
            }
        }

        if ($message === '') {
            if (!isset($conn) || $conn->connect_error) {
                $message = 'Database connection error.';
            } else {
                $sql = "UPDATE menu_items SET item_name = ?, restaurant_id = ?, category = ?, price = ?, description = ?, prep_time = ?, status = ?, item_image = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    $message = 'Prepare failed: ' . $conn->error;
                } else {
                    $stmt->bind_param('sisdsissi', $item_name, $restaurant_id, $category, $price, $description, $prep_time, $status, $imageName, $update_id);
                    if ($stmt->execute()) {
                        $success = true;
                        $message = 'Menu item updated successfully.';
                    } else {
                        $message = 'Update error: ' . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
    }
}

// Handle Delete Menu Item
if (isset($_POST['delete_id'])) {
    $delete_id = sanitize($_POST['delete_id'] ?? '', 'int');

    if (!$delete_id) {
        $message = 'Invalid item ID.';
    } else {
        if (!isset($conn) || $conn->connect_error) {
            $message = 'Database connection error.';
        } else {
            // Get item image to delete
            $img_sql = "SELECT item_image FROM menu_items WHERE id = ?";
            $img_stmt = $conn->prepare($img_sql);
            $img_stmt->bind_param('i', $delete_id);
            $img_stmt->execute();
            $img_result = $img_stmt->get_result();
            $img_item = $img_result->fetch_assoc();
            $img_stmt->close();

            $sql = "DELETE FROM menu_items WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $message = 'Prepare failed: ' . $conn->error;
            } else {
                $stmt->bind_param('i', $delete_id);
                if ($stmt->execute()) {
                    // Delete image file if exists
                    if ($img_item && $img_item['item_image'] && file_exists(__DIR__ . '/../Assets/img/' . $img_item['item_image'])) {
                        unlink(__DIR__ . '/../Assets/img/' . $img_item['item_image']);
                    }
                    $success = true;
                    $message = 'Menu item deleted successfully.';
                } else {
                    $message = 'Delete error: ' . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

// Handle Add Menu Item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['update_id']) && !isset($_POST['delete_id'])) {
    // ensure enctype multipart/form-data for file
    $item_name = sanitize($_POST['item_name'] ?? '', 'string');
    $restaurant_id = sanitize($_POST['restaurant_id'] ?? '', 'int');
    $category = sanitize($_POST['category'] ?? '', 'string');
    $price = sanitize($_POST['price'] ?? '', 'float');
    $description = sanitize($_POST['description'] ?? '', 'string');
    $prep_time = sanitize($_POST['prep_time'] ?? '', 'int');
    $status = sanitize($_POST['status'] ?? 'available', 'string');

    // Validate required
    if (!$item_name || !$restaurant_id || !$category || $price === false) {
        $message = 'Please fill required fields correctly.';
    } else {
        // Handle image upload
        $imageName = '';
        if (!empty($_FILES['item_image']['name'])) {
            $file = $_FILES['item_image'];
            if ($file['error'] === UPLOAD_ERR_OK) {
                $allowed = ['image/jpeg', 'image/png', 'image/gif'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                if (!in_array($mime, $allowed)) {
                    $message = 'Invalid image type. Allowed: jpg, png, gif.';
                } elseif ($file['size'] > 5 * 1024 * 1024) {
                    $message = 'Image too large. Max 5MB.';
                } else {
                    $ext = '';
                    if ($mime === 'image/jpeg') $ext = '.jpg';
                    if ($mime === 'image/png') $ext = '.png';
                    if ($mime === 'image/gif') $ext = '.gif';
                    $imageName = uniqid('menu_', true) . $ext;
                    $dest = __DIR__ . '/../Assets/img/' . $imageName;
                    if (!move_uploaded_file($file['tmp_name'], $dest)) {
                        $message = 'Failed to move uploaded image.';
                    }
                }
            } else {
                $message = 'Image upload error.';
            }
        }

        if ($message === '') {
            // Insert into menu_items table
            if (!isset($conn) || $conn->connect_error) {
                $message = 'Database connection error.';
            } else {
                $status = 'available';
                $sql = "INSERT INTO menu_items (item_name, restaurant_id, category, price, description, prep_time, status, item_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    $message = 'Prepare failed: ' . $conn->error;
                } else {
                    $stmt->bind_param('sisdsiss', $item_name, $restaurant_id, $category, $price, $description, $prep_time, $status, $imageName);
                    if ($stmt->execute()) {
                        $success = true;
                        $message = 'Menu item added successfully.';
                    } else {
                        $message = 'Insert error: ' . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
    }

}

?>

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
                                    <?php
                                    // Fetch menu items with restaurant names and extra fields for editing
                                    $sql = "SELECT m.id, m.restaurant_id, r.restaurant_name, m.item_name, m.category, m.price, m.status, m.description, m.prep_time, m.item_image 
                                            FROM menu_items m INNER JOIN restaurants r ON m.restaurant_id = r.id";
                                    $result = $conn->query($sql);
                                    if ($result && $result->num_rows > 0) {
                                        while ($item = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo $item['id']; ?></td>
                                        <td><?php echo htmlspecialchars($item['item_name'], ENT_QUOTES); ?></td>
                                        <td><?php echo htmlspecialchars($item['restaurant_name'], ENT_QUOTES); ?></td>
                                        <td><?php echo htmlspecialchars($item['category']); ?></td>
                                        <td><?php echo htmlspecialchars($item['price'], ENT_QUOTES); ?></td>
                                        <td><span class="badge <?php echo ($item['status'] === 'available') ? 'bg-success' : 'bg-warning'; ?>"><?php echo htmlspecialchars($item['status'], ENT_QUOTES); ?></span></td>
                                        <td>
                                            <button class="btn btn-admin btn-admin-primary btn-sm me-1 editMenuBtn" 
                                                data-id="<?php echo $item['id']; ?>" 
                                                data-restaurant-id="<?php echo $item['restaurant_id']; ?>" 
                                                data-name="<?php echo htmlspecialchars($item['item_name'], ENT_QUOTES); ?>" 
                                                data-category="<?php echo htmlspecialchars($item['category'], ENT_QUOTES); ?>" 
                                                data-price="<?php echo htmlspecialchars($item['price'], ENT_QUOTES); ?>" 
                                                data-status="<?php echo htmlspecialchars($item['status'], ENT_QUOTES); ?>" 
                                                data-prep="<?php echo htmlspecialchars($item['prep_time'], ENT_QUOTES); ?>" 
                                                data-description="<?php echo htmlspecialchars($item['description'], ENT_QUOTES); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-admin btn-admin-danger btn-sm deleteMenuBtn" data-id="<?php echo $item['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="7" class="text-center">No menu items found.</td></tr>';
                                    }
                                    ?>
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
                            <form id="addMenuItemForm" method="post" action="" enctype="multipart/form-data" novalidate>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Item Name *</label>
                                        <input type="text" id="itemName" name="item_name" class="form-control form-control-admin" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Restaurant *</label>
                                        <select id="restaurantId" name="restaurant_id" class="form-control form-control-admin" required>
                                            <option value="">Select Restaurant</option>
                                            <?php foreach ($restaurants as $r): ?>
                                                <option value="<?php echo htmlspecialchars($r['id'], ENT_QUOTES); ?>"><?php echo htmlspecialchars($r['restaurant_name'], ENT_QUOTES); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Category *</label>
                                        <select id="category" name="category" class="form-control form-control-admin" required>
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
                                        <input type="number" id="price" name="price" class="form-control form-control-admin" min="0" step="1" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea id="description" name="description" class="form-control form-control-admin" rows="3"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Item Image</label>
                                    <input type="file" id="itemImage" name="item_image" class="form-control form-control-admin" accept="image/*">
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Preparation Time (minutes)</label>
                                        <input type="number" id="prepTime" name="prep_time" class="form-control form-control-admin" value="15" min="1">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Status</label>
                                        <select id="status" name="status" class="form-control form-control-admin">
                                            <option value="available">Available</option>
                                            <option value="unavailable">Unavailable</option>
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-admin btn-admin-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" id="addMenuItemSubmit" class="btn btn-admin btn-admin-success">Add Menu Item</button>
                        </div>
                    </div>
                </div>
            </div>
                </div>
            </div>

            <!-- Update Menu Item Modal -->
            <div class="modal fade admin-modal" id="updateMenuItemModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Update Menu Item</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="updateMenuItemForm" method="post" action="" enctype="multipart/form-data" novalidate>
                                <input type="hidden" id="updateItemId" name="update_id" value="">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Item Name *</label>
                                        <input type="text" id="updateItemName" name="update_item_name" class="form-control form-control-admin" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Restaurant *</label>
                                        <select id="updateRestaurantId" name="update_restaurant_id" class="form-control form-control-admin" required>
                                            <option value="">Select Restaurant</option>
                                            <?php foreach ($restaurants as $r): ?>
                                                <option value="<?php echo htmlspecialchars($r['id'], ENT_QUOTES); ?>"><?php echo htmlspecialchars($r['restaurant_name'], ENT_QUOTES); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Category *</label>
                                        <select id="updateCategory" name="update_category" class="form-control form-control-admin" required>
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
                                        <input type="number" id="updatePrice" name="update_price" class="form-control form-control-admin" min="0" step="1" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea id="updateDescription" name="update_description" class="form-control form-control-admin" rows="3"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Item Image</label>
                                    <input type="file" id="updateItemImage" name="update_item_image" class="form-control form-control-admin" accept="image/*">
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Preparation Time (minutes)</label>
                                        <input type="number" id="updatePrepTime" name="update_prep_time" class="form-control form-control-admin" value="15" min="1">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Status</label>
                                        <select id="updateStatus" name="update_status" class="form-control form-control-admin">
                                            <option value="available">Available</option>
                                            <option value="unavailable">Unavailable</option>
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-admin btn-admin-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" id="updateMenuItemSubmit" class="btn btn-admin btn-admin-primary">Update Item</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div class="modal fade admin-modal" id="deleteMenuItemModal" tabindex="-1">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Delete Menu Item</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete this menu item?</p>
                            <form id="deleteMenuItemForm" method="post" action="">
                                <input type="hidden" id="deleteItemId" name="delete_id" value="">
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-admin btn-admin-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" id="confirmDeleteBtn" class="btn btn-admin btn-admin-danger">Delete</button>
                        </div>
                    </div>
                </div>
            </div>

            <script src="../Assets/bootstrap/js/bootstrap.bundle.min.js"></script>
            <script src="../Assets/sweetalert2/sweetalert2.all.min.js"></script>
            
            <?php if (!empty($message)): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: <?php echo json_encode($success ? 'Success!' : 'Error!'); ?>,
                        text: <?php echo json_encode($message); ?>,
                        icon: <?php echo json_encode($success ? 'success' : 'error'); ?>,
                        confirmButtonText: 'Ok',
                        confirmButtonColor: <?php echo json_encode($success ? '#28a745' : '#dc3545'); ?>
                    }).then((result) => {
                        <?php if ($success && (isset($_POST['update_id']) || isset($_POST['delete_id']) || isset($_POST['item_name']))): ?>
                        if (result.isConfirmed) {
                            location.reload();
                        }
                        <?php endif; ?>
                    });
                });
            </script>
            <?php endif; ?>
            
            <script>
        (function() {
            function clearValidationErrors(form) {
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
            }

            function showValidationError(el, message) {
                el.classList.add('is-invalid');
                let feedback = el.parentNode.querySelector('.invalid-feedback');
                if (!feedback) {
                    feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback d-block';
                    el.parentNode.appendChild(feedback);
                }
                feedback.textContent = message;
            }

            function validateImageFile(fileInput) {
                if (!fileInput || !fileInput.files || fileInput.files.length === 0) return true; // optional
                const file = fileInput.files[0];
                const allowed = ['image/jpeg', 'image/png', 'image/gif'];
                const maxSize = 5 * 1024 * 1024; // 5MB
                if (!allowed.includes(file.type)) return 'Allowed image types: jpg, png, gif';
                if (file.size > maxSize) return 'Image must be smaller than 5MB';
                return true;
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Add form behavior
                const form = document.getElementById('addMenuItemForm');
                const submitBtn = document.getElementById('addMenuItemSubmit');

                submitBtn.addEventListener('click', function(e) {
                    clearValidationErrors(form);

                    const name = document.getElementById('itemName');
                    const restaurant = document.getElementById('restaurantId');
                    const category = document.getElementById('category') || document.querySelector('[name="category"]');
                    const price = document.getElementById('price');
                    const description = document.getElementById('description');
                    const image = document.getElementById('itemImage');
                    const prep = document.getElementById('prepTime');
                    const status = document.getElementById('status');

                    let valid = true;

                    if (!name.value.trim()) {
                        showValidationError(name, 'Item name is required');
                        valid = false;
                    }
                    if (!restaurant.value) {
                        showValidationError(restaurant, 'Please select a restaurant');
                        valid = false;
                    }
                    if (!category || (category && !category.value)) {
                        if (category) showValidationError(category, 'Please select a category');
                        valid = false;
                    }
                    const p = parseFloat(price.value);
                    if (isNaN(p) || p <= 0) {
                        showValidationError(price, 'Price must be a positive number');
                        valid = false;
                    }
                    if (description.value && description.value.length > 1000) {
                        showValidationError(description, 'Description is too long');
                        valid = false;
                    }
                    const imgValid = validateImageFile(image);
                    if (imgValid !== true) {
                        showValidationError(image, imgValid);
                        valid = false;
                    }
                    const prepVal = parseInt(prep.value);
                    if (isNaN(prepVal) || prepVal <= 0) {
                        showValidationError(prep, 'Preparation time must be at least 1 minute');
                        valid = false;
                    }
                    if (!status.value) {
                        showValidationError(status, 'Please select status');
                        valid = false;
                    }

                    if (!valid) return;

                    // Submit the form to the server so server-side handler inserts into DB
                    submitBtn.disabled = true;
                    form.submit();
                });

                // Also validate on native submit
                form.addEventListener('submit', function(e) {
                    // prevent accidental submit - use same validation
                    e.preventDefault();
                    submitBtn.click();
                });

                // Edit / Delete handlers (guard element existence)
                const updateModalEl = document.getElementById('updateMenuItemModal');
                let updateModal = null;
                if (updateModalEl && typeof bootstrap !== 'undefined') {
                    updateModal = new bootstrap.Modal(updateModalEl);
                }
                const deleteModalEl = document.getElementById('deleteMenuItemModal');
                let deleteModal = null;
                if (deleteModalEl && typeof bootstrap !== 'undefined') {
                    deleteModal = new bootstrap.Modal(deleteModalEl);
                }

                document.addEventListener('click', function(e) {
                    const editBtn = e.target.closest('.editMenuBtn');
                    if (editBtn) {
                        const id = editBtn.getAttribute('data-id');
                        document.getElementById('updateItemId').value = id;
                        document.getElementById('updateItemName').value = editBtn.getAttribute('data-name') || '';
                        const restId = editBtn.getAttribute('data-restaurant-id') || '';
                        if (restId) document.getElementById('updateRestaurantId').value = restId;
                        document.getElementById('updateCategory').value = editBtn.getAttribute('data-category') || '';
                        document.getElementById('updatePrice').value = editBtn.getAttribute('data-price') || '';
                        document.getElementById('updateDescription').value = editBtn.getAttribute('data-description') || '';
                        document.getElementById('updatePrepTime').value = editBtn.getAttribute('data-prep') || '15';
                        document.getElementById('updateStatus').value = editBtn.getAttribute('data-status') || 'available';
                        if (updateModal) updateModal.show();
                    }

                    const delBtn = e.target.closest('.deleteMenuBtn');
                    if (delBtn) {
                        const id = delBtn.getAttribute('data-id');
                        document.getElementById('deleteItemId').value = id;
                        if (deleteModal) deleteModal.show();
                    }
                });

                // Update submit
                document.getElementById('updateMenuItemSubmit').addEventListener('click', function() {
                    const uform = document.getElementById('updateMenuItemForm');
                    clearValidationErrors(uform);
                    const name = document.getElementById('updateItemName');
                    const restaurant = document.getElementById('updateRestaurantId');
                    const price = document.getElementById('updatePrice');
                    let valid = true;
                    if (!name.value.trim()) { showValidationError(name, 'Item name is required'); valid = false; }
                    if (!restaurant.value) { showValidationError(restaurant, 'Please select a restaurant'); valid = false; }
                    const p = parseFloat(price.value);
                    if (isNaN(p) || p <= 0) { showValidationError(price, 'Price must be a positive number'); valid = false; }
                    if (!valid) return;
                    uform.submit();
                });

                // Confirm delete
                document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
                    document.getElementById('deleteMenuItemForm').submit();
                });
                                            
            });
        })();
                                            
    </script>
</body>

</html>