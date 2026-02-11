<?php
$db = parse_url(getenv("DATABASE_URL"));

$conn = new mysqli(
    $db["host"],
    $db["user"],
    $db["pass"],
    ltrim($db["path"], "/")
);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
