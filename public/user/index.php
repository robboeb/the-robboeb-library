<?php
require_once __DIR__ . '/../../src/services/AuthService.php';
require_once __DIR__ . '/../../src/helpers/DatabaseHelper.php';
require_once __DIR__ . '/../../config/constants.php';

AuthService::requireAuth();
$currentUser = AuthService::getCurrentUser();

// Get user's active loans
$user_id = $currentUser['user_id'];
$pdo = DatabaseHelper::getConnection();
$loan_sql = "SELECT l.*, b.title, b.isbn, b.cover_image,
             CONCAT(a.first_name, ' ', a.last_name) as author_name,
             DATEDIFF(l.due_date, CURDATE()) as days_until_due
             FROM loans l
             JOIN books b ON l.book_id = b.book_id
             LEFT JOIN book_authors ba ON b.book_id = ba.book_id
             LEFT JOIN authors a ON ba.author_id = a.author_id
             WHERE l.user_id = :user_id AND l.status = 'active'
             ORDER BY l.due_date ASC";
$loan_stmt = $pdo->prepare($loan_sql);
$loan_stmt->execute([':user_id' => $user_id]);
$active_loans = $loan_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get pending requests
$pending_sql = "SELECT l.*, b.title, b.isbn, b.cover_image,
                CONCAT(a.first_name, ' ', a.last_name) as author_name
                FROM loans l
                JOIN books b ON l.book_id = b.book_id
                LEFT JOIN book_authors ba ON b.book_id = ba.book_id
                LEFT JOIN authors a ON ba.author_id = a.author_id
                WHERE l.user_id = :user_id AND l.status = 'pending'
                ORDER BY l.created_at DESC";
$pending_stmt = $pdo->prepare($pending_sql);
$pending_stmt->execute([':user_id' => $user_id]);
$pending_requests = $pending_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get returned books history (last 10)
$returned_sql = "SELECT l.*, b.title, b.isbn, b.cover_image,
                 CONCAT(a.first_name, ' ', a.last_name) as author_name,
                 DATEDIFF(l.return_date, l.checkout_date) as days_borrowed
                 FROM loans l
                 JOIN books b ON l.book_id = b.book_id
                 LEFT JOIN book_authors ba ON b.book_id = ba.book_id
                 LEFT JOIN authors a ON ba.author_id = a.author_id
                 WHERE l.user_id = :user_id AND l.status = 'returned'
                 ORDER BY l.return_date DESC
                 LIMIT 10";
