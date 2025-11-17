<?php
require_once __DIR__ . '/../../src/services/AuthService.php';
require_once __DIR__ . '/../../src/helpers/DatabaseHelper.php';
require_once __DIR__ . '/../../config/constants.php';

AuthService::requireAuth();
$currentUser = AuthService::getCurrentUser();

// Get user's active loans
$user_id = $currentUser['user_id'];
$pdo = DatabaseHelper::getConnection();
$loan_sql = "SELECT l.*, b.title, b.isbn, b.cover_image,
             CONCAT(a.first_name, ' ', a.last_name) as author_name,
             DATEDIFF(l.due_date, CURDATE()) as days_until_due
             FROM loans l
             JOIN books b ON l.book_id = b.book_id
             LEFT JOIN book_authors ba ON b.book_id = ba.book_id
             LEFT JOIN authors a ON ba.author_id = a.author_id
             WHERE l.user_id = :user_id AND l.status = 'active'
             ORDER BY l.due_date ASC";
$loan_stmt = $pdo->prepare($loan_sql);
$loan_stmt->execute([':user_id' => $user_id]);
$active_loans = $loan_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get pending requests
$pending_sql = "SELECT l.*, b.title, b.isbn, b.cover_image,
                CONCAT(a.first_name, ' ', a.last_name) as author_name
                FROM loans l
                JOIN books b ON l.book_id = b.book_id
                LEFT JOIN book_authors ba ON b.book_id = ba.book_id
                LEFT JOIN authors a ON ba.author_id = a.author_id
                WHERE l.user_id = :user_id AND l.status = 'pending'
                ORDER BY l.created_at DESC";
