<?php
require_once __DIR__ . '/../src/services/AuthService.php';
require_once __DIR__ . '/../src/helpers/DatabaseHelper.php';
require_once __DIR__ . '/../config/constants.php';

$currentUser = AuthService::getCurrentUser();
$pdo = DatabaseHelper::getConnection();

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

// Build query
$sql = "SELECT b.*, 
        CONCAT(a.first_name, ' ', a.last_name) as author_name,
        c.name as category_name
        FROM books b
        LEFT JOIN book_authors ba ON b.book_id = ba.book_id
        LEFT JOIN authors a ON ba.author_id = a.author_id
        LEFT JOIN categories c ON b.category_id = c.category_id
        WHERE 1=1";

if ($search) {
    $sql .= " AND (b.title LIKE :search OR a.first_name LIKE :search OR a.last_name LIKE :search)";
}

if ($category) {
    $sql .= " AND b.category_id = :category";
}

$sql .= " ORDER BY b.created_at DESC, b.title ASC";

$stmt = $pdo->prepare($sql);
if ($search) {
    $stmt->bindValue(':search', "%$search%");
}
if ($category) {
    $stmt->bindValue(':category', $category);
}
$stmt->execute();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$cat_sql = "SELECT * FROM categories ORDER BY name ASC";
$categories = $pdo->query($cat_sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Books - KH LIBRARY</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #eeeeee 0%, #ffffff 100%);
            color: #111111;
            min-height: 100vh;
        }
        
        /* Navigation */
        .navbar {
            background: #ffffff;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }
        
        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 24px;
            font-weight: 800;
            color: #111111;
            text-decoration: none;
        }
        
        .nav-brand img {
            height: 40px;
            width: 40px;
        }
        
        .nav-links {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .nav-link {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            color: #111111;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .nav-link:hover {
            background: #eeeeee;
            color: #ff5722;
        }
        
        .nav-link.active {
            background: #ff5722;
            color: #ffffff;
        }
        
        .btn {
            padding: 10px 24px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #ff5722 0%, #ee3900 100%);
            color: #ffffff;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 87, 34, 0.4);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid #ff5722;
            color: #ff5722;
        }
        
        .btn-outline:hover {
            background: #ff5722;
            color: #ffffff;
        }
        
        /* Main Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 30px;
        }
        
        /* Header */
        .page-header {
            margin-bottom: 40px;
        }
        
        .page-title {
            font-size: 42px;
            font-weight: 800;
            color: #111111;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .page-title i {
            color: #ff5722;
        }
        
        .page-subtitle {
            font-size: 18px;
            color: #666;
            font-weight: 400;
        }
        
        /* Search Section */
        .search-bar {
            background: #ffffff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            margin-bottom: 40px;
        }
        
        .search-form {
            display: grid;
            grid-template-columns: 1fr 250px auto;
            gap: 15px;
        }
        
        .search-input, .search-select {
            padding: 14px 20px;
            border: 2px solid #eeeeee;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .search-input:focus, .search-select:focus {
            outline: none;
            border-color: #ff5722;
            box-shadow: 0 0 0 3px rgba(255, 87, 34, 0.1);
        }
        
        /* Books Grid */
        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }
        
        .book-card {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: box-shadow 0.3s ease;
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .book-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        
        .book-cover-container {
            position: relative;
            width: 100%;
            padding: 30px 30px 20px;
            background: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .book-cover {
            width: 100%;
            max-width: 200px;
            height: 280px;
            object-fit: cover;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .wishlist-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #ffffff;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            z-index: 10;
        }
        
        .wishlist-btn i {
            font-size: 18px;
            color: #999;
            transition: all 0.3s ease;
        }
        
        .wishlist-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(255, 87, 34, 0.3);
        }
        
        .wishlist-btn:hover i {
            color: #ff5722;
        }
        
        .wishlist-btn.active i {
            color: #ff5722;
            font-weight: 900;
        }
        
        .book-info {
            padding: 20px 25px 25px;
            flex: 1;
            display: flex;
            flex-direction: column;
            text-align: center;
        }
        
        .book-price {
            font-size: 24px;
            font-weight: 800;
            color: #111111;
            margin-bottom: 8px;
        }
        
        .book-rating {
            margin-bottom: 12px;
        }
        
        .book-rating i {
            color: #fbbf24;
            font-size: 14px;
        }
        
        .book-rating i.empty {
            color: #d1d5db;
        }
        
        .book-title {
            font-size: 15px;
            font-weight: 600;
            color: #666;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 40px;
            line-height: 1.4;
        }
        
        .book-author {
            font-size: 13px;
            color: #999;
            margin-bottom: 18px;
        }
        
        .book-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: auto;
        }
        
        .action-btn {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            background: #ffffff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .action-btn i {
            font-size: 18px;
            color: #666;
            transition: color 0.3s ease;
        }
        
        .action-btn:hover {
            border-color: #ff5722;
            background: #fff5f2;
        }
        
        .action-btn:hover i {
            color: #ff5722;
        }
        
        .action-btn.primary {
            background: #ff5722;
            border-color: #ff5722;
            flex: 1;
            max-width: 120px;
        }
        
        .action-btn.primary i {
            color: #ffffff;
        }
        
        .action-btn.primary:hover {
            background: #ee3900;
            border-color: #ee3900;
        }
        
        .availability-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            z-index: 10;
        }
        
        .availability-badge.available {
            background: #10b981;
            color: #ffffff;
        }
        
        .availability-badge.unavailable {
            background: #ef4444;
            color: #ffffff;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }
        
        .empty-state i {
            font-size: 80px;
            color: #eeeeee;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            font-size: 28px;
            color: #111111;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            font-size: 16px;
            color: #666;
        }
        
        /* Footer */
        .footer {
            background: #111111;
            color: #ffffff;
            padding: 40px 0 20px;
            margin-top: 60px;
        }
        
        .footer-content {
            max-width: 1400px;
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
        }
        
        .footer-bottom a {
            color: #ff5722;
            text-decoration: none;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .search-form {
                grid-template-columns: 1fr;
            }
            
            .books-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 20px;
            }
            
            .nav-links {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="<?php echo BASE_URL; ?>/public/home.php" class="nav-brand">
                <img src="https://s3.ca-central-1.amazonaws.com/logojoy/logos/231703335/symbol.svg?1537014.9000000358" alt="KH LIBRARY">
                <span>KH LIBRARY</span>
            </a>
            <div class="nav-links">
                <a href="<?php echo BASE_URL; ?>/public/home.php" class="nav-link">
                    <i class="fas fa-home"></i> Home
                </a>
                <a href="<?php echo BASE_URL; ?>/public/browse-new.php" class="nav-link active">
                    <i class="fas fa-book"></i> Browse Books
                </a>
                <?php if ($currentUser): ?>
                    <?php if ($currentUser['user_type'] === 'admin'): ?>
                        <a href="<?php echo BASE_URL; ?>/public/admin/index.php" class="nav-link">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/public/user/index.php" class="nav-link">
                            <i class="fas fa-user"></i> My Account
                        </a>
                    <?php endif; ?>
                    <button onclick="logout()" class="btn btn-outline">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/public/login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-book-open"></i>
                Discover Our Collection
            </h1>
            <p class="page-subtitle">Explore <?php echo count($books); ?> amazing books in our library</p>
        </div>

        <!-- Search Bar -->
        <div class="search-bar">
            <form method="GET" class="search-form">
                <input type="text" 
                       name="search" 
                       class="search-input" 
                       placeholder="Search by title or author..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <select name="category" class="search-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['category_id']; ?>" 
                                <?php echo $category == $cat['category_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
        </div>

        <!-- Books Grid -->
        <?php if (empty($books)): ?>
            <div class="empty-state">
                <i class="fas fa-book-open"></i>
                <h3>No Books Found</h3>
                <p>Try adjusting your search or filters to find what you're looking for</p>
            </div>
        <?php else: ?>
            <div class="books-grid">
                <?php foreach ($books as $book): ?>
                    <div class="book-card">
                        <div class="book-cover-container">
                            <span class="availability-badge <?php echo $book['available_quantity'] > 0 ? 'available' : 'unavailable'; ?>">
                                <i class="fas fa-circle"></i>
                                <?php echo $book['available_quantity']; ?> Available
                            </span>
                            <button class="wishlist-btn" onclick="toggleWishlist(<?php echo $book['book_id']; ?>, this)" title="Add to Wishlist">
                                <i class="far fa-heart"></i>
                            </button>
                            <img src="<?php echo htmlspecialchars($book['cover_image'] ?: BASE_URL . '/public/assets/images/book-placeholder.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($book['title']); ?>" 
                                 class="book-cover">
                        </div>
                        <div class="book-info">
                            <div class="book-price">FREE</div>
                            <div class="book-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star empty"></i>
                            </div>
                            <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                            <p class="book-author"><?php echo htmlspecialchars($book['author_name'] ?: 'Unknown Author'); ?></p>
                            <div class="book-actions">
                                <?php if ($currentUser && $currentUser['user_type'] === 'patron' && $book['available_quantity'] > 0): ?>
                                    <button class="action-btn primary" onclick="requestBorrow(<?php echo $book['book_id']; ?>, '<?php echo htmlspecialchars(addslashes($book['title'])); ?>')" title="Request to Borrow">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="action-btn primary" onclick="viewDetails(<?php echo $book['book_id']; ?>)" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                <?php endif; ?>
                                <button class="action-btn" onclick="viewDetails(<?php echo $book['book_id']; ?>)" title="Quick View">
                                    <i class="fas fa-search"></i>
                                </button>
                                <button class="action-btn" onclick="shareBook(<?php echo $book['book_id']; ?>, '<?php echo htmlspecialchars(addslashes($book['title'])); ?>')" title="Share">
                                    <i class="fas fa-share-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>KH LIBRARY</h3>
                <p>Your trusted library management system for discovering and borrowing amazing books.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>/public/home.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/public/browse-new.php"><i class="fas fa-book"></i> Browse Books</a></li>
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
            <p>&copy; 2025 KH LIBRARY. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Wishlist functionality
        let wishlist = JSON.parse(localStorage.getItem('bookWishlist') || '[]');
        
        // Initialize wishlist buttons on page load
        document.addEventListener('DOMContentLoaded', function() {
            wishlist.forEach(bookId => {
                const btn = document.querySelector(`.wishlist-btn[onclick*="${bookId}"]`);
                if (btn) {
                    btn.classList.add('active');
                    btn.querySelector('i').classList.remove('far');
                    btn.querySelector('i').classList.add('fas');
                }
            });
        });
        
        function toggleWishlist(bookId, button) {
            const icon = button.querySelector('i');
            const index = wishlist.indexOf(bookId);
            
            if (index > -1) {
                // Remove from wishlist
                wishlist.splice(index, 1);
                button.classList.remove('active');
                icon.classList.remove('fas');
                icon.classList.add('far');
                showNotification('Removed from wishlist', 'info');
            } else {
                // Add to wishlist
                wishlist.push(bookId);
                button.classList.add('active');
                icon.classList.remove('far');
                icon.classList.add('fas');
                showNotification('Added to wishlist ❤️', 'success');
            }
            
            localStorage.setItem('bookWishlist', JSON.stringify(wishlist));
        }
        
        function viewDetails(bookId) {
            window.location.href = '<?php echo BASE_URL; ?>/public/book-detail.php?id=' + bookId;
        }
        
        function requestBorrow(bookId, bookTitle) {
            if (!confirm(`Request to borrow "${bookTitle}"?\n\nLoan Period: <?php echo DEFAULT_LOAN_PERIOD; ?> days\n\nYour request will be sent to the admin for approval.`)) {
                return;
            }

            fetch('<?php echo BASE_URL; ?>/api/loans/request', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    book_id: parseInt(bookId)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('✓ Request submitted successfully!', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    throw new Error(data.error?.message || 'Failed to submit request');
                }
            })
            .catch(error => {
                showNotification('❌ ' + error.message, 'error');
            });
        }
        
        function shareBook(bookId, bookTitle) {
            const url = window.location.origin + '<?php echo BASE_URL; ?>/public/book-detail.php?id=' + bookId;
            
            if (navigator.share) {
                navigator.share({
                    title: bookTitle,
                    text: 'Check out this book: ' + bookTitle,
                    url: url
                }).catch(() => {});
            } else {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(url).then(() => {
                    showNotification('Link copied to clipboard!', 'success');
                }).catch(() => {
                    showNotification('Could not copy link', 'error');
                });
            }
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
                    window.location.href = '<?php echo BASE_URL; ?>/public/home.php';
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
