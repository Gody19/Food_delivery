<?php
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                                    // Fetch menu items with restaurant names
                                    $sql = "SELECT m.id,r.restaurant_name,m.item_name,m.category,m.price,m.status 
                                            FROM menu_items m INNER JOIN restaurants r ON m.restaurant_id = r.id";
                                    $result = $conn->query($sql);
                                    if($result->num_rows > 0){
                                        while($item = $result->fetch_assoc()){

                                    ?>
                                    <td><?php echo $item['id']; ?></td>
                                    <td><strong><?php echo $item['item_name']; ?></strong></td>
                                    <td><?php echo $item['restaurant_name']; ?></td>
                                    <td><?php echo $item['category']; ?></td>
                                    <td><?php echo $item['price']; ?></td>
                                    <td><span class="badge <?php echo ($item['status'] === 'available') ? 'bg-success' : 'bg-warning'; ?>"><?php echo $item['status']; ?></span></td>
                                    <td>
                                        <button class="btn btn-admin btn-admin-primary btn-sm me-1">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-admin btn-admin-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tbody>

                                    <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="7" class="text-center">No menu items found.</td></tr>';
                                    }
                                    ?>
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

    <script src="../Assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../Assets/sweetalert2/sweetalert2.all.min.js"></script>
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
                const form = document.getElementById('addMenuItemForm');
                const submitBtn = document.getElementById('addMenuItemSubmit');
                const modalEl = document.getElementById('addMenuItemModal');

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
            });
        })();
    </script>
</body>

</html>