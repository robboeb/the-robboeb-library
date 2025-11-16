<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../src/services/AuthService.php';
require_once __DIR__ . '/../../src/helpers/DatabaseHelper.php';

AuthService::requireAdmin();

try {
    $pdo = DatabaseHelper::getConnection();
    
    $book_id = $_GET['book_id'] ?? null;
    if (!$book_id) {
        throw new Exception('Book ID is required');
    }
    
    // Get book with author
    $stmt = $pdo->prepare("
        SELECT b.*, 
               GROUP_CONCAT(ba.author_id) as author_ids,
               GROUP_CONCAT(CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ') as authors
        FROM books b
        LEFT JOIN book_authors ba ON b.book_id = ba.book_id
        LEFT JOIN authors a ON ba.author_id = a.author_id
        WHERE b.book_id = :id
        GROUP BY b.book_id
    ");
    $stmt->execute([':id' => $book_id]);
    $book = $stmt->fetch();
    
    if (!$book) {
        throw new Exception('Book not found');
    }
    
    echo json_encode([
        'success' => true,
        'book' => $book
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching book: ' . $e->getMessage()
    ]);
}
