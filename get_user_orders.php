<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to view orders']);
    exit;
}

//database connection
require_once 'config/connection.php';

$user_id = $_SESSION['user_id'];

try {
    // Get orders
    $stmt = $conn->prepare(" SELECT o.*, COUNT(oi.id) as item_count, SUM(oi.quantity) as total_items
        FROM orders o LEFT JOIN order_items oi ON o.id = oi.order_id WHERE o.user_id = ?
        GROUP BY o.id ORDER BY o.inserted_at DESC LIMIT 50");
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    
    while ($order = $result->fetch_assoc()) {
        // Get order items
        $items_stmt = $conn->prepare(" SELECT oi.*, mi.item_name, mi.item_image, r.restaurant_name FROM order_items oi
            JOIN menu_items mi ON oi.menu_item_id = mi.id
            JOIN restaurants r ON mi.restaurant_id = r.id WHERE oi.order_id = ?");
        
        $items_stmt->bind_param("i", $order['id']);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();
        
        $order['items'] = [];
        $restaurants = [];
        
        while ($item = $items_result->fetch_assoc()) {
            $order['items'][] = $item;
            if (!in_array($item['restaurant_name'], $restaurants)) {
                $restaurants[] = $item['restaurant_name'];
            }
        }
        
        $order['restaurant_names'] = implode(', ', $restaurants);
        $orders[] = $order;
    }
    
    echo json_encode([
        'success' => true,
        'orders' => $orders,
        'count' => count($orders)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error loading orders',
        'error' => $e->getMessage()
    ]);
}
?>