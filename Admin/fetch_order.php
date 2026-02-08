<?php
include '../config/connection.php';

if(!isset($_POST['order_id'])) die(json_encode(['success'=>false,'msg'=>'Order ID missing']));

$order_id = (int)$_POST['order_id'];

// Fetch order info
$stmt = $conn->prepare("
SELECT o.id, o.user_id, o.total_amount, o.status, o.inserted_at,
       o.delivery_address, o.phone, o.payment_method,
       u.username, u.email AS user_email,
       r.restaurant_name
FROM orders o
JOIN users u ON o.user_id = u.id
JOIN order_items oi ON o.id = oi.order_id
JOIN menu_items m ON oi.menu_item_id = m.id
JOIN restaurants r ON m.restaurant_id = r.id
WHERE o.id = ?
GROUP BY o.id
");
$stmt->bind_param("i",$order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if(!$order) die(json_encode(['success'=>false,'msg'=>'Order not found']));

// Fetch order items
$stmt2 = $conn->prepare("
SELECT m.item_name, oi.quantity, oi.price, (oi.quantity*oi.price) AS total
FROM order_items oi
JOIN menu_items m ON oi.menu_item_id = m.id
WHERE oi.order_id = ?
");
$stmt2->bind_param("i",$order_id);
$stmt2->execute();
$result2 = $stmt2->get_result();

$order_items = [];
$subtotal = 0;
while($row = $result2->fetch_assoc()){
    $order_items[] = $row;
    $subtotal += $row['total'];
}

$delivery_fee = 2000;
$tax = $subtotal * 0.18;
$total_amount = $subtotal + $tax + $delivery_fee;

echo json_encode([
    'success'=>true,
    'order'=>$order,
    'items'=>$order_items,
    'subtotal'=>$subtotal,
    'tax'=>$tax,
    'delivery_fee'=>$delivery_fee,
    'total_amount'=>$total_amount
]);
