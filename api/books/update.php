<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../src/services/AuthService.php';
require_once __DIR__ . '/../../src/helpers/DatabaseHelper.php';

AuthService::requireAdmin();

try {
    $pdo = DatabaseHelper::getConnection();
    
    $book_id = $_POST['book_id'] ?? null;
    if (!$book_id) {
        throw new Exception('Book ID is required');
    }
    
    // Get current book data
    $stmt = $pdo->prepare("SELECT * FROM books WHERE book_id = :id");
    $stmt->execute([':id' => $book_id]);
    $current_book = $stmt->fetch();
    
    if (!$current_book) {
        throw new Exception('Book not found');
    }
    
    // Get form data
    $title = $_POST['title'] ?? $current_book['title'];
    $isbn = $_POST['isbn'] ?? $current_book['isbn'];
    $category_id = $_POST['category_id'] ?? $current_book['category_id'];
    $publication_year = $_POST['publication_year'] ?? $current_book['publication_year'];
    $description = $_POST['description'] ?? $current_book['description'];
    $total_quantity = $_POST['total_quantity'] ?? $current_book['total_quantity'];
    $available_quantity = $_POST['available_quantity'] ?? $current_book['available_quantity'];
    $publisher = $_POST['publisher'] ?? $current_book['publisher'];
    $cover_image = $_POST['cover_image'] ?? $current_book['cover_image'];
    $pdf_file = $_POST['pdf_file'] ?? $current_book['pdf_file'];
    
    // Handle cover image upload
    if (isset($_FILES['cover_image_file']) && $_FILES['cover_image_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../../public/uploads/covers/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['cover_image_file']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('cover_') . '.' . $file_extension;
        $file_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['cover_image_file']['tmp_name'], $file_path)) {
            // Delete old cover if it exists and is a local file
            if ($current_book['cover_image'] && strpos($current_book['cover_image'], '/uploads/covers/') !== false) {
                $old_file = __DIR__ . '/../../public' . parse_url($current_book['cover_image'], PHP_URL_PATH);
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }
            $cover_image = '/library-pro/public/uploads/covers/' . $file_name;
        }
    }
    
    // Handle PDF upload
    if (isset($_FILES['pdf_file_upload']) && $_FILES['pdf_file_upload']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../../public/uploads/pdfs/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['pdf_file_upload']['name'], PATHINFO_EXTENSION);
        if (strtolower($file_extension) === 'pdf') {
            $file_name = uniqid('book_') . '.pdf';
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['pdf_file_upload']['tmp_name'], $file_path)) {
                // Delete old PDF if it exists
                if ($current_book['pdf_file'] && strpos($current_book['pdf_file'], '/uploads/pdfs/') !== false) {
                    $old_file = __DIR__ . '/../../public' . parse_url($current_book['pdf_file'], PHP_URL_PATH);
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                }
                $pdf_file = '/library-pro/public/uploads/pdfs/' . $file_name;
            }
        }
    }
    
    // Check if pdf_file and publisher columns exist
    $columns_check = $pdo->query("SHOW COLUMNS FROM books")->fetchAll(PDO::FETCH_COLUMN);
    $has_pdf_file = in_array('pdf_file', $columns_check);
    $has_publisher = in_array('publisher', $columns_check);
    
    // Build UPDATE query based on available columns
    if ($has_pdf_file && $has_publisher) {
        $sql = "UPDATE books 
                SET title = :title, isbn = :isbn, category_id = :category_id, 
                    publication_year = :publication_year, description = :description,
                    total_quantity = :total_quantity, available_quantity = :available_quantity,
                    publisher = :publisher, cover_image = :cover_image, pdf_file = :pdf_file,
                    updated_at = CURRENT_TIMESTAMP
                WHERE book_id = :book_id";
        $params = [
            ':title' => $title,
            ':isbn' => $isbn,
            ':category_id' => $category_id,
            ':publication_year' => $publication_year,
            ':description' => $description,
            ':total_quantity' => $total_quantity,
            ':available_quantity' => $available_quantity,
            ':publisher' => $publisher,
            ':cover_image' => $cover_image,
            ':pdf_file' => $pdf_file,
            ':book_id' => $book_id
        ];
    } else {
        // Fallback to basic columns
        $sql = "UPDATE books 
                SET title = :title, isbn = :isbn, category_id = :category_id, 
                    publication_year = :publication_year, description = :description,
                    total_quantity = :total_quantity, available_quantity = :available_quantity,
                    cover_image = :cover_image, updated_at = CURRENT_TIMESTAMP
                WHERE book_id = :book_id";
        $params = [
            ':title' => $title,
            ':isbn' => $isbn,
            ':category_id' => $category_id,
            ':publication_year' => $publication_year,
            ':description' => $description,
            ':total_quantity' => $total_quantity,
            ':available_quantity' => $available_quantity,
            ':cover_image' => $cover_image,
            ':book_id' => $book_id
        ];
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // Update author if provided
    if (!empty($_POST['author_id'])) {
        // Delete existing author links
        $stmt = $pdo->prepare("DELETE FROM book_authors WHERE book_id = :book_id");
        $stmt->execute([':book_id' => $book_id]);
        
        // Add new author link
        $stmt = $pdo->prepare("INSERT INTO book_authors (book_id, author_id) VALUES (:book_id, :author_id)");
        $stmt->execute([':book_id' => $book_id, ':author_id' => $_POST['author_id']]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Book updated successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error updating book: ' . $e->getMessage()
    ]);
}
