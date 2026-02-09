<?php
include 'include/check_login.php';
include '../config/connection.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="../Assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="../Assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../Assets/sweetalert2/sweetalert2.min.css">
    <link rel="stylesheet" href="css/style.css">

</head>

<body>
    <?php include 'include/header.php'; ?>
    <div id="content">
        <?php include 'include/aside.php'; ?>
        <!-- Customers Section -->
        <div class="content-container section-content" id="customers-section">
            <div class="page-header">
                <div class="page-title">
                    <h1>Customer Management</h1>
                    <p>View and manage customer accounts</p>
                </div>

                <div class="search-box" style="width: 300px;">
                    <input type="text" class="form-control form-control-admin" placeholder="Search customers...">
                    <i class="fas fa-search"></i>
                </div>
            </div>

            <div>
                <button class="btn btn-success" style="float: right;" data-bs-toggle="modal" data-bs-target="#userModal">
                    <i class="fas fa-plus me-1"></i> Add Users
                </button>
            </div>
            <!-- Customers Table -->
            <div class="dashboard-card">
                <?php
                $sql = "SELECT * FROM users";
                $result = $conn->query($sql);

                ?>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <!-- <th>Total Orders</th>
                                    <th>Total Spent</th>
                                    <th>Joined Date</th> -->
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="customersTable">
                                <?php
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {

                                ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['username']); ?></td>
                                            <td><?= htmlspecialchars($row['email']); ?></td>
                                            <td><?= htmlspecialchars($row['phone_number']); ?></td>
                                            <td><?= htmlspecialchars($row['role']); ?></td>
                                            <td>
                                                <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">
                                                    Edit
                                                </a>

                                                <a href="delete_user.php?id=<?= $row['id'] ?>"
                                                    class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Delete this user?')">
                                                    Delete
                                                </a>
                                            </td>

                                        </tr>
                                        <!-- Customers will be populated by JavaScript -->
                            </tbody>
                    <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="7" class="text-center">No recent Users found</td></tr>';
                                }
                                $conn->close();
                    ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- User Form Modal -->
        <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Add New User</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="userForm">
                            <input type="hidden" id="userId">
                            <div class="mb-3">
                                <label for="name" class="form-label">User Name *</label>
                                <input type="text" name="username" class="form-control" id="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" id="email" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="role" class="form-label">Role *</label>
                                    <select class="form-select" id="role" required>
                                        <option value="">Select Role</option>
                                        <option value="admin">Administrator</option>
                                        <option value="restaurant_owner">Restaurant Owner</option>
                                        <option value="user">User</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Status *</label>
                                    <select class="form-select" id="status" required>
                                        <option value="">Select Status</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="pending">Pending</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone">
                            </div>
                            <div class="mb-3">
                                <label for="avatar" class="form-label">Avatar URL (Optional)</label>
                                <input type="text" class="form-control" id="avatar" placeholder="https://example.com/avatar.jpg">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary-custom" onclick="saveUser()">Save User</button>
                    </div>
                </div>
            </div>
        </div>

    </div>



    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="deleteUserName"></strong>?</p>
                    <p class="text-muted small">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger-custom" id="confirmDeleteBtn">Delete User</button>
                </div>
            </div>
        </div>
    </div>
    <?php include 'include/footer.php' ?>
    <!-- Bootstrap & Custom JS -->
    <script src="../Assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../Assets/sweetalert2/sweetalert2.all.min.js"></script>
    <script>
        <?php if (isset($_GET['deleted'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: 'User deleted successfully',
                confirmButtonColor: '#28a745'
            });
        <?php endif; ?>

        <?php if (isset($_GET['updated'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: 'User updated successfully',
                confirmButtonColor: '#007bff'
            });
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Something went wrong!',
                confirmButtonColor: '#dc3545'
            });
        <?php endif; ?>
        // Filter users based on search and filters
        function filterUsers() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const roleFilter = document.getElementById('roleFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;

            filteredUsers = users.filter(user => {
                // Search filter
                const matchesSearch = !searchInput ||
                    user.name.toLowerCase().includes(searchInput) ||
                    user.email.toLowerCase().includes(searchInput) ||
                    (user.phone && user.phone.toLowerCase().includes(searchInput));

                // Role filter
                const matchesRole = roleFilter === 'all' || user.role === roleFilter;

                // Status filter
                const matchesStatus = statusFilter === 'all' || user.status === statusFilter;

                return matchesSearch && matchesRole && matchesStatus;
            });

            renderUsers();
        }

        // Reset all filters
        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('roleFilter').value = 'all';
            document.getElementById('statusFilter').value = 'all';
            filterUsers();
        }

        // Show user form modal
        function showUserForm(userId = null) {
            const modal = new bootstrap.Modal(document.getElementById('userModal'));
            const form = document.getElementById('userForm');
            const modalTitle = document.getElementById('modalTitle');

            if (userId) {
                // Edit mode
                const user = users.find(u => u.id === userId);
                if (user) {
                    document.getElementById('userId').value = user.id;
                    document.getElementById('name').value = user.name;
                    document.getElementById('email').value = user.email;
                    document.getElementById('role').value = user.role;
                    document.getElementById('status').value = user.status;
                    document.getElementById('phone').value = user.phone || '';
                    document.getElementById('avatar').value = user.avatar || '';
                    modalTitle.textContent = 'Edit User';
                }
            } else {
                // Add mode
                form.reset();
                document.getElementById('userId').value = '';
                modalTitle.textContent = 'Add New User';
            }

            modal.show();
        }
    </script>
</body>

</html>