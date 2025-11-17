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
    <title>KHLIBRARY - Digital Library Platform</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="public-nav">
        <div class="container">
            <div class="nav-content">
                <div class="nav-brand">
                    <img src="https://s3.ca-central-1.amazonaws.com/logojoy/logos/231703335/symbol.svg?1537014.9000000358" alt="KHLIBRARY" class="brand-logo">
                    <span>KHLIBRARY</span>
                </div>
                <div class="nav-links">
                    <a href="<?php echo BASE_URL; ?>/public/home.php" class="nav-link active">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <a href="<?php echo BASE_URL; ?>/public/browse.php" class="nav-link">
                        <i class="fas fa-book"></i> Browse Books
                    </a>
                    <?php if ($isLoggedIn): ?>
                        <?php if ($currentUser['user_type'] === 'admin'): ?>
                            <a href="<?php echo BASE_URL; ?>/public/admin/index.php" class="nav-link">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>/public/user/profile.php" class="nav-link">
                                <i class="fas fa-user"></i> My Profile
                            </a>
                        <?php endif; ?>
                        <button onclick="logout()" class="btn btn-outline" style="margin-left: var(--space-2);">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
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

    <!-- Hero Banner -->
    <section class="hero-banner">
        <div class="hero-background">
            <div class="hero-shape shape-1"></div>
            <div class="hero-shape shape-2"></div>
            <div class="hero-shape shape-3"></div>
        </div>
        <div class="container">
            <div class="hero-grid">
                <div class="hero-left">
                    <div class="hero-badge">
                        <i class="fas fa-book-reader"></i>
                        <span>Digital Library Platform</span>
                    </div>
                    <h1 class="hero-heading">
                        Discover Your Next
                        <span class="gradient-text">Great Read</span>
                    </h1>
                    <p class="hero-description">
                        Explore thousands of books, borrow instantly, and immerse yourself in stories that inspire. 
                        Your literary journey starts here.
                    </p>
                    <div class="hero-cta">
                        <a href="<?php echo BASE_URL; ?>/public/browse.php" class="cta-primary">
                            <span>Start Exploring</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <?php if (!$isLoggedIn): ?>
                        <a href="<?php echo BASE_URL; ?>/public/login.php" class="cta-secondary">
                            <i class="fas fa-user-plus"></i>
                            <span>Join Free</span>
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="hero-stats-inline">
                        <div class="stat-inline">
                            <strong><?php echo number_format($stats['total_books'] ?? 0); ?>+</strong>
                            <span>Books</span>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-inline">
                            <strong><?php echo number_format($stats['total_users'] ?? 0); ?>+</strong>
                            <span>Members</span>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-inline">
                            <strong><?php echo number_format($stats['total_categories'] ?? 0); ?>+</strong>
                            <span>Categories</span>
                        </div>
                    </div>
                </div>
                <div class="hero-right">
                    <div class="book-stack">
                        <div class="book-card book-1">
                            <div class="book-spine"></div>
                            <div class="book-cover">
                                <i class="fas fa-book"></i>
                            </div>
                        </div>
                        <div class="book-card book-2">
                            <div class="book-spine"></div>
                            <div class="book-cover">
                                <i class="fas fa-book-open"></i>
                            </div>
                        </div>
                        <div class="book-card book-3">
                            <div class="book-spine"></div>
                            <div class="book-cover">
                                <i class="fas fa-bookmark"></i>
                            </div>
                        </div>
                    </div>
                    <div class="floating-element element-1">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="floating-element element-2">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="floating-element element-3">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Showcase -->
    <section class="features-showcase">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Why Choose Us</span>
                <h2 class="section-heading">Everything You Need in One Place</h2>
            </div>
            <div class="features-modern-grid">
                <div class="feature-modern">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon-bg"></div>
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3>Instant Access</h3>
                    <p>Browse and borrow books 24/7 from anywhere in the world</p>
                </div>
                <div class="feature-modern">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon-bg"></div>
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Secure Platform</h3>
                    <p>Your data is protected with enterprise-grade security</p>
                </div>
                <div class="feature-modern">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon-bg"></div>
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3>Smart Notifications</h3>
                    <p>Never miss a due date with automated reminders</p>
                </div>
                <div class="feature-modern">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon-bg"></div>
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Mobile Friendly</h3>
                    <p>Seamless experience across all your devices</p>
                </div>
            </div>
        </div>
    </section>

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
                <a href="<?php echo BASE_URL; ?>/public/browse.php?category=2" class="category-card">
                    <i class="fas fa-graduation-cap"></i>
                    <h3>Non-Fiction</h3>
                    <p>Real Stories & Facts</p>
                </a>
                <a href="<?php echo BASE_URL; ?>/public/browse.php?category=6" class="category-card">
                    <i class="fas fa-user"></i>
                    <h3>Biography</h3>
                    <p>Life Stories</p>
                </a>
                <a href="<?php echo BASE_URL; ?>/public/browse.php?category=5" class="category-card">
                    <i class="fas fa-laptop-code"></i>
                    <h3>Technology</h3>
                    <p>Tech & Programming</p>
                </a>
                <a href="<?php echo BASE_URL; ?>/public/browse.php?category=3" class="category-card">
                    <i class="fas fa-flask"></i>
                    <h3>Science</h3>
                    <p>Scientific Discovery</p>
                </a>
                <a href="<?php echo BASE_URL; ?>/public/browse.php?category=7" class="category-card">
                    <i class="fas fa-child"></i>
                    <h3>Children</h3>
                    <p>Kids & Young Adult</p>
                </a>
                <a href="<?php echo BASE_URL; ?>/public/browse.php?category=8" class="category-card">
                    <i class="fas fa-briefcase"></i>
                    <h3>Business</h3>
                    <p>Leadership & Success</p>
                </a>
                <a href="<?php echo BASE_URL; ?>/public/browse.php?category=9" class="category-card">
                    <i class="fas fa-brain"></i>
                    <h3>Self-Help</h3>
                    <p>Personal Growth</p>
                </a>
                <a href="<?php echo BASE_URL; ?>/public/browse.php?category=10" class="category-card">
                    <i class="fas fa-utensils"></i>
                    <h3>Cooking</h3>
                    <p>Recipes & Food</p>
                </a>
                <a href="<?php echo BASE_URL; ?>/public/browse.php?category=11" class="category-card">
                    <i class="fas fa-plane"></i>
                    <h3>Travel</h3>
                    <p>Adventure & Guides</p>
                </a>
                <a href="<?php echo BASE_URL; ?>/public/browse.php?category=12" class="category-card">
                    <i class="fas fa-palette"></i>
                    <h3>Art & Design</h3>
                    <p>Creative Inspiration</p>
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
                        <img src="https://s3.ca-central-1.amazonaws.com/logojoy/logos/231703335/symbol.svg?1537014.9000000358" alt="KHLIBRARY" class="brand-logo-footer">
                        KHLIBRARY
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
                <p>&copy; 2025 KHLIBRARY. All rights reserved. | Developed by <a href="https://t.me/eirsvi" target="_blank" style="color: #ff5722; text-decoration: none;">eirsvi.t.me</a> | <a href="https://github.com/robboeb/the-robboeb-library.git" target="_blank" style="color: #ff5722; text-decoration: none;"><i class="fab fa-github"></i> GitHub</a></p>
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
        
        // Logout function
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                fetch('<?php echo BASE_URL; ?>/api/auth/logout', {
                    method: 'POST',
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '<?php echo BASE_URL; ?>/public/login.php?logout=1';
                    }
                })
                .catch(error => {
                    console.error('Logout error:', error);
                    window.location.href = '<?php echo BASE_URL; ?>/public/login.php?logout=1';
                });
            }
        }
    </script>
</body>
</html>
