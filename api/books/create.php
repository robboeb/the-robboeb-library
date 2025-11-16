<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../src/services/AuthService.php';
require_once __DIR__ . '/../../src/helpers/DatabaseHelper.php';

AuthService::requireAdmin();

try {
    $pdo = DatabaseHelper::getConnection();
    
    // Get form data
    $title = $_POST['title'] ?? '';
    $isbn = $_POST['isbn'] ?? '';
    $category_id = $_POST['category_id'] ?? null;
    $publication_year = $_POST['publication_year'] ?? null;
    $description = $_POST['description'] ?? '';
    $total_quantity = $_POST['total_quantity'] ?? 1;
    $available_quantity = $_POST['available_quantity'] ?? $total_quantity;
    $publisher = $_POST['publisher'] ?? '';
    $cover_image = $_POST['cover_image'] ?? '';
    $pdf_file = $_POST['pdf_file'] ?? '';
    
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
                $pdf_file = '/library-pro/public/uploads/pdfs/' . $file_name;
            }
        }
    }
    
    // Check if pdf_file and publisher columns exist
    $columns_check = $pdo->query("SHOW COLUMNS FROM books")->fetchAll(PDO::FETCH_COLUMN);
    $has_pdf_file = in_array('pdf_file', $columns_check);
    $has_publisher = in_array('publisher', $columns_check);
    
    // Build INSERT query based on available columns
    if ($has_pdf_file && $has_publisher) {
        $sql = "INSERT INTO books (title, isbn, category_id, publication_year, description, 
                          total_quantity, available_quantity, publisher, cover_image, pdf_file)
                VALUES (:title, :isbn, :category_id, :publication_year, :description, 
                        :total_quantity, :available_quantity, :publisher, :cover_image, :pdf_file)";
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
            ':pdf_file' => $pdf_file
        ];
    } else {
        // Fallback to basic columns
        $sql = "INSERT INTO books (title, isbn, category_id, publication_year, description, 
                          total_quantity, available_quantity, cover_image)
                VALUES (:title, :isbn, :category_id, :publication_year, :description, 
                        :total_quantity, :available_quantity, :cover_image)";
        $params = [
            ':title' => $title,
            ':isbn' => $isbn,
            ':category_id' => $category_id,
            ':publication_year' => $publication_year,
            ':description' => $description,
            ':total_quantity' => $total_quantity,
            ':available_quantity' => $available_quantity,
            ':cover_image' => $cover_image
        ];
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    $book_id = $pdo->lastInsertId();
    
    // Link author if provided
    if (!empty($_POST['author_id'])) {
        $stmt = $pdo->prepare("INSERT INTO book_authors (book_id, author_id) VALUES (:book_id, :author_id)");
        $stmt->execute([':book_id' => $book_id, ':author_id' => $_POST['author_id']]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Book created successfully',
        'book_id' => $book_id
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error creating book: ' . $e->getMessage()
    ]);
}
