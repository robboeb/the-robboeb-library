<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../src/helpers/DatabaseHelper.php';

$stats = DatabaseHelper::getDashboardStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - ROBBOEB Libra</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/main.css">
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
                    <a href="<?php echo BASE_URL; ?>/public/about.php" class="nav-link active">
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

    <!-- About Hero -->
    <section class="page-hero">
        <div class="container">
            <h1><i class="fas fa-info-circle"></i> About ROBBOEB Libra</h1>
            <p>Your modern library management solution</p>
        </div>
    </section>

    <!-- About Content -->
    <section class="content-section">
        <div class="container">
            <div class="about-grid">
                <div class="about-content">
                    <h2>Our Mission</h2>
                    <p>ROBBOEB Libra is dedicated to revolutionizing library management through modern technology and user-friendly design. We believe that accessing knowledge should be simple, efficient, and enjoyable for everyone.</p>
                    
                    <h2>What We Offer</h2>
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i> Comprehensive book catalog management</li>
                        <li><i class="fas fa-check-circle"></i> Easy-to-use borrowing system</li>
                        <li><i class="fas fa-check-circle"></i> Real-time availability tracking</li>
                        <li><i class="fas fa-check-circle"></i> Advanced search and filtering</li>
                        <li><i class="fas fa-check-circle"></i> User-friendly interface</li>
                        <li><i class="fas fa-check-circle"></i> Secure authentication system</li>
                    </ul>

                    <h2>Our Technology</h2>
                    <p>Built with modern web technologies, ROBBOEB Libra provides a fast, reliable, and secure platform for library management. Our system uses direct database queries for real-time data access and features a responsive design that works seamlessly across all devices.</p>
                </div>

                <div class="about-stats">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($stats['total_books']); ?></h3>
                            <p>Total Books</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($stats['total_users']); ?></h3>
                            <p>Active Members</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-book-reader"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($stats['active_loans']); ?></h3>
                            <p>Active Loans</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($stats['total_categories']); ?></h3>
                            <p>Categories</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <h2 class="section-title">Get In Touch</h2>
            <div class="contact-grid">
                <div class="contact-card">
                    <i class="fas fa-envelope"></i>
                    <h3>Email</h3>
                    <p>info@robboeb-libra.com</p>
                </div>
                <div class="contact-card">
                    <i class="fas fa-phone"></i>
                    <h3>Phone</h3>
                    <p>+1 234 567 890</p>
                </div>
                <div class="contact-card">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>Address</h3>
                    <p>123 Library St, City</p>
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
    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-toggle')?.addEventListener('click', function() {
            document.querySelector('.nav-links').classList.toggle('active');
        });
    </script>
</body>
</html>
