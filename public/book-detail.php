<?php
require_once __DIR__ . '/../src/services/AuthService.php';
require_once __DIR__ . '/../src/helpers/DatabaseHelper.php';
require_once __DIR__ . '/../config/constants.php';

$currentUser = AuthService::getCurrentUser();
$pdo = DatabaseHelper::getConnection();

// Get book ID from URL
$bookId = $_GET['id'] ?? null;

if (!$bookId) {
    header('Location: ' . BASE_URL . '/public/browse-new.php');
    exit;
}

// Get book details
$sql = "SELECT b.*, 
        CONCAT(a.first_name, ' ', a.last_name) as author_name,
        c.name as category_name,
        a.biography as author_bio
        FROM books b
        LEFT JOIN book_authors ba ON b.book_id = ba.book_id
        LEFT JOIN authors a ON ba.author_id = a.author_id
        LEFT JOIN categories c ON b.category_id = c.category_id
        WHERE b.book_id = :book_id
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
$stmt->execute();
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    header('Location: ' . BASE_URL . '/public/browse-new.php');
    exit;
}

// Check if user has active loan for this book
$userLoanStatus = null;
if ($currentUser) {
    $loanSql = "SELECT status, due_date, loan_date FROM loans 
                WHERE user_id = :user_id AND book_id = :book_id 
                AND status IN ('pending', 'borrowed')
                ORDER BY created_at DESC LIMIT 1";
    $loanStmt = $pdo->prepare($loanSql);
    $loanStmt->execute([':user_id' => $currentUser['user_id'], ':book_id' => $bookId]);
    $userLoanStatus = $loanStmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title']); ?> - KHLIBRARY</title>
    <link rel="icon" type="image/svg+xml" href="https://s3.ca-central-1.amazonaws.com/logojoy/logos/231703335/symbol.svg?1537014.9000000358">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/navbar-unified.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
        }
        
        .btn-secondary {
            background: #eeeeee;
            color: #111111;
        }
        
        .btn-secondary:hover {
            background: #dddddd;
        }
        
        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 30px;
        }
        
        /* Back Button */
        .back-button {
            margin-bottom: 30px;
        }
        
        /* Book Detail Layout */
        .book-detail {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 0;
        }
        
        .book-cover-section {
            background: linear-gradient(135deg, #ff5722 0%, #ee3900 100%);
            padding: 50px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        .book-cover-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 1;
        }
        
        .book-cover-large {
            width: 100%;
            max-width: 300px;
            height: auto;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            border: 5px solid rgba(255,255,255,0.2);
            position: relative;
            z-index: 1;
            margin-bottom: 30px;
        }
        
        .availability-status {
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            position: relative;
            z-index: 1;
        }
        
        .availability-status.available {
            background: rgba(16, 185, 129, 0.95);
            color: white;
        }
        
        .availability-status.unavailable {
            background: rgba(239, 68, 68, 0.95);
            color: white;
        }
        
        .book-info-section {
            padding: 50px;
        }
        
        .book-title-main {
            font-size: 36px;
            font-weight: 800;
            color: #111111;
            margin-bottom: 15px;
            line-height: 1.2;
        }
        
        .book-author-main {
            font-size: 20px;
            color: #666;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .book-author-main i {
            color: #ff5722;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 2px solid #eeeeee;
        }
        
        .info-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #ff5722;
        }
        
        .info-label {
            font-size: 12px;
            color: #999;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .info-value {
            font-size: 18px;
            color: #111111;
            font-weight: 700;
        }
        
        .description-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: #111111;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            color: #ff5722;
        }
        
        .description-text {
            font-size: 16px;
            line-height: 1.8;
            color: #666;
        }
        
        .action-section {
            background: linear-gradient(135deg, #ff5722 0%, #ee3900 100%);
            padding: 30px;
            border-radius: 16px;
            color: white;
        }
        
        .action-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .action-button {
            width: 100%;
            padding: 16px 30px;
            font-size: 18px;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .action-button-white {
            background: white;
            color: #ff5722;
        }
        
        .action-button-white:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .action-button-disabled {
            background: rgba(255,255,255,0.3);
            color: rgba(255,255,255,0.7);
            cursor: not-allowed;
        }
        
        .action-info {
            margin-top: 20px;
            padding: 20px;
            background: rgba(255,255,255,0.15);
            border-radius: 12px;
            font-size: 14px;
            line-height: 1.8;
        }
        
        .status-alert {
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 600;
        }
        
        .status-alert.pending {
            background: #fef3c7;
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }
        
        .status-alert.borrowed {
            background: #dbeafe;
            color: #1e40af;
            border-left: 4px solid #3b82f6;
        }
        
        .status-alert i {
            font-size: 24px;
        }
        
        /* Responsive */
        @media (max-width: 968px) {
            .book-detail {
                grid-template-columns: 1fr;
            }
            
            .book-cover-section {
                padding: 40px 30px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
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
                    <img src="https://s3.ca-central-1.amazonaws.com/logojoy/logos/231703335/symbol.svg?1537014.9000000358" alt="KHLIBRARY" class="brand-logo">
                    <span>KHLIBRARY</span>
                </div>
                <div class="nav-links">
                    <a href="<?php echo BASE_URL; ?>/public/browse.php" class="nav-link">
                        <i class="fas fa-book"></i> Browse Books
                    </a>
                    <?php if ($currentUser): ?>
                        <?php if ($currentUser['user_type'] === 'admin'): ?>
                            <a href="<?php echo BASE_URL; ?>/public/admin/index.php" class="nav-link">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>/public/user/profile.php" class="nav-link">
                                <i class="fas fa-user"></i> User Profile
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

    <!-- Main Content -->
    <div class="container">
        <!-- Back Button -->
        <div class="back-button">
            <a href="<?php echo BASE_URL; ?>/public/browse.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Browse
            </a>
        </div>

        <!-- Book Detail -->
        <div class="book-detail">
            <!-- Left: Book Cover -->
            <div class="book-cover-section">
                <img src="<?php echo htmlspecialchars($book['cover_image'] ?: BASE_URL . '/public/assets/images/book-placeholder.jpg'); ?>" 
                     alt="<?php echo htmlspecialchars($book['title']); ?>" 
                     class="book-cover-large">
                <div class="availability-status <?php echo $book['available_quantity'] > 0 ? 'available' : 'unavailable'; ?>">
                    <i class="fas fa-circle"></i>
                    <?php echo $book['available_quantity']; ?> Copies Available
                </div>
            </div>

            <!-- Right: Book Information -->
            <div class="book-info-section">
                <h1 class="book-title-main"><?php echo htmlspecialchars($book['title']); ?></h1>
                <p class="book-author-main">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($book['author_name'] ?: 'Unknown Author'); ?>
                </p>

                <!-- User Loan Status Alert -->
                <?php if ($userLoanStatus): ?>
                    <div class="status-alert <?php echo $userLoanStatus['status']; ?>">
                        <i class="fas fa-<?php echo $userLoanStatus['status'] === 'pending' ? 'clock' : 'book-reader'; ?>"></i>
                        <div>
                            <?php if ($userLoanStatus['status'] === 'pending'): ?>
                                <strong>Request Pending</strong> - Your borrow request is awaiting admin approval
                            <?php else: ?>
                                <strong>Currently Borrowed</strong> - Due date: <?php echo date('M d, Y', strtotime($userLoanStatus['due_date'])); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Book Info Grid -->
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-tag"></i>
                            Category
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($book['category_name'] ?: 'Uncategorized'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-barcode"></i>
                            ISBN
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($book['isbn'] ?: 'N/A'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-building"></i>
                            Publisher
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($book['publisher'] ?: 'N/A'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-calendar"></i>
                            Year
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($book['publication_year'] ?: 'N/A'); ?></div>
                    </div>
                </div>

                <!-- Description -->
                <?php if ($book['description']): ?>
                    <div class="description-section">
                        <h2 class="section-title">
                            <i class="fas fa-align-left"></i>
                            Description
                        </h2>
                        <p class="description-text"><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Action Section -->
                <div class="action-section">
                    <?php if ($currentUser && $currentUser['user_type'] === 'patron'): ?>
                        <?php if ($userLoanStatus): ?>
                            <div class="action-title">
                                <i class="fas fa-check-circle"></i>
                                Already Requested
                            </div>
                            <button class="action-button action-button-disabled" disabled>
                                <i class="fas fa-ban"></i>
                                Request Already Submitted
                            </button>
                            <div class="action-info">
                                You have already requested this book. Check your dashboard to track the status of your request.
                            </div>
                        <?php elseif ($book['available_quantity'] > 0): ?>
                            <div class="action-title">
                                <i class="fas fa-hand-paper"></i>
                                Borrow This Book
                            </div>
                            <button onclick="requestBorrow()" class="action-button action-button-white">
                                <i class="fas fa-paper-plane"></i>
                                Request to Borrow
                            </button>
                            <div class="action-info">
                                <strong>Loan Period:</strong> <?php echo DEFAULT_LOAN_PERIOD; ?> days<br>
                                Your request will be reviewed by the library admin. You'll be notified once approved.
                            </div>
                        <?php else: ?>
                            <div class="action-title">
                                <i class="fas fa-exclamation-triangle"></i>
                                Currently Unavailable
                            </div>
                            <button class="action-button action-button-disabled" disabled>
                                <i class="fas fa-ban"></i>
                                Not Available
                            </button>
                            <div class="action-info">
                                All copies are currently borrowed. Please check back later.
                            </div>
                        <?php endif; ?>
                    <?php elseif ($currentUser && $currentUser['user_type'] === 'admin'): ?>
                        <div class="action-title">
                            <i class="fas fa-user-shield"></i>
                            Admin Actions
                        </div>
                        <a href="<?php echo BASE_URL; ?>/public/admin/loans.php" class="action-button action-button-white">
                            <i class="fas fa-cog"></i>
                            Manage in Admin Panel
                        </a>
                    <?php else: ?>
                        <div class="action-title">
                            <i class="fas fa-sign-in-alt"></i>
                            Login Required
                        </div>
                        <a href="<?php echo BASE_URL; ?>/public/login.php" class="action-button action-button-white">
                            <i class="fas fa-user-circle"></i>
                            Login to Borrow
                        </a>
                        <div class="action-info">
                            Create a free account to start borrowing books from our library.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                fetch('<?php echo BASE_URL; ?>/api/auth/logout', {
                    method: 'POST',
                    credentials: 'same-origin'
                }).then(() => {
                    window.location.href = '<?php echo BASE_URL; ?>/public/browse.php';
                });
            }
        }

        function requestBorrow() {
            if (!confirm('Request to borrow "<?php echo addslashes($book['title']); ?>"?\n\nLoan Period: <?php echo DEFAULT_LOAN_PERIOD; ?> days\n\nYour request will be sent to the admin for approval.')) {
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
                    book_id: <?php echo $bookId; ?>
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✓ Request Submitted Successfully!\n\nYour borrow request has been sent to the library admin.\nYou will be notified once it is approved.');
                    window.location.reload();
                } else {
                    throw new Error(data.error?.message || 'Failed to submit request');
                }
            })
            .catch(error => {
                alert('❌ Request Failed\n\n' + error.message);
            });
        }
    </script>
</body>
</html>
