<?php
$db = parse_url(getenv("DATABASE_URL"));

// Extract host WITHOUT port
$host = $db["host"] ?? null;

// Extract port (default to 3306 for MySQL)
$port = $db["port"] ?? 3306;

// If port is still attached to host (common bug), split it
if (strpos($host, ':') !== false) {
    list($host, $port) = explode(':', $host, 2);
}

$conn = new mysqli(
    $host,
    $db["user"] ?? null,
    $db["pass"] ?? null,
    ltrim($db["path"] ?? '', '/'),
    (int)$port  // ⚠️ CRITICAL: Port as 5th parameter
);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Optional: Set charset
$conn->set_charset('utf8mb4');
?>
