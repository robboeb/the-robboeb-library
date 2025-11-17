<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../src/helpers/DatabaseHelper.php';
require_once __DIR__ . '/../src/services/AuthService.php';

AuthService::initSession();
$isAuthenticated = AuthService::isAuthenticated();
$currentUser = $isAuthenticated ? AuthService::getCurrentUser() : null;

// Get book ID from URL
$bookId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$bookId) {
    header('Location: ' . BASE_URL . '/public/browse.php');
    exit;
}

// Get book details with author and category
$pdo = DatabaseHelper::getConnection();
$sql = "SELECT b.*, 
        GROUP_CONCAT(CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ') as authors,
        c.name as category_name
        FROM books b
        LEFT JOIN book_authors ba ON b.book_id = ba.book_id
        LEFT JOIN authors a ON ba.author_id = a.author_id
        LEFT JOIN categories c ON b.category_id = c.category_id
        WHERE b.book_id = :book_id
        GROUP BY b.book_id";

$stmt = $pdo->prepare($sql);
$stmt->execute([':book_id' => $bookId]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    header('Location: ' . BASE_URL . '/public/browse.php');
    exit;
}

// Check if user has pending or active loan for this book
$userLoanStatus = null;
if ($currentUser) {
    $loanSql = "SELECT status, due_date, checkout_date FROM loans 
                WHERE user_id = :user_id AND book_id = :book_id 
                AND status IN ('pending', 'active')
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
    <title><?php echo htmlspecialchars($book['title']); ?> - KH LIBRARY</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .book-detail-hero { background: linear-gradient(135deg, #fff3f0 0%, #ffe5de 100%); min-height: 100vh; padding: 80px 20px 60px; }
        .detail-container { max-width: 1200px; margin: 0 auto; }
        .detail-grid { display: grid; grid-template-columns: 380px 1fr; gap: 50px; align-items: start; }
        .book-cover-section { position: sticky; top: 100px; }
        .book-cover-card { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        .book-cover-img { width: 100%; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.15); margin-bottom: 20px; }
        .book-info-section { background: white; border-radius: 20px; padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        .book-title { font-size: 36px; font-weight: 700; color: #111111; margin: 0 0 16px 0; line-height: 1.2; }
        .book-author { font-size: 20px; color: #616161; margin: 0 0 24px 0; display: flex; align-items: center; gap: 10px; }
        .book-author i { color: #ff5722; }
        .badge-group { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 30px; }
        .badge { padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
        .badge-available { background: #d1fae5; color: #065f46; }
        .badge-unavailable { background: #fee2e2; color: #991b1b; }
        .badge-category { background: #fff3f0; color: #ff5722; border: 2px solid #ff5722; }
        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin: 30px 0; }
        .info-item { padding: 16px; background: #fafafa; border-radius: 12px; border-left: 4px solid #ff5722; }
        .info-label { font-size: 12px; color: #9e9e9e; font-weight: 600; text-transform: uppercase; margin-bottom: 6px; }
        .info-value { font-size: 16px; color: #111111; font-weight: 600; }
        .description-section { margin: 30px 0; padding: 24px; background: #f5f5f5; border-radius: 12px; }
        .section-title { font-size: 20px; font-weight: 700; color: #111111; margin: 0 0 16px 0; display: flex; align-items: center; gap: 10px; }
        .section-title i { color: #ff5722; }
        .action-section { margin-top: 40px; padding-top: 30px; border-top: 2px solid #eeeeee; }
        .btn-borrow { width: 100%; padding: 18px; font-size: 18px; font-weight: 700; background: linear-gradient(135deg, #ff5722 0%, #ee3900 100%); color: white; border: none; border-radius: 12px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 16px rgba(255, 87, 34, 0.3); }
        .btn-borrow:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(255, 87, 34, 0.4); }
        .btn-borrow:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .status-alert { padding: 16px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; font-weight: 600; }
        .status-pending { background: #fef3c7; color: #92400e; border: 2px solid #fbbf24; }
        .status-active { background: #dbeafe; color: #1e40af; border: 2px solid #3b82f6; }
        .login-prompt { background: linear-gradient(135deg, #fff3f0 0%, #ffe5de 100%); padding: 24px; border-radius: 12px; text-align: center; border: 2px dashed #ff5722; }
        @media (max-width: 968px) {
            .detail-grid { grid-template-columns: 1fr; gap: 30px; }
            .book-cover-section { position: relative; top: 0; }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="public-nav">
        <div class="container">
            <div class="nav-content">
                <div class="nav-brand">
                    <img src="https://s3.ca-central-1.amazonaws.com/logojoy/logos/231703335/symbol.svg?1537014.9000000358" alt="KH LIBRARY" class="brand-logo">
                    <span>KH LIBRARY</span>
                </div>
                <div class="nav-links">
                    <a href="<?php echo BASE_URL; ?>/public/home.php" class="nav-link">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <a href="<?php echo BASE_URL; ?>/public/browse.php" class="nav-link">
                        <i class="fas fa-book"></i> Browse Books
                    </a>
                    <?php if ($isAuthenticated): ?>
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

    <!-- Book Details Hero -->
    <div class="book-detail-hero">
        <div class="detail-container">
            <a href="<?php echo BASE_URL; ?>/public/browse.php" style="display: inline-flex; align-items: center; gap: 8px; color: #ff5722; text-decoration: none; font-weight: 600; margin-bottom: 30px; padding: 10px 20px; background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <i class="fas fa-arrow-left"></i> Back to Browse
            </a>

            <div class="detail-grid">
                <!-- Book Cover Section -->
                <div class="book-cover-section">
                    <div class="book-cover-card">
                        <?php if (!empty($book['cover_image'])): ?>
                            <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($book['title']); ?>"
                                 class="book-cover-img"
                                 onerror="this.src='<?php echo BASE_URL; ?>/public/assets/images/book-placeholder.jpg'">
                        <?php else: ?>
                            <div style="width: 100%; aspect-ratio: 2/3; background: linear-gradient(135deg, #ff5722 0%, #ee3900 100%); border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: white; text-align: center; padding: 30px;">
                                <i class="fas fa-book" style="font-size: 60px; margin-bottom: 16px; opacity: 0.9;"></i>
                                <span style="font-size: 16px; font-weight: 600;"><?php echo htmlspecialchars($book['title']); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div style="text-align: center; padding: 20px 0;">
                            <div class="badge-group" style="justify-content: center;">
                                <?php if ($book['available_quantity'] > 0): ?>
                                    <span class="badge badge-available">
                                        <i class="fas fa-check-circle"></i>
                                        <?php echo $book['available_quantity']; ?> Available
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-unavailable">
                                        <i class="fas fa-times-circle"></i>
                                        Not Available
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Book Information Section -->
                <div class="book-info-section">
                    <h1 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h1>
                    
                    <div class="book-author">
                        <i class="fas fa-user-edit"></i>
                        <span><?php echo htmlspecialchars($book['authors'] ?: 'Unknown Author'); ?></span>
                    </div>

                    <div class="badge-group">
                        <span class="badge badge-category">
                            <i class="fas fa-tag"></i>
                            <?php echo htmlspecialchars($book['category_name'] ?: 'Uncategorized'); ?>
                        </span>
                        <span class="badge" style="background: #e0f2fe; color: #075985;">
                            <i class="fas fa-calendar-alt"></i>
                            <?php echo DEFAULT_LOAN_PERIOD; ?> Days Loan
                        </span>
                    </div>

                    <!-- Book Details Grid -->
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">ISBN</div>
                            <div class="info-value"><?php echo htmlspecialchars($book['isbn'] ?: 'N/A'); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Publisher</div>
                            <div class="info-value"><?php echo htmlspecialchars($book['publisher'] ?: 'N/A'); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Publication Year</div>
                            <div class="info-value"><?php echo htmlspecialchars($book['publication_year'] ?: 'N/A'); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Total Copies</div>
                            <div class="info-value"><?php echo $book['total_quantity']; ?> Copies</div>
                        </div>
                    </div>

                    <!-- Description -->
                    <?php if (!empty($book['description'])): ?>
                    <div class="description-section">
                        <h3 class="section-title">
                            <i class="fas fa-align-left"></i>
                            Description
                        </h3>
                        <p style="color: #616161; line-height: 1.8; margin: 0;">
                            <?php echo nl2br(htmlspecialchars($book['description'])); ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <!-- Action Section -->
                    <div class="action-section">
                        <?php if ($currentUser && $currentUser['user_type'] === 'patron'): ?>
                            <?php if ($userLoanStatus): ?>
                                <?php if ($userLoanStatus['status'] === 'pending'): ?>
                                    <div class="status-alert status-pending">
                                        <i class="fas fa-clock" style="font-size: 24px;"></i>
                                        <div>
                                            <div>Request Pending Approval</div>
                                            <div style="font-size: 13px; font-weight: 400; margin-top: 4px;">Your borrowing request is awaiting admin approval</div>
                                        </div>
                                    </div>
                                <?php elseif ($userLoanStatus['status'] === 'active'): ?>
                                    <div class="status-alert status-active">
                                        <i class="fas fa-book-reader" style="font-size: 24px;"></i>
                                        <div>
                                            <div>Currently Borrowed</div>
                                            <div style="font-size: 13px; font-weight: 400; margin-top: 4px;">
                                                Due: <?php echo date('F d, Y', strtotime($userLoanStatus['due_date'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if ($book['available_quantity'] > 0): ?>
                                    <button onclick="requestBorrow()" class="btn-borrow" id="borrowBtn">
                                        <i class="fas fa-hand-paper"></i> Request to Borrow This Book
                                    </button>
                                <?php else: ?>
                                    <button class="btn-borrow" disabled>
                                        <i class="fas fa-times-circle"></i> Currently Unavailable
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php elseif ($currentUser && $currentUser['user_type'] === 'admin'): ?>
                            <a href="<?php echo BASE_URL; ?>/public/admin/loans.php" class="btn-borrow" style="display: block; text-align: center; text-decoration: none;">
                                <i class="fas fa-cog"></i> Manage in Admin Panel
                            </a>
                        <?php else: ?>
                            <div class="login-prompt">
                                <i class="fas fa-info-circle" style="font-size: 32px; color: #ff5722; margin-bottom: 12px;"></i>
                                <h3 style="margin: 0 0 8px 0; color: #111111; font-size: 18px;">Login Required to Borrow</h3>
                                <p style="margin: 0 0 20px 0; color: #616161; font-size: 14px;">
                                    You need an account to borrow books. Contact the admin office to create an account.
                                </p>
                                <a href="<?php echo BASE_URL; ?>/public/login.php" class="btn-borrow" style="display: block; text-decoration: none; text-align: center;">
                                    <i class="fas fa-sign-in-alt"></i> Login to Continue
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>/public/assets/js/api.js"></script>
    <script>
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                fetch('<?php echo BASE_URL; ?>/api/auth/logout', {
                    method: 'POST',
                    credentials: 'same-origin'
                }).then(() => {
                    window.location.href = '<?php echo BASE_URL; ?>/public/login.php?logout=1';
                });
            }
        }

        async function requestBorrow() {
            const btn = document.getElementById('borrowBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

            try {
                const response = await fetch('<?php echo BASE_URL; ?>/api/loans/request', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ book_id: <?php echo $bookId; ?> })
                });

                const data = await response.json();

                if (data.success) {
                    alert('✓ Borrow request submitted successfully!\n\nYour request is pending admin approval. You will be notified once approved.');
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.error?.message || 'Failed to submit request'));
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-hand-paper"></i> Request to Borrow This Book';
                }
            } catch (error) {
                alert('Error: Failed to submit request. Please try again.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-hand-paper"></i> Request to Borrow This Book';
            }
        }

        // Mobile menu toggle
        document.querySelector('.mobile-menu-toggle')?.addEventListener('click', function() {
            document.querySelector('.nav-links').classList.toggle('active');
        });
    </script>
</body>
</html>
