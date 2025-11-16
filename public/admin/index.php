<?php
require_once __DIR__ . '/../../src/services/AuthService.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../src/helpers/DatabaseHelper.php';

AuthService::requireAdmin();
$currentUser = AuthService::getCurrentUser();

// Get dashboard data
$stats = DatabaseHelper::getDashboardStats();
$recent_activity = DatabaseHelper::getRecentActivity(5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <header class="top-bar">
                <h1>Dashboard</h1>
                <div class="user-info">
                       <div class="user-details">
                            <span class="user-name"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></span>
                            <span class="user-role">Administrator</span>
                        </div>
                  
                    <button onclick="logout()" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </div>
            </header>
            
            <div class="content-area">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-book"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_books']; ?></h3>
                            <p>Total Books</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-exchange-alt"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $stats['active_loans']; ?></h3>
                            <p>Active Loans</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $stats['overdue_loans']; ?></h3>
                            <p>Overdue Loans</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_users']; ?></h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <h3>Recent Activity</h3>
                        <div class="activity-list">
                            <?php if (empty($recent_activity)): ?>
                                <p class="loading">No recent activity</p>
                            <?php else: ?>
                                <?php foreach ($recent_activity as $activity): ?>
                                    <div class="activity-item" style="padding: 12px 0; border-bottom: 1px solid #eee;">
                                        <div style="font-weight: 500;">
                                            <?php echo htmlspecialchars($activity['user_name']); ?> borrowed 
                                            <?php echo htmlspecialchars($activity['book_title']); ?>
                                        </div>
                                        <small style="color: #666;">
                                            Due: <?php echo date('M d, Y', strtotime($activity['due_date'])); ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <h3>Quick Actions</h3>
                        <div class="quick-actions">
                            <a href="books.php" class="action-btn">
                                <i class="fas fa-plus"></i> Add New Book
                            </a>
                            <a href="users.php" class="action-btn">
                                <i class="fas fa-user-plus"></i> Add New User
                            </a>
                            <a href="loans.php" class="action-btn">
                                <i class="fas fa-book-reader"></i> Checkout Book
                            </a>
                            <a href="reports.php" class="action-btn">
                                <i class="fas fa-file-export"></i> Generate Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="<?php echo BASE_URL; ?>/public/assets/js/sidebar.js"></script>
    <script>
        function logout() {
            window.location.href = '<?php echo BASE_URL; ?>/api/auth/logout';
            setTimeout(() => {
                window.location.href = '<?php echo BASE_URL; ?>/public/login.php';
            }, 100);
        }
    </script>
</body>
</html>
