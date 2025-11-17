<?php
require_once __DIR__ . '/../../src/services/AuthService.php';
require_once __DIR__ . '/../../src/helpers/DatabaseHelper.php';

header('Content-Type: application/json');

// Require admin authentication
AuthService::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => ['message' => 'Method not allowed']]);
    exit;
}

// Get loan_id from URL path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));
$loanId = null;

// Find loan_id in path (format: /api/loans/{id}/approve)
foreach ($pathParts as $i => $part) {
    if ($part === 'loans' && isset($pathParts[$i + 1]) && is_numeric($pathParts[$i + 1])) {
        $loanId = (int)$pathParts[$i + 1];
        break;
    }
}

if (!$loanId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => ['message' => 'Loan ID is required']]);
    exit;
}

// Get request body
$data = json_decode(file_get_contents('php://input'), true);
$dueDate = $data['due_date'] ?? null;

if (!$dueDate) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => ['message' => 'Due date is required']]);
    exit;
}

try {
    $pdo = DatabaseHelper::getConnection();
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Check if loan exists and is pending
    $checkSql = "SELECT l.*, b.available_quantity 
                 FROM loans l 
                 JOIN books b ON l.book_id = b.book_id 
                 WHERE l.loan_id = :loan_id AND l.status = 'pending'";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':loan_id' => $loanId]);
    $loan = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$loan) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => ['message' => 'Pending loan not found']]);
        exit;
    }
    
    // Check if book is available
    if ($loan['available_quantity'] <= 0) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => ['message' => 'Book is not available']]);
        exit;
    }
    
    // Update loan status to active
    $updateLoanSql = "UPDATE loans 
                      SET status = 'active', 
                          checkout_date = CURDATE(), 
                          due_date = :due_date 
                      WHERE loan_id = :loan_id";
    $updateLoanStmt = $pdo->prepare($updateLoanSql);
    $updateLoanStmt->execute([
        ':loan_id' => $loanId,
        ':due_date' => $dueDate
    ]);
    
    // Decrease book available quantity
    $updateBookSql = "UPDATE books 
                      SET available_quantity = available_quantity - 1 
                      WHERE book_id = :book_id";
    $updateBookStmt = $pdo->prepare($updateBookSql);
    $updateBookStmt->execute([':book_id' => $loan['book_id']]);
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Loan approved successfully'
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => ['message' => 'Failed to approve loan: ' . $e->getMessage()]
    ]);
}
