<?php
include 'config/connection.php';

// Initialize message variables
$success = false;
$message = '';

function sanitize($data, $type = 'string') {

    if ($data === null) return false;

    switch ($type) {

        case 'string':
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
            break;

        case 'phone':
            $data = preg_replace('/\D+/', '', $data);
            if (!preg_match('/^\d{9}$/', $data)) {
                return false;
            }
            break;

        case 'role':
            $allowed = ['user', 'restaurant_owner', 'admin'];
            $data = strtolower(trim($data));
            if (!in_array($data, $allowed)) {
                return false;
            }
            break;

        case 'password':
            $data = trim($data);
            if (strlen($data) < 6) {
                return false;
            }
            break;

        case 'email':
            $data = filter_var(trim($data), FILTER_SANITIZE_EMAIL);
            if (!filter_var($data, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
            break;

        case 'url':
            $data = filter_var($data, FILTER_SANITIZE_URL);
            if (!filter_var($data, FILTER_VALIDATE_URL)) {
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

        default:
            return false;
    }

    return $data;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '', 'string');
    $email = sanitize($_POST['email'] ?? '', 'email');
    $phone_number = sanitize($_POST['phone_number'] ?? '', 'phone');
    $password = sanitize($_POST['password'] ?? '', 'password');

    $role = 'user'; // default role

    // Validate sanitized results
    if (!$username || !$email || !$phone_number || !$password) {
        $message = 'Invalid input detected.';
    } elseif (!isset($conn) || $conn->connect_error) {
        $message = 'Database connection error.';
    } else {
        // Check duplicate email
        $sql = "SELECT id FROM users WHERE email = ? LIMIT 1";
        $query = $conn->prepare($sql);
        if (!$query) {
            $message = 'Prepare failed: ' . $conn->error;
        } else {
            $query->bind_param('s', $email);
            $query->execute();
            $query->store_result();

            if ($query->num_rows > 0) {
                $message = 'Email already registered.';
                $query->close();
            } else {
                $query->close();

                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                // Insert user using prepared statement
                $sql = "INSERT INTO users (username, email, phone_number, role, password) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    $message = 'Prepare failed: ' . $conn->error;
                } else {
                    $stmt->bind_param('sssss', $username, $email, $phone_number, $role, $hashedPassword);
                    if ($stmt->execute()) {
                        $success = true;
                        $message = 'User registered successfully.';
                    } else {
                        $message = 'Error: ' . $stmt->error;
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
    <title>FoodChap Tanzania | Food Delivery System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="Assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="Assets/fontawesome/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="Assets/sweetalert2/sweetalert2.min.css">
    <link rel="stylesheet" href="Assets/css/index.css" >
</head>
<body>
    <!-- Display registration message from backend using SweetAlert -->
    <?php if (!empty($message)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: <?php echo $success ? "'success'" : "'error'"; ?>,
                title: <?php echo $success ? "'Success!'" : "'Registration Failed'"; ?>,
                text: <?php echo json_encode($message); ?>,
                confirmButtonColor: <?php echo $success ? "'#28a745'" : "'#dc3545'"; ?>
            }).then(() => {
                <?php if ($success): ?>
                    // Reset form and close modal on success
                    document.getElementById('registerFormElement').reset();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('authModal'));
                    if (modal) modal.hide();
                <?php endif; ?>
            });
        });
    </script>
    <?php endif; ?>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-utensils me-2"></i>FoodChap <span class="tz-flag">🇹🇿</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#restaurants">Restaurants</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#menu">Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#testimonials">Testimonials</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#payment-methods">Payments</a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center">
                    <div class="input-group me-3 d-none d-md-flex">
                        <input type="text" class="form-control" placeholder="Search for food or restaurants">
                        <button class="btn btn-outline-primary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    
                    <button class="btn btn-outline-primary position-relative me-2" id="cartToggle">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="badge-cart" id="cartCount">0</span>
                    </button>
                    
                    <!-- User Authentication -->
                    <div class="dropdown" id="authDropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i> Sign In
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#authModal">Sign In / Register</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" id="viewOrders">My Orders</a></li>
                            <li><a class="dropdown-item" href="#" id="viewProfile">My Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item d-none" href="#" id="logoutBtn">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Authentication Modal -->
    <div class="modal fade" id="authModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content auth-modal-content">
                <div class="auth-header">
                    <h3 class="mb-0">Welcome to FoodChap</h3>
                    <p class="mb-0">Sign in or create an account</p>
                </div>
                <div class="auth-body">
                    <div class="d-flex justify-content-center mb-4">
                        <div class="auth-tab active" id="loginTab">Sign In</div>
                        <div class="auth-tab" id="registerTab">Register</div>
                    </div>
                    
                    <!-- Login Form -->
                    <div class="auth-form active" id="loginForm">
                        <form id="loginFormElement">
                            <div class="mb-3">
                                <label for="loginEmail" class="form-label">Email or Phone Number</label>
                                <input type="text" class="form-control" id="loginEmail" placeholder="Enter email or phone" required>
                            </div>
                            <div class="mb-3">
                                <label for="loginPassword" class="form-label">Password</label>
                                <input type="password" class="form-control" id="loginPassword" placeholder="Enter password" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="rememberMe">
                                <label class="form-check-label" for="rememberMe">Remember me</label>
                                <a href="#" class="float-end">Forgot password?</a>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">Sign In</button>
                            
                            <div class="text-center mb-3">Or sign in with</div>
                            
                            <div class="row">
                                <div class="col-6">
                                    <button type="button" class="social-login-btn">
                                        <i class="fab fa-google me-2"></i> Google
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="social-login-btn">
                                        <i class="fab fa-facebook me-2"></i> Facebook
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Register Form -->
                    <div class="auth-form" id="registerForm">
                        <form method="post" action="index.php" id="registerFormElement">
                           
                                <div class="mb-3">
                                    <label for="username" class="form-label">User Name <span>*</span></label>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter user name" >
                            </div>
                            <div class="mb-3">
                                <label for="registerEmail" class="form-label">Email Address <span>*</span></label>
                                <input type="email" class="form-control" name="email" id="registerEmail" placeholder="Enter email" >
                            </div>
                            <div class="mb-3">
                                <label for="phoneNumber" class="form-label">Phone Number <span>*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">+255</span>
                                    <input type="tel" name="phone_number" class="form-control" id="phoneNumber" placeholder="712345678" >
                                </div>
                            </div>
                            <div class="mb-3 d-none">
                                <label for="" class="form-label">Role <span>*</span></label>
                                <input type="text" name="role" class="form-control" placeholder="Enter role" value="user">
                            </div>
                            <div class="mb-3">
                                <label for="registerPassword" class="form-label">Password <span>*</span></label>
                                <input type="password" name="password" class="form-control" id="registerPassword" placeholder="Enter password" >
                            </div>
                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Confirm Password <span>*</span></label>
                                <input type="password" name="confirm_password" class="form-control" id="confirmPassword" placeholder="Confirm password" >
                            </div>
                            <!-- <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="agreeTerms" required>
                                <label class="form-check-label" for="agreeTerms">I agree to the <a href="#">Terms & Conditions</a></label>
                            </div> -->
                            <button type="submit" class="btn btn-primary w-100">Create Account</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cart Sidebar -->
    <div class="cart-overlay" id="cartOverlay"></div>
    
    <div class="cart-sidebar" id="cartSidebar">
        <div class="p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">Your Cart</h4>
                <button class="btn btn-sm btn-outline-secondary" id="closeCart">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="cartItems">
                <!-- Cart items will be dynamically added here -->
                <div class="text-center text-muted py-4" id="emptyCartMessage">
                    <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                    <p>Your cart is empty</p>
                </div>
            </div>
            
            <div class="order-summary mt-4 d-none" id="orderSummary">
                <h5 class="mb-3">Order Summary</h5>
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal</span>
                    <span id="cartSubtotal">TZS 0</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Delivery Fee</span>
                    <span id="deliveryFee">TZS 2,500</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>Tax (18%)</span>
                    <span id="cartTax">TZS 0</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-4">
                    <strong>Total</strong>
                    <strong id="cartTotal">TZS 0</strong>
                </div>
                
                <!-- Payment Method Selection -->
                <div class="mb-4">
                    <h6 class="mb-3">Select Payment Method</h6>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="payment-option form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="mpesaPayment" value="mpesa" checked>
                                <label class="form-check-label" for="mpesaPayment">
                                    <i class="fas fa-mobile-alt mpesa-color me-1"></i> M-Pesa
                                </label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="payment-option form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="tigoPesaPayment" value="tigopesa">
                                <label class="form-check-label" for="tigoPesaPayment">
                                    <i class="fas fa-mobile-alt tigo-color me-1"></i> Tigo Pesa
                                </label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="payment-option form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="airtelMoneyPayment" value="airtelmoney">
                                <label class="form-check-label" for="airtelMoneyPayment">
                                    <i class="fas fa-mobile-alt airtel-color me-1"></i> Airtel Money
                                </label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="payment-option form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="crdbPayment" value="crdb">
                                <label class="form-check-label" for="crdbPayment">
                                    <i class="fas fa-university crdb-color me-1"></i> CRDB Bank
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button class="btn btn-primary w-100" id="checkoutBtn">
                    <i class="fas fa-lock me-2"></i>Proceed to Checkout
                </button>
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero-section" id="home">
        <div class="container">
            <div class="text-center">
                <h1 class="hero-title">Delicious food delivered to your door</h1>
                <p class="lead mb-4">Order from your favorite restaurants in Tanzania and get it delivered in minutes</p>
                
                <div class="search-box">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-lg" placeholder="What are you craving today?">
                        <button class="btn btn-primary btn-lg" type="button">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container mt-5">
        <!-- Restaurant Menu Header (Hidden by default) -->
        <div class="restaurant-menu-header" id="restaurantMenuHeader">
            <div class="row align-items-center">
                <div class="col-md-9">
                    <div class="d-flex align-items-center">
                        <a class="back-to-restaurants me-3" id="backToRestaurants">
                            <i class="fas fa-arrow-left me-1"></i> Back to Restaurants
                        </a>
                        <div>
                            <h2 class="restaurant-name-display mb-1" id="restaurantNameDisplay">Restaurant Name</h2>
                            <div class="restaurant-info-display">
                                <span id="restaurantCuisineDisplay">Cuisine Type</span> • 
                                <span id="restaurantRatingDisplay">Rating</span> • 
                                <span id="restaurantDeliveryDisplay">Delivery Time</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 text-md-end mt-2 mt-md-0">
                    <button class="btn btn-primary" id="orderFromRestaurant">
                        <i class="fas fa-shopping-cart me-1"></i> Order Now
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Restaurants Section -->
        <section id="restaurants" class="mb-5">
            <h2 class="section-title">Popular Restaurants in Tanzania</h2>
            <div class="row" id="restaurantsContainer">
                <!-- Restaurants will be loaded dynamically -->
            </div>
        </section>
        
        <!-- Menu Section -->
        <section id="menu" class="mb-5">
            <h2 class="section-title">Today's Specials</h2>
            <div class="row" id="menuContainer">
                <!-- Menu items will be loaded dynamically -->
            </div>
        </section>
        
        <!-- Restaurant Menu Section -->
        <section id="restaurantMenuSection" class="mb-5" style="display: none;">
            <h2 class="section-title" id="restaurantMenuTitle">Menu</h2>
            <div class="row" id="restaurantMenuContainer">
                <!-- Restaurant-specific menu items will be loaded dynamically -->
            </div>
        </section>
        
        <!-- Testimonials Section -->
        <section id="testimonials" class="mb-5">
            <h2 class="section-title">What Our Customers Say</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="testimonial-card">
                        <div class="testimonial-rating mb-3">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"FoodChap has transformed how I order food in Dar es Salaam. The delivery is always on time and the food is fresh!"</p>
                        <div class="d-flex align-items-center">
                            <div class="user-avatar me-3">JM</div>
                            <div>
                                <div class="testimonial-author">Joseph Mwambene</div>
                                <div class="text-muted small">Dar es Salaam</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="testimonial-card">
                        <div class="testimonial-rating mb-3">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <p class="testimonial-text">"I love the variety of restaurants available. Being able to pay with M-Pesa makes everything so convenient!"</p>
                        <div class="d-flex align-items-center">
                            <div class="user-avatar me-3">AK</div>
                            <div>
                                <div class="testimonial-author">Aisha Kimaro</div>
                                <div class="text-muted small">Arusha</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="testimonial-card">
                        <div class="testimonial-rating mb-3">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"As a busy professional in Dodoma, FoodChap saves me so much time. The food always arrives hot and delicious."</p>
                        <div class="d-flex align-items-center">
                            <div class="user-avatar me-3">DK</div>
                            <div>
                                <div class="testimonial-author">David Kato</div>
                                <div class="text-muted small">Dodoma</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Payment Methods Section -->
        <section id="payment-methods" class="mb-5">
            <h2 class="section-title">Tanzania Payment Methods</h2>
            <p class="mb-4">We support all popular payment methods in Tanzania for your convenience</p>
            <div class="row">
                <div class="col-md-3 col-6 mb-4">
                    <div class="payment-method-card">
                        <div class="payment-icon mpesa-color">
                            <i class="fas fa-mobile-alt fa-2x"></i>
                        </div>
                        <h5>Vodacom M-Pesa</h5>
                        <p class="small text-muted mb-0">Pay instantly with your M-Pesa wallet</p>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="payment-method-card">
                        <div class="payment-icon tigo-color">
                            <i class="fas fa-mobile-alt fa-2x"></i>
                        </div>
                        <h5>Tigo Pesa</h5>
                        <p class="small text-muted mb-0">Secure payments with Tigo Pesa</p>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="payment-method-card">
                        <div class="payment-icon airtel-color">
                            <i class="fas fa-mobile-alt fa-2x"></i>
                        </div>
                        <h5>Airtel Money</h5>
                        <p class="small text-muted mb-0">Quick payments via Airtel Money</p>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="payment-method-card">
                        <div class="payment-icon crdb-color">
                            <i class="fas fa-university fa-2x"></i>
                        </div>
                        <h5>CRDB Bank</h5>
                        <p class="small text-muted mb-0">Bank transfers and card payments</p>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-shield-alt text-primary me-2"></i>Secure Payments</h5>
                            <p class="card-text">All payments are encrypted and secure. Your financial information is never stored on our servers.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-bolt text-warning me-2"></i>Instant Confirmation</h5>
                            <p class="card-text">Get instant payment confirmation and order updates via SMS and email.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="mb-3"><i class="fas fa-utensils me-2"></i>FoodChap <span class="tz-flag">🇹🇿</span></h5>
                    <p>Delivering happiness to your doorstep across Tanzania since 2023. Order from the best restaurants in your city.</p>
                </div>
                <div class="col-md-2 mb-4">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#home" class="text-white-50 text-decoration-none">Home</a></li>
                        <li><a href="#restaurants" class="text-white-50 text-decoration-none">Restaurants</a></li>
                        <li><a href="#menu" class="text-white-50 text-decoration-none">Menu</a></li>
                        <li><a href="#payment-methods" class="text-white-50 text-decoration-none">Payments</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h5 class="mb-3">Cities We Serve</h5>
                    <ul class="list-unstyled">
                        <li class="mb-1">Dar es Salaam</li>
                        <li class="mb-1">Arusha</li>
                        <li class="mb-1">Mwanza</li>
                        <li class="mb-1">Dodoma</li>
                        <li>Zanzibar</li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h5 class="mb-3">Download Our App</h5>
                    <div class="d-flex flex-column">
                        <button class="btn btn-outline-light mb-2">
                            <i class="fab fa-apple me-2"></i>App Store
                        </button>
                        <button class="btn btn-outline-light">
                            <i class="fab fa-google-play me-2"></i>Google Play
                        </button>
                    </div>
                </div>
            </div>
            <hr class="mt-0 mb-4">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2026 FoodChap Tanzania. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-white-50 me-3"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white-50 me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white-50 me-3"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white-50"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="Assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="Assets/sweetalert2/sweetalert2.all.min.js"></script>
    
    <script>
        // Enhanced data structure with restaurant-food relationships
        const restaurants = [
            {
                id: 1,
                name: "Mama Ntilie Restaurant",
                cuisine: "Traditional Tanzanian, Nyama Choma",
                rating: 4.7,
                deliveryTime: "25-35 min",
                image: "https://images.unsplash.com/photo-1565299585323-38d6b0865b47?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80",
                menuItems: [101, 105, 107, 108]
            },
            {
                id: 2,
                name: "Burger King Dar",
                cuisine: "Burgers, American, Fast Food",
                rating: 4.5,
                deliveryTime: "15-25 min",
                image: "https://images.unsplash.com/photo-1568901346375-23c9450c58cd?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80",
                menuItems: [102, 109, 110, 111]
            },
            {
                id: 3,
                name: "Tokyo Sushi Lounge",
                cuisine: "Japanese, Sushi, Asian",
                rating: 4.8,
                deliveryTime: "30-40 min",
                image: "https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80",
                menuItems: [103, 112, 113, 114]
            },
            {
                id: 4,
                name: "Mexican Grill Arusha",
                cuisine: "Mexican, Tacos, Street Food",
                rating: 4.4,
                deliveryTime: "20-30 min",
                image: "https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80",
                menuItems: [104, 115, 116, 117]
            }
        ];

        const menuItems = [
            {
                id: 101,
                name: "Nyama Choma Special",
                description: "Grilled goat meat served with ugali and kachumbari",
                price: 18000,
                rating: 4.8,
                category: "Traditional",
                restaurantId: 1,
                image: "https://images.unsplash.com/photo-1546833999-b9f581a1996d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80"
            },
            {
                id: 102,
                name: "Cheeseburger Deluxe",
                description: "Beef patty with cheddar, lettuce, tomato, and special sauce",
                price: 12000,
                rating: 4.6,
                category: "Burger",
                restaurantId: 2,
                image: "https://images.unsplash.com/photo-1568901346375-23c9450c58cd?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80"
            },
            {
                id: 103,
                name: "California Roll",
                description: "Crab, avocado, and cucumber roll with sesame seeds",
                price: 15000,
                rating: 4.7,
                category: "Sushi",
                restaurantId: 3,
                image: "https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80"
            },
            {
                id: 104,
                name: "Chicken Tacos",
                description: "Soft tacos with grilled chicken, salsa, and fresh cilantro",
                price: 10000,
                rating: 4.5,
                category: "Mexican",
                restaurantId: 4,
                image: "https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80"
            },
            {
                id: 105,
                name: "Wali na Maharage",
                description: "Rice with beans cooked in coconut milk, Tanzanian style",
                price: 8000,
                rating: 4.9,
                category: "Traditional",
                restaurantId: 1,
                image: "https://images.unsplash.com/photo-1598866594230-a7c12756260f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80"
            },
            {
                id: 106,
                name: "Caesar Salad",
                description: "Fresh romaine lettuce with croutons and caesar dressing",
                price: 9000,
                rating: 4.4,
                category: "Salad",
                restaurantId: null,
                image: "https://images.unsplash.com/photo-1546793665-c74683f339c1?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80"
            }
        ];

        // Cart data
        let cart = [];
        const deliveryFee = 2500; // TZS
        const taxRate = 0.18; // 18% VAT in Tanzania
        
        // Current restaurant view state
        let currentRestaurantView = null;
        
        // User authentication state
        let currentUser = null;

        // DOM elements
        const cartSidebar = document.getElementById('cartSidebar');
        const cartOverlay = document.getElementById('cartOverlay');
        const cartToggle = document.getElementById('cartToggle');
        const closeCart = document.getElementById('closeCart');
        const cartCount = document.getElementById('cartCount');
        const cartItems = document.getElementById('cartItems');
        const emptyCartMessage = document.getElementById('emptyCartMessage');
        const orderSummary = document.getElementById('orderSummary');
        const cartSubtotal = document.getElementById('cartSubtotal');
        const cartTax = document.getElementById('cartTax');
        const cartTotal = document.getElementById('cartTotal');
        const checkoutBtn = document.getElementById('checkoutBtn');
        const restaurantsContainer = document.getElementById('restaurantsContainer');
        const menuContainer = document.getElementById('menuContainer');
        const restaurantMenuHeader = document.getElementById('restaurantMenuHeader');
        const restaurantNameDisplay = document.getElementById('restaurantNameDisplay');
        const restaurantCuisineDisplay = document.getElementById('restaurantCuisineDisplay');
        const restaurantRatingDisplay = document.getElementById('restaurantRatingDisplay');
        const restaurantDeliveryDisplay = document.getElementById('restaurantDeliveryDisplay');
        const backToRestaurants = document.getElementById('backToRestaurants');
        const orderFromRestaurant = document.getElementById('orderFromRestaurant');
        const restaurantMenuSection = document.getElementById('restaurantMenuSection');
        const restaurantMenuContainer = document.getElementById('restaurantMenuContainer');
        const restaurantMenuTitle = document.getElementById('restaurantMenuTitle');
        
        // Authentication elements
        const authModal = new bootstrap.Modal(document.getElementById('authModal'));
        const loginTab = document.getElementById('loginTab');
        const registerTab = document.getElementById('registerTab');
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const loginFormElement = document.getElementById('loginFormElement');
        const registerFormElement = document.getElementById('registerFormElement');
        const userDropdown = document.getElementById('userDropdown');
        const logoutBtn = document.getElementById('logoutBtn');
        const viewOrders = document.getElementById('viewOrders');
        const viewProfile = document.getElementById('viewProfile');

        // Format currency for Tanzania Shillings
        function formatCurrency(amount) {
            return 'TZS ' + amount.toLocaleString('en-TZ');
        }

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            loadRestaurants();
            loadMenuItems();
            updateCartCount();
            
            // Check if user is already logged in
            checkLoggedInUser();
            
            // Event listeners for cart
            cartToggle.addEventListener('click', toggleCart);
            closeCart.addEventListener('click', toggleCart);
            cartOverlay.addEventListener('click', toggleCart);
            checkoutBtn.addEventListener('click', checkout);
            
            // Event listeners for restaurant view
            backToRestaurants.addEventListener('click', showAllRestaurants);
            orderFromRestaurant.addEventListener('click', function() {
                if (currentRestaurantView) {
                    document.getElementById('restaurantMenuSection').scrollIntoView({ behavior: 'smooth' });
                }
            });
            
            // Authentication event listeners
            loginTab.addEventListener('click', () => switchAuthTab('login'));
            registerTab.addEventListener('click', () => switchAuthTab('register'));
            
            loginFormElement.addEventListener('submit', handleLogin);
            registerFormElement.addEventListener('submit', handleRegister);
            
            logoutBtn.addEventListener('click', handleLogout);
            viewOrders.addEventListener('click', handleViewOrders);
            viewProfile.addEventListener('click', handleViewProfile);
        });

        // Check if user is logged in
        function checkLoggedInUser() {
            const savedUser = localStorage.getItem('foodExpressUser');
            if (savedUser) {
                currentUser = JSON.parse(savedUser);
                updateUserInterface();
            }
        }

        // Update user interface based on login state
        function updateUserInterface() {
            if (currentUser) {
                userDropdown.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div class="user-avatar me-2">${currentUser.firstName.charAt(0)}${currentUser.lastName.charAt(0)}</div>
                        <span>${currentUser.firstName}</span>
                    </div>
                `;
                logoutBtn.classList.remove('d-none');
            } else {
                userDropdown.innerHTML = '<i class="fas fa-user me-1"></i> Sign In';
                logoutBtn.classList.add('d-none');
            }
        }

        // Switch between login and register tabs
        function switchAuthTab(tab) {
            if (tab === 'login') {
                loginTab.classList.add('active');
                registerTab.classList.remove('active');
                loginForm.classList.add('active');
                registerForm.classList.remove('active');
            } else {
                registerTab.classList.add('active');
                loginTab.classList.remove('active');
                registerForm.classList.add('active');
                loginForm.classList.remove('active');
            }
        }

        // Handle login form submission
        function handleLogin(e) {
            e.preventDefault();
            
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            
            // Simple validation
            if (!email || !password) {
                showAlert('Please fill in all fields', 'danger');
                return;
            }
            
            // Mock authentication - in real app, this would be an API call
            if (password.length < 6) {
                showAlert('Password must be at least 6 characters', 'danger');
                return;
            }
            
            // Create mock user
            currentUser = {
                firstName: 'John',
                lastName: 'Doe',
                email: email,
                phone: '+255712345678'
            };
            
            // Save to localStorage
            localStorage.setItem('foodExpressUser', JSON.stringify(currentUser));
            
            // Update UI
            updateUserInterface();
            
            // Close modal and show success
            authModal.hide();
            showAlert('Successfully logged in!', 'success');
            
            // Reset form
            loginFormElement.reset();
        }


        // Handle register form submission: validate and submit normally
        function handleRegister(e) {
            e.preventDefault();

            // Clear previous error messages
            clearValidationErrors();

            const userName = document.getElementById('username').value.trim();
            const email = document.getElementById('registerEmail').value.trim();
            const phone = document.getElementById('phoneNumber').value.trim();
            const password = document.getElementById('registerPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            let hasErrors = false;

            // Validation - prevent submission if validation fails
            if (!userName || !email || !phone || !password || !confirmPassword) {
                showValidationError('username', 'Please fill in all fields');
                showValidationError('registerEmail', 'Please fill in all fields');
                showValidationError('phoneNumber', 'Please fill in all fields');
                showValidationError('registerPassword', 'Please fill in all fields');
                showValidationError('confirmPassword', 'Please fill in all fields');
                hasErrors = true;
            }

            if (userName && userName.length < 3) {
                showValidationError('username', 'Username must be at least 3 characters');
                hasErrors = true;
            }

            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showValidationError('registerEmail', 'Please enter a valid email address');
                hasErrors = true;
            }

            if (phone && !/^\d{9}$/.test(phone)) {
                showValidationError('phoneNumber', 'Phone number must be 9 digits');
                hasErrors = true;
            }

            if (password && password.length < 6) {
                showValidationError('registerPassword', 'Password must be at least 6 characters');
                hasErrors = true;
            }

            if (password && confirmPassword && password !== confirmPassword) {
                showValidationError('confirmPassword', 'Passwords do not match');
                hasErrors = true;
            }

            // If validation fails, keep modal open and display errors
            if (hasErrors) {
                return;
            }

            // Validation passed — submit the form normally
            registerFormElement.submit();
        }

        
        // Helper function to show validation errors
        function showValidationError(fieldId, errorMessage) {
            const field = document.getElementById(fieldId);
            const formGroup = field.closest('.mb-3');
            
            // Add error class to field
            field.classList.add('is-invalid');
            
            // Create or update error message
            let errorElement = formGroup.querySelector('.invalid-feedback');
            if (!errorElement) {
                errorElement = document.createElement('div');
                errorElement.className = 'invalid-feedback d-block';
                formGroup.appendChild(errorElement);
            }
            errorElement.textContent = errorMessage;
        }
        
        // Helper function to clear validation errors
        function clearValidationErrors() {
            const registerForm = document.getElementById('registerForm');
            const fields = registerForm.querySelectorAll('.is-invalid');
            const errorMessages = registerForm.querySelectorAll('.invalid-feedback');
            
            fields.forEach(field => {
                field.classList.remove('is-invalid');
            });
            
            errorMessages.forEach(msg => {
                msg.remove();
            });
        }

        // Handle logout
        function handleLogout() {
            currentUser = null;
            localStorage.removeItem('foodExpressUser');
            updateUserInterface();
            showAlert('Successfully logged out', 'success');
        }

        // Handle view orders
        function handleViewOrders(e) {
            e.preventDefault();
            if (!currentUser) {
                authModal.show();
                return;
            }
            showAlert('Your orders will be displayed here', 'info');
        }

        // Handle view profile
        function handleViewProfile(e) {
            e.preventDefault();
            if (!currentUser) {
                authModal.show();
                return;
            }
            showAlert('Your profile information will be displayed here', 'info');
        }

        // Show alert message
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            alertDiv.style.top = '100px';
            alertDiv.style.right = '20px';
            alertDiv.style.zIndex = '2000';
            alertDiv.style.minWidth = '300px';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(alertDiv);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        // Load restaurants to the page
        function loadRestaurants() {
            restaurantsContainer.innerHTML = '';
            
            restaurants.forEach(restaurant => {
                const restaurantCard = document.createElement('div');
                restaurantCard.className = 'col-md-6 col-lg-3';
                restaurantCard.innerHTML = `
                    <div class="restaurant-card card h-100 border-0 shadow-sm">
                        <img src="${restaurant.image}" class="restaurant-img card-img-top" alt="${restaurant.name}">
                        <div class="card-body">
                            <h5 class="card-title">${restaurant.name}</h5>
                            <p class="card-text text-muted small">${restaurant.cuisine}</p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="restaurant-rating">
                                    <i class="fas fa-star"></i> ${restaurant.rating}
                                </div>
                                <span class="text-muted">${restaurant.deliveryTime}</span>
                            </div>
                            <button class="btn view-menu-btn w-100" data-id="${restaurant.id}">
                                <i class="fas fa-utensils me-1"></i> View Menu
                            </button>
                        </div>
                    </div>
                `;
                restaurantsContainer.appendChild(restaurantCard);
            });
            
            // Add event listeners to "View Menu" buttons
            document.querySelectorAll('.view-menu-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const restaurantId = parseInt(this.getAttribute('data-id'));
                    showRestaurantMenu(restaurantId);
                });
            });
        }

        // Load all menu items to the page
        function loadMenuItems() {
            menuContainer.innerHTML = '';
            
            // Show only first 6 items as "Today's Specials"
            const specialItems = menuItems.slice(0, 6);
            
            specialItems.forEach(item => {
                const menuItemCard = createMenuItemCard(item);
                menuContainer.appendChild(menuItemCard);
            });
        }

        // Create a menu item card element
        function createMenuItemCard(item) {
            const menuItemCard = document.createElement('div');
            menuItemCard.className = 'col-md-6 col-lg-4';
            menuItemCard.innerHTML = `
                <div class="food-card card h-100 border-0 shadow-sm">
                    <img src="${item.image}" class="food-img card-img-top" alt="${item.name}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0">${item.name}</h5>
                            <div class="food-price">${formatCurrency(item.price)}</div>
                        </div>
                        <p class="card-text text-muted small">${item.description}</p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <span class="food-category">${item.category}</span>
                                <div class="food-rating d-inline-block">
                                    <i class="fas fa-star"></i> ${item.rating}
                                </div>
                            </div>
                            <button class="btn btn-primary btn-sm add-to-cart" data-id="${item.id}" data-name="${item.name}" data-price="${item.price}" data-image="${item.image}">
                                <i class="fas fa-plus me-1"></i> Add
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            // Add event listener to "Add to Cart" button
            const addButton = menuItemCard.querySelector('.add-to-cart');
            addButton.addEventListener('click', function() {
                const id = parseInt(this.getAttribute('data-id'));
                const name = this.getAttribute('data-name');
                const price = parseFloat(this.getAttribute('data-price'));
                const image = this.getAttribute('data-image');
                
                addToCart(id, name, price, image);
            });
            
            return menuItemCard;
        }

        // Show restaurant-specific menu
        function showRestaurantMenu(restaurantId) {
            const restaurant = restaurants.find(r => r.id === restaurantId);
            
            if (!restaurant) return;
            
            // Set current restaurant view
            currentRestaurantView = restaurant;
            
            // Update restaurant menu header
            restaurantNameDisplay.textContent = restaurant.name;
            restaurantCuisineDisplay.textContent = restaurant.cuisine;
            restaurantRatingDisplay.textContent = `${restaurant.rating} ★`;
            restaurantDeliveryDisplay.textContent = restaurant.deliveryTime;
            
            // Show restaurant menu header and hide regular sections
            restaurantMenuHeader.classList.add('active');
            document.getElementById('restaurants').style.display = 'none';
            document.getElementById('menu').style.display = 'none';
            restaurantMenuSection.style.display = 'block';
            restaurantMenuTitle.textContent = `${restaurant.name} Menu`;
            
            // Load restaurant menu items
            loadRestaurantMenuItems(restaurantId);
            
            // Scroll to top of menu section
            restaurantMenuSection.scrollIntoView({ behavior: 'smooth' });
        }

        // Load restaurant-specific menu items
        function loadRestaurantMenuItems(restaurantId) {
            restaurantMenuContainer.innerHTML = '';
            
            // Get menu items for this restaurant
            const restaurantMenuItems = menuItems.filter(item => 
                item.restaurantId === restaurantId || restaurantId === item.restaurantId
            );
            
            if (restaurantMenuItems.length === 0) {
                restaurantMenuContainer.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">Menu coming soon</h4>
                        <p class="text-muted">This restaurant is currently updating their menu.</p>
                    </div>
                `;
                return;
            }
            
            // Group items by category
            const itemsByCategory = {};
            restaurantMenuItems.forEach(item => {
                if (!itemsByCategory[item.category]) {
                    itemsByCategory[item.category] = [];
                }
                itemsByCategory[item.category].push(item);
            });
            
            // Display items by category
            for (const category in itemsByCategory) {
                // Add category header
                const categoryHeader = document.createElement('div');
                categoryHeader.className = 'col-12 mt-4 mb-2';
                categoryHeader.innerHTML = `<h4 class="text-primary">${category}</h4>`;
                restaurantMenuContainer.appendChild(categoryHeader);
                
                // Add items for this category
                itemsByCategory[category].forEach(item => {
                    const menuItemCard = createMenuItemCard(item);
                    restaurantMenuContainer.appendChild(menuItemCard);
                });
            }
        }

        // Show all restaurants (go back from restaurant menu view)
        function showAllRestaurants() {
            // Hide restaurant menu header and show regular sections
            restaurantMenuHeader.classList.remove('active');
            document.getElementById('restaurants').style.display = 'block';
            document.getElementById('menu').style.display = 'block';
            restaurantMenuSection.style.display = 'none';
            
            // Scroll to restaurants section
            document.getElementById('restaurants').scrollIntoView({ behavior: 'smooth' });
            
            // Clear current restaurant view
            currentRestaurantView = null;
        }

        // Cart functions
        function toggleCart() {
            cartSidebar.classList.toggle('open');
            cartOverlay.classList.toggle('active');
            
            if (cartSidebar.classList.contains('open')) {
                updateCartDisplay();
            }
        }

        function addToCart(id, name, price, image) {
            // Check if item already exists in cart
            const existingItem = cart.find(item => item.id === id);
            
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    id: id,
                    name: name,
                    price: price,
                    image: image,
                    quantity: 1
                });
            }
            
            updateCartCount();
            updateCartDisplay();
            
            // Show a quick confirmation
            const button = document.querySelector(`.add-to-cart[data-id="${id}"]`);
            if (button) {
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check me-1"></i> Added';
                button.classList.remove('btn-primary');
                button.classList.add('btn-success');
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-primary');
                }, 1000);
            }
            
            // Open cart if it's not open
            if (!cartSidebar.classList.contains('open')) {
                toggleCart();
            }
        }

        function removeFromCart(id) {
            cart = cart.filter(item => item.id !== id);
            updateCartCount();
            updateCartDisplay();
        }

        function updateCartCount() {
            const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
            cartCount.textContent = totalItems;
        }

        function updateCartDisplay() {
            cartItems.innerHTML = '';
            
            if (cart.length === 0) {
                emptyCartMessage.classList.remove('d-none');
                orderSummary.classList.add('d-none');
                return;
            }
            
            emptyCartMessage.classList.add('d-none');
            orderSummary.classList.remove('d-none');
            
            let subtotal = 0;
            
            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;
                
                const cartItem = document.createElement('div');
                cartItem.className = 'cart-item';
                cartItem.innerHTML = `
                    <div class="row align-items-center">
                        <div class="col-3">
                            <img src="${item.image}" class="cart-item-img" alt="${item.name}">
                        </div>
                        <div class="col-6">
                            <h6 class="mb-1">${item.name}</h6>
                            <p class="mb-0 text-muted">${formatCurrency(item.price)}</p>
                        </div>
                        <div class="col-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <button class="cart-quantity-btn decrease-quantity" data-id="${item.id}">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <span class="mx-2">${item.quantity}</span>
                                <button class="cart-quantity-btn increase-quantity" data-id="${item.id}">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-2">
                        <button class="btn btn-sm btn-outline-danger remove-item" data-id="${item.id}">
                            <i class="fas fa-trash me-1"></i>Remove
                        </button>
                    </div>
                `;
                cartItems.appendChild(cartItem);
            });
            
            // Add event listeners to quantity buttons
            document.querySelectorAll('.decrease-quantity').forEach(button => {
                button.addEventListener('click', function() {
                    const id = parseInt(this.getAttribute('data-id'));
                    updateQuantity(id, -1);
                });
            });
            
            document.querySelectorAll('.increase-quantity').forEach(button => {
                button.addEventListener('click', function() {
                    const id = parseInt(this.getAttribute('data-id'));
                    updateQuantity(id, 1);
                });
            });
            
            document.querySelectorAll('.remove-item').forEach(button => {
                button.addEventListener('click', function() {
                    const id = parseInt(this.getAttribute('data-id'));
                    removeFromCart(id);
                });
            });
            
            // Update summary
            const tax = subtotal * taxRate;
            const total = subtotal + deliveryFee + tax;
            
            cartSubtotal.textContent = formatCurrency(subtotal);
            cartTax.textContent = formatCurrency(tax);
            cartTotal.textContent = formatCurrency(total);
        }

        function updateQuantity(id, change) {
            const item = cart.find(item => item.id === id);
            
            if (item) {
                item.quantity += change;
                
                if (item.quantity <= 0) {
                    removeFromCart(id);
                } else {
                    updateCartCount();
                    updateCartDisplay();
                }
            }
        }

        function checkout() {
            if (cart.length === 0) {
                showAlert('Your cart is empty!', 'danger');
                return;
            }
            
            // Check if user is logged in
            if (!currentUser) {
                showAlert('Please sign in to complete your order', 'warning');
                authModal.show();
                return;
            }
            
            // Get selected payment method
            const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
            const paymentMethods = {
                'mpesa': 'Vodacom M-Pesa',
                'tigopesa': 'Tigo Pesa',
                'airtelmoney': 'Airtel Money',
                'crdb': 'CRDB Bank'
            };
            
            const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0) + deliveryFee;
            const tax = total * taxRate;
            const finalTotal = total + tax;
            
            // Simulate payment process
            showAlert(`Processing ${paymentMethods[paymentMethod]} payment...`, 'info');
            
            setTimeout(() => {
                const orderNumber = Math.floor(100000 + Math.random() * 900000);
                showAlert(`Order #${orderNumber} confirmed! Your food will be delivered soon.`, 'success');
                
                // Clear cart
                cart = [];
                updateCartCount();
                updateCartDisplay();
                toggleCart();
            }, 2000);
        }
    </script>
</body>
</html>
