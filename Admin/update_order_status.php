<?php
include '../config/connection.php';

if(!isset($_POST['order_id'], $_POST['status'])) die(json_encode(['success'=>false,'msg'=>'Missing parameters']));

$order_id = (int)$_POST['order_id'];
$status = $_POST['status'];
$valid = ['pending','confirmed','preparing','delivered','cancelled'];
if(!in_array($status,$valid)) die(json_encode(['success'=>false,'msg'=>'Invalid status']));

$stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
$stmt->bind_param("si",$status,$order_id);
if($stmt->execute()){
    echo json_encode(['success'=>true,'msg'=>'Order status updated']);
} else {
    echo json_encode(['success'=>false,'msg'=>'Failed to update status']);
}
?>