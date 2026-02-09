<?php
include 'include/check_login.php';
include '../config/connection.php';

// Check ID
if (!isset($_GET['id'])) {
    echo "User ID is required";
    header("Location: users.php");
    exit;
}

$user_id = intval($_GET['id']);

// Fetch user
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "User not found";
    exit;
}

$user = $result->fetch_assoc();

// Handle update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone_number'];
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if (!empty($password)) {

        if ($password !== $confirm) {
            $error = "Passwords do not match!";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $update = "UPDATE users SET username='$username', email='$email', phone_number='$phone', role='$role', password='$hashed' WHERE id=$user_id";
        }
    } else {
        // Update without changing password
        $update = "UPDATE users SET username='$username', email='$email', phone_number='$phone', role='$role' WHERE id=$user_id";
    }

    if (!isset($error) && $conn->query($update)) {
        header("Location: users.php?updated=1");
        exit;
    } else {
        $error = "Failed to update user";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="../Assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/aside.php'; ?>

    <div id="content">
        <h2>Edit User</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="auth-form">

            <form method="post">

                <div class="mb-3">
                    <label class="form-label">User Name <span>*</span></label>
                    <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email Address <span>*</span></label>
                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone Number <span>*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">+255</span>
                        <input type="tel" name="phone_number" class="form-control" value="<?= htmlspecialchars($user['phone_number']) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Role <span>*</span></label>
                    <select name="role" id="role" class="form-control">
                        <option  name="role" value="<?= htmlspecialchars($user['role']) ?>" required>
                        <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>user</option>
                        <option value="restaurant_owner" <?= $user['role'] == 'restaurant_owner' ? 'selected' : '' ?>>Restaurant Owner</option>
                        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        New Password (leave empty to keep current)
                    </label>
                    <input type="password" name="password" class="form-control" placeholder="Enter new password">
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm password">
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Update Account
                </button>

            </form>

        </div>
    </div>

    <?php include 'include/footer.php'; ?>
</body>

</html>