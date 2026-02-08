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
            if (!filter_var($data, FILTER_VALIDATE_EMAIL)) return false;
            break;
        case 'int':
            $data = filter_var($data, FILTER_SANITIZE_NUMBER_INT);
            if (!filter_var($data, FILTER_VALIDATE_INT)) return false;
            break;
        case 'float':
            $data = filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            if (!filter_var($data, FILTER_VALIDATE_FLOAT)) return false;
            break;
        case 'url':
            $data = filter_var($data, FILTER_SANITIZE_URL);
            if (!filter_var($data, FILTER_VALIDATE_URL)) return false;
            break;
        case 'image':
            if (!isset($data['tmp_name']) || $data['error'] !== UPLOAD_ERR_OK) return false;
            $allowedMime = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            $uploadDir = '../Assets/img/';
            if ($data['size'] > $maxSize) return false;
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $data['tmp_name']);
            finfo_close($finfo);
            if (!in_array($mime, $allowedMime)) return false;
            $ext = image_type_to_extension(exif_imagetype($data['tmp_name']));
            $filename = uniqid('img_', true) . $ext;
            if (!move_uploaded_file($data['tmp_name'], $uploadDir . $filename)) return false;
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
        $stmt = $conn->prepare("INSERT INTO restaurants (restaurant_name, restaurant_owner, cuisine_type, phone_number, address, commission_rate, status, restaurant_image)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
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

// Fetch restaurants from database
$restaurants = [];
if (isset($conn) && !$conn->connect_error) {
    $query = "SELECT  r.id, r.restaurant_name, r.restaurant_owner, r.cuisine_type, r.phone_number, r.address, r.commission_rate,  r.status,  r.restaurant_image,
            COUNT(DISTINCT o.id) AS total_orders, IFNULL(SUM(o.total_amount), 0) AS total_amount
            FROM restaurants r LEFT JOIN menu_items m ON r.id = m.restaurant_id 
            LEFT JOIN order_items oi ON m.id = oi.menu_item_id LEFT JOIN orders o ON oi.order_id = o.id GROUP BY r.id ORDER BY r.id DESC";

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
    <link rel="stylesheet" href="../Assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../Assets/sweetalert2/sweetalert2.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php if (!empty($message)): ?>
        <script src="../Assets/sweetalert2/sweetalert2.all.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: <?= $success ? "'success'" : "'error'" ?>,
                    title: <?= $success ? "'Success!'" : "'Error'" ?>,
                    text: <?= json_encode($message) ?>,
                    confirmButtonColor: <?= $success ? "'#28a745'" : "'#dc3545'" ?>
                }).then(() => {
                    <?php if ($success): ?>window.location.href = 'restaurant.php';
                <?php endif; ?>
                });
            });
        </script>
    <?php endif; ?>

    <?php include 'include/aside.php'; ?>
    <div id="content">
        <?php include 'include/header.php'; ?>
        <div class="main-content">
            <div class="page-header">
                <h1>Restaurant Management</h1>
                <p>Manage restaurants in your delivery network</p>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addRestaurantModal">
                    <i class="fas fa-plus me-1"></i> Add Restaurant
                </button>
            </div>

            <!-- Restaurants Grid -->
            <div class="row mt-4">
                <?php if (empty($restaurants)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-store fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No restaurants found. Add one to get started.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($restaurants as $res): ?>
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <div>
                                            <h5 class="card-title mb-1"><?= htmlspecialchars($res['restaurant_name']) ?></h5>
                                            <p class="text-muted mb-0"><?= htmlspecialchars($res['cuisine_type']) ?></p>
                                        </div>
                                        <span class="badge <?= $res['status'] == 'active' ? 'bg-success' : ($res['status'] == 'inactive' ? 'bg-warning' : 'bg-danger') ?>"><?= htmlspecialchars($res['status']) ?></span>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <div class="text-muted small">Total Orders</div>
                                            <div class="fw-bold"><?= $res['total_orders'] ?></div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-muted small">Total Revenue</div>
                                            <div class="fw-bold">TZS <?= number_format($res['total_amount']) ?></div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-primary btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#restaurantDetailModal_<?= $res['id'] ?>">View Details</button>
                                        <a href="edit_restaurant.php?id=<?= $res['id'] ?>" class="btn btn-warning btn-sm flex-fill text-white">
                                            Edit <i class="fas fa-edit ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Restaurant Detail Modal -->
                        <div class="modal fade" id="restaurantDetailModal_<?= $res['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><?= htmlspecialchars($res['restaurant_name']) ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6 text-center mb-3">
                                                <?php if (!empty($res['restaurant_image'])): ?>
                                                    <img src="../Assets/img/<?= htmlspecialchars($res['restaurant_image']) ?>" class="img-fluid rounded" style="max-height:300px;object-fit:cover;">
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Status:</strong> <span class="badge <?= $res['status'] == 'active' ? 'bg-success' : ($res['status'] == 'inactive' ? 'bg-warning' : 'bg-danger') ?>"><?= htmlspecialchars($res['status']) ?></span></p>
                                                <p><strong>Owner:</strong> <?= htmlspecialchars($res['restaurant_owner']) ?></p>
                                                <p><strong>Cuisine Type:</strong> <?= htmlspecialchars($res['cuisine_type']) ?></p>
                                                <p><strong>Commission Rate:</strong> <?= $res['commission_rate'] ?>%</p>
                                                <p><strong>Phone:</strong> +255<?= htmlspecialchars($res['phone_number']) ?></p>
                                                <p><strong>Address:</strong> <?= htmlspecialchars($res['address']) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <!-- Add Restaurant Modal -->
            <div class="modal fade" id="addRestaurantModal" tabindex="-1" aria-labelledby="addRestaurantModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form action="" method="post" enctype="multipart/form-data">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addRestaurantModalLabel">Add New Restaurant</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Restaurant Name *</label>
                                        <input type="text" name="restaurant_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Owner Name *</label>
                                        <input type="text" name="owner_name" class="form-control" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Cuisine Type *</label>
                                        <select name="cuisine_type" class="form-control" required>
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
                                        <input type="tel" name="phone_number" class="form-control" pattern="\d{9}" placeholder="e.g. 712345678" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Address *</label>
                                    <textarea name="address" class="form-control" rows="2" required></textarea>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Commission Rate (%) *</label>
                                        <input type="number" name="commission_rate" class="form-control" value="15" min="5" max="30" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-control">
                                            <option value="active" selected>Active</option>
                                            <option value="inactive">Inactive</option>
                                            <option value="suspended">Suspended</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Restaurant Image</label>
                                    <input type="file" name="restaurant_image" accept="image/*" class="form-control">
                                    <small class="text-muted">Optional. JPG, PNG, GIF only, max 5MB.</small>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">Add Restaurant</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="../Assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../Assets/fontawesome/js/all.min.js"></script>
    <script src="../Assets/sweetalert2/sweetalert2.all.min.js"></script>
</body>

</html>