$returned_stmt = $pdo->prepare($returned_sql);
$returned_stmt->execute([':user_id' => $user_id]);
$returned_books = $returned_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate stats
$overdue = 0;
$due_soon = 0;
foreach ($active_loans as $loan) {
    if ($loan['days_until_due'] < 0) $overdue++;
    if ($loan['days_until_due'] >= 0 && $loan['days_until_due'] <= 3) $due_soon++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - KH LIBRARY</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/main.css">
    <style>
        .btn-return:hover {
            background: #667eea !important;
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        .btn-return:active {
            transform: translateY(0);
        }
        .btn-return:disabled {
            opacity: 0.6;
            cursor: not-allowed;
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
                    <a href="<?php echo BASE_URL; ?>/public/user/index.php" class="nav-link active">
                        <i class="fas fa-user"></i> My Profile
                    </a>
                    <button onclick="logout()" class="btn btn-outline" style="margin-left: var(--space-2);">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </div>
                <button class="mobile-menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Profile Content -->
    <section class="dashboard-section" style="padding: 40px 0; background: linear-gradient(135deg, #fff3f0 0%, #ffe5de 100%); min-height: calc(100vh - 80px);">
        <div class="container" style="max-width: 1200px;">
            
            <!-- Profile Header -->
            <div style="background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(255, 87, 34, 0.15); margin-bottom: 40px; border: 2px solid #ffccc2;">
                <div style="display: flex; align-items: center; gap: 30px; flex-wrap: wrap;">
                    <div style="width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, #ff5722 0%, #ee3900 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 56px; font-weight: bold; flex-shrink: 0; box-shadow: 0 8px 20px rgba(255, 87, 34, 0.3);">
                        <?php echo strtoupper(substr($currentUser['first_name'], 0, 1)); ?>
                    </div>
                    <div style="flex: 1; min-width: 250px;">
                        <h1 style="margin: 0 0 12px 0; font-size: 36px; color: #111111; font-weight: 700;">
                            <?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?>
                        </h1>
                        <p style="color: #616161; font-size: 17px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-envelope" style="color: #ff5722;"></i> 
                            <?php echo htmlspecialchars($currentUser['email']); ?>
                        </p>
                        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                            <span style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 18px; background: linear-gradient(135deg, #ff5722 0%, #ee3900 100%); color: white; border-radius: 25px; font-size: 14px; font-weight: 600; box-shadow: 0 4px 12px rgba(255, 87, 34, 0.3);">
                                <i class="fas fa-user"></i> Library Member
                            </span>
                            <span style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 18px; background: #d1fae5; color: #065f46; border-radius: 25px; font-size: 14px; font-weight: 600;">
                                <i class="fas fa-check-circle"></i> Active Account
                            </span>
                        </div>
                    </div>
                    <div>
                        <a href="<?php echo BASE_URL; ?>/public/user/profile.php" class="btn" style="background: linear-gradient(135deg, #ff5722 0%, #ee3900 100%); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; box-shadow: 0 4px 12px rgba(255, 87, 34, 0.3); transition: all 0.3s ease;">
                            <i class="fas fa-user-edit"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; margin-bottom: 40px;">
                <div style="background: white; padding: 30px; border-radius: 16px; border-left: 5px solid #ff5722; text-align: center; box-shadow: 0 4px 16px rgba(0,0,0,0.08); transition: transform 0.3s ease;">
                    <div style="width: 70px; height: 70px; margin: 0 auto 16px; background: linear-gradient(135deg, #fff3f0 0%, #ffe5de 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-book-reader" style="font-size: 32px; color: #ff5722;"></i>
                    </div>
                    <h3 style="font-size: 32px; margin: 10px 0 5px 0; color: #111111; font-weight: 700;"><?php echo count($active_loans); ?></h3>
                    <p style="color: #616161; font-size: 15px; margin: 0; font-weight: 500;">Currently Borrowed</p>
                </div>
                <div style="background: white; padding: 30px; border-radius: 16px; border-left: 5px solid #ff7e55; text-align: center; box-shadow: 0 4px 16px rgba(0,0,0,0.08); transition: transform 0.3s ease;">
                    <div style="width: 70px; height: 70px; margin: 0 auto 16px; background: linear-gradient(135deg, #fff3f0 0%, #ffe5de 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-hourglass-half" style="font-size: 32px; color: #ff7e55;"></i>
                    </div>
                    <h3 style="font-size: 32px; margin: 10px 0 5px 0; color: #111111; font-weight: 700;"><?php echo count($pending_requests); ?></h3>
                    <p style="color: #616161; font-size: 15px; margin: 0; font-weight: 500;">Pending Requests</p>
                </div>
                <div style="background: white; padding: 30px; border-radius: 16px; border-left: 5px solid #ef4444; text-align: center; box-shadow: 0 4px 16px rgba(0,0,0,0.08); transition: transform 0.3s ease;">
                    <div style="width: 70px; height: 70px; margin: 0 auto 16px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 32px; color: #ef4444;"></i>
                    </div>
                    <h3 style="font-size: 32px; margin: 10px 0 5px 0; color: #111111; font-weight: 700;"><?php echo $overdue; ?></h3>
                    <p style="color: #616161; font-size: 15px; margin: 0; font-weight: 500;">Overdue Books</p>
                </div>
                <div style="background: white; padding: 30px; border-radius: 16px; border-left: 5px solid #f59e0b; text-align: center; box-shadow: 0 4px 16px rgba(0,0,0,0.08); transition: transform 0.3s ease;">
                    <div style="width: 70px; height: 70px; margin: 0 auto 16px; background: #fef3c7; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-clock" style="font-size: 32px; color: #f59e0b;"></i>
                    </div>
                    <h3 style="font-size: 32px; margin: 10px 0 5px 0; color: #111111; font-weight: 700;"><?php echo $due_soon; ?></h3>
                    <p style="color: #616161; font-size: 15px; margin: 0; font-weight: 500;">Due Soon (3 days)</p>
                </div>
            </div>

            <!-- Pending Requests -->
            <?php if (!empty($pending_requests)): ?>
                <div style="background: white; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 30px;">
                    <h2 style="margin: 0 0 25px 0; font-size: 24px; color: #2d3748; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-hourglass-half"></i> Pending Requests
                    </h2>
                    
                    <div style="display: grid; gap: 20px;">
                        <?php foreach ($pending_requests as $request): ?>
                            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 20px; border-left: 4px solid #3b82f6;">
                                <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 10px;">
                                    <div style="flex: 1; min-width: 200px;">
                                        <h3 style="margin: 0 0 8px 0; font-size: 20px; color: #2d3748; font-weight: 600;"><?php echo htmlspecialchars($request['title']); ?></h3>
                                        <p style="margin: 0; color: #718096; font-size: 14px;">
                                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($request['author_name'] ?: 'Unknown Author'); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; background: #dbeafe; color: #1e40af;">
                                            <i class="fas fa-clock"></i> PENDING APPROVAL
                                        </span>
                                    </div>
                                </div>
                                
                                <div style="margin-top: 15px; padding: 12px; background: white; border-radius: 8px;">
                                    <p style="margin: 0 0 8px 0; color: #3b82f6; font-size: 14px; font-weight: 500;">
                                        <i class="fas fa-info-circle"></i> Your request is waiting for admin approval.
                                    </p>
                                    <p style="margin: 0; color: #718096; font-size: 13px;">
                                        <i class="fas fa-calendar-alt"></i> Loan period: <strong><?php echo DEFAULT_LOAN_PERIOD; ?> days</strong> (will be set upon approval)
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- My Borrowed Books -->
            <?php if (!empty($active_loans)): ?>
                <div style="background: white; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                    <h2 style="margin: 0 0 25px 0; font-size: 24px; color: #2d3748; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-books"></i> My Borrowed Books
                    </h2>
                    
                    <div style="display: grid; gap: 20px;">
                        <?php foreach ($active_loans as $loan): ?>
                            <div style="background: #f9fafb; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; <?php if ($loan['days_until_due'] < 0) echo 'border-left: 4px solid #ef4444;'; elseif ($loan['days_until_due'] <= 3) echo 'border-left: 4px solid #f59e0b;'; else echo 'border-left: 4px solid #10b981;'; ?>">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                                    <div style="flex: 1; min-width: 200px;">
                                        <h3 style="margin: 0 0 8px 0; font-size: 20px; color: #2d3748; font-weight: 600;"><?php echo htmlspecialchars($loan['title']); ?></h3>
                                        <p style="margin: 0; color: #718096; font-size: 14px;">
                                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($loan['author_name'] ?: 'Unknown Author'); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <?php if ($loan['days_until_due'] < 0): ?>
                                            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; background: #fee2e2; color: #991b1b;">
                                                <i class="fas fa-exclamation-circle"></i> OVERDUE
                                            </span>
                                        <?php elseif ($loan['days_until_due'] <= 3): ?>
                                            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; background: #fef3c7; color: #92400e;">
                                                <i class="fas fa-clock"></i> DUE SOON
                                            </span>
                                        <?php else: ?>
                                            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; background: #d1fae5; color: #065f46;">
                                                <i class="fas fa-check-circle"></i> ACTIVE
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; padding: 15px; background: white; border-radius: 8px;">
                                    <div>
                                        <p style="margin: 0 0 5px 0; font-size: 11px; color: #718096; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Borrowed Date</p>
                                        <p style="margin: 0; font-size: 15px; color: #2d3748; font-weight: 500;">
                                            <i class="fas fa-calendar-check" style="color: #667eea;"></i> <?php echo date('M d, Y', strtotime($loan['created_at'])); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <p style="margin: 0 0 5px 0; font-size: 11px; color: #718096; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Due Date</p>
                                        <p style="margin: 0; font-size: 15px; color: <?php echo $loan['days_until_due'] < 0 ? '#ef4444' : '#2d3748'; ?>; font-weight: 500;">
                                            <i class="fas fa-calendar-times" style="color: <?php echo $loan['days_until_due'] < 0 ? '#ef4444' : '#667eea'; ?>;"></i> <?php echo date('M d, Y', strtotime($loan['due_date'])); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <p style="margin: 0 0 5px 0; font-size: 11px; color: #718096; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Loan Period</p>
                                        <p style="margin: 0; font-size: 15px; color: #2d3748; font-weight: 500;">
                                            <i class="fas fa-calendar-alt" style="color: #667eea;"></i> <?php echo DEFAULT_LOAN_PERIOD; ?> days
                                        </p>
                                    </div>
                                    <div>
                                        <p style="margin: 0 0 5px 0; font-size: 11px; color: #718096; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Days Remaining</p>
                                        <p style="margin: 0; font-size: 15px; font-weight: 600; color: <?php echo $loan['days_until_due'] < 0 ? '#ef4444' : ($loan['days_until_due'] <= 3 ? '#f59e0b' : '#10b981'); ?>;">
                                            <i class="fas fa-hourglass-half"></i> 
                                            <?php 
                                            if ($loan['days_until_due'] < 0) {
                                                echo abs($loan['days_until_due']) . ' days overdue';
                                            } elseif ($loan['days_until_due'] == 0) {
                                                echo 'Due today!';
                                            } else {
                                                echo $loan['days_until_due'] . ' days left';
                                            }
                                            ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <?php if ($loan['days_until_due'] < 0): ?>
                                <div style="margin-top: 15px; padding: 12px; background: #fef2f2; border-radius: 8px; border: 1px solid #fecaca;">
                                    <p style="margin: 0; color: #991b1b; font-size: 14px; font-weight: 500;">
                                        <i class="fas fa-info-circle"></i> This book is overdue. Please return it as soon as possible to avoid additional fines.
                                    </p>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Return Book Button -->
                                <div style="margin-top: 15px; text-align: right;">
                                    <button onclick="returnBook(<?php echo $loan['loan_id']; ?>, '<?php echo htmlspecialchars(addslashes($loan['title'])); ?>')" 
                                            class="btn btn-outline btn-return" 
                                            style="background: white; color: #667eea; border: 2px solid #667eea; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                                        <i class="fas fa-undo"></i> Return Book
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php elseif (empty($pending_requests)): ?>
                <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <i class="fas fa-book-open" style="font-size: 64px; color: #cbd5e0; margin-bottom: 20px;"></i>
                    <h3 style="margin: 0 0 10px 0; font-size: 24px; color: #2d3748;">No Borrowed Books</h3>
                    <p style="margin: 0 0 25px 0; color: #718096; font-size: 16px;">You don't have any borrowed books at the moment</p>
                    <a href="<?php echo BASE_URL; ?>/public/browse.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-search"></i> Browse Books
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </section>

            <!-- Returned Books History -->
            <?php if (!empty($returned_books)): ?>
                <div style="background: white; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-top: 30px;">
                    <h2 style="margin: 0 0 25px 0; font-size: 24px; color: #2d3748; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-history"></i> Borrowing History
                    </h2>
                    
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f9fafb; border-bottom: 2px solid #e2e8f0;">
                                    <th style="padding: 12px; text-align: left; font-size: 13px; color: #718096; font-weight: 600; text-transform: uppercase;">Book Title</th>
                                    <th style="padding: 12px; text-align: left; font-size: 13px; color: #718096; font-weight: 600; text-transform: uppercase;">Borrowed Date</th>
                                    <th style="padding: 12px; text-align: left; font-size: 13px; color: #718096; font-weight: 600; text-transform: uppercase;">Returned Date</th>
                                    <th style="padding: 12px; text-align: center; font-size: 13px; color: #718096; font-weight: 600; text-transform: uppercase;">Days Borrowed</th>
                                    <th style="padding: 12px; text-align: center; font-size: 13px; color: #718096; font-weight: 600; text-transform: uppercase;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($returned_books as $book): ?>
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td style="padding: 16px;">
                                            <div style="font-weight: 600; color: #2d3748; margin-bottom: 4px;"><?php echo htmlspecialchars($book['title']); ?></div>
                                            <div style="font-size: 13px; color: #718096;">
                                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($book['author_name'] ?: 'Unknown'); ?>
                                            </div>
                                        </td>
                                        <td style="padding: 16px; color: #2d3748;">
                                            <?php echo date('M d, Y', strtotime($book['checkout_date'])); ?>
                                        </td>
                                        <td style="padding: 16px; color: #2d3748;">
                                            <?php echo date('M d, Y', strtotime($book['return_date'])); ?>
                                        </td>
                                        <td style="padding: 16px; text-align: center;">
                                            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; background: #e0f2fe; color: #075985;">
                                                <?php echo $book['days_borrowed']; ?> days
                                            </span>
                                        </td>
                                        <td style="padding: 16px; text-align: center;">
                                            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; background: #d1fae5; color: #065f46;">
                                                <i class="fas fa-check-circle"></i> Returned
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="public-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>
                        <img src="https://s3.ca-central-1.amazonaws.com/logojoy/logos/231703335/symbol.svg?1537014.9000000358" alt="KH LIBRARY" class="brand-logo-footer">
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
                <p>&copy; 2025 KHLIBRARY. All rights reserved. | Developed by <a href="https://t.me/eirsvi" target="_blank" style="color: #ff5722; text-decoration: none;">eirsvi.t.me</a> | <a href="https://github.com/robboeb/the-robboeb-library.git" target="_blank" style="color: #ff5722; text-decoration: none;"><i class="fab fa-github"></i> GitHub</a></p>
            </div>
        </div>
    </footer>

    <script>
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
    
    function returnBook(loanId, bookTitle) {
        if (confirm(`Are you sure you want to return "${bookTitle}"?\n\nThis action cannot be undone.`)) {
            // Disable the button to prevent double clicks
            event.target.disabled = true;
            event.target.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Returning...';
            
            fetch('<?php echo BASE_URL; ?>/api/loans/return.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ loan_id: loanId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`✓ "${data.book_title}" has been returned successfully!`);
                    // Reload the page to update the dashboard
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                    event.target.disabled = false;
                    event.target.innerHTML = '<i class="fas fa-undo"></i> Return Book';
                }
            })
            .catch(error => {
                alert('Failed to return book. Please try again.');
                console.error('Error:', error);
                event.target.disabled = false;
                event.target.innerHTML = '<i class="fas fa-undo"></i> Return Book';
            });
        }
    }
    
    document.querySelector('.mobile-menu-toggle')?.addEventListener('click', function() {
        document.querySelector('.nav-links').classList.toggle('active');
    });
    </script>
</body>
</html>
