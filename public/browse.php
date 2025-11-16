<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../src/helpers/DatabaseHelper.php';

$pdo = DatabaseHelper::getConnection();

// Get filter parameters
$search = $_GET['search'] ?? '';
$category_id = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Build query
$where = [];
$params = [];

if ($search) {
    $where[] = "(b.title LIKE :search OR b.isbn LIKE :search OR CONCAT(a.first_name, ' ', a.last_name) LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($category_id) {
    $where[] = "b.category_id = :category_id";
    $params[':category_id'] = $category_id;
}

if ($status) {
    if ($status === 'available') {
        $where[] = "b.available_quantity > 0";
    } elseif ($status === 'borrowed') {
        $where[] = "b.available_quantity = 0";
    }
}

$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Determine sort order
$order_by = match($sort) {
    'title-asc' => 'b.title ASC',
    'title-desc' => 'b.title DESC',
    'author' => 'authors ASC',
    default => 'b.created_at DESC'
};

// Get total count
$count_sql = "SELECT COUNT(DISTINCT b.book_id) as total 
              FROM books b
              LEFT JOIN book_authors ba ON b.book_id = ba.book_id
              LEFT JOIN authors a ON ba.author_id = a.author_id
              $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_books = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_books / $per_page);

// Get books
$sql = "SELECT b.*, c.name as category_name,
        b.total_quantity as total_copies,
        b.available_quantity as available_copies,
        CASE 
            WHEN b.available_quantity > 0 THEN 'available'
            ELSE 'borrowed'
        END as status,
        GROUP_CONCAT(CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ') as authors
        FROM books b
        LEFT JOIN categories c ON b.category_id = c.category_id
        LEFT JOIN book_authors ba ON b.book_id = ba.book_id
        LEFT JOIN authors a ON ba.author_id = a.author_id
        $where_clause
        GROUP BY b.book_id
        ORDER BY $order_by
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories and statistics
$categories = DatabaseHelper::getAllCategories();
$all_stats = DatabaseHelper::getDashboardStats();
$stats = [
    'total_books' => $all_stats['total_books'],
    'available_books' => $all_stats['available_books']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Books - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/browse.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="public-nav">
        <div class="container">
            <div class="nav-content">
                <div class="nav-brand">
                    <img src="<?php echo BASE_URL; ?>/public/assets/brand/symbol.svg" alt="ROBBOEB Libra" class="brand-logo">
                    <span>ROBBOEB Libra</span>
                </div>
                <div class="nav-links">
                    <a href="<?php echo BASE_URL; ?>/public/home.php" class="nav-link">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <a href="<?php echo BASE_URL; ?>/public/browse.php" class="nav-link active">
                        <i class="fas fa-book"></i> Browse Books
                    </a>
                    <a href="<?php echo BASE_URL; ?>/public/login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                </div>
                <button class="mobile-menu-toggle" onclick="document.querySelector('.nav-links').classList.toggle('active')">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Browse Section -->
    <section class="browse-section">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1><i class="fas fa-book"></i> Browse Our Collection</h1>
                <p>Discover thousands of books across various genres and categories</p>
            </div>

            <!-- Stats Bar -->
            <div class="stats-bar">
                <div class="stat-item">
                    <i class="fas fa-book"></i>
                    <div>
                        <span class="stat-number"><?php echo $stats['total_books']; ?></span>
                        <span class="stat-label">Total Books</span>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <span class="stat-number"><?php echo $stats['available_books']; ?></span>
                        <span class="stat-label">Available</span>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-tags"></i>
                    <div>
                        <span class="stat-number"><?php echo count($categories); ?></span>
                        <span class="stat-label">Categories</span>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <form method="GET" action="" class="browse-controls">
                <div class="search-container">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Search by title, author, or ISBN..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="filter-container">
                    <div class="filter-group">
                        <label><i class="fas fa-tag"></i> Category</label>
                        <select name="category" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>" <?php echo $category_id == $cat['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label><i class="fas fa-sort"></i> Sort By</label>
                        <select name="sort" onchange="this.form.submit()">
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="title-asc" <?php echo $sort === 'title-asc' ? 'selected' : ''; ?>>Title (A-Z)</option>
                            <option value="title-desc" <?php echo $sort === 'title-desc' ? 'selected' : ''; ?>>Title (Z-A)</option>
                            <option value="author" <?php echo $sort === 'author' ? 'selected' : ''; ?>>Author</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label><i class="fas fa-filter"></i> Status</label>
                        <select name="status" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="available" <?php echo $status === 'available' ? 'selected' : ''; ?>>Available</option>
                            <option value="borrowed" <?php echo $status === 'borrowed' ? 'selected' : ''; ?>>Borrowed</option>
                        </select>
                    </div>
                </div>
                
                <input type="hidden" name="page" value="1">
                <button type="submit" class="btn btn-primary" style="margin-top: 12px;">
                    <i class="fas fa-search"></i> Apply Filters
                </button>
            </form>

            <!-- Category Pills -->
            <div class="category-pills">
                <a href="?<?php echo http_build_query(array_merge($_GET, ['category' => '', 'page' => 1])); ?>" 
                   class="category-pill <?php echo !$category_id ? 'active' : ''; ?>">
                    <i class="fas fa-th"></i> All Books
                </a>
                <?php foreach (array_slice($categories, 0, 8) as $cat): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['category' => $cat['category_id'], 'page' => 1])); ?>" 
                       class="category-pill <?php echo $category_id == $cat['category_id'] ? 'active' : ''; ?>">
                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($cat['category_name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Books Grid -->
            <div class="books-container">
                <div class="books-grid">
                    <?php if (empty($books)): ?>
                        <div class="empty-state">
                            <i class="fas fa-search"></i>
                            <h3>No books found</h3>
                            <p>Try adjusting your search or filters</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($books as $book): ?>
                            <div class="book-card" onclick="showBookDetails(<?php echo $book['book_id']; ?>)">
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
                                    <span class="book-status-badge <?php echo $book['status'] === 'available' ? 'badge-success' : 'badge-warning'; ?>">
                                        <?php echo ucfirst($book['status']); ?>
                                    </span>
                                </div>
                                <div class="book-info">
                                    <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                                    <div class="book-author">
                                        <i class="fas fa-user"></i>
                                        <?php echo htmlspecialchars($book['authors'] ?: 'Unknown Author'); ?>
                                    </div>
                                    <?php if ($book['category_name']): ?>
                                        <span class="book-category"><?php echo htmlspecialchars($book['category_name']); ?></span>
                                    <?php endif; ?>
                                    <?php if ($book['isbn']): ?>
                                        <div class="book-isbn">ISBN: <?php echo htmlspecialchars($book['isbn']); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="book-footer">
                                    <span style="font-size: 12px; color: var(--gray-500);">
                                        <i class="fas fa-copy"></i> <?php echo $book['total_copies'] ?? 0; ?> copies
                                    </span>
                                    <a href="<?php echo BASE_URL; ?>/public/login.php" class="book-action-btn" onclick="event.stopPropagation();">
                                        <i class="fas fa-book-reader"></i> Borrow
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination-container">
                        <div class="pagination-info">
                            Showing <?php echo $offset + 1; ?>-<?php echo min($offset + $per_page, $total_books); ?> of <?php echo $total_books; ?> books
                        </div>
                        <div class="pagination-controls">
                            <?php if ($page > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="pagination-btn">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                                   class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="pagination-btn">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
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

    <script>
        function showBookDetails(bookId) {
            window.location.href = '<?php echo BASE_URL; ?>/public/book-details.php?id=' + bookId;
        }
    </script>
</body>
</html>
