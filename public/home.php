<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../src/helpers/DatabaseHelper.php';
require_once __DIR__ . '/../src/services/AuthService.php';

// Check if user is logged in
AuthService::initSession();
$isLoggedIn = AuthService::isAuthenticated();
$currentUser = $isLoggedIn ? AuthService::getCurrentUser() : null;

// Get statistics for home page
$stats = DatabaseHelper::getDashboardStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KH LIBRARY - Library Management System</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <a href="<?php echo BASE_URL; ?>/public/home.php" class="nav-link active">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <a href="<?php echo BASE_URL; ?>/public/browse.php" class="nav-link">
                        <i class="fas fa-book"></i> Browse Books
                    </a>
                    <?php if ($isLoggedIn): ?>
                        <a href="<?php echo $currentUser['user_type'] === 'admin' ? BASE_URL . '/public/admin/index.php' : BASE_URL . '/public/user/index.php'; ?>" class="nav-link">
                            <div style="display: inline-flex; align-items: center; gap: 8px;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold;">
                                    <?php echo strtoupper(substr($currentUser['first_name'], 0, 1)); ?>
                                </div>
                                <span><?php echo htmlspecialchars($currentUser['first_name']); ?></span>
                            </div>
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

    <!-- Categories Section -->
    <section class="categories-section">
        <div class="container">
            <h2 class="section-title">Browse by Category</h2>
            <div class="categories-grid">
                <a href="<?php echo BASE_URL; ?>/public/browse.php?category=1" class="category-card">
                    <i class="fas fa-book"></i>
                    <h3>Fiction</h3>
                    <p>Novels & Stories</p>
                </a>
                <a href="<?php echo BASE_URL; ?>/public/browse.php?category=3" class="category-card">
                    <i class="fas fa-rocket"></i>
                    <h3>Science Fiction</h3>
                    <p>Futuristic Tales</p>
                </a>
                <a href="<?php echo BASE_URL; ?>/public/browse.php?category=4" class="category-card">
                    <i class="fas fa-dragon"></i>
                    <h3>Fantasy</h3>
                    <p>Magical Worlds</p>
                </a>
                <a href="<?php echo BASE_URL; ?>/public/browse.php?category=5" class="category-card">
                    <i class="fas fa-search"></i>
                    <h3>Mystery</h3>
                    <p>Detective Stories</p>
                </a>
                <a href="<?php echo BASE_URL; ?>/public/browse.php?category=6" class="category-card">
                    <i class="fas fa-landmark"></i>
                    <h3>Classic Literature</h3>
                    <p>Timeless Classics</p>
                </a>
                <a href="<?php echo BASE_URL; ?>/public/browse.php?category=9" class="category-card">
                    <i class="fas fa-scroll"></i>
                    <h3>History</h3>
                    <p>Historical Books</p>
                </a>
                <a href="<?php echo BASE_URL; ?>/public/browse.php?category=10" class="category-card">
                    <i class="fas fa-heart"></i>
                    <h3>Romance</h3>
                    <p>Love Stories</p>
                </a>
                <a href="<?php echo BASE_URL; ?>/public/browse.php?category=11" class="category-card">
                    <i class="fas fa-ghost"></i>
                    <h3>Horror</h3>
                    <p>Thrilling Tales</p>
                </a>
            </div>
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
                    <h4>About</h4>
                    <p>KH Library is your trusted library management system featuring 50 world-famous books. Browse, borrow, and enjoy reading.</p>
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
                <p>&copy; 2025 KH LIBRARY. All rights reserved.</p>
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
