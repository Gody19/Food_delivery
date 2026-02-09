<?php
include 'include/check_login.php';
include '../config/connection.php';

if (!isset($_SESSION['user_id'])) {
?>
    <!DOCTYPE html>
    <html>

    <head>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>

    <body>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Login required',
                text: 'Please login first!'
            }).then(() => {
                window.location.href = "../index.php";
            });
        </script>
    </body>

    </html>
<?php
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$success = false;

/* ---------- UPDATE PROFILE ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];

    if (!$username || !$email || !$phone) {
        $message = "All fields are required!";
    } else {

        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username=?, email=?, phone_number=?, password=? WHERE id=?");
            $stmt->bind_param("ssssi", $username, $email, $phone, $hashed, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username=?, email=?, phone_number=? WHERE id=?");
            $stmt->bind_param("sssi", $username, $email, $phone, $user_id);
        }

        if ($stmt->execute()) {
            $success = true;
            $message = "Profile updated successfully!";

            // update session
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['phone_number'] = $phone;
        } else {
            $message = "Update failed!";
        }

        $stmt->close();
    }
}

/* ---------- FETCH USER ---------- */
$stmt = $conn->prepare("SELECT username,email,phone_number,role FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html>

<head>
    <title>My Profile</title>
    <link rel="stylesheet" href="../Assets/fontawesome/css/all.min.css">
    <link href="../Assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Assets/sweetalert2/sweetalert2.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="../Assets/sweetalert2/sweetalert2.all.min.js"></script>

    <style>
        .card {
            max-width: 600px;
            margin: 50px auto;
            border-radius: 12px;
        }
    </style>
</head>

<body>

    <?php if ($message): ?>
        <script>
            Swal.fire({
                icon: '<?= $success ? "success" : "error" ?>',
                title: '<?= $success ? "Success" : "Error" ?>',
                text: "<?= $message ?>"
            });
        </script>
    <?php endif; ?>

    <div id="content">

        <?php include 'include/header.php'; ?>
        <div class="main-content">
            <?php include 'include/aside.php'; ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-4">My Profile</h3>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone_number']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>

</body>

</html>