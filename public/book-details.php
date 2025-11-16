<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../src/helpers/DatabaseHelper.php';
require_once __DIR__ . '/../src/services/AuthService.php';

AuthService::initSession();
$isAuthenticated = AuthService::isAuthenticated();

// Get book ID from URL
$bookId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$bookId) {
    header('Location: ' . BASE_URL . '/public/browse.php');
    exit;
}

// Get book details
$book = DatabaseHelper::getBookById($bookId);

if (!$book) {
    header('Location: ' . BASE_URL . '/public/browse.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title']); ?> - ROBBOEB Libra</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/book-details.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <a href="<?php echo BASE_URL; ?>/public/about.php" class="nav-link">
                        <i class="fas fa-info-circle"></i> About
                    </a>
                    <?php if ($isAuthenticated): ?>
                        <a href="<?php echo BASE_URL; ?>/public/<?php echo $_SESSION['user_type']; ?>/index.php" class="btn btn-primary">
                            <i class="fas fa-user"></i> Dashboard
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/public/login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    <?php endif; ?>
                </div>
                <button class="mobile-menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Book Details -->
    <section class="book-details-section">
        <div class="container">
            <a href="<?php echo BASE_URL; ?>/public/browse.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Browse
            </a>

            <div class="book-details-grid">
                <div class="book-cover-large">
                    <?php if (!empty($book['cover_image'])): ?>
                        <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" 
                             alt="<?php echo htmlspecialchars($book['title']); ?>"
                             class="book-cover-image"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="book-cover-placeholder" style="display: none;">
                            <i class="fas fa-book"></i>
                            <span class="book-title-overlay"><?php echo htmlspecialchars($book['title']); ?></span>
                        </div>
                    <?php else: ?>
                        <div class="book-cover-placeholder">
                            <i class="fas fa-book"></i>
                            <span class="book-title-overlay"><?php echo htmlspecialchars($book['title']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="book-details-content">
                    <h1 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h1>
                    
                    <div class="book-meta">
                        <div class="meta-item">
                            <i class="fas fa-user"></i>
                            <span><strong>Author:</strong> <?php echo htmlspecialchars($book['authors'] ?: 'Unknown Author'); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-tag"></i>
                            <span><strong>Category:</strong> <?php echo htmlspecialchars($book['category_name'] ?: 'Uncategorized'); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-barcode"></i>
                            <span><strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn'] ?: 'N/A'); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-calendar"></i>
                            <span><strong>Published:</strong> <?php echo htmlspecialchars($book['publication_year'] ?: 'N/A'); ?></span>
                        </div>
                    </div>

                    <?php if (!empty($book['description'])): ?>
                        <div class="book-description">
                            <h2><i class="fas fa-align-left"></i> Description</h2>
                            <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="book-availability">
                        <h2><i class="fas fa-info-circle"></i> Availability</h2>
                        <div class="availability-info">
                            <div class="availability-item">
                                <span class="label">Status:</span>
                                <?php if ($book['status'] === 'available'): ?>
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle"></i> Available
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-danger">
                                        <i class="fas fa-times-circle"></i> Not Available
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="availability-item">
                                <span class="label">Total Copies:</span>
                                <span class="value"><?php echo $book['total_quantity']; ?></span>
                            </div>
                            <div class="availability-item">
                                <span class="label">Available Copies:</span>
                                <span class="value"><?php echo $book['available_quantity']; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="book-actions">
                        <?php if ($book['status'] === 'available'): ?>
                            <?php if ($isAuthenticated): ?>
                                <button class="btn btn-primary btn-lg" onclick="borrowBook(<?php echo $book['book_id']; ?>)">
                                    <i class="fas fa-book-reader"></i> Borrow This Book
                                </button>
                            <?php else: ?>
                                <a href="<?php echo BASE_URL; ?>/public/login.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt"></i> Login to Borrow
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-lg" disabled>
                                <i class="fas fa-times-circle"></i> Currently Unavailable
                            </button>
                        <?php endif; ?>
                    </div>
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
                        <li><a href="<?php echo BASE_URL; ?>/public/login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
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

        function borrowBook(bookId) {
            // This would typically make an API call to borrow the book
            alert('Borrow functionality will be implemented in the user dashboard.');
            window.location.href = '<?php echo BASE_URL; ?>/public/user/index.php';
        }
    </script>
</body>
</html>
