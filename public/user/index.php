<?php
require_once __DIR__ . '/../../src/services/AuthService.php';
require_once __DIR__ . '/../../src/helpers/DatabaseHelper.php';
require_once __DIR__ . '/../../config/constants.php';

AuthService::requireAuth();
$currentUser = AuthService::getCurrentUser();

// Get available books
$books = DatabaseHelper::getAllBooks(['status' => 'available', 'limit' => 12]);

// Get user's active loans
$user_id = $currentUser['user_id'];
$pdo = DatabaseHelper::getConnection();
$loan_sql = "SELECT l.*, b.title, b.isbn, 
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - THE ROBBOEB LIBRARY</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/main.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="public-nav">
        <div class="container">
            <div class="nav-content">
                <div class="nav-brand">
                    <img src="<?php echo BASE_URL; ?>/public/assets/brand/symbol.svg" alt="THE ROBBOEB LIBRARY" class="brand-logo">
                    <span>THE ROBBOEB LIBRARY</span>
                </div>
                <div class="nav-links">
                    <a href="<?php echo BASE_URL; ?>/public/home.php" class="nav-link">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <a href="<?php echo BASE_URL; ?>/public/browse.php" class="nav-link">
                        <i class="fas fa-book"></i> Browse Books
                    </a>
                    <a href="<?php echo BASE_URL; ?>/public/user/index.php" class="nav-link active">
                        <i class="fas fa-user"></i> My Dashboard
                    </a>
                    <form method="POST" action="<?php echo BASE_URL; ?>/api/auth/logout.php" style="display: inline;">
                        <button type="submit" class="btn btn-outline" style="margin-left: var(--space-2);">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
                <button class="mobile-menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- User Dashboard -->
    <section class="dashboard-section">
        <div class="container">
            <div class="dashboard-header">
                <h1><i class="fas fa-user-circle"></i> Welcome, <?php echo htmlspecialchars($currentUser['first_name']); ?>!</h1>
                <p>Manage your borrowed books and discover new titles</p>
            </div>

            <!-- Active Loans Section -->
            <?php if (!empty($active_loans)): ?>
                <div class="section-card">
                    <h2><i class="fas fa-book-reader"></i> My Active Loans (<?php echo count($active_loans); ?>)</h2>
                    <div class="loans-grid">
                        <?php foreach ($active_loans as $loan): ?>
                            <div class="loan-card">
                                <div class="loan-header">
                                    <h3><?php echo htmlspecialchars($loan['title']); ?></h3>
                                    <?php if ($loan['days_until_due'] < 0): ?>
                                        <span class="badge badge-danger">
                                            <i class="fas fa-exclamation-circle"></i> Overdue
                                        </span>
                                    <?php elseif ($loan['days_until_due'] <= 3): ?>
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clock"></i> Due Soon
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i> Active
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="loan-details">
                                    <p><i class="fas fa-user"></i> <strong>Author:</strong> <?php echo htmlspecialchars($loan['author_name'] ?: 'Unknown'); ?></p>
                                    <p><i class="fas fa-calendar"></i> <strong>Borrowed:</strong> <?php echo date('M d, Y', strtotime($loan['loan_date'])); ?></p>
                                    <p><i class="fas fa-calendar-check"></i> <strong>Due:</strong> <?php echo date('M d, Y', strtotime($loan['due_date'])); ?></p>
                                    <?php if ($loan['days_until_due'] >= 0): ?>
                                        <p><i class="fas fa-hourglass-half"></i> <strong><?php echo $loan['days_until_due']; ?> days remaining</strong></p>
                                    <?php else: ?>
                                        <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> <strong><?php echo abs($loan['days_until_due']); ?> days overdue</strong></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="section-card">
                    <div class="empty-state">
                        <i class="fas fa-book-open"></i>
                        <h3>No Active Loans</h3>
                        <p>You don't have any borrowed books at the moment</p>
                        <a href="<?php echo BASE_URL; ?>/public/browse.php" class="btn btn-primary">
                            <i class="fas fa-search"></i> Browse Books
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Available Books Section -->
            <div class="section-card">
                <div class="section-header">
                    <h2><i class="fas fa-book"></i> Available Books</h2>
                    <a href="<?php echo BASE_URL; ?>/public/browse.php" class="btn btn-outline">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="books-grid">
                    <?php if (empty($books)): ?>
                        <div class="empty-state">
                            <i class="fas fa-book"></i>
                            <p>No books available at the moment</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($books as $book): ?>
                            <div class="book-card" onclick="window.location.href='<?php echo BASE_URL; ?>/public/book-details.php?id=<?php echo $book['book_id']; ?>'" style="cursor: pointer;">
                                <div class="book-cover">
                                    <?php if (!empty($book['cover_image'])): ?>
                                        <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($book['title']); ?>"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="book-cover-fallback" style="display: none;">
                                            <i class="fas fa-book"></i>
                                            <span class="book-title-overlay"><?php echo htmlspecialchars($book['title']); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <div class="book-cover-fallback">
                                            <i class="fas fa-book"></i>
                                            <span class="book-title-overlay"><?php echo htmlspecialchars($book['title']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="book-info">
                                    <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                                    <p class="book-author">
                                        <i class="fas fa-user"></i>
                                        <?php echo htmlspecialchars($book['authors'] ?: 'Unknown Author'); ?>
                                    </p>
                                    <p class="book-category">
                                        <i class="fas fa-tag"></i>
                                        <?php echo htmlspecialchars($book['category_name'] ?: 'Uncategorized'); ?>
                                    </p>
                                    <div class="book-footer">
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i> Available
                                        </span>
                                        <a href="<?php echo BASE_URL; ?>/public/book-details.php?id=<?php echo $book['book_id']; ?>" class="btn btn-sm btn-primary" onclick="event.stopPropagation();">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="public-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>
                        <img src="<?php echo BASE_URL; ?>/public/assets/brand/symbol.svg" alt="THE ROBBOEB LIBRARY" class="brand-logo-footer">
                        THE ROBBOEB LIBRARY
                    </h3>
                    <p>Your trusted library management system for modern reading experiences.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>/public/home.php"><i class="fas fa-home"></i> Home</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/public/browse.php"><i class="fas fa-book"></i> Browse Books</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/public/about.php"><i class="fas fa-info-circle"></i> About</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <ul>
                        <li><i class="fas fa-envelope"></i> info@robboeb-libra.com</li>
                        <li><i class="fas fa-phone"></i> +1 234 567 890</li>
                        <li><i class="fas fa-map-marker-alt"></i> 123 Library St, City</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 THE ROBBOEB LIBRARY. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="<?php echo BASE_URL; ?>/public/assets/js/utils.js"></script>
    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-toggle')?.addEventListener('click', function() {
            document.querySelector('.nav-links').classList.toggle('active');
        });
    </script>
</body>
</html>