$pending_stmt = $pdo->prepare($pending_sql);
$pending_stmt->execute([':user_id' => $user_id]);
$pending_requests = $pending_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate stats
$overdue = 0;
$due_soon = 0;
foreach ($active_loans as $loan) {
    if ($loan['days_until_due'] < 0) $overdue++;
    if ($loan['days_until_due'] >= 0 && $loan['days_until_due'] <= 3) $due_soon++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - KH LIBRARY</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/main.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="public-nav">
        <div class="container">
            <div class="nav-content">
                <div class="nav-brand">
                    <img src="<?php echo BASE_URL; ?>/public/assets/brand/symbol.svg" alt="KH LIBRARY" class="brand-logo">
                    <span>KH LIBRARY</span>
                </div>
                <div class="nav-links">
                    <a href="<?php echo BASE_URL; ?>/public/home.php" class="nav-link">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <a href="<?php echo BASE_URL; ?>/public/browse.php" class="nav-link">
                        <i class="fas fa-book"></i> Browse Books
                    </a>
                    <a href="<?php echo BASE_URL; ?>/public/user/index.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i> My Dashboard
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

    <!-- Dashboard Content -->
    <section class="dashboard-section" style="padding: 40px 0; background: #f7fafc;">
        <div class="container" style="max-width: 1200px;">
            
            <!-- Welcome Header -->
            <div style="text-align: center; margin-bottom: 40px;">
                <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: bold; margin: 0 auto 20px;">
                    <?php echo strtoupper(substr($currentUser['first_name'], 0, 1)); ?>
                </div>
                <h1 style="margin: 0 0 10px 0; font-size: 32px; color: #2d3748;">Welcome, <?php echo htmlspecialchars($currentUser['first_name']); ?>!</h1>
                <p style="color: #718096; font-size: 16px; margin: 0;">Manage your borrowed books and discover new titles</p>
            </div>

            <!-- Quick Stats -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
                <div style="background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                    <i class="fas fa-book-reader" style="font-size: 32px; color: #667eea; margin-bottom: 10px;"></i>
                    <h3 style="font-size: 28px; margin: 10px 0 5px 0; color: #2d3748;"><?php echo count($active_loans); ?></h3>
                    <p style="color: #718096; font-size: 14px; margin: 0;">Currently Borrowed</p>
                </div>
                <div style="background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                    <i class="fas fa-hourglass-half" style="font-size: 32px; color: #3b82f6; margin-bottom: 10px;"></i>
                    <h3 style="font-size: 28px; margin: 10px 0 5px 0; color: #2d3748;"><?php echo count($pending_requests); ?></h3>
                    <p style="color: #718096; font-size: 14px; margin: 0;">Pending Requests</p>
                </div>
                <div style="background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                    <i class="fas fa-exclamation-triangle" style="font-size: 32px; color: #ef4444; margin-bottom: 10px;"></i>
                    <h3 style="font-size: 28px; margin: 10px 0 5px 0; color: #2d3748;"><?php echo $overdue; ?></h3>
                    <p style="color: #718096; font-size: 14px; margin: 0;">Overdue Books</p>
                </div>
                <div style="background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                    <i class="fas fa-clock" style="font-size: 32px; color: #f59e0b; margin-bottom: 10px;"></i>
                    <h3 style="font-size: 28px; margin: 10px 0 5px 0; color: #2d3748;"><?php echo $due_soon; ?></h3>
                    <p style="color: #718096; font-size: 14px; margin: 0;">Due Soon (3 days)</p>
                </div>
            </div>

            <!-- Pending Requests -->
            <?php if (!empty($pending_requests)): ?>
                <div style="background: white; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 30px;">
                    <h2 style="margin: 0 0 25px 0; font-size: 24px; color: #2d3748; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-hourglass-half"></i> Pending Requests
                    </h2>
                    
                    <div style="display: grid; gap: 20px;">
                        <?php foreach ($pending_requests as $request): ?>
                            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 20px; border-left: 4px solid #3b82f6;">
                                <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 10px;">
                                    <div style="flex: 1; min-width: 200px;">
                                        <h3 style="margin: 0 0 8px 0; font-size: 20px; color: #2d3748; font-weight: 600;"><?php echo htmlspecialchars($request['title']); ?></h3>
                                        <p style="margin: 0; color: #718096; font-size: 14px;">
                                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($request['author_name'] ?: 'Unknown Author'); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; background: #dbeafe; color: #1e40af;">
                                            <i class="fas fa-clock"></i> PENDING APPROVAL
                                        </span>
                                    </div>
                                </div>
                                
                                <div style="margin-top: 15px; padding: 12px; background: white; border-radius: 8px;">
                                    <p style="margin: 0 0 8px 0; color: #3b82f6; font-size: 14px; font-weight: 500;">
                                        <i class="fas fa-info-circle"></i> Your request is waiting for admin approval.
                                    </p>
                                    <p style="margin: 0; color: #718096; font-size: 13px;">
                                        <i class="fas fa-calendar-alt"></i> Loan period: <strong><?php echo DEFAULT_LOAN_PERIOD; ?> days</strong> (will be set upon approval)
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- My Borrowed Books -->
            <?php if (!empty($active_loans)): ?>
                <div style="background: white; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                    <h2 style="margin: 0 0 25px 0; font-size: 24px; color: #2d3748; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-books"></i> My Borrowed Books
                    </h2>
                    
                    <div style="display: grid; gap: 20px;">
                        <?php foreach ($active_loans as $loan): ?>
                            <div style="background: #f9fafb; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; <?php if ($loan['days_until_due'] < 0) echo 'border-left: 4px solid #ef4444;'; elseif ($loan['days_until_due'] <= 3) echo 'border-left: 4px solid #f59e0b;'; else echo 'border-left: 4px solid #10b981;'; ?>">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                                    <div style="flex: 1; min-width: 200px;">
                                        <h3 style="margin: 0 0 8px 0; font-size: 20px; color: #2d3748; font-weight: 600;"><?php echo htmlspecialchars($loan['title']); ?></h3>
                                        <p style="margin: 0; color: #718096; font-size: 14px;">
                                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($loan['author_name'] ?: 'Unknown Author'); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <?php if ($loan['days_until_due'] < 0): ?>
                                            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; background: #fee2e2; color: #991b1b;">
                                                <i class="fas fa-exclamation-circle"></i> OVERDUE
                                            </span>
                                        <?php elseif ($loan['days_until_due'] <= 3): ?>
                                            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; background: #fef3c7; color: #92400e;">
                                                <i class="fas fa-clock"></i> DUE SOON
                                            </span>
                                        <?php else: ?>
                                            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; background: #d1fae5; color: #065f46;">
                                                <i class="fas fa-check-circle"></i> ACTIVE
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; padding: 15px; background: white; border-radius: 8px;">
                                    <div>
                                        <p style="margin: 0 0 5px 0; font-size: 11px; color: #718096; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Borrowed Date</p>
                                        <p style="margin: 0; font-size: 15px; color: #2d3748; font-weight: 500;">
                                            <i class="fas fa-calendar-check" style="color: #667eea;"></i> <?php echo date('M d, Y', strtotime($loan['created_at'])); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <p style="margin: 0 0 5px 0; font-size: 11px; color: #718096; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Due Date</p>
                                        <p style="margin: 0; font-size: 15px; color: <?php echo $loan['days_until_due'] < 0 ? '#ef4444' : '#2d3748'; ?>; font-weight: 500;">
                                            <i class="fas fa-calendar-times" style="color: <?php echo $loan['days_until_due'] < 0 ? '#ef4444' : '#667eea'; ?>;"></i> <?php echo date('M d, Y', strtotime($loan['due_date'])); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <p style="margin: 0 0 5px 0; font-size: 11px; color: #718096; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Loan Period</p>
                                        <p style="margin: 0; font-size: 15px; color: #2d3748; font-weight: 500;">
                                            <i class="fas fa-calendar-alt" style="color: #667eea;"></i> <?php echo DEFAULT_LOAN_PERIOD; ?> days
                                        </p>
                                    </div>
                                    <div>
                                        <p style="margin: 0 0 5px 0; font-size: 11px; color: #718096; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Days Remaining</p>
                                        <p style="margin: 0; font-size: 15px; font-weight: 600; color: <?php echo $loan['days_until_due'] < 0 ? '#ef4444' : ($loan['days_until_due'] <= 3 ? '#f59e0b' : '#10b981'); ?>;">
                                            <i class="fas fa-hourglass-half"></i> 
                                            <?php 
                                            if ($loan['days_until_due'] < 0) {
                                                echo abs($loan['days_until_due']) . ' days overdue';
                                            } elseif ($loan['days_until_due'] == 0) {
                                                echo 'Due today!';
                                            } else {
                                                echo $loan['days_until_due'] . ' days left';
                                            }
                                            ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <?php if ($loan['days_until_due'] < 0): ?>
                                <div style="margin-top: 15px; padding: 12px; background: #fef2f2; border-radius: 8px; border: 1px solid #fecaca;">
                                    <p style="margin: 0; color: #991b1b; font-size: 14px; font-weight: 500;">
                                        <i class="fas fa-info-circle"></i> This book is overdue. Please return it as soon as possible to avoid additional fines.
                                    </p>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php elseif (empty($pending_requests)): ?>
                <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <i class="fas fa-book-open" style="font-size: 64px; color: #cbd5e0; margin-bottom: 20px;"></i>
                    <h3 style="margin: 0 0 10px 0; font-size: 24px; color: #2d3748;">No Borrowed Books</h3>
                    <p style="margin: 0 0 25px 0; color: #718096; font-size: 16px;">You don't have any borrowed books at the moment</p>
                    <a href="<?php echo BASE_URL; ?>/public/browse.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-search"></i> Browse Books
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </section>

    <!-- Footer -->
    <footer class="public-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>
                        <img src="<?php echo BASE_URL; ?>/public/assets/brand/symbol.svg" alt="KH LIBRARY" class="brand-logo-footer">
                        KH LIBRARY
                    </h3>
                    <p>Your trusted library management system.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>/public/home.php"><i class="fas fa-home"></i> Home</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/public/browse.php"><i class="fas fa-book"></i> Browse Books</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <ul>
                        <li><i class="fas fa-envelope"></i> info@khlibrary.com</li>
                        <li><i class="fas fa-phone"></i> +1-234-567-8900</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 KH LIBRARY. All rights reserved.</p>
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
    
    document.querySelector('.mobile-menu-toggle')?.addEventListener('click', function() {
        document.querySelector('.nav-links').classList.toggle('active');
    });
    </script>
</body>
</html>
