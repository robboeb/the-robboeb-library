<?php
require_once __DIR__ . '/../src/services/AuthService.php';
require_once __DIR__ . '/../src/helpers/DatabaseHelper.php';
require_once __DIR__ . '/../config/constants.php';

$currentUser = AuthService::getCurrentUser();
$pdo = DatabaseHelper::getConnection();

// Get all available books
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

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

$sql .= " ORDER BY b.title ASC";

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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/main.css">
    <style>
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        .book-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }
        .book-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(102, 126, 234, 0.25);
        }
        .book-card:hover .book-cover {
            transform: scale(1.05);
        }
        .book-cover-wrapper {
            width: 100%;
            height: 350px;
            overflow: hidden;
            position: relative;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .book-cover {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .book-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .book-card:hover .book-overlay {
            opacity: 1;
        }
        .book-overlay-text {
            color: white;
            font-size: 16px;
            font-weight: 600;
            text-align: center;
        }
        .book-info {
            padding: 20px;
        }
        .book-title {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            margin: 0 0 8px 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 48px;
        }
        .book-author {
            font-size: 14px;
            color: #718096;
            margin: 0 0 12px 0;
        }
        .book-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            font-size: 13px;
            flex-wrap: wrap;
            gap: 8px;
        }
        .availability {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
        }
        .availability.available {
            background: #d1fae5;
            color: #065f46;
        }
        .availability.unavailable {
            background: #fee2e2;
            color: #991b1b;
        }
        .loan-period {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            background: #dbeafe;
            color: #1e40af;
        }
        .search-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .search-form {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 15px;
        }
        
        /* Book Detail Modal - Full Landscape Design */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.92);
            backdrop-filter: blur(12px);
            animation: modalFadeIn 0.3s ease-out;
            overflow-y: auto;
            padding: 40px;
        }
        
        .modal-content {
            background: #ffffff;
            margin: 0 auto;
            max-width: 1400px;
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 30px 90px rgba(0, 0, 0, 0.6);
            animation: modalSlideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            overflow: hidden;
            position: relative;
            display: grid;
            grid-template-columns: 450px 1fr;
            min-height: 650px;
        }
        
        .modal-close-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 50%;
            color: #667eea;
            font-size: 24px;
            cursor: pointer;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .modal-close-btn:hover {
            background: #667eea;
            color: white;
            transform: rotate(90deg) scale(1.1);
        }
        
        /* Left Side - Book Cover */
        .modal-left-panel {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .modal-left-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 1;
        }
        
        .modal-book-cover-container {
            position: relative;
            z-index: 1;
            text-align: center;
        }
        
        .modal-book-cover-large {
            width: 300px;
            height: 450px;
            object-fit: cover;
            border-radius: 16px;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.6);
            border: 6px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 35px;
            transition: transform 0.3s;
        }
        
        .modal-book-cover-large:hover {
            transform: scale(1.02);
        }
        
        .modal-book-badges {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .badge-pill {
            padding: 10px 18px;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .badge-available {
            background: rgba(16, 185, 129, 0.95);
            color: white;
        }
        
        .badge-unavailable {
            background: rgba(239, 68, 68, 0.95);
            color: white;
        }
        
        .badge-loan {
            background: rgba(59, 130, 246, 0.95);
            color: white;
        }
        
        /* Right Side - Book Details */
        .modal-right-panel {
            padding: 60px 50px;
            overflow-y: auto;
            background: #ffffff;
            display: flex;
            flex-direction: column;
        }
        
        .modal-book-header {
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 3px solid #e2e8f0;
        }
        
        .modal-book-title {
            font-size: 40px;
            font-weight: 800;
            margin: 0 0 18px 0;
            line-height: 1.2;
            color: #1e293b;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .modal-book-author {
            font-size: 22px;
            font-weight: 600;
            margin: 0;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .modal-book-author i {
            color: #667eea;
            font-size: 24px;
        }
        
        .info-section {
            margin-bottom: 35px;
        }
        
        .info-section-title {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #667eea;
            margin: 0 0 20px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }
        
        .info-item {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 25px;
            border-radius: 14px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s;
        }
        
        .info-item:hover {
            border-color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.15);
        }
        
        .info-item-label {
            font-size: 12px;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-item-label i {
            color: #667eea;
            font-size: 16px;
        }
        
        .info-item-value {
            font-size: 20px;
            color: #1e293b;
            font-weight: 700;
        }
        
        .loan-period-highlight {
            background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
            border: 3px solid #93c5fd;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 40px;
        }
        
        .loan-period-highlight .info-item-label {
            color: #1e40af;
            font-size: 13px;
            margin-bottom: 12px;
        }
        
        .loan-period-highlight .info-item-value {
            font-size: 36px;
            color: #667eea;
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 800;
        }
        
        .loan-period-highlight .info-item-value i {
            font-size: 32px;
        }
        
        .action-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 35px;
            border-radius: 18px;
            color: white;
            margin-top: auto;
        }
        
        .action-title {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 25px 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .action-button {
            width: 100%;
            padding: 20px 35px;
            font-size: 19px;
            font-weight: 700;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            text-decoration: none;
        }
        
        .action-button-primary {
            background: white;
            color: #667eea;
        }
        
        .action-button-primary:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
        }
        
        .action-button-primary:active {
            transform: translateY(-2px);
        }
        
        .action-button-disabled {
            background: rgba(255, 255, 255, 0.3);
            color: rgba(255, 255, 255, 0.7);
            cursor: not-allowed;
        }
        
        .action-info {
            margin-top: 25px;
            padding: 22px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.25);
        }
        
        .action-info-title {
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 14px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .action-info-list {
            margin: 0;
            padding-left: 22px;
            font-size: 15px;
            line-height: 2;
            opacity: 0.96;
        }
        
        .action-info-list li {
            margin-bottom: 10px;
        }
        
        .action-info-list strong {
            font-weight: 700;
        }
        
        @keyframes modalFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes modalSlideUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @media (max-width: 768px) {
            .search-form {
                grid-template-columns: 1fr;
            }
            .modal-body {
                grid-template-columns: 1fr;
            }
            .modal-book-cover {
                height: 300px;
            }
        }
    </style>
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
                    <a href="<?php echo BASE_URL; ?>/public/browse.php" class="nav-link active">
                        <i class="fas fa-book"></i> Browse Books
                    </a>
                    <?php if ($currentUser): ?>
                        <a href="<?php echo BASE_URL; ?>/public/user/index.php" class="nav-link">
                            <i class="fas fa-tachometer-alt"></i> My Dashboard
                        </a>
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

    <!-- Browse Section -->
    <section style="padding: 40px 0; background: #f7fafc; min-height: calc(100vh - 200px);">
        <div class="container" style="max-width: 1400px;">
            
            <h1 style="font-size: 36px; margin-bottom: 30px; color: #2d3748;">
                <i class="fas fa-book"></i> Browse Books
            </h1>

            <!-- Search & Filter -->
            <div class="search-section">
                <form method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Search by title or author..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           style="padding: 12px 20px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 15px;">
                    <select name="category" style="padding: 12px 20px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 15px;">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>" <?php echo $category == $cat['category_id'] ? 'selected' : ''; ?>>
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
                <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 12px;">
                    <i class="fas fa-book-open" style="font-size: 64px; color: #cbd5e0; margin-bottom: 20px;"></i>
                    <h3 style="margin: 0 0 10px 0; font-size: 24px; color: #2d3748;">No Books Found</h3>
                    <p style="margin: 0; color: #718096;">Try adjusting your search or filters</p>
                </div>
            <?php else: ?>
                <div class="book-grid">
                    <?php foreach ($books as $book): ?>
                        <div class="book-card" onclick="showBookDetail(<?php echo htmlspecialchars(json_encode($book)); ?>)">
                            <div class="book-cover-wrapper">
                                <img src="<?php echo $book['cover_image'] ?: BASE_URL . '/public/assets/images/book-placeholder.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($book['title']); ?>" class="book-cover">
                                <div class="book-overlay">
                                    <div class="book-overlay-text">
                                        <i class="fas fa-eye" style="font-size: 24px; margin-bottom: 8px;"></i>
                                        <div>Click to view details</div>
                                    </div>
                                </div>
                            </div>
                            <div class="book-info">
                                <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                                <p class="book-author">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($book['author_name'] ?: 'Unknown Author'); ?>
                                </p>
                                <div class="book-meta">
                                    <span style="color: #718096; font-size: 12px;">
                                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($book['category_name'] ?: 'Uncategorized'); ?>
                                    </span>
                                    <span class="availability <?php echo $book['available_quantity'] > 0 ? 'available' : 'unavailable'; ?>">
                                        <i class="fas fa-circle"></i>
                                        <?php echo $book['available_quantity']; ?> Available
                                    </span>
                                </div>
                                <div style="margin-bottom: 15px;">
                                    <span class="loan-period">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?php echo DEFAULT_LOAN_PERIOD; ?> days loan period
                                    </span>
                                </div>
                                <?php if ($currentUser && isset($currentUser['user_type']) && $currentUser['user_type'] === 'patron'): ?>
                                    <?php if ($book['available_quantity'] > 0): ?>
                                        <button onclick="event.stopPropagation(); requestBorrow(<?php echo $book['book_id']; ?>, '<?php echo htmlspecialchars(addslashes($book['title'])); ?>')" 
                                                class="btn btn-primary" style="width: 100%;">
                                            <i class="fas fa-hand-paper"></i> Request to Borrow
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-outline" style="width: 100%; cursor: not-allowed;" disabled onclick="event.stopPropagation();">
                                            <i class="fas fa-times-circle"></i> Not Available
                                        </button>
                                    <?php endif; ?>
                                <?php elseif ($currentUser && isset($currentUser['user_type']) && $currentUser['user_type'] === 'admin'): ?>
                                    <a href="<?php echo BASE_URL; ?>/public/admin/loans.php" class="btn btn-outline" style="width: 100%; text-align: center;" onclick="event.stopPropagation();">
                                        <i class="fas fa-cog"></i> Manage in Admin
                                    </a>
                                <?php elseif (!$currentUser): ?>
                                    <a href="<?php echo BASE_URL; ?>/public/login.php" class="btn btn-outline" style="width: 100%; text-align: center;" onclick="event.stopPropagation();">
                                        <i class="fas fa-sign-in-alt"></i> Login to Borrow
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </section>

    <!-- Book Detail Modal - Landscape Design -->
    <div id="bookDetailModal" class="modal">
        <div class="modal-content">
            <!-- Close Button -->
            <button class="modal-close-btn" onclick="closeBookDetail()" title="Close (ESC)">
                <i class="fas fa-times"></i>
            </button>
            
            <!-- Left Panel - Book Cover -->
            <div class="modal-left-panel">
                <div class="modal-book-cover-container">
                    <img id="modalBookCover" src="" alt="Book Cover" class="modal-book-cover-large">
                    <div class="modal-book-badges" id="modalBookBadges">
                        <!-- Badges will be inserted here -->
                    </div>
                </div>
            </div>
            
            <!-- Right Panel - Book Details -->
            <div class="modal-right-panel">
                <!-- Book Header -->
                <div class="modal-book-header">
                    <h1 class="modal-book-title" id="modalBookTitle"></h1>
                    <p class="modal-book-author">
                        <i class="fas fa-user-circle"></i>
                        <span id="modalBookAuthor"></span>
                    </p>
                </div>
                
                <!-- Book Information -->
                <div class="info-section">
                    <h3 class="info-section-title">
                        <i class="fas fa-info-circle"></i>
                        Book Information
                    </h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-item-label">
                                <i class="fas fa-tag"></i>
                                Category
                            </div>
                            <div class="info-item-value" id="modalBookCategory"></div>
                        </div>
                        <div class="info-item">
                            <div class="info-item-label">
                                <i class="fas fa-barcode"></i>
                                ISBN Number
                            </div>
                            <div class="info-item-value" id="modalBookISBN"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Loan Period Highlight -->
                <div class="loan-period-highlight">
                    <div class="info-item-label">
                        <i class="fas fa-calendar-check"></i>
                        Standard Loan Period
                    </div>
                    <div class="info-item-value">
                        <i class="fas fa-clock"></i>
                        <?php echo DEFAULT_LOAN_PERIOD; ?> Days
                    </div>
                </div>
                
                <!-- Action Section -->
                <div class="action-section" id="modalActionSection">
                    <!-- Action content will be inserted here -->
                </div>
            </div>
        </div>
    </div>

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
    const currentUser = <?php echo $currentUser ? json_encode($currentUser) : 'null'; ?>;
    const loanPeriod = <?php echo DEFAULT_LOAN_PERIOD; ?>;
    
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

    function requestBorrow(bookId, bookTitle) {
        if (!bookId) {
            alert('Error: Invalid book ID');
            const btn = document.getElementById('borrowBtn');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Request to Borrow';
                btn.style.opacity = '1';
            }
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
        .then(response => {
            // Parse JSON first to get error details
            return response.json().then(data => {
                if (!response.ok) {
                    // Include server error message if available
                    const errorMsg = data.error?.message || `Server error (${response.status})`;
                    throw new Error(errorMsg);
                }
                return data;
            });
        })
        .then(data => {
            if (data.success) {
                // Close modal
                closeBookDetail();
                
                // Show success message
                alert(`✓ Request Submitted Successfully!\n\n` +
                      `Book: ${bookTitle}\n` +
                      `Loan Period: ${loanPeriod} days\n\n` +
                      `Your borrow request has been sent to the library admin.\n` +
                      `You will be notified once it is approved.\n\n` +
                      `Check your dashboard to track the status of your request.`);
                
                // Reload page to update availability
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } else {
                throw new Error(data.error?.message || 'Failed to submit request');
            }
        })
        .catch(error => {
            console.error('Borrow request error:', error);
            console.error('Book ID:', bookId);
            console.error('User:', currentUser);
            
            // Show detailed error message
            let errorMessage = error.message;
            
            // Provide helpful suggestions based on error
            if (errorMessage.includes('not authenticated') || errorMessage.includes('login')) {
                errorMessage += '\n\nPlease refresh the page and login again.';
            } else if (errorMessage.includes('not available')) {
                errorMessage += '\n\nThis book may have just been borrowed by someone else.';
            } else if (errorMessage.includes('already have')) {
                errorMessage += '\n\nCheck your dashboard to see your existing request.';
            }
            
            alert(`❌ Request Failed\n\n${errorMessage}\n\nIf the problem persists, please contact support.`);
            
            // Reset button
            const btn = document.getElementById('borrowBtn');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Request to Borrow';
                btn.style.opacity = '1';
            }
        });
    }

    function showBookDetail(book) {
        const modal = document.getElementById('bookDetailModal');
        const baseUrl = '<?php echo BASE_URL; ?>';
        
        // Set book cover
        document.getElementById('modalBookCover').src = book.cover_image || baseUrl + '/public/assets/images/book-placeholder.jpg';
        
        // Set book title and author
        document.getElementById('modalBookTitle').textContent = book.title;
        document.getElementById('modalBookAuthor').textContent = book.author_name || 'Unknown Author';
        
        // Set badges
        const badgesEl = document.getElementById('modalBookBadges');
        let badgesHTML = '';
        
        if (book.available_quantity > 0) {
            badgesHTML += `
                <span class="badge-pill badge-available">
                    <i class="fas fa-check-circle"></i>
                    ${book.available_quantity} Available
                </span>
            `;
        } else {
            badgesHTML += `
                <span class="badge-pill badge-unavailable">
                    <i class="fas fa-times-circle"></i>
                    Not Available
                </span>
            `;
        }
        
        badgesHTML += `
            <span class="badge-pill badge-loan">
                <i class="fas fa-calendar-alt"></i>
                ${loanPeriod} Days Loan
            </span>
        `;
        
        badgesEl.innerHTML = badgesHTML;
        
        // Set book details
        document.getElementById('modalBookCategory').textContent = book.category_name || 'Uncategorized';
        document.getElementById('modalBookISBN').textContent = book.isbn || 'N/A';
        
        // Set action section based on user type
        const actionSection = document.getElementById('modalActionSection');
        
        if (currentUser && currentUser.user_type === 'patron') {
            if (book.available_quantity > 0) {
                actionSection.innerHTML = `
                    <h3 class="action-title">
                        <i class="fas fa-hand-paper"></i>
                        Borrow This Book
                    </h3>
                    <button onclick="handleBorrowRequest(${book.book_id}, '${book.title.replace(/'/g, "\\'")}')" 
                            id="borrowBtn"
                            class="action-button action-button-primary">
                        <i class="fas fa-paper-plane"></i>
                        Request to Borrow
                    </button>
                    <div class="action-info">
                        <p class="action-info-title">
                            <i class="fas fa-lightbulb"></i>
                            How Borrowing Works
                        </p>
                        <ul class="action-info-list">
                            <li><strong>Submit Request:</strong> Click the button above to send your borrow request</li>
                            <li><strong>Admin Review:</strong> Library admin will review your request (usually within 24 hours)</li>
                            <li><strong>Get Notified:</strong> You'll receive notification once approved</li>
                            <li><strong>Loan Period:</strong> You can keep the book for <strong>${loanPeriod} days</strong></li>
                            <li><strong>Track Status:</strong> Check your dashboard anytime to see request status</li>
                        </ul>
                    </div>
                `;
            } else {
                actionSection.innerHTML = `
                    <h3 class="action-title" style="color: #991b1b;">
                        <i class="fas fa-exclamation-triangle"></i>
                        Currently Unavailable
                    </h3>
                    <button class="action-button action-button-disabled" disabled>
                        <i class="fas fa-ban"></i>
                        Not Available
                    </button>
                    <div class="action-info" style="border-left-color: #ef4444; background: #fef2f2;">
                        <p class="action-info-title" style="color: #991b1b;">
                            <i class="fas fa-info-circle"></i>
                            All Copies Borrowed
                        </p>
                        <p style="margin: 0; color: #7f1d1d; font-size: 15px; line-height: 1.8;">
                            All copies of this book are currently borrowed. Please check back later or browse similar books in the <strong>${book.category_name || 'same'}</strong> category.
                        </p>
                    </div>
                `;
            }
        } else if (currentUser && currentUser.user_type === 'admin') {
            actionSection.innerHTML = `
                <h3 class="action-title">
                    <i class="fas fa-user-shield"></i>
                    Admin Actions
                </h3>
                <a href="${baseUrl}/public/admin/loans.php" class="action-button action-button-primary">
                    <i class="fas fa-cog"></i>
                    Manage in Admin Panel
                </a>
                <div class="action-info">
                    <p class="action-info-title">
                        <i class="fas fa-info-circle"></i>
                        Admin Note
                    </p>
                    <p style="margin: 0; color: #475569; font-size: 15px;">
                        As an administrator, you can manage all book loans, approve pending requests, and handle returns through the admin panel.
                    </p>
                </div>
            `;
        } else {
            actionSection.innerHTML = `
                <h3 class="action-title">
                    <i class="fas fa-sign-in-alt"></i>
                    Login Required
                </h3>
                <a href="${baseUrl}/public/login.php" class="action-button action-button-primary">
                    <i class="fas fa-user-circle"></i>
                    Login to Borrow Books
                </a>
                <div class="action-info">
                    <p class="action-info-title">
                        <i class="fas fa-info-circle"></i>
                        Join Our Library
                    </p>
                    <p style="margin: 0; color: #475569; font-size: 15px; line-height: 1.8;">
                        Create a free account to start borrowing books from our library. Enjoy access to thousands of books with a ${loanPeriod}-day loan period for each book.
                    </p>
                </div>
            `;
        }
        
        // Show modal
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        // Scroll to top
        setTimeout(() => {
            modal.scrollTop = 0;
        }, 50);
    }
    
    function handleBorrowRequest(bookId, bookTitle) {
        const btn = document.getElementById('borrowBtn');
        if (!btn) return;
        
        // Confirm with user
        if (!confirm(`Request to borrow "${bookTitle}"?\n\nLoan Period: ${loanPeriod} days\n\nYour request will be sent to the admin for approval.`)) {
            return;
        }
        
        // Disable button and show loading
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting Request...';
        btn.style.opacity = '0.7';
        
        // Call the existing requestBorrow function
        requestBorrow(bookId, bookTitle);
    }

    function closeBookDetail() {
        const modal = document.getElementById('bookDetailModal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('bookDetailModal');
        if (event.target === modal) {
            closeBookDetail();
        }
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeBookDetail();
        }
    });

    document.querySelector('.mobile-menu-toggle')?.addEventListener('click', function() {
        document.querySelector('.nav-links').classList.toggle('active');
    });
    </script>
</body>
</html>
