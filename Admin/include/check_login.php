<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
?>
<!DOCTYPE html>
<html>
<head>
    <script src="../../Assets/sweetalert2/sweetalert2.all.min.js"></script>
</head>
<body>

<script>
Swal.fire({
    icon: 'error',
    title: 'Unauthorized Access',
    text: 'Please login as admin!',
    confirmButtonColor: '#dc3545'
}).then(() => {
    window.location.href = "index.php";
});
</script>

</body>
</html>
<?php
exit();
}
?>
