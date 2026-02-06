<?php
session_start();
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

// Fetch popular restaurants from database
$popularRestaurants = [];
if (isset($conn) && !$conn->connect_error) {
    $query = "SELECT id, restaurant_name, restaurant_owner, cuisine_type, phone_number, address, commission_rate, status, restaurant_image
              FROM restaurants WHERE status = 'active' ORDER BY id DESC LIMIT 12";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $popularRestaurants[] = $row;
        }
    }
}

// Fetch all restaurants for full list
$allRestaurants = [];
if (isset($conn) && !$conn->connect_error) {
    $query = "SELECT id, restaurant_name, restaurant_owner, cuisine_type, phone_number, address, commission_rate, status, restaurant_image FROM restaurants WHERE status = 'active' ORDER BY id DESC";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $allRestaurants[] = $row;
        }
    }
}

// Fetch menu items from database
$menuItems = [];
if (isset($conn) && !$conn->connect_error) {
    $query = "SELECT m.id, m.item_name, m.description, m.price, m.category, m.restaurant_id, m.item_image, r.restaurant_name
              FROM menu_items m 
              INNER JOIN restaurants r ON m.restaurant_id = r.id 
              WHERE m.status = 'available' 
              ORDER BY m.id DESC LIMIT 12";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $menuItems[] = [
                'id' => $row['id'],
                'name' => $row['item_name'],
                'description' => $row['description'],
                'price' => (int)$row['price'],
                'category' => $row['category'],
                'restaurantId' => $row['restaurant_id'],
                'restaurantName' => $row['restaurant_name'],
                'image' => !empty($row['item_image']) ? 'Assets/img/' . $row['item_image'] : 'https://via.placeholder.com/300x200?text=' . urlencode($row['item_name']),
                'rating' => 4.5
            ];
        }
    }
}

// Handle login via AJAX
if (isset($_POST['login_email']) && isset($_POST['login_password'])) {
    $login_email = sanitize($_POST['login_email'] ?? '', 'email');
    $login_password = $_POST['login_password'] ?? '';

    if (!$login_email || !$login_password) {
        echo json_encode(['success' => false, 'message' => 'Email and password required']);
        exit;
    }

    if (!isset($conn) || $conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection error']);
        exit;
    }

    $sql = "SELECT id, username, email, phone_number, password FROM users WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param('s', $login_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Email not found']);
        exit;
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    if (!password_verify($login_password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid password']);
        exit;
    }

    // Login successful
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['phone_number'] = $user['phone_number'];

    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'phone_number' => $user['phone_number']
        ]
    ]);
    exit;
}

