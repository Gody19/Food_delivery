<?php
include 'include/check_login.php';
include '../config/connection.php';

// Initialize
$success = false;
$message = '';

// Get restaurant ID from URL
$restaurantId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$restaurantId) {
    die("Invalid restaurant ID");
}

// Fetch restaurant from DB
$stmt = "SELECT * FROM restaurants WHERE id = $restaurantId";

$result = $conn->query($stmt);
$restaurant = $result->fetch_assoc();

if (!$restaurant) {
    die("Restaurant not found");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $restaurantName = trim($_POST['restaurant_name']);
    $ownerName = trim($_POST['owner_name']);
    $cuisineType = trim($_POST['cuisine_type']);
    $phoneNumber = trim($_POST['phone_number']);
    $address = trim($_POST['address']);
    $commissionRate = floatval($_POST['commission_rate']);
    $status = $_POST['status'];

    // Handle optional image upload
    $restaurantImage = $restaurant['restaurant_image']; // Keep old image by default
    if (!empty($_FILES['restaurant_image']['name'])) {
        $file = $_FILES['restaurant_image'];
        $allowedMime = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024;

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (in_array($mime, $allowedMime) && $file['size'] <= $maxSize) {
            $ext = image_type_to_extension(exif_imagetype($file['tmp_name']));
            $filename = uniqid('img_', true) . $ext;
            if (move_uploaded_file($file['tmp_name'], '../Assets/img/' . $filename)) {
                $restaurantImage = $filename;
            }
        }
    }

    // Update restaurant in DB
    $stmt = $conn->prepare("UPDATE restaurants SET restaurant_name=?, restaurant_owner=?, cuisine_type=?, phone_number=?, address=?, commission_rate=?, status=?, restaurant_image=? WHERE id=?");
    $stmt->bind_param(
        "sssssdssi",
        $restaurantName,
        $ownerName,
        $cuisineType,
        $phoneNumber,
        $address,
        $commissionRate,
        $status,
        $restaurantImage,
        $restaurantId
    );

    if ($stmt->execute()) {
        $success = true;
        $message = "Restaurant updated successfully!";
        // Refresh data
        $restaurant['restaurant_name'] = $restaurantName;
        $restaurant['restaurant_owner'] = $ownerName;
        $restaurant['cuisine_type'] = $cuisineType;
        $restaurant['phone_number'] = $phoneNumber;
        $restaurant['address'] = $address;
        $restaurant['commission_rate'] = $commissionRate;
        $restaurant['status'] = $status;
        $restaurant['restaurant_image'] = $restaurantImage;
    } else {
        $message = "Error updating restaurant: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Restaurant</title>
    <link rel="stylesheet" href="../Assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="../Assets/sweetalert2/sweetalert2.all.min.js"></script>
</head>

<body class="p-4">

    <?php if ($message): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: <?= $success ? "'success'" : "'error'" ?>,
                    title: <?= $success ? "'Success!'" : "'Error'" ?>,
                    text: <?= json_encode($message) ?>,
                    confirmButtonColor: <?= $success ? "'#28a745'" : "'#dc3545'" ?>
                });
            });
        </script>
    <?php endif; ?>
    <div id="content">
        <?php include 'include/header.php'; ?>
        <?php include 'include/aside.php'; ?>
        <div class="main-content">
            <!-- Restaurants Grid -->

            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="" method="post" enctype="multipart/form-data">

                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Restaurant Name *</label>
                                    <input type="text" name="restaurant_name" value="<?= htmlspecialchars($restaurant['restaurant_name'] ?? '') ?>" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Owner Name *</label>
                                    <input type="text" name="owner_name" value="<?= htmlspecialchars($restaurant['restaurant_owner'] ?? '') ?>" class="form-control" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Cuisine Type *</label>
                                    <select name="cuisine_type" class="form-control" required>
                                        <option value="">Select Cuisine</option>
                                        <option value="tanzanian" <?= $restaurant['cuisine_type'] == 'tanzanian' ? 'selected' : '' ?>>Tanzanian</option>
                                        <option value="indian" <?= $restaurant['cuisine_type'] == 'indian' ? 'selected' : '' ?>>Indian</option>
                                        <option value="chinese" <?= $restaurant['cuisine_type'] == 'chinese' ? 'selected' : '' ?>>Chinese</option>
                                        <option value="italian" <?= $restaurant['cuisine_type'] == 'italian' ? 'selected' : '' ?>>Italian</option>
                                        <option value="fast-food" <?= $restaurant['cuisine_type'] == 'fast-food' ? 'selected' : '' ?>>Fast Food</option>
                                        <option value="seafood" <?= $restaurant['cuisine_type'] == 'seafood' ? 'selected' : '' ?>>Seafood</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number *</label>
                                    <input type="tel" name="phone_number" value="<?= htmlspecialchars($restaurant['phone_number'] ?? '') ?>" class="form-control" pattern="\d{9}" placeholder="e.g. 712345678" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Address *</label>
                                <textarea name="address" class="form-control" rows="2" required><?= htmlspecialchars($restaurant['address'] ?? '') ?></textarea>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Commission Rate (%) *</label>
                                    <input type="number" name="commission_rate" class="form-control" value="<?= htmlspecialchars($restaurant['commission_rate'] ?? 15) ?>" min="5" max="30" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-control">
                                        <option value="active" <?= $restaurant['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                                        <option value="inactive" <?= $restaurant['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                        <option value="suspended" <?= $restaurant['status'] == 'suspended' ? 'selected' : '' ?>>Suspended</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Restaurant Image</label>
                                <?php if (!empty($restaurant['restaurant_image'])): ?>
                                    <div class="mb-2">
                                        <img src="../Assets/img/<?= htmlspecialchars($restaurant['restaurant_image']) ?>" class="img-thumbnail" style="max-height:150px;">
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="restaurant_image" accept="image/*" class="form-control">
                                <small class="text-muted">Optional. JPG, PNG, GIF only, max 5MB.</small>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Update Restaurant</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script src="../Assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../Assets/fontawesome/js/all.min.js"></script>
</body>

</html>