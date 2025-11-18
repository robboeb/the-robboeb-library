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

// Get currently borrowed books (active loans)
$borrowed_sql = "SELECT l.*, b.title, b.cover_image, b.isbn,
                CONCAT(a.first_name, ' ', a.last_name) as author_name,
                DATEDIFF(l.due_date, CURDATE()) as days_until_due
                FROM loans l
                JOIN books b ON l.book_id = b.book_id
                LEFT JOIN book_authors ba ON b.book_id = ba.book_id
                LEFT JOIN authors a ON ba.author_id = a.author_id
                WHERE l.user_id = :user_id AND l.status = 'borrowed'
                ORDER BY l.due_date ASC";
$borrowed_stmt = $pdo->prepare($borrowed_sql);
$borrowed_stmt->execute([':user_id' => $currentUser['user_id']]);
$borrowedBooks = $borrowed_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get pending requests
$pending_sql = "SELECT l.*, b.title, b.cover_image, b.isbn,
                CONCAT(a.first_name, ' ', a.last_name) as author_name
                FROM loans l
                JOIN books b ON l.book_id = b.book_id
                LEFT JOIN book_authors ba ON b.book_id = ba.book_id
                LEFT JOIN authors a ON ba.author_id = a.author_id
                WHERE l.user_id = :user_id AND l.status = 'pending'
                ORDER BY l.created_at DESC";
