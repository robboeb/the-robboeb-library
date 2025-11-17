<?php
require_once __DIR__ . '/../../src/services/AuthService.php';
require_once __DIR__ . '/../../src/helpers/DatabaseHelper.php';

header('Content-Type: application/json');

// Require authentication
AuthService::requireAuth();
$currentUser = AuthService::getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get loan_id from request
$data = json_decode(file_get_contents('php://input'), true);
$loan_id = $data['loan_id'] ?? null;

if (!$loan_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Loan ID is required']);
    exit;
}

try {
    $pdo = DatabaseHelper::getConnection();
    
    // Verify the loan belongs to the current user and is active
    $check_sql = "SELECT l.*, b.title 
                  FROM loans l 
                  JOIN books b ON l.book_id = b.book_id 
                  WHERE l.loan_id = :loan_id 
                  AND l.user_id = :user_id 
                  AND l.status = 'active'";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([
        ':loan_id' => $loan_id,
        ':user_id' => $currentUser['user_id']
    ]);
    $loan = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$loan) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Loan not found or already returned']);
        exit;
    }
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Update loan status to returned
    $update_loan_sql = "UPDATE loans 
                        SET status = 'returned', 
                            return_date = NOW() 
                        WHERE loan_id = :loan_id";
    $update_loan_stmt = $pdo->prepare($update_loan_sql);
    $update_loan_stmt->execute([':loan_id' => $loan_id]);
    
    // Update book availability (increment available_quantity)
    $update_book_sql = "UPDATE books 
                        SET available_quantity = available_quantity + 1 
                        WHERE book_id = :book_id";
    $update_book_stmt = $pdo->prepare($update_book_sql);
    $update_book_stmt->execute([':book_id' => $loan['book_id']]);
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Book returned successfully',
        'book_title' => $loan['title']
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to return book: ' . $e->getMessage()]);
}
