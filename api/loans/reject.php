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

// Find loan_id in path (format: /api/loans/{id}/reject)
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

try {
    $pdo = DatabaseHelper::getConnection();
    
    // Check if loan exists and is pending
    $checkSql = "SELECT * FROM loans WHERE loan_id = :loan_id AND status = 'pending'";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':loan_id' => $loanId]);
    $loan = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$loan) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => ['message' => 'Pending loan not found']]);
        exit;
    }
    
    // Update loan status to rejected
    $updateSql = "UPDATE loans SET status = 'rejected' WHERE loan_id = :loan_id";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([':loan_id' => $loanId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Loan request rejected'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => ['message' => 'Failed to reject loan: ' . $e->getMessage()]
    ]);
}