$pending_stmt = $pdo->prepare($pending_sql);
$pending_stmt->execute([':user_id' => $currentUser['user_id']]);
$pendingRequests = $pending_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate stats
$overdue = 0;
$due_soon = 0;
foreach ($borrowedBooks as $loan) {
    if ($loan['days_until_due'] < 0) $overdue++;
    if ($loan['days_until_due'] >= 0 && $loan['days_until_due'] <= 3) $due_soon++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - KHLIBRARY</title>
    <link rel="icon" type="image/svg+xml" href="https://s3.ca-central-1.amazonaws.com/logojoy/logos/231703335/symbol.svg?1537014.9000000358">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            color: #212121;
        }
        
        /* Navigation */
        .navbar {
            background: #ffffff;
            border-bottom: 3px solid #ff5722;
            padding: 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
        }
        
        .nav-container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 70px;
        }
        
        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 24px;
            font-weight: 800;
            color: #ff5722;
            text-decoration: none;
        }
        
        .brand-logo {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }
        
        .nav-links {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            color: #424242;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
        }
        
        .nav-link:hover {
            background: #ffebee;
            color: #d84315;
        }
        
        .nav-link.active {
            background: #ff5722;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(255, 87, 34, 0.3);
        }
        
        .btn-logout {
            background: #ffffff;
            color: #ff5722;
            border: 2px solid #ff5722;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-logout:hover {
            background: #ff5722;
            color: #ffffff;
        }
        
        /* Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 30px;
        }
        
        /* Profile Header */
        .profile-header {
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            border-left: 4px solid #ff5722;
        }
        
        .profile-info {
            display: flex;
            align-items: center;
            gap: 25px;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff5722 0%, #ee3900 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: 800;
            flex-shrink: 0;
        }
        
        .profile-details h1 {
            font-size: 32px;
            font-weight: 800;
            color: #212121;
            margin-bottom: 8px;
        }
        
        .profile-details p {
            color: #616161;
            font-size: 15px;
            margin-bottom: 4px;
        }
        
        .profile-details i {
            color: #ff5722;
            width: 20px;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #ff5722;
            border: 1px solid #f5f5f5;
            transition: all 0.2s;
        }
        
        .stat-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }
        
        .stat-card i {
            font-size: 32px;
            color: #ff5722;
            margin-bottom: 12px;
        }
        
        .stat-card h3 {
            font-size: 36px;
            font-weight: 800;
            color: #212121;
            margin-bottom: 6px;
        }
        
        .stat-card p {
            font-size: 14px;
            color: #757575;
            font-weight: 600;
        }
        
        /* Section */
        .section {
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            border: 1px solid #f5f5f5;
        }
        
        .section-title {
            font-size: 24px;
            font-weight: 800;
            color: #212121;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            color: #ff5722;
        }
        
        /* Borrowed Books List */
        .borrowed-list {
            display: grid;
            gap: 20px;
        }
        
        .borrowed-item {
            background: #fafafa;
            border-radius: 12px;
            padding: 20px;
            border-left: 4px solid #ff5722;
            display: flex;
            gap: 20px;
            align-items: center;
            border: 1px solid #e0e0e0;
            transition: all 0.2s;
        }
        
        .borrowed-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .borrowed-item.overdue {
            border-left-color: #d32f2f;
            background: #ffebee;
            border-color: #ffcdd2;
        }
        
        .borrowed-item.due-soon {
            border-left-color: #f57c00;
            background: #fff3e0;
            border-color: #ffe0b2;
        }
        
        .book-cover-small {
            width: 80px;
            height: 110px;
            object-fit: cover;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            flex-shrink: 0;
        }
        
        .borrowed-details {
            flex: 1;
        }
        
        .borrowed-details h3 {
            font-size: 18px;
            font-weight: 700;
            color: #212121;
            margin-bottom: 6px;
        }
        
        .borrowed-details p {
            font-size: 14px;
            color: #616161;
            margin-bottom: 4px;
        }
        
        .due-info {
            font-weight: 700;
            margin-top: 8px;
        }
        
        .due-info.overdue {
            color: #c62828;
        }
        
        .due-info.due-soon {
            color: #ef6c00;
        }
        
        .due-info.ok {
            color: #2e7d32;
        }
        
        .btn-return {
            background: #ff5722;
            color: #ffffff;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-return:hover {
            background: #ee3900;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 87, 34, 0.3);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #757575;
        }
        
        .empty-state i {
            font-size: 64px;
            color: #e0e0e0;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            font-size: 24px;
            color: #212121;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .empty-state p {
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .btn-browse {
            background: #ff5722;
            color: #ffffff;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            transition: all 0.3s;
        }
        
        .btn-browse:hover {
            background: #ee3900;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 87, 34, 0.3);
        }
        
        /* Footer */
        .footer {
            background: #212121;
            color: #ffffff;
            padding: 40px 0 20px;
            margin-top: 60px;
        }
        
        .footer-content {
            max-width: 1600px;
            margin: 0 auto;
            padding: 0 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 30px;
        }
        
        .footer-section h3 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #ff5722;
            font-weight: 700;
        }
        
        .footer-section p, .footer-section li {
            color: #eeeeee;
            line-height: 1.8;
        }
        
        .footer-section ul {
            list-style: none;
        }
        
        .footer-section a {
            color: #eeeeee;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-section a:hover {
            color: #ff5722;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #333;
            color: #999;
            max-width: 1600px;
            margin: 0 auto;
            padding-left: 30px;
            padding-right: 30px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .profile-info {
                flex-direction: column;
                text-align: center;
            }
            
            .borrowed-item {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="<?php echo BASE_URL; ?>/public/browse.php" class="nav-brand">
                <img src="https://s3.ca-central-1.amazonaws.com/logojoy/logos/231703335/symbol.svg?1537014.9000000358" alt="KHLIBRARY" class="brand-logo">
                <span>KHLIBRARY</span>
            </a>
            <div class="nav-links">
                <a href="<?php echo BASE_URL; ?>/public/browse.php" class="nav-link">
                    <i class="fas fa-book"></i> Browse Books
                </a>
                <a href="<?php echo BASE_URL; ?>/public/user/profile.php" class="nav-link active">
                    <i class="fas fa-user"></i> User Profile
                </a>
                <button onclick="logout()" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-info">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($currentUser['first_name'], 0, 1)); ?>
                </div>
                <div class="profile-details">
                    <h1><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></h1>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($userDetails['email']); ?></p>
                    <p><i class="fas fa-calendar"></i> Member since <?php echo date('F Y', strtotime($userDetails['created_at'])); ?></p>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-book-reader"></i>
                <h3><?php echo count($borrowedBooks); ?></h3>
                <p>Currently Borrowed</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-clock"></i>
                <h3><?php echo count($pendingRequests); ?></h3>
                <p>Pending Requests</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-exclamation-triangle"></i>
                <h3><?php echo $overdue; ?></h3>
                <p>Overdue Books</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-calendar-day"></i>
                <h3><?php echo $due_soon; ?></h3>
                <p>Due Soon (3 days)</p>
            </div>
        </div>

        <!-- Pending Requests -->
        <?php if (!empty($pendingRequests)): ?>
        <div class="section">
            <h2 class="section-title">
                <i class="fas fa-hourglass-half"></i>
                Pending Requests
            </h2>
            <div class="borrowed-list">
                <?php foreach ($pendingRequests as $request): ?>
                    <div class="borrowed-item">
                        <img src="<?php echo htmlspecialchars($request['cover_image'] ?: BASE_URL . '/public/assets/images/book-placeholder.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($request['title']); ?>" 
                             class="book-cover-small">
                        <div class="borrowed-details">
                            <h3><?php echo htmlspecialchars($request['title']); ?></h3>
                            <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($request['author_name'] ?: 'Unknown Author'); ?></p>
                            <p class="due-info" style="color: #f59e0b;"><i class="fas fa-info-circle"></i> Waiting for admin approval</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Currently Borrowed -->
        <div class="section">
            <h2 class="section-title">
                <i class="fas fa-book-reader"></i>
                Currently Borrowed
            </h2>
            <?php if (empty($borrowedBooks)): ?>
                <div class="empty-state">
                    <i class="fas fa-book-open"></i>
                    <h3>No Borrowed Books</h3>
                    <p>You don't have any borrowed books at the moment</p>
                    <a href="<?php echo BASE_URL; ?>/public/browse.php" class="btn-browse">
                        <i class="fas fa-book"></i> Browse Books
                    </a>
                </div>
            <?php else: ?>
                <div class="borrowed-list">
                    <?php foreach ($borrowedBooks as $loan): ?>
                        <div class="borrowed-item <?php 
                            if ($loan['days_until_due'] < 0) echo 'overdue';
                            elseif ($loan['days_until_due'] <= 3) echo 'due-soon';
                        ?>">
                            <img src="<?php echo htmlspecialchars($loan['cover_image'] ?: BASE_URL . '/public/assets/images/book-placeholder.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($loan['title']); ?>" 
                                 class="book-cover-small">
                            <div class="borrowed-details">
                                <h3><?php echo htmlspecialchars($loan['title']); ?></h3>
                                <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($loan['author_name'] ?: 'Unknown Author'); ?></p>
                                <p><i class="fas fa-calendar-check"></i> Borrowed: <?php echo date('M d, Y', strtotime($loan['loan_date'])); ?></p>
                                <p><i class="fas fa-calendar-times"></i> Due: <?php echo date('M d, Y', strtotime($loan['due_date'])); ?></p>
                                <p class="due-info <?php 
                                    if ($loan['days_until_due'] < 0) echo 'overdue';
                                    elseif ($loan['days_until_due'] <= 3) echo 'due-soon';
                                    else echo 'ok';
                                ?>">
                                    <i class="fas fa-hourglass-half"></i>
                                    <?php 
                                    if ($loan['days_until_due'] < 0) {
                                        echo abs($loan['days_until_due']) . ' days overdue!';
                                    } elseif ($loan['days_until_due'] == 0) {
                                        echo 'Due today!';
                                    } else {
                                        echo $loan['days_until_due'] . ' days remaining';
                                    }
                                    ?>
                                </p>
                            </div>
                            <button onclick="returnBook(<?php echo $loan['loan_id']; ?>, '<?php echo htmlspecialchars(addslashes($loan['title'])); ?>')" class="btn-return">
                                <i class="fas fa-undo"></i> Return Book
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>KHLIBRARY</h3>
                <p>Your trusted library management system for discovering and borrowing amazing books.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>/public/browse.php"><i class="fas fa-book"></i> Browse Books</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/public/user/profile.php"><i class="fas fa-user"></i> User Profile</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <ul>
                    <li><i class="fas fa-envelope"></i> info@khlibrary.com</li>
                    <li><i class="fas fa-phone"></i> +1-234-567-8900</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 KHLIBRARY. All rights reserved. | Developed by <a href="https://t.me/eirsvi" target="_blank" style="color: #ff5722; text-decoration: none; font-weight: 600;">eirsvi.t.me</a> | <a href="https://github.com/robboeb/the-robboeb-library" target="_blank" style="color: #ff5722; text-decoration: none; font-weight: 600;"><i class="fab fa-github"></i> GitHub</a></p>
        </div>
    </footer>

    <script>
        function returnBook(loanId, bookTitle) {
            if (!confirm(`Return "${bookTitle}"?\n\nAre you sure you want to return this book?`)) {
                return;
            }

            fetch('<?php echo BASE_URL; ?>/api/loans/' + loanId + '/return', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('✓ Book returned successfully!', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    throw new Error(data.error?.message || 'Failed to return book');
                }
            })
            .catch(error => {
                showNotification('❌ ' + error.message, 'error');
            });
        }
        
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 16px 24px;
                background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
                color: white;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 10000;
                font-weight: 600;
                animation: slideIn 0.3s ease;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
        
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                fetch('<?php echo BASE_URL; ?>/api/auth/logout', {
                    method: 'POST',
                    credentials: 'same-origin'
                }).then(() => {
                    window.location.href = '<?php echo BASE_URL; ?>/public/browse.php';
                }).catch(() => {
                    window.location.href = '<?php echo BASE_URL; ?>/public/browse.php';
                });
            }
        }
    </script>
    <style>
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    </style>
</body>
</html>
