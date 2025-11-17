<?php
require_once __DIR__ . '/../../src/services/AuthService.php';
require_once __DIR__ . '/../../src/helpers/DatabaseHelper.php';

header('Content-Type: application/json');

// Require authentication
AuthService::requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => ['message' => 'Method not allowed']]);
    exit;
}

try {
    $pdo = DatabaseHelper::getConnection();
    
    // Get all loans with book and user information
    $sql = "SELECT l.*, 
            b.title as book_title,
            CONCAT(u.first_name, ' ', u.last_name) as user_name,
            l.checkout_date as loan_date
            FROM loans l
            JOIN books b ON l.book_id = b.book_id
            JOIN users u ON l.user_id = u.user_id
            WHERE l.status IN ('active', 'returned')
            ORDER BY l.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $loans
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => ['message' => 'Failed to fetch loans: ' . $e->getMessage()]
    ]);
}
