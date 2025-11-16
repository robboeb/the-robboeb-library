<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../src/services/AuthService.php';
require_once __DIR__ . '/../../src/helpers/DatabaseHelper.php';

AuthService::requireAdmin();

try {
    $pdo = DatabaseHelper::getConnection();
    
    $book_id = $_POST['book_id'] ?? $_GET['book_id'] ?? null;
    if (!$book_id) {
        throw new Exception('Book ID is required');
    }
    
    // Get book data to delete files
    $stmt = $pdo->prepare("SELECT cover_image, pdf_file FROM books WHERE book_id = :id");
    $stmt->execute([':id' => $book_id]);
    $book = $stmt->fetch();
    
    if (!$book) {
        throw new Exception('Book not found');
    }
    
    // Delete cover image if it's a local file
    if ($book['cover_image'] && strpos($book['cover_image'], '/uploads/covers/') !== false) {
        $file_path = __DIR__ . '/../../public' . parse_url($book['cover_image'], PHP_URL_PATH);
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // Delete PDF file if it exists
    if ($book['pdf_file'] && strpos($book['pdf_file'], '/uploads/pdfs/') !== false) {
        $file_path = __DIR__ . '/../../public' . parse_url($book['pdf_file'], PHP_URL_PATH);
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // Delete book_authors relationships
    $stmt = $pdo->prepare("DELETE FROM book_authors WHERE book_id = :book_id");
    $stmt->execute([':book_id' => $book_id]);
    
    // Delete book
    $stmt = $pdo->prepare("DELETE FROM books WHERE book_id = :book_id");
    $stmt->execute([':book_id' => $book_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Book deleted successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting book: ' . $e->getMessage()
    ]);
}
