
    <!-- Footer -->
    <footer id="admin-footer" class="mt-auto">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="text-muted small">
                        <i class="fas fa-copyright me-1"></i> <?php echo date('Y'); ?> FoodChap Tanzania. All rights reserved.
                        <span class="mx-2">|</span>
                        <i class="fas fa-server me-1"></i> Server: <?php echo gethostname(); ?>
                        <span class="mx-2">|</span>
                        <i class="fas fa-database me-1"></i> MySQL: Online
                    </div>
                </div>
                <!-- <div class="col-md-6 text-md-end">
                    <div class="text-muted small">
                        <i class="fas fa-user-shield me-1"></i> Logged in as: <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>
                        <span class="mx-2">|</span>
                        <i class="fas fa-clock me-1"></i> Last login: <?php echo $_SESSION['last_login'] ?? 'Today'; ?>
                        <span class="mx-2">|</span>
                        <a href="system_status.php" class="text-muted text-decoration-none">
                            <i class="fas fa-heartbeat me-1"></i> System Status
                        </a>
                    </div>
                </div> -->
            </div>
        </div>
    </footer>
