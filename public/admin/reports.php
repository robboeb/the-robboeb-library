<?php
require_once __DIR__ . '/../../src/services/AuthService.php';
require_once __DIR__ . '/../../config/constants.php';

AuthService::requireAdmin();
$currentUser = AuthService::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <header class="top-bar">
                <div class="top-bar-left">
                    <h1><i class="fas fa-chart-bar"></i> Reports & Analytics</h1>
                </div>
                <div class="top-bar-right">
                    <div class="user-info">
                        <div class="user-avatar"><?php echo strtoupper(substr($currentUser['first_name'], 0, 1)); ?></div>
                        <div class="user-details">
                            <span class="user-name"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></span>
                            <span class="user-role">Administrator</span>
                        </div>
                    </div>
                    <button onclick="logout()" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </div>
            </header>
            
            <div class="content-area">
                <div class="grid grid-cols-2">
                    <div class="data-card">
                        <div class="card-header" style="padding: 20px; border-bottom: 1px solid var(--gray-200);">
                            <h3 style="font-size: 18px; font-weight: 600;"><i class="fas fa-star"></i> Popular Books</h3>
                        </div>
                        <div style="padding: 20px;">
                            <div id="popularBooks">Loading...</div>
                        </div>
                    </div>
                    
                    <div class="data-card">
                        <div class="card-header" style="padding: 20px; border-bottom: 1px solid var(--gray-200);">
                            <h3 style="font-size: 18px; font-weight: 600;"><i class="fas fa-users"></i> Active Users</h3>
                        </div>
                        <div style="padding: 20px;">
                            <div id="activeUsers">Loading...</div>
                        </div>
                    </div>
                    
                    <div class="data-card">
                        <div class="card-header" style="padding: 20px; border-bottom: 1px solid var(--gray-200);">
                            <h3 style="font-size: 18px; font-weight: 600;"><i class="fas fa-chart-pie"></i> Category Distribution</h3>
                        </div>
                        <div style="padding: 20px;">
                            <div id="categoryDist">Loading...</div>
                        </div>
                    </div>
                    
                    <div class="data-card">
                        <div class="card-header" style="padding: 20px; border-bottom: 1px solid var(--gray-200);">
                            <h3 style="font-size: 18px; font-weight: 600;"><i class="fas fa-info-circle"></i> System Info</h3>
                        </div>
                        <div style="padding: 20px;">
                            <div id="systemInfo">Loading...</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="<?php echo BASE_URL; ?>/public/assets/js/utils.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/components.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/api.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/sidebar.js"></script>
    <script>
        async function loadReports() {
            try {
                const [popular, active, categories, dashboard] = await Promise.all([
                    API.reports.getPopularBooks(5),
                    API.reports.getActiveUsers(5),
                    API.reports.getCategoryDistribution(),
                    API.reports.getDashboard()
                ]);
                
                // Popular Books
                if (popular.success && popular.data.length > 0) {
                    document.getElementById('popularBooks').innerHTML = popular.data.map((book, i) => `
                        <div style="padding: 12px 0; border-bottom: 1px solid var(--gray-100);">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong>${i + 1}. ${Utils.escapeHtml(book.title)}</strong>
                                    <div style="font-size: 12px; color: var(--gray-500);">${book.loan_count} loans</div>
                                </div>
                                <span class="badge badge-primary">${book.loan_count}</span>
                            </div>
                        </div>
                    `).join('');
                } else {
                    document.getElementById('popularBooks').innerHTML = '<p style="color: var(--gray-500);">No data available</p>';
                }
                
                // Active Users
                if (active.success && active.data.length > 0) {
                    document.getElementById('activeUsers').innerHTML = active.data.map((user, i) => `
                        <div style="padding: 12px 0; border-bottom: 1px solid var(--gray-100);">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong>${i + 1}. ${Utils.escapeHtml(user.user_name)}</strong>
                                    <div style="font-size: 12px; color: var(--gray-500);">${user.loan_count} loans</div>
                                </div>
                                <span class="badge badge-success">${user.loan_count}</span>
                            </div>
                        </div>
                    `).join('');
                } else {
                    document.getElementById('activeUsers').innerHTML = '<p style="color: var(--gray-500);">No data available</p>';
                }
                
                // Category Distribution
                if (categories.success && categories.data.length > 0) {
                    const total = categories.data.reduce((sum, cat) => sum + parseInt(cat.book_count), 0);
                    document.getElementById('categoryDist').innerHTML = categories.data.map(cat => {
                        const percentage = total > 0 ? Math.round((cat.book_count / total) * 100) : 0;
                        return `
                            <div style="margin-bottom: 16px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                    <span style="font-weight: 500;">${Utils.escapeHtml(cat.category_name)}</span>
                                    <span style="color: var(--gray-600);">${cat.book_count} books (${percentage}%)</span>
                                </div>
                                <div style="height: 8px; background: var(--gray-200); border-radius: 4px; overflow: hidden;">
                                    <div style="height: 100%; background: var(--primary-600); width: ${percentage}%;"></div>
                                </div>
                            </div>
                        `;
                    }).join('');
                } else {
                    document.getElementById('categoryDist').innerHTML = '<p style="color: var(--gray-500);">No data available</p>';
                }
                
                // System Info
                if (dashboard.success) {
                    const stats = dashboard.data;
                    document.getElementById('systemInfo').innerHTML = `
                        <div style="display: grid; gap: 12px;">
                            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--gray-50); border-radius: 8px;">
                                <span>Total Books</span>
                                <strong>${stats.total_books || 0}</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--gray-50); border-radius: 8px;">
                                <span>Total Users</span>
                                <strong>${stats.total_users || 0}</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--gray-50); border-radius: 8px;">
                                <span>Active Loans</span>
                                <strong>${stats.active_loans || 0}</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--gray-50); border-radius: 8px;">
                                <span>Overdue Loans</span>
                                <strong style="color: var(--error-600);">${stats.overdue_loans || 0}</strong>
                            </div>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading reports:', error);
                UIComponents.showToast('Failed to load reports', 'error');
            }
        }
        
        function logout() {
            API.auth.logout().then(() => window.location.href = '/library-pro/public/login.php');
        }
        
        document.addEventListener('DOMContentLoaded', loadReports);
    </script>
</body>
</html>
