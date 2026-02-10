<?php
if (isset($_GET['timeout'])): ?>
<script src="../Assets/sweetalert2/sweetalert2.all.min.js"></script>
<script>
Swal.fire({
    icon: 'warning',
    title: 'Session expired',
    text: 'You were logged out due to inactivity'
});
</script>
<?php endif; ?>

<?php if (isset($_GET['unauthorized'])): ?>
<script src="../Assets/sweetalert2/sweetalert2.all.min.js"></script>
<script>
Swal.fire({
    icon: 'error',
    title: 'Unauthorized access',
    text: 'Please login as admin'
});
</script>
<?php endif; ?>
<?php

session_start();
include '../config/connection.php';

function sanitize($data)
{
    return htmlspecialchars(trim($data));
}

if (isset($_POST['login_email']) && isset($_POST['login_password'])) {

    $login_email = sanitize($_POST['login_email']);
    $login_password = $_POST['login_password'];

    if (!$login_email || !$login_password) {
        $error = "Email and password required";
    } else {

        $sql = "SELECT id, username, email, phone_number, password, role 
                FROM users 
                WHERE email = ? LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $login_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error = "Email not found";
        } else {

            $user = $result->fetch_assoc();

            if (!password_verify($login_password, $user['password'])) {
                $error = "Invalid password";
            } else {

                // SUCCESS LOGIN
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['phone_number'] = $user['phone_number'];
                $_SESSION['role'] = $user['role'];

                $success = true;
                $redirect = ($user['role'] === 'admin')
                    ? "dashboard.php"
                    : "index.php";
            }
        }

        $stmt->close();
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Food Delivery System</title>
    <link rel="stylesheet" href="../Assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../Assets/sweetalert2/sweetalert2.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            background-image: linear-gradient(to right bottom, #e6f2ff, #f5f9ff);
        }

        .login-container {
            display: flex;
            width: 900px;
            height: 600px;
            background-color: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0, 82, 204, 0.15);
        }

        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            top: -50px;
            left: -50px;
        }

        .left-panel::after {
            content: '';
            position: absolute;
            width: 150px;
            height: 150px;
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            bottom: -50px;
            right: -50px;
        }

        .logo {
            display: flex;
            align-items: center;
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }

        .logo-icon {
            font-size: 32px;
            margin-right: 15px;
            color: #5dade2;
        }

        .logo-text {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .welcome-text {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .welcome-desc {
            font-size: 16px;
            line-height: 1.6;
            opacity: 0.9;
            position: relative;
            z-index: 1;
            margin-bottom: 30px;
        }

        .features {
            list-style-type: none;
            position: relative;
            z-index: 1;
        }

        .features li {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .features i {
            margin-right: 12px;
            color: #5dade2;
        }

        .right-panel {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            margin-bottom: 40px;
        }

        .login-header h2 {
            color: #2a5298;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #666;
            font-size: 16px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #444;
            font-weight: 500;
            font-size: 15px;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #2a5298;
            font-size: 18px;
        }

        .form-control {
            width: 100%;
            padding: 16px 16px 16px 50px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
            background-color: #f9fbfd;
        }

        .form-control:focus {
            outline: none;
            border-color: #2a5298;
            box-shadow: 0 0 0 3px rgba(42, 82, 152, 0.1);
            background-color: white;
        }

        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .remember-me {
            display: flex;
            align-items: center;
        }

        .remember-me input {
            margin-right: 8px;
        }

        .remember-me label {
            color: #555;
            font-size: 14px;
        }

        .forgot-password {
            color: #2a5298;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .login-btn {
            background: linear-gradient(to right, #1e3c72, #2a5298);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 10px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            margin-bottom: 25px;
        }

        .login-btn:hover {
            background: linear-gradient(to right, #2a5298, #3a66c8);
            transform: translateY(-2px);
            box-shadow: 0 7px 15px rgba(42, 82, 152, 0.2);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }

        .footer-links {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .footer-links a {
            color: #2a5298;
            text-decoration: none;
            font-weight: 500;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        @media (max-width: 950px) {
            .login-container {
                width: 95%;
                height: auto;
            }
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                height: auto;
            }

            .left-panel {
                padding: 40px 30px;
            }

            .right-panel {
                padding: 40px 30px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <!-- Left Panel with Information -->
        <div class="left-panel">
            <div class="logo">
                <i class="fas fa-utensils logo-icon"></i>
                <div class="logo-text">Foodchap</div>
            </div>

            <h1 class="welcome-text">Admin Dashboard</h1>
            <p class="welcome-desc">
                Access the Foodchap administration panel to manage restaurants, orders, delivery personnel, and customer accounts.
            </p>

            <ul class="features d-none" style="display: none;">
                <li><i class="fas fa-check-circle"></i> Manage all restaurant partners</li>
                <li><i class="fas fa-check-circle"></i> Monitor real-time orders</li>
                <li><i class="fas fa-check-circle"></i> Track delivery personnel</li>
                <li><i class="fas fa-check-circle"></i> Generate sales reports</li>
                <li><i class="fas fa-check-circle"></i> Handle customer support</li>
            </ul>
        </div>

        <!-- Right Panel with Login Form -->
        <div class="right-panel">
            <div class="login-header">
                <h2>Admin Login</h2>
                <p>Sign in to your account to continue</p>
            </div>

            <form method="post" action="" id="loginForm">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="login_email" class="form-control" placeholder="Enter your username or email">
                    </div>
                    <div class="error-message" name="login_password" id="username-error">Please enter a valid username or email</div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="login_password" class="form-control" placeholder="Enter your password">
                    </div>
                    <div class="error-message" id="password-error">Password must be Enter</div>
                </div>

                <div class="options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="#" class="forgot-password">Forgot password?</a>
                </div>

                <button type="submit" class="login-btn">Sign In</button>
            </form>

            <div class="footer-links">
                <p>Need help? <a href="#">Contact System Administrator</a></p>
                <p>© 2023 Foodchap Admin System. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script src="../Assets/sweetalert2/sweetalert2.all.min.js"></script>
    <script>
        <?php if (isset($error)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Login Failed',
                text: '<?= $error ?>'
            });
        <?php endif; ?>

        <?php if (isset($success)): ?>
            Swal.fire({
                icon: 'success',
                title: 'Login Successful',
                text: 'Welcome <?= $_SESSION['username'] ?>',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.href = "<?= $redirect ?>";
            });
        <?php endif; ?>
        document.getElementById('loginForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const usernameError = document.getElementById('username-error');
            const passwordError = document.getElementById('password-error');

            let isValid = true;

            // Reset error messages
            usernameError.style.display = 'none';
            passwordError.style.display = 'none';

            // Username validation
            if (!username || username.length < 3) {
                usernameError.style.display = 'block';
                isValid = false;
            }

            // Password validation
            if (!password || password.length < 6) {
                passwordError.style.display = 'block';
                isValid = false;
            }

            // If form is valid, simulate login
            if (isValid) {
               document.getElementById('loginForm').submit();
            }
        });

        // Forgot password functionality
        document.querySelector('.forgot-password').addEventListener('click', function(event) {
            event.preventDefault();
            alert('A password reset link will be sent to your registered email address.');
        });
    </script>
</body>

</html>