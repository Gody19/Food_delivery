<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodExpress | Food Delivery System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff6b6b;
            --secondary-color: #ffa500;
            --dark-color: #333;
            --light-color: #f8f9fa;
            --success-color: #28a745;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark-color);
        }
        
        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
        }
        
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            margin-bottom: 30px;
        }
        
        .hero-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .search-box {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .section-title {
            color: var(--primary-color);
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 10px;
            margin-bottom: 30px;
            font-weight: 700;
        }
        
        .restaurant-card, .food-card {
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 20px;
            height: 100%;
        }
        
        .restaurant-card:hover, .food-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .restaurant-img {
            height: 180px;
            object-fit: cover;
        }
        
        .food-img {
            height: 150px;
            object-fit: cover;
        }
        
        .restaurant-rating, .food-rating {
            color: var(--secondary-color);
            font-weight: 600;
        }
        
        .food-price {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .food-category {
            background-color: var(--light-color);
            border-radius: 20px;
            padding: 5px 15px;
            font-size: 0.85rem;
            margin-right: 5px;
            margin-bottom: 5px;
            display: inline-block;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #ff5252;
            border-color: #ff5252;
        }
        
        .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .cart-sidebar {
            background-color: white;
            border-left: 1px solid #dee2e6;
            height: calc(100vh - 76px);
            position: fixed;
            top: 76px;
            right: -400px;
            width: 400px;
            transition: right 0.3s;
            z-index: 1000;
            overflow-y: auto;
        }
        
        .cart-sidebar.open {
            right: 0;
        }
        
        .cart-overlay {
            position: fixed;
            top: 76px;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }
        
        .cart-overlay.active {
            display: block;
        }
        
        .cart-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        
        .cart-item-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .cart-quantity-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: var(--light-color);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .order-summary {
            background-color: var(--light-color);
            border-radius: 10px;
            padding: 20px;
        }
        
        .badge-cart {
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            position: absolute;
            top: -5px;
            right: -5px;
        }
        
        .restaurant-menu-header {
            background-color: var(--light-color);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: none;
        }
        
        .restaurant-menu-header.active {
            display: block;
        }
        
        .back-to-restaurants {
            color: var(--primary-color);
            cursor: pointer;
            font-weight: 600;
        }
        
        .restaurant-name-display {
            font-size: 1.8rem;
            color: var(--primary-color);
            font-weight: 700;
        }
        
        .restaurant-info-display {
            color: #666;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .cart-sidebar {
                width: 100%;
                right: -100%;
            }
            
            .restaurant-name-display {
                font-size: 1.5rem;
            }
        }
        
        footer {
            background-color: var(--dark-color);
            color: white;
            padding: 40px 0 20px;
            margin-top: 50px;
        }
        
        .view-menu-btn {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: white;
            font-weight: 600;
        }
        
        .view-menu-btn:hover {
            background-color: #e59400;
            border-color: #e59400;
            color: white;
        }
        
        .restaurant-tag {
            background-color: var(--primary-color);
            color: white;
            border-radius: 5px;
            padding: 3px 8px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            margin-right: 5px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-utensils me-2"></i>FoodExpress
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
                    
                    <button class="btn btn-primary">
                        <i class="fas fa-user me-1"></i> Sign In
                    </button>
                </div>
            </div>
        </div>
    </nav>

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
                    <span id="cartSubtotal">$0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Delivery Fee</span>
                    <span id="deliveryFee">$2.99</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>Tax</span>
                    <span id="cartTax">$0.00</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-4">
                    <strong>Total</strong>
                    <strong id="cartTotal">$0.00</strong>
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
                <p class="lead mb-4">Order from your favorite restaurants and get it delivered in minutes</p>
                
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
            <h2 class="section-title">Popular Restaurants</h2>
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
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="mb-3"><i class="fas fa-utensils me-2"></i>FoodExpress</h5>
                    <p>Delivering happiness to your doorstep since 2023. Order from the best restaurants in town.</p>
                </div>
                <div class="col-md-2 mb-4">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#home" class="text-white-50 text-decoration-none">Home</a></li>
                        <li><a href="#restaurants" class="text-white-50 text-decoration-none">Restaurants</a></li>
                        <li><a href="#menu" class="text-white-50 text-decoration-none">Menu</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">About Us</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h5 class="mb-3">Contact Us</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-phone me-2"></i>+1 (555) 123-4567</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i>support@foodexpress.com</li>
                        <li><i class="fas fa-map-marker-alt me-2"></i>123 Food St, City, State</li>
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
                    <p class="mb-0">&copy; 2023 FoodExpress. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-white-50 me-3"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white-50 me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white-50 me-3"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white-50"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Enhanced data structure with restaurant-food relationships
        const restaurants = [
            {
                id: 1,
                name: "Italian Bistro",
                cuisine: "Italian, Pizza, Pasta",
                rating: 4.7,
                deliveryTime: "25-35 min",
                image: "https://images.unsplash.com/photo-1565299585323-38d6b0865b47?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80",
                menuItems: [101, 105, 107, 108] // IDs of menu items for this restaurant
            },
            {
                id: 2,
                name: "Burger Junction",
                cuisine: "Burgers, American, Fast Food",
                rating: 4.5,
                deliveryTime: "15-25 min",
                image: "https://images.unsplash.com/photo-1568901346375-23c9450c58cd?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80",
                menuItems: [102, 109, 110, 111]
            },
            {
                id: 3,
                name: "Sushi Master",
                cuisine: "Japanese, Sushi, Asian",
                rating: 4.8,
                deliveryTime: "30-40 min",
                image: "https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80",
                menuItems: [103, 112, 113, 114]
            },
            {
                id: 4,
                name: "Taco Fiesta",
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
                name: "Pepperoni Pizza",
                description: "Classic pepperoni pizza with mozzarella cheese and tomato sauce",
                price: 14.99,
                rating: 4.8,
                category: "Pizza",
                restaurantId: 1,
                image: "https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80"
            },
            {
                id: 102,
                name: "Cheeseburger Deluxe",
                description: "Beef patty with cheddar, lettuce, tomato, and special sauce",
                price: 12.99,
                rating: 4.6,
                category: "Burger",
                restaurantId: 2,
                image: "https://images.unsplash.com/photo-1568901346375-23c9450c58cd?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80"
            },
            {
                id: 103,
                name: "California Roll",
                description: "Crab, avocado, and cucumber roll with sesame seeds",
                price: 8.99,
                rating: 4.7,
                category: "Sushi",
                restaurantId: 3,
                image: "https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80"
            },
            {
                id: 104,
                name: "Chicken Tacos",
                description: "Soft tacos with grilled chicken, salsa, and fresh cilantro",
                price: 10.99,
                rating: 4.5,
                category: "Mexican",
                restaurantId: 4,
                image: "https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80"
            },
            {
                id: 105,
                name: "Spaghetti Carbonara",
                description: "Classic pasta with pancetta, eggs, and parmesan cheese",
                price: 13.99,
                rating: 4.9,
                category: "Pasta",
                restaurantId: 1,
                image: "https://images.unsplash.com/photo-1598866594230-a7c12756260f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80"
            },
            {
                id: 106,
                name: "Caesar Salad",
                description: "Fresh romaine lettuce with croutons and caesar dressing",
                price: 9.99,
                rating: 4.4,
                category: "Salad",
                restaurantId: null, // Available at multiple restaurants
                image: "https://images.unsplash.com/photo-1546793665-c74683f339c1?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80"
            },
            {
                id: 107,
                name: "Garlic Breadsticks",
                description: "Freshly baked breadsticks with garlic butter and herbs",
                price: 5.99,
                rating: 4.5,
                category: "Appetizer",
                restaurantId: 1,
                image: "https://images.unsplash.com/photo-1573140247632-f8fd74997d5c?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80"
            },
            {
                id: 108,
                name: "Tiramisu",
                description: "Classic Italian dessert with coffee-soaked ladyfingers",
                price: 7.99,
                rating: 4.8,
                category: "Dessert",
                restaurantId: 1,
                image: "https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?ixlib=rb-1.2.1&auto=format&fit=crop&w-1350&q=80"
            },
            {
                id: 109,
                name: "BBQ Bacon Burger",
                description: "Burger with bacon, cheddar, and smoky BBQ sauce",
                price: 14.99,
                rating: 4.7,
                category: "Burger",
                restaurantId: 2,
                image: "https://images.unsplash.com/photo-1553979459-d2229ba7433b?ixlib=rb-1.2.1&auto=format&fit=crop&w-1350&q=80"
            },
            {
                id: 110,
                name: "French Fries",
                description: "Crispy golden fries with a pinch of sea salt",
                price: 4.99,
                rating: 4.3,
                category: "Side",
                restaurantId: 2,
                image: "https://images.unsplash.com/photo-1573080496219-bb080dd4f877?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80"
            },
            {
                id: 111,
                name: "Chocolate Milkshake",
                description: "Creamy milkshake with rich chocolate flavor",
                price: 6.49,
                rating: 4.6,
                category: "Drink",
                restaurantId: 2,
                image: "https://images.unsplash.com/photo-1572490122747-3968b75cc699?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80"
            },
            {
                id: 112,
                name: "Salmon Nigiri",
                description: "Fresh salmon slices over seasoned sushi rice",
                price: 12.99,
                rating: 4.8,
                category: "Sushi",
                restaurantId: 3,
                image: "https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80"
            },
            {
                id: 113,
                name: "Miso Soup",
                description: "Traditional Japanese soybean paste soup with tofu",
                price: 3.99,
                rating: 4.4,
                category: "Soup",
                restaurantId: 3,
                image: "https://images.unsplash.com/photo-1547592180-85f173990554?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80"
            },
            {
                id: 114,
                name: "Green Tea Ice Cream",
                description: "Creamy ice cream with authentic matcha flavor",
                price: 5.99,
                rating: 4.7,
                category: "Dessert",
                restaurantId: 3,
                image: "https://images.unsplash.com/photo-1563805042-7684c019e1cb?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80"
            },
            {
                id: 115,
                name: "Beef Burrito",
                description: "Large flour tortilla filled with seasoned beef, rice, and beans",
                price: 11.99,
                rating: 4.6,
                category: "Mexican",
                restaurantId: 4,
                image: "https://images.unsplash.com/photo-1626700051175-6818013e1d4f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80"
            },
            {
                id: 116,
                name: "Guacamole & Chips",
                description: "Freshly made guacamole with crispy tortilla chips",
                price: 7.99,
                rating: 4.5,
                category: "Appetizer",
                restaurantId: 4,
                image: "https://images.unsplash.com/photo-1534308983496-4fabb1a015ee?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80"
            },
            {
                id: 117,
                name: "Churros",
                description: "Fried dough pastry sprinkled with cinnamon sugar",
                price: 6.99,
                rating: 4.8,
                category: "Dessert",
                restaurantId: 4,
                image: "https://images.unsplash.com/photo-1581018496270-f5c3d7f5af0c?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80"
            }
        ];

        // Cart data
        let cart = [];
        const deliveryFee = 2.99;
        const taxRate = 0.08; // 8%
        
        // Current restaurant view state
        let currentRestaurantView = null;

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

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            loadRestaurants();
            loadMenuItems();
            updateCartCount();
            
            // Event listeners for cart
            cartToggle.addEventListener('click', toggleCart);
            closeCart.addEventListener('click', toggleCart);
            cartOverlay.addEventListener('click', toggleCart);
            checkoutBtn.addEventListener('click', checkout);
            
            // Event listeners for restaurant view
            backToRestaurants.addEventListener('click', showAllRestaurants);
            orderFromRestaurant.addEventListener('click', function() {
                if (currentRestaurantView) {
                    // Scroll to restaurant menu section
                    document.getElementById('restaurantMenuSection').scrollIntoView({ behavior: 'smooth' });
                }
            });
        });

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
                            <div class="food-price">$${item.price.toFixed(2)}</div>
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
            document.getElementById('restaurants').