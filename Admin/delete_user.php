<?php

include '../config/connection.php';

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: users.php?error=invalid");
    exit;
}

$user_id = intval($_GET['id']);

// Optional: check if user exists first
$check = $conn->query("SELECT id FROM users WHERE id = $user_id");

if ($check->num_rows == 0) {
    header("Location: users.php?error=notfound");
    exit;
}

// Delete user
$sql = "DELETE FROM users WHERE id = $user_id";

if ($conn->query($sql)) {
    header("Location: users.php?deleted=1");
} else {
    header("Location: users.php?error=failed");
}

$conn->close();
exit;
?>
