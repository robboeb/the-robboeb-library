<?php
require_once __DIR__ . '/../../src/services/AuthService.php';
require_once __DIR__ . '/../../src/helpers/DatabaseHelper.php';
require_once __DIR__ . '/../../config/constants.php';

AuthService::requireAuth();
$currentUser = AuthService::getCurrentUser();

// Get user's full details from database
$pdo = DatabaseHelper::getConnection();
$user_sql = "SELECT * FROM users WHERE user_id = :user_id";
$user_stmt = $pdo->prepare($user_sql);
$user_stmt->execute([':user_id' => $currentUser['user_id']]);
$userDetails = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Get user statistics
$stats_sql = "SELECT 
    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_loans,
    COUNT(CASE WHEN status = 'returned' THEN 1 END) as total_returned,
    COUNT(*) as total_loans,
    SUM(CASE WHEN status = 'active' AND due_date < CURDATE() THEN 1 ELSE 0 END) as overdue_count
    FROM loans WHERE user_id = :user_id";
$stats_stmt = $pdo->prepare($stats_sql);
$stats_stmt->execute([':user_id' => $currentUser['user_id']]);
$userStats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - KH LIBRARY</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/main.css">
    <style>
        /* Simplified clean styles */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        .profile-details {
            background: white;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .profile-details h2 {
            margin: 0 0 25px 0;
            color: #2d3748;
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .detail-label {
            font-size: 13px;
            color: #718096;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .detail-value {
            font-size: 16px;
            color: #2d3748;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .detail-value i {
            color: #667eea;
            width: 20px;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .status-active {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .action-buttons {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-info h1 {
                font-size: 24px;
            }
            
            .detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="public-nav">
        <div class="container">
            <div class="nav-content">
                <div class="nav-brand">
                    <img src="https://s3.ca-central-1.amazonaws.com/logojoy/logos/231703335/symbol.svg?1537014.9000000358" alt="KH LIBRARY" class="brand-logo">
                    <span>KH LIBRARY</span>
                </div>
                <div class="nav-links">
                    <a href="<?php echo BASE_URL; ?>/public/home.php" class="nav-link">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <a href="<?php echo BASE_URL; ?>/public/browse.php" class="nav-link">
                        <i class="fas fa-book"></i> Browse Books
                    </a>
                    <a href="<?php echo BASE_URL; ?>/public/user/profile.php" class="nav-link active">
                        <i class="fas fa-user"></i> My Profile
                    </a>
                    <button onclick="logout()" class="btn btn-outline" style="margin-left: var(--space-2);">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </div>
                <button class="mobile-menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Profile Content -->
    <div style="background: #f5f5f5; min-height: calc(100vh - 80px); padding: 50px 20px;">
        <div style="max-width: 900px; margin: 0 auto;">
            
            <!-- Profile Card -->
            <div style="background: white; border-radius: 16px; padding: 40px; margin-bottom: 30px; box-shadow: 0 2px 12px rgba(0,0,0,0.08);">
                <div style="display: flex; align-items: center; gap: 25px; margin-bottom: 30px; flex-wrap: wrap;">
                    <div style="width: 90px; height: 90px; border-radius: 50%; background: linear-gradient(135deg, #ff5722 0%, #ee3900 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 40px; font-weight: 700; flex-shrink: 0;">
                        <?php echo strtoupper(substr($currentUser['first_name'], 0, 1)); ?>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <h1 style="margin: 0 0 8px 0; font-size: 28px; color: #111111; font-weight: 700;">
                            <?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?>
                        </h1>
                        <p style="margin: 4px 0; color: #616161; font-size: 15px;">
                            <i class="fas fa-envelope" style="color: #ff5722; width: 18px;"></i> 
                            <?php echo htmlspecialchars($userDetails['email']); ?>
                        </p>
                        <p style="margin: 4px 0; color: #616161; font-size: 15px;">
                            <i class="fas fa-calendar" style="color: #ff5722; width: 18px;"></i> 
                            Member since <?php echo date('F Y', strtotime($userDetails['created_at'])); ?>
                        </p>
                    </div>
                </div>
                
                <!-- Stats Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px;">
                    <div style="text-align: center; padding: 25px 15px; background: #fafafa; border-radius: 12px; border: 2px solid #eeeeee;">
                        <i class="fas fa-book-reader" style="font-size: 28px; color: #ff5722; margin-bottom: 10px;"></i>
                        <h3 style="margin: 8px 0 4px 0; font-size: 32px; color: #111111; font-weight: 700;"><?php echo $userStats['active_loans']; ?></h3>
                        <p style="margin: 0; color: #616161; font-size: 14px; font-weight: 500;">Active Loans</p>
                    </div>
                    <div style="text-align: center; padding: 25px 15px; background: #fafafa; border-radius: 12px; border: 2px solid #eeeeee;">
                        <i class="fas fa-check-circle" style="font-size: 28px; color: #10b981; margin-bottom: 10px;"></i>
                        <h3 style="margin: 8px 0 4px 0; font-size: 32px; color: #111111; font-weight: 700;"><?php echo $userStats['total_returned']; ?></h3>
                        <p style="margin: 0; color: #616161; font-size: 14px; font-weight: 500;">Books Returned</p>
                    </div>
                    <div style="text-align: center; padding: 25px 15px; background: #fafafa; border-radius: 12px; border: 2px solid #eeeeee;">
                        <i class="fas fa-history" style="font-size: 28px; color: #3b82f6; margin-bottom: 10px;"></i>
                        <h3 style="margin: 8px 0 4px 0; font-size: 32px; color: #111111; font-weight: 700;"><?php echo $userStats['total_loans']; ?></h3>
                        <p style="margin: 0; color: #616161; font-size: 14px; font-weight: 500;">Total Borrowed</p>
                    </div>
                    <div style="text-align: center; padding: 25px 15px; background: #fafafa; border-radius: 12px; border: 2px solid #eeeeee;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 28px; color: #ef4444; margin-bottom: 10px;"></i>
                        <h3 style="margin: 8px 0 4px 0; font-size: 32px; color: #111111; font-weight: 700;"><?php echo $userStats['overdue_count']; ?></h3>
                        <p style="margin: 0; color: #616161; font-size: 14px; font-weight: 500;">Overdue Books</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="text-align: center;">
                <a href="<?php echo BASE_URL; ?>/public/browse.php" style="display: inline-block; background: #ff5722; color: white; padding: 14px 32px; font-size: 16px; border-radius: 10px; text-decoration: none; font-weight: 600; margin: 0 8px; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(255, 87, 34, 0.3);">
                    <i class="fas fa-book"></i> Browse Books
                </a>
                <a href="<?php echo BASE_URL; ?>/public/user/index.php" style="display: inline-block; background: white; color: #ff5722; padding: 14px 32px; font-size: 16px; border-radius: 10px; text-decoration: none; font-weight: 600; margin: 0 8px; border: 2px solid #ff5722; transition: all 0.3s ease;">
                    <i class="fas fa-user"></i> My Loans
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="public-footer" style="margin-top: 60px;">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>
                        <img src="https://s3.ca-central-1.amazonaws.com/logojoy/logos/231703335/symbol.svg?1537014.9000000358" alt="KH LIBRARY" class="brand-logo-footer">
                        KH LIBRARY
                    </h3>
                    <p>Your trusted library management system for modern reading experiences.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>/public/home.php"><i class="fas fa-home"></i> Home</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/public/browse.php"><i class="fas fa-book"></i> Browse Books</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/public/login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <ul>
                        <li><i class="fas fa-envelope"></i> info@khlibrary.com</li>
                        <li><i class="fas fa-phone"></i> +1-234-567-8900</li>
                        <li><i class="fas fa-map-marker-alt"></i> 123 Library Street, Book City</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 KHLIBRARY. All rights reserved. | Developed by <a href="https://t.me/eirsvi" target="_blank" style="color: #ff5722; text-decoration: none;">eirsvi.t.me</a> | <a href="https://github.com/robboeb/the-robboeb-library.git" target="_blank" style="color: #ff5722; text-decoration: none;"><i class="fab fa-github"></i> GitHub</a></p>
            </div>
        </div>
    </footer>

    <script>
    function logout() {
        if (confirm('Are you sure you want to logout?')) {
            fetch('<?php echo BASE_URL; ?>/api/auth/logout', {
                method: 'POST',
                credentials: 'same-origin'
            }).then(() => {
                window.location.href = '<?php echo BASE_URL; ?>/public/home.php';
            }).catch(() => {
                window.location.href = '<?php echo BASE_URL; ?>/public/home.php';
            });
        }
    }
    
    // Mobile menu toggle
    document.querySelector('.mobile-menu-toggle')?.addEventListener('click', function() {
        document.querySelector('.nav-links').classList.toggle('active');
    });
    </script>
</body>
</html>
