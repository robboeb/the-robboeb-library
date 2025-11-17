<?php
require_once __DIR__ . '/../../src/services/AuthService.php';
require_once __DIR__ . '/../../src/helpers/DatabaseHelper.php';

header('Content-Type: application/json');

// Require admin authentication
AuthService::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => ['message' => 'Method not allowed']]);
    exit;
}

try {
    $pdo = DatabaseHelper::getConnection();
    
    // Get total books
    $booksStmt = $pdo->query("SELECT COUNT(*) as count FROM books");
    $totalBooks = $booksStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get total users
    $usersStmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'patron'");
    $totalUsers = $usersStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get active loans
    $activeLoansStmt = $pdo->query("SELECT COUNT(*) as count FROM loans WHERE status = 'active'");
    $activeLoans = $activeLoansStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get overdue loans
    $overdueStmt = $pdo->query("SELECT COUNT(*) as count FROM loans WHERE status = 'active' AND due_date < CURDATE()");
    $overdueLoans = $overdueStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get pending requests
    $pendingStmt = $pdo->query("SELECT COUNT(*) as count FROM loans WHERE status = 'pending'");
    $pendingRequests = $pendingStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get returned today
    $returnedTodayStmt = $pdo->query("SELECT COUNT(*) as count FROM loans WHERE status = 'returned' AND DATE(return_date) = CURDATE()");
    $returnedToday = $returnedTodayStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo json_encode([
        'success' => true,
        'data' => [
            'total_books' => (int)$totalBooks,
            'total_users' => (int)$totalUsers,
            'active_loans' => (int)$activeLoans,
            'overdue_loans' => (int)$overdueLoans,
            'pending_requests' => (int)$pendingRequests,
            'returned_today' => (int)$returnedToday
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => ['message' => 'Failed to fetch dashboard data: ' . $e->getMessage()]
    ]);
}
