<?php
session_start();

// Destroy session
$_SESSION = [];
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">

<!-- Disable cache -->
<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">

<script src="../../Assets/sweetalert2/sweetalert2.all.min.js"></script>
</head>

<body>

<script>
// Force disable back navigation
history.pushState(null, null, location.href);
window.onpopstate = function () {
    history.go(1);
};

// Logout alert
Swal.fire({
    icon: 'success',
    title: 'Logged out',
    text: 'You have been logged out successfully',
    timer: 2000,
    showConfirmButton: false
}).then(() => {
    window.location.replace("../index.php");
});
</script>

</body>
</html>
