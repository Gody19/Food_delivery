<nav id="top-nav">
    <button id="sidebar-toggle">
        <i class="fas fa-bars"></i>
    </button>

    <a href="profile.php" class="user-info text-decoration-none text-dark d-flex align-items-center">
        <div class="user-avatar">AD</div>
        <div class="ms-2">
            <div class="fw-bold"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></div>
            <small class="text-muted"><?= htmlspecialchars($_SESSION['role'] ?? 'Administrator') ?></small>
        </div>
    </a>
</nav>


<!-- SweetAlert for logout confirmation -->
<script src="../../Assets/sweetalert2/sweetalert2.all.min.js"></script>
<script>
document.getElementById('logoutBtn').addEventListener('click', function(e) {
    e.preventDefault();
    
    Swal.fire({
        title: 'Confirm Logout',
        text: 'Are you sure you want to logout?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, logout!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Clear browser storage
            localStorage.clear();
            sessionStorage.clear();
            
            // Redirect to logout
            window.location.href = 'include/logout.php';
        }
    });
});
</script>