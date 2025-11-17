<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <img src="<?php echo BASE_URL; ?>/public/assets/brand/symbol.svg" alt="KH LIBRARY" class="brand-logo">
            <span>KH LIBRARY</span>
        </div>
        <button class="toggle-btn" id="sidebarToggle" title="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    <nav class="sidebar-nav">
        <a href="index.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        <a href="books.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'books.php' ? 'active' : ''; ?>">
            <i class="fas fa-book"></i>
            <span>Books</span>
        </a>
        <a href="users.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Users</span>
        </a>
        <a href="loans.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'loans.php' ? 'active' : ''; ?>">
            <i class="fas fa-exchange-alt"></i>
            <span>Loans</span>
        </a>
        <a href="categories.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
            <i class="fas fa-tags"></i>
            <span>Categories</span>
        </a>
        <a href="authors.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'authors.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-edit"></i>
            <span>Authors</span>
        </a>
        <a href="reports.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i>
            <span>Reports</span>
        </a>
    </nav>
    <div class="sidebar-footer">
        <!-- User Profile -->
        <div class="sidebar-user-footer">
            <div class="user-avatar-small">
                <?php echo strtoupper(substr($currentUser['first_name'], 0, 1)); ?>
            </div>
            <div class="user-info-footer">
                <div class="user-name-footer"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></div>
                <div class="user-role-footer">Administrator</div>
            </div>
        </div>
        
        <a href="<?php echo BASE_URL; ?>/public/home.php" class="nav-item">
            <i class="fas fa-home"></i>
            <span>Public Site</span>
        </a>
        <a href="#" onclick="logout(); return false;" class="nav-item logout-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>

<script>
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        // Show loading state
        const logoutBtn = document.querySelector('.logout-item');
        if (logoutBtn) {
            logoutBtn.style.opacity = '0.6';
            logoutBtn.style.pointerEvents = 'none';
        }
        
        // Call logout API
        fetch('<?php echo BASE_URL; ?>/api/auth/logout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            // Redirect to homepage
            window.location.href = '<?php echo BASE_URL; ?>/public/home.php';
        })
        .catch(error => {
            console.error('Logout error:', error);
            // Redirect anyway to ensure logout
            window.location.href = '<?php echo BASE_URL; ?>/public/home.php';
        });
    }
}
</script>
