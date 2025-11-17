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
                    <img src="https://s3.ca-central-1.amazonaws.com/logojoy/logos/231703335/symbol.svg?1537014.9000000358" alt="THE ROBBOEB LIBRARY" class="brand-logo">
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
    <section style="min-height: calc(100vh - 80px); background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 20px; display: flex; align-items: center;">
        <div class="container" style="max-width: 1400px;">
            <a href="<?php echo BASE_URL; ?>/public/browse.php" style="display: inline-flex; align-items: center; gap: 8px; color: white; text-decoration: none; font-weight: 600; margin-bottom: 30px; padding: 10px 20px; background: rgba(255,255,255,0.2); border-radius: 8px; backdrop-filter: blur(10px);">
                <i class="fas fa-arrow-left"></i> Back to Browse
            </a>

            <div style="background: white; border-radius: 24px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden;">
                <div style="display: grid; grid-template-columns: 400px 1fr; min-height: 600px;">
                    <!-- Book Cover Section -->
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <?php if (!empty($book['cover_image'])): ?>
                            <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($book['title']); ?>"
                                 style="width: 100%; max-width: 320px; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3);"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div style="display: none; width: 100%; max-width: 320px; aspect-ratio: 2/3; background: rgba(255,255,255,0.2); border-radius: 16px; backdrop-filter: blur(10px); flex-direction: column; align-items: center; justify-content: center; color: white; text-align: center; padding: 30px;">
                                <i class="fas fa-book" style="font-size: 80px; margin-bottom: 20px; opacity: 0.8;"></i>
                                <span style="font-size: 18px; font-weight: 600;"><?php echo htmlspecialchars($book['title']); ?></span>
                            </div>
                        <?php else: ?>
                            <div style="width: 100%; max-width: 320px; aspect-ratio: 2/3; background: rgba(255,255,255,0.2); border-radius: 16px; backdrop-filter: blur(10px); display: flex; flex-direction: column; align-items: center; justify-content: center; color: white; text-align: center; padding: 30px;">
                                <i class="fas fa-book" style="font-size: 80px; margin-bottom: 20px; opacity: 0.8;"></i>
                                <span style="font-size: 18px; font-weight: 600;"><?php echo htmlspecialchars($book['title']); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Availability Badges -->
                        <div style="margin-top: 30px; display: flex; gap: 12px; flex-wrap: wrap; justify-content: center;">
                            <?php if ($book['status'] === 'available'): ?>
                                <div style="background: rgba(16, 185, 129, 0.2); backdrop-filter: blur(10px); padding: 12px 24px; border-radius: 30px; color: white; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-check-circle"></i> <?php echo $book['available_quantity']; ?> Available
                                </div>
                            <?php else: ?>
                                <div style="background: rgba(239, 68, 68, 0.2); backdrop-filter: blur(10px); padding: 12px 24px; border-radius: 30px; color: white; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-times-circle"></i> Not Available
                                </div>
                            <?php endif; ?>
                            <div style="background: rgba(59, 130, 246, 0.2); backdrop-filter: blur(10px); padding: 12px 24px; border-radius: 30px; color: white; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-calendar"></i> 14 Days Loan
                            </div>
                        </div>
                    </div>

                    <!-- Book Information Section -->
                    <div style="padding: 50px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <h1 style="font-size: 36px; font-weight: 700; color: #1f2937; margin: 0 0 20px 0; line-height: 1.2;">
                                <?php echo htmlspecialchars($book['title']); ?>
                            </h1>
                            
                            <!-- Meta Information Grid -->
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 30px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 48px; height: 48px; background: #eff6ff; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #3b82f6;">
                                        <i class="fas fa-user" style="font-size: 20px;"></i>
                                    </div>
                                    <div>
                                        <div style="font-size: 12px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Author</div>
                                        <div style="font-size: 16px; color: #1f2937; font-weight: 600;"><?php echo htmlspecialchars($book['authors'] ?: 'Unknown'); ?></div>
                                    </div>
                                </div>
                                
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 48px; height: 48px; background: #fef3c7; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #f59e0b;">
                                        <i class="fas fa-tag" style="font-size: 20px;"></i>
                                    </div>
                                    <div>
                                        <div style="font-size: 12px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Category</div>
                                        <div style="font-size: 16px; color: #1f2937; font-weight: 600;"><?php echo htmlspecialchars($book['category_name'] ?: 'Uncategorized'); ?></div>
                                    </div>
                                </div>
                                
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 48px; height: 48px; background: #f3e8ff; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #a855f7;">
                                        <i class="fas fa-barcode" style="font-size: 20px;"></i>
                                    </div>
                                    <div>
                                        <div style="font-size: 12px; color: #6b7280; font-weight: 600; text-transform: uppercase;">ISBN</div>
                                        <div style="font-size: 16px; color: #1f2937; font-weight: 600;"><?php echo htmlspecialchars($book['isbn'] ?: 'N/A'); ?></div>
                                    </div>
                                </div>
                                
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 48px; height: 48px; background: #dcfce7; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #10b981;">
                                        <i class="fas fa-calendar" style="font-size: 20px;"></i>
                                    </div>
                                    <div>
                                        <div style="font-size: 12px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Published</div>
                                        <div style="font-size: 16px; color: #1f2937; font-weight: 600;"><?php echo htmlspecialchars($book['publication_year'] ?: 'N/A'); ?></div>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($book['description'])): ?>
                                <div style="margin-bottom: 30px;">
                                    <h2 style="font-size: 18px; font-weight: 700; color: #1f2937; margin: 0 0 12px 0; display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-align-left" style="color: #667eea;"></i> Description
                                    </h2>
                                    <p style="color: #4b5563; line-height: 1.7; margin: 0; font-size: 15px;">
                                        <?php echo nl2br(htmlspecialchars($book['description'])); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Action Button -->
                        <div>
                            <?php if ($book['status'] === 'available'): ?>
                                <?php if ($isAuthenticated): ?>
                                    <button onclick="borrowBook(<?php echo $book['book_id']; ?>)" style="width: 100%; padding: 18px 32px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 12px; font-size: 18px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 12px; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 30px rgba(102, 126, 234, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                        <i class="fas fa-book-reader"></i> Borrow This Book
                                    </button>
                                <?php else: ?>
                                    <a href="<?php echo BASE_URL; ?>/public/login.php" style="width: 100%; padding: 18px 32px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 12px; font-size: 18px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 12px; text-decoration: none; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 30px rgba(102, 126, 234, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                        <i class="fas fa-sign-in-alt"></i> Login to Borrow
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <button disabled style="width: 100%; padding: 18px 32px; background: #e5e7eb; color: #9ca3af; border: none; border-radius: 12px; font-size: 18px; font-weight: 700; cursor: not-allowed; display: flex; align-items: center; justify-content: center; gap: 12px;">
                                    <i class="fas fa-times-circle"></i> Currently Unavailable
                                </button>
                            <?php endif; ?>
                        </div>
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
                        <img src="https://s3.ca-central-1.amazonaws.com/logojoy/logos/231703335/symbol.svg?1537014.9000000358" alt="THE ROBBOEB LIBRARY" class="brand-logo-footer">
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
