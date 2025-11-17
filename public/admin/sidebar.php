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
        <a href="<?php echo BASE_URL; ?>/public/home.php" class="nav-item">
            <i class="fas fa-home"></i>
            <span>Public Site</span>
        </a>
        <button type="button" onclick="handleLogout(event)" class="nav-item logout-item" style="width: 100%; text-align: left; background: none; border: none; cursor: pointer; font-family: inherit; font-size: inherit;">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </button>
    </div>
</aside>

<script>
function handleLogout(event) {
    // Prevent any default behavior
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    // Show confirmation dialog
    if (confirm('Are you sure you want to logout?\n\nYou will need to login again to access the admin panel.')) {
        performLogout();
    }
}

function performLogout() {
    // Show loading state
    const logoutBtn = document.querySelector('.logout-item');
    if (logoutBtn) {
        logoutBtn.style.opacity = '0.6';
        logoutBtn.style.pointerEvents = 'none';
        logoutBtn.disabled = true;
        logoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Logging out...</span>';
    }
    
    console.log('Starting logout process...');
    
    // Call logout API
    fetch('<?php echo BASE_URL; ?>/api/auth/logout', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin',
        cache: 'no-cache'
    })
    .then(response => {
        console.log('Logout API response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Logout response data:', data);
        
        if (data.success) {
            console.log('Logout successful, clearing storage...');
            // Clear any local storage
            if (typeof(Storage) !== "undefined") {
                localStorage.clear();
                sessionStorage.clear();
            }
            
            // Force redirect to login page with cache busting
            console.log('Redirecting to login page...');
            setTimeout(() => {
                window.location.replace('<?php echo BASE_URL; ?>/public/login.php?logout=1&t=' + Date.now());
            }, 100);
        } else {
            throw new Error(data.error?.message || 'Logout failed');
        }
    })
    .catch(error => {
        console.error('Logout error:', error);
        // Clear storage and redirect anyway to ensure logout
        if (typeof(Storage) !== "undefined") {
            localStorage.clear();
            sessionStorage.clear();
        }
        // Force redirect even on error
        window.location.replace('<?php echo BASE_URL; ?>/public/login.php?logout=1&t=' + Date.now());
    });
}

// Legacy function for compatibility
function logout() {
    handleLogout(null);
}
</script>
