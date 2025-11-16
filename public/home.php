<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../src/helpers/DatabaseHelper.php';

// Get available books for public viewing
$books = DatabaseHelper::getAllBooks(['status' => 'available', 'limit' => 12]);
$stats = DatabaseHelper::getDashboardStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ROBBOEB Libra - Library Management System</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="public-nav">
        <div class="container">
            <div class="nav-content">
                <div class="nav-brand">
                    <img src="<?php echo BASE_URL; ?>/public/assets/brand/symbol.svg" alt="ROBBOEB Libra" class="brand-logo">
                    <span>ROBBOEB Library</span>
                </div>
                <div class="nav-links">
                    <a href="<?php echo BASE_URL; ?>/public/home.php" class="nav-link active">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <a href="<?php echo BASE_URL; ?>/public/browse.php" class="nav-link">
                        <i class="fas fa-book"></i> Browse Books
                    </a>
                    <a href="<?php echo BASE_URL; ?>/public/about.php" class="nav-link">
                        <i class="fas fa-info-circle"></i> About
                    </a>
                    <a href="<?php echo BASE_URL; ?>/public/login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                </div>
                <button class="mobile-menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Welcome to ROBBOEB Libra</h1>
                    <p>Discover thousands of books and manage your reading journey with our modern library management system.</p>
                    <div class="hero-actions">
                        <a href="<?php echo BASE_URL; ?>/public/browse.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-search"></i> Browse Books
                        </a>
                        <a href="<?php echo BASE_URL; ?>/public/login.php" class="btn btn-outline btn-lg">
                            <i class="fas fa-user"></i> Member Login
                        </a>
                    </div>
                </div>
                <div class="hero-image">
                    <i class="fas fa-book-reader"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2 class="section-title">Why Choose ROBBOEB Libra?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3>Vast Collection</h3>
                    <p>Access thousands of books across various genres and categories</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Easy Search</h3>
                    <p>Find your favorite books quickly with our advanced search system</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>24/7 Access</h3>
                    <p>Browse and reserve books anytime, anywhere</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h3>Secure System</h3>
                    <p>Your data and reading history are safe with us</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Available Books Section -->
    <section class="books-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Available Books</h2>
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
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="public-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>
                        <img src="<?php echo BASE_URL; ?>/public/assets/brand/symbol.svg" alt="ROBBOEB Libra" class="brand-logo-footer">
                        ROBBOEB Libra
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
                <p>&copy; 2025 ROBBOEB Libra. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="<?php echo BASE_URL; ?>/public/assets/js/utils.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/components.js"></script>
    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-toggle')?.addEventListener('click', function() {
            document.querySelector('.nav-links').classList.toggle('active');
        });
    </script>
</body>
</html>
