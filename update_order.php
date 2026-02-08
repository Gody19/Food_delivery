<?php
session_start();
header('Content-Type: application/json');
require_once 'config/connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$order_id = intval($_POST['order_id']);
$field = $_POST['field'];
$value = $_POST['value'];
$user_id = $_SESSION['user_id'];

// Allowed fields to update
$allowed_fields = ['delivery_address', 'payment_method', 'phone'];

if (!in_array($field, $allowed_fields)) {
    echo json_encode(['success' => false, 'message' => 'Invalid field']);
    exit;
}

// Check if order belongs to user and is editable
$check_stmt = $conn->prepare("
    SELECT status, expires_at 
    FROM orders 
    WHERE id = ? AND user_id = ?
");
$check_stmt->bind_param("ii", $order_id, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$order = $check_result->fetch_assoc();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

// Check if order can be edited
$can_edit = in_array($order['status'], ['pending', 'confirmed']) && 
            strtotime($order['expires_at']) > time();

if (!$can_edit) {
    echo json_encode(['success' => false, 'message' => 'Order cannot be edited']);
    exit;
}

// Update the field
$update_stmt = $conn->prepare("UPDATE orders SET $field = ? WHERE id = ?");
$update_stmt->bind_param("si", $value, $order_id);

if ($update_stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Order updated']);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed']);
}
?>