<?php
session_start();

$timeout = 1200; // 10 minutes

// session expired
if (isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity'] > $timeout)) {

    session_unset();
    session_destroy();

    header("Location: index.php?timeout=1");
    exit();
}

// update activity
$_SESSION['last_activity'] = time();

// role check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?unauthorized=1");
    exit();
}
?>