// Handle order placement
if (isset($_POST['place_order'])) {
    // Check if user is logged in via session
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Please login first']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $cart_items = $_POST['cart_items'] ?? '[]';
    $delivery_address = sanitize($_POST['delivery_address'] ?? '', 'string');
    $payment_method = sanitize($_POST['payment_method'] ?? '', 'string');
    $phone = sanitize($_POST['phone'] ?? '', 'string');
    
    if (!$delivery_address || !$payment_method || !$phone) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }

    if (!isset($conn) || $conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection error']);
        exit;
    }

    // Decode cart items
    $items = json_decode($cart_items, true);
    if (empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        exit;
    }

    // Calculate order total
    $subtotal = 0;
    $delivery_fee = 2500;
    $tax_rate = 0.18;

    foreach ($items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }

    $tax = $subtotal * $tax_rate;
    $total_amount = $subtotal + $delivery_fee + $tax;

    // Insert order into database
    $order_status = 'pending';
    $inserted_at = date('Y-m-d H:i:s');
    
    $order_sql = "INSERT INTO orders (user_id, total_amount, delivery_address, payment_method, phone, status, inserted_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
    $order_stmt = $conn->prepare($order_sql);
    
    if (!$order_stmt) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        exit;
    }

    $order_stmt->bind_param('idsssss', $user_id, $total_amount, $delivery_address, $payment_method, $phone, $order_status, $inserted_at);
    
    if (!$order_stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Error placing order: ' . $order_stmt->error]);
        $order_stmt->close();
        exit;
    }

    $order_id = $order_stmt->insert_id;
    $order_stmt->close();

    // Insert order items
    $items_sql = "INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)";
    $items_stmt = $conn->prepare($items_sql);
    
    if (!$items_stmt) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        exit;
    }

    foreach ($items as $item) {
        $menu_item_id = $item['id'];
        $quantity = $item['quantity'];
        $item_price = $item['price'];
        
        $items_stmt->bind_param('iiii', $order_id, $menu_item_id, $quantity, $item_price);
        if (!$items_stmt->execute()) {
            echo json_encode(['success' => false, 'message' => 'Error adding items: ' . $items_stmt->error]);
            $items_stmt->close();
            exit;
        }
    }
    $items_stmt->close();

    // Generate QR code data - use a unique token for security
    $qr_code = bin2hex(random_bytes(16)); // Generate unique QR code token
    $status_update = 'confirmed'; // Update status to confirmed after payment
    
    // Update order with QR code
    $qr_update_sql = "UPDATE orders SET qr_code = ?, status = ? WHERE id = ?";
    $qr_stmt = $conn->prepare($qr_update_sql);
    $qr_stmt->bind_param('ssi', $qr_code, $status_update, $order_id);
    $qr_stmt->execute();
    $qr_stmt->close();

    // Generate order number
    $order_number = str_pad($order_id, 6, '0', STR_PAD_LEFT);
    
    // Create QR code URL (using QR Server API)
    $qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qr_code);

    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully!',
        'order_id' => $order_id,
        'order_number' => $order_number,
        'total_amount' => $total_amount,
        'qr_code' => $qr_code,
        'qr_url' => $qr_url,
        'delivery_address' => $delivery_address
    ]);
    exit;
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
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-utensils me-2"></i>FoodChap <span class="tz-flag">🇹🇿</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#restaurants">Restaurants</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#menu">Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#testimonials">Testimonials</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#payment-methods">Payments</a>
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

        // Use actual menu items from database (PHP)
        const menuItems = <?php echo json_encode($menuItems); ?>;
        
        // Use actual restaurants from database (PHP)
        const restaurantsData = <?php echo json_encode($popularRestaurants); ?>;

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
                const displayName = currentUser.username || currentUser.email;
                userDropdown.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div class="user-avatar me-2">${displayName.charAt(0).toUpperCase()}</div>
                        <span>${displayName}</span>
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
            
            // Send login request to backend
            const formData = new FormData();
            formData.append('login_email', email);
            formData.append('login_password', password);
            
            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentUser = {
                        id: data.user.id,
                        username: data.user.username,
                        email: data.user.email,
                        phone: data.user.phone_number
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
                } else {
                    showAlert(data.message || 'Login failed', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Login error. Please try again.', 'danger');
            });
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
            
            // Use actual restaurant data from PHP
            const actualRestaurants = <?php echo json_encode($popularRestaurants); ?>;
            
            if (!actualRestaurants || actualRestaurants.length === 0) {
                restaurantsContainer.innerHTML = '<div class="col-12 text-center py-5"><p class="text-muted">No restaurants available at this time.</p></div>';
                return;
            }
            
            actualRestaurants.forEach(restaurant => {
                const restaurantCard = document.createElement('div');
                restaurantCard.className = 'col-md-6 col-lg-3';
                restaurantCard.innerHTML = `
                    <div class="restaurant-card card h-100 border-0 shadow-sm">
                        <img src="Assets/img/${escapeHtml(restaurant.restaurant_image)}" class="restaurant-img card-img-top" alt="${escapeHtml(restaurant.restaurant_name)}">
                        <div class="card-body">
                            <h5 class="card-title">${capitalizeText(escapeHtml(restaurant.restaurant_name))}</h5>
                            <p class="card-text text-muted small">${capitalizeText(escapeHtml(restaurant.cuisine_type))}</p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="restaurant-rating">
                                    <i class="fas fa-star"></i> 4.5
                                </div>
                                <span class="text-muted">25-35 min</span>
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

        function capitalizeText(text) {
            return String(text)
                .toLowerCase()
                .split(' ')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
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
            // Find restaurant from restaurantsData
            const restaurant = restaurantsData.find(r => r.id === restaurantId);
            
            if (!restaurant) return;
            
            // Set current restaurant view
            currentRestaurantView = {
                id: restaurant.id,
                name: restaurant.restaurant_name,
                cuisine: restaurant.cuisine_type,
                deliveryTime: '30-40 min',
                rating: 4.5
            };
            
            // Update restaurant menu header
            restaurantNameDisplay.textContent = currentRestaurantView.name;
            restaurantCuisineDisplay.textContent = currentRestaurantView.cuisine;
            restaurantRatingDisplay.textContent = `${currentRestaurantView.rating} ★`;
            restaurantDeliveryDisplay.textContent = currentRestaurantView.deliveryTime;
            
            // Show restaurant menu header and hide regular sections
            restaurantMenuHeader.classList.add('active');
            document.getElementById('restaurants').style.display = 'none';
            document.getElementById('menu').style.display = 'none';
            restaurantMenuSection.style.display = 'block';
            restaurantMenuTitle.textContent = `${currentRestaurantView.name} Menu`;
            
            // Load restaurant menu items
            loadRestaurantMenuItems(restaurantId);
            
            // Scroll to top of menu section
            restaurantMenuSection.scrollIntoView({ behavior: 'smooth' });
        }

        // Load restaurant-specific menu items
        function loadRestaurantMenuItems(restaurantId) {
            restaurantMenuContainer.innerHTML = '';
            
            // Get menu items for this restaurant from database items
            const restaurantMenuItems = menuItems.filter(item => 
                item.restaurantId === restaurantId
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
            
            // Calculate totals
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const tax = subtotal * taxRate;
            const total = subtotal + deliveryFee + tax;
            
            // Show delivery address prompt
            const deliveryAddress = prompt('Enter your delivery address:');
            if (!deliveryAddress) return;
            
            // Prepare order data
            const formData = new FormData();
            formData.append('place_order', '1');
            formData.append('cart_items', JSON.stringify(cart));
            formData.append('delivery_address', deliveryAddress);
            formData.append('payment_method', paymentMethod);
            formData.append('phone', currentUser.phone || '');
            
            // Show processing message
            showAlert(`Processing ${paymentMethods[paymentMethod]} payment...`, 'info');
            
            // Send order to server
            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Calculate totals for receipt
                    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                    const tax = subtotal * taxRate;
                    
                    // Create receipt HTML with QR code
                    let receiptHtml = `
                        <div class="order-receipt" style="text-align: center; padding: 20px;">
                            <h2 style="color: #667eea; margin-bottom: 20px;"><i class="fas fa-check-circle"></i> Order Confirmed!</h2>
                            <p style="font-size: 20px; font-weight: bold;">Order #${data.order_number}</p>
                            <hr>
                            
                            <div style="margin: 20px 0;">
                                <p><strong>QR Code for Delivery</strong></p>
                                <img src="${data.qr_url}" style="width: 200px; height: 200px; border: 3px solid #667eea; padding: 8px; border-radius: 10px; margin: 15px 0; background: white;">
                                <p style="font-size: 11px; color: #666; font-family: monospace; word-break: break-all; background: #f5f5f5; padding: 8px; border-radius: 5px;">${data.qr_code}</p>
                                <p style="font-size: 12px; color: #666; margin-top: 10px;"><i class="fas fa-info-circle"></i> Show this code to the delivery person</p>
                            </div>
                            
                            <hr>
                            <p><strong>Delivery Address</strong></p>
                            <p style="color: #333;">${data.delivery_address}</p>
                            
                            <hr>
                            <h5 style="color: #333; text-align: left;">Order Summary</h5>
                    `;
                    
                    // Add cart items to receipt
                    cart.forEach(item => {
                        const itemTotal = item.price * item.quantity;
                        receiptHtml += `
                            <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; text-align: left;">
                                <span>${item.name}</span>
                                <span style="font-weight: 500;">${item.quantity} × ${formatCurrency(item.price)} = ${formatCurrency(itemTotal)}</span>
                            </div>
                        `;
                    });
                    
                    receiptHtml += `
                            <div style="display: flex; justify-content: space-between; padding: 10px 0; text-align: left;">
                                <span>Subtotal</span>
                                <span>${formatCurrency(subtotal)}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 10px 0; text-align: left;">
                                <span>Delivery Fee</span>
                                <span>${formatCurrency(deliveryFee)}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 10px 0; text-align: left;">
                                <span>Tax (18%)</span>
                                <span>${formatCurrency(tax)}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 12px 0; font-weight: bold; font-size: 16px; text-align: left; border-top: 2px solid #667eea; border-bottom: 2px solid #667eea;">
                                <span>Total Amount</span>
                                <span style="color: #667eea;">${formatCurrency(data.total_amount)}</span>
                            </div>
                            <p style="font-size: 13px; color: #666; margin-top: 15px;">
                                <i class="fas fa-truck me-2"></i>Your food is being prepared and will be delivered soon!
                            </p>
                        </div>
                    `;
                    
                    // Show SweetAlert with receipt
                    Swal.fire({
                        title: 'Order Successfully Placed!',
                        html: receiptHtml,
                        icon: 'success',
                        confirmButtonColor: '#667eea',
                        confirmButtonText: 'Done',
                        width: '600px',
                        showCancelButton: true,
                        cancelButtonText: 'Print Receipt',
                        cancelButtonColor: '#6c757d'
                    }).then((result) => {
                        if (result.isDismissed && result.dismiss === Swal.DismissReason.cancel) {
                            printReceipt(data.order_number, data.qr_url, data.qr_code, data.delivery_address, subtotal, tax, data.total_amount, cart);
                        }
                        // Clear cart
                        cart = [];
                        updateCartCount();
                        updateCartDisplay();
                        toggleCart();
                    });
                } else {
                    showAlert(data.message || 'Error placing order', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error placing order. Please try again.', 'danger');
            });
        }
        // Print receipt function
        function printReceipt(orderNumber, qrUrl, qrCode, address, subtotal, tax, total, items) {
            const printWindow = window.open('', '', 'height=700,width=600');
            
            let itemsHtml = '';
            items.forEach(item => {
                const itemTotal = item.price * item.quantity;
                itemsHtml += `
                    <tr>
                        <td style="text-align: left;">${item.name}</td>
                        <td style="text-align: center;">${item.quantity}</td>
                        <td style="text-align: right;">TZS ${item.price.toLocaleString()}</td>
                        <td style="text-align: right;">TZS ${itemTotal.toLocaleString()}</td>
                    </tr>
                `;
            });

            const receiptContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Order Receipt #${orderNumber}</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            max-width: 400px;
                            margin: 0 auto;
                            padding: 20px;
                            color: #333;
                        }
                        .receipt-header {
                            text-align: center;
                            margin-bottom: 20px;
                            border-bottom: 2px solid #333;
                            padding-bottom: 15px;
                        }
                        .receipt-header h1 {
                            margin: 0 0 5px 0;
                            font-size: 24px;
                        }
                        .receipt-header p {
                            margin: 3px 0;
                            font-size: 12px;
                        }
                        .qr-section {
                            text-align: center;
                            margin: 20px 0;
                            padding: 15px 0;
                            border-top: 1px dashed #999;
                            border-bottom: 1px dashed #999;
                        }
                        .qr-code-img {
                            width: 150px;
                            height: 150px;
                        }
                        .qr-code-text {
                            font-size: 10px;
                            margin-top: 5px;
                            word-break: break-all;
                            font-family: monospace;
                        }
                        table {
                            width: 100%;
                            margin: 15px 0;
                            border-collapse: collapse;
                        }
                        th {
                            text-align: left;
                            border-bottom: 1px solid #999;
                            padding: 5px 0;
                            font-weight: bold;
                            font-size: 12px;
                        }
                        td {
                            padding: 5px 0;
                            font-size: 12px;
                        }
                        .total-row {
                            font-weight: bold;
                            border-top: 2px solid #333;
                            border-bottom: 2px solid #333;
                            padding: 8px 0;
                        }
                        .address-section {
                            margin: 15px 0;
                            padding: 10px;
                            background: #f5f5f5;
                            border-radius: 5px;
                            font-size: 12px;
                        }
                        .footer {
                            text-align: center;
                            margin-top: 20px;
                            font-size: 11px;
                            color: #666;
                            border-top: 1px dashed #999;
                            padding-top: 10px;
                        }
                        @media print {
                            body { margin: 0; padding: 10px; }
                        }
                    </style>
                </head>
                <body>
                    <div class="receipt-header">
                        <h1>FoodChap</h1>
                        <p>Order Receipt</p>
                        <p>Order #${orderNumber}</p>
                    </div>
                    
                    <div class="qr-section">
                        <p><strong>Delivery QR Code</strong></p>
                        <img src="${qrUrl}" class="qr-code-img" alt="QR Code">
                        <div class="qr-code-text">${qrCode}</div>
                    </div>
                    
                    <div class="address-section">
                        <strong>Delivery Address</strong><br>
                        ${address}
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th style="text-align: left;">Item</th>
                                <th style="text-align: center;">Qty</th>
                                <th style="text-align: right;">Price</th>
                                <th style="text-align: right;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHtml}
                        </tbody>
                    </table>
                    
                    <div style="text-align: right;">
                        <div style="padding: 5px 0; font-size: 12px;">
                            <span style="display: inline-block; width: 150px; text-align: left;">Subtotal:</span>
                            <span>TZS ${subtotal.toLocaleString()}</span>
                        </div>
                        <div style="padding: 5px 0; font-size: 12px;">
                            <span style="display: inline-block; width: 150px; text-align: left;">Delivery Fee:</span>
                            <span>TZS 2,500</span>
                        </div>
                        <div style="padding: 5px 0; font-size: 12px;">
                            <span style="display: inline-block; width: 150px; text-align: left;">Tax (18%):</span>
                            <span>TZS ${tax.toLocaleString()}</span>
                        </div>
                        <div class="total-row" style="padding: 8px 0;">
                            <span style="display: inline-block; width: 150px; text-align: left;">TOTAL:</span>
                            <span>TZS ${total.toLocaleString()}</span>
                        </div>
                    </div>
                    
                    <div class="footer">
                        <p>Thank you for your order!</p>
                        <p>Thank you for ordering with FoodChap Tanzania</p>
                        <p>${new Date().toLocaleString()}</p>
                    </div>
                </body>
                </html>
            `;

            printWindow.document.write(receiptContent);
            printWindow.document.close();
            
            // Print after a short delay to ensure content loads
            setTimeout(() => {
                printWindow.print();
            }, 250);
        }
    </script>
</body>
</html>
