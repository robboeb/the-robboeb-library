<?php
require_once __DIR__ . '/../../src/services/AuthService.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../src/helpers/DatabaseHelper.php';

AuthService::requireAdmin();
$currentUser = AuthService::getCurrentUser();

$message = '';
$messageType = '';

// Handle form submissions directly (no API)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = DatabaseHelper::getConnection();
    
    try {
        // DELETE operation
        if (isset($_POST['action']) && $_POST['action'] === 'delete') {
            $book_id = $_POST['book_id'];
            
            // Get book data to delete files
            $stmt = $pdo->prepare("SELECT cover_image, pdf_file FROM books WHERE book_id = :id");
            $stmt->execute([':id' => $book_id]);
            $book = $stmt->fetch();
            
            // Delete files if they exist
            if ($book && !empty($book['cover_image']) && strpos($book['cover_image'], '/uploads/') !== false) {
                $file_path = __DIR__ . '/../..' . parse_url($book['cover_image'], PHP_URL_PATH);
                if (file_exists($file_path)) @unlink($file_path);
            }
            if ($book && !empty($book['pdf_file']) && strpos($book['pdf_file'], '/uploads/') !== false) {
                $file_path = __DIR__ . '/../..' . parse_url($book['pdf_file'], PHP_URL_PATH);
                if (file_exists($file_path)) @unlink($file_path);
            }
            
            // Delete book_authors relationships
            $stmt = $pdo->prepare("DELETE FROM book_authors WHERE book_id = :book_id");
            $stmt->execute([':book_id' => $book_id]);
            
            // Delete book
            $stmt = $pdo->prepare("DELETE FROM books WHERE book_id = :book_id");
            $stmt->execute([':book_id' => $book_id]);
            
            $message = 'Book deleted successfully';
            $messageType = 'success';
        }
        // CREATE or UPDATE operation
        else {
            $book_id = $_POST['book_id'] ?? null;
            $title = $_POST['title'] ?? '';
            $isbn = $_POST['isbn'] ?? '';
            $category_id = $_POST['category_id'] ?? null;
            $author_id = $_POST['author_id'] ?? null;
            $publication_year = $_POST['publication_year'] ?? null;
            $description = $_POST['description'] ?? '';
            $publisher = $_POST['publisher'] ?? '';
            $total_quantity = $_POST['total_quantity'] ?? 1;
            $available_quantity = $_POST['available_quantity'] ?? 1;
            $cover_image = $_POST['cover_image'] ?? '';
            $pdf_file = $_POST['pdf_file'] ?? '';
            
            // Handle cover image upload
            if (isset($_FILES['cover_image_file']) && $_FILES['cover_image_file']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../../public/uploads/covers/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                
                $file_extension = pathinfo($_FILES['cover_image_file']['name'], PATHINFO_EXTENSION);
                $file_name = uniqid('cover_') . '.' . $file_extension;
                $file_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['cover_image_file']['tmp_name'], $file_path)) {
                    $cover_image = BASE_URL . '/public/uploads/covers/' . $file_name;
                }
            }
            
            // Handle PDF upload
            if (isset($_FILES['pdf_file_upload']) && $_FILES['pdf_file_upload']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../../public/uploads/pdfs/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                
                $file_extension = pathinfo($_FILES['pdf_file_upload']['name'], PATHINFO_EXTENSION);
                if (strtolower($file_extension) === 'pdf') {
                    $file_name = uniqid('book_') . '.pdf';
                    $file_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['pdf_file_upload']['tmp_name'], $file_path)) {
                        $pdf_file = BASE_URL . '/public/uploads/pdfs/' . $file_name;
                    }
                }
            }
            
            // Check which columns exist
            $columns = $pdo->query("SHOW COLUMNS FROM books")->fetchAll(PDO::FETCH_COLUMN);
            $has_pdf = in_array('pdf_file', $columns);
            $has_publisher = in_array('publisher', $columns);
            
            if ($book_id) {
                // UPDATE
                if ($has_pdf && $has_publisher) {
                    $sql = "UPDATE books SET title = :title, isbn = :isbn, category_id = :category_id,
                            publication_year = :publication_year, description = :description,
                            total_quantity = :total_quantity, available_quantity = :available_quantity,
                            publisher = :publisher, cover_image = :cover_image, pdf_file = :pdf_file
                            WHERE book_id = :book_id";
                    $params = compact('title', 'isbn', 'category_id', 'publication_year', 'description',
                                    'total_quantity', 'available_quantity', 'publisher', 'cover_image', 'pdf_file', 'book_id');
                } else {
                    $sql = "UPDATE books SET title = :title, isbn = :isbn, category_id = :category_id,
                            publication_year = :publication_year, description = :description,
                            total_quantity = :total_quantity, available_quantity = :available_quantity,
                            cover_image = :cover_image WHERE book_id = :book_id";
                    $params = compact('title', 'isbn', 'category_id', 'publication_year', 'description',
                                    'total_quantity', 'available_quantity', 'cover_image', 'book_id');
                }
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                // Update author
                $pdo->prepare("DELETE FROM book_authors WHERE book_id = :book_id")->execute([':book_id' => $book_id]);
                if ($author_id) {
                    $pdo->prepare("INSERT INTO book_authors (book_id, author_id) VALUES (:book_id, :author_id)")
                        ->execute([':book_id' => $book_id, ':author_id' => $author_id]);
                }
                
                $message = 'Book updated successfully';
            } else {
                // CREATE
                if ($has_pdf && $has_publisher) {
                    $sql = "INSERT INTO books (title, isbn, category_id, publication_year, description,
                            total_quantity, available_quantity, publisher, cover_image, pdf_file)
                            VALUES (:title, :isbn, :category_id, :publication_year, :description,
                            :total_quantity, :available_quantity, :publisher, :cover_image, :pdf_file)";
                    $params = compact('title', 'isbn', 'category_id', 'publication_year', 'description',
                                    'total_quantity', 'available_quantity', 'publisher', 'cover_image', 'pdf_file');
                } else {
                    $sql = "INSERT INTO books (title, isbn, category_id, publication_year, description,
                            total_quantity, available_quantity, cover_image)
                            VALUES (:title, :isbn, :category_id, :publication_year, :description,
                            :total_quantity, :available_quantity, :cover_image)";
                    $params = compact('title', 'isbn', 'category_id', 'publication_year', 'description',
                                    'total_quantity', 'available_quantity', 'cover_image');
                }
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $book_id = $pdo->lastInsertId();
                
                // Link author
                if ($author_id) {
                    $pdo->prepare("INSERT INTO book_authors (book_id, author_id) VALUES (:book_id, :author_id)")
                        ->execute([':book_id' => $book_id, ':author_id' => $author_id]);
                }
                
                $message = 'Book created successfully';
            }
            $messageType = 'success';
        }
        
        // Redirect to avoid form resubmission
        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=' . urlencode($message) . '&type=' . $messageType);
        exit;
        
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Get message from redirect
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $messageType = $_GET['type'] ?? 'info';
}

// Get PDO connection for later use
$pdo = DatabaseHelper::getConnection();

// Get all books and categories
$books = DatabaseHelper::getAllBooks();
$categories = DatabaseHelper::getAllCategories();
$authors = DatabaseHelper::getAllAuthors();
$stats = DatabaseHelper::getDashboardStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books Management - <?php echo APP_NAME; ?></title>
    <link rel="icon" type="image/svg+xml" href="https://s3.ca-central-1.amazonaws.com/logojoy/logos/231703335/symbol.svg?1537014.9000000358">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <header class="top-bar">
                <div class="top-bar-left">
                    <h1><i class="fas fa-book"></i> Books Management</h1>
                </div>
            </header>
            
            <div class="content-area">
                <?php if ($message): ?>
                    <div class="notification notification-<?php echo $messageType; ?>" id="notification">
                        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <span><?php echo htmlspecialchars($message); ?></span>
                        <button onclick="this.parentElement.remove()" style="background: none; border: none; color: inherit; cursor: pointer; margin-left: auto;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endif; ?>
                
                <div class="page-header-section">
                    <div class="page-stats">
                        <div class="stat-item">
                            <i class="fas fa-book"></i>
                            <div>
                                <span class="stat-value"><?php echo $stats['total_books']; ?></span>
                                <span class="stat-label">Total Books</span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <span class="stat-value"><?php echo $stats['available_books']; ?></span>
                                <span class="stat-label">Available</span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-book-reader"></i>
                            <div>
                                <span class="stat-value"><?php echo $stats['total_books'] - $stats['available_books']; ?></span>
                                <span class="stat-label">Borrowed</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="toolbar">
                    <div class="toolbar-left">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" placeholder="Search by title, author, or ISBN...">
                        </div>
                        <div class="filter-group">
                            <select id="statusFilter" class="filter-select">
                                <option value="">All Status</option>
                                <option value="available">Available</option>
                                <option value="borrowed">Borrowed</option>
                                <option value="reserved">Reserved</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                            <select id="categoryFilter" class="filter-select">
                                <option value="">All Categories</option>
                            </select>
                        </div>
                    </div>
                    <div class="toolbar-right">
                        <button onclick="showAddBookModal()" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Book
                        </button>
                    </div>
                </div>
                
                <div class="data-card">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th width="60">ID</th>
                                    <th width="80">Cover</th>
                                    <th>Title</th>
                                    <th>Author(s)</th>
                                    <th width="130">ISBN</th>
                                    <th width="120">Category</th>
                                    <th width="100">Copies</th>
                                    <th width="80">Files</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="booksTableBody">
                                <?php if (empty($books)): ?>
                                    <tr>
                                        <td colspan="9" class="empty-cell">No books found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($books as $book): ?>
                                        <?php
                                        // Get first author ID
                                        $author_id = '';
                                        if (!empty($book['book_id'])) {
                                            $author_stmt = $pdo->prepare("SELECT author_id FROM book_authors WHERE book_id = :book_id LIMIT 1");
                                            $author_stmt->execute([':book_id' => $book['book_id']]);
                                            $author_row = $author_stmt->fetch();
                                            $author_id = $author_row ? $author_row['author_id'] : '';
                                        }
                                        ?>
                                        <tr data-book-id="<?php echo $book['book_id']; ?>"
                                            data-title="<?php echo htmlspecialchars($book['title']); ?>"
                                            data-isbn="<?php echo htmlspecialchars($book['isbn']); ?>"
                                            data-category-id="<?php echo $book['category_id']; ?>"
                                            data-author-id="<?php echo $author_id; ?>"
                                            data-publication-year="<?php echo $book['publication_year'] ?? ''; ?>"
                                            data-description="<?php echo htmlspecialchars($book['description'] ?? ''); ?>"
                                            data-publisher="<?php echo htmlspecialchars($book['publisher'] ?? ''); ?>"
                                            data-total-quantity="<?php echo $book['total_quantity']; ?>"
                                            data-available-quantity="<?php echo $book['available_quantity']; ?>"
                                            data-cover-image="<?php echo htmlspecialchars($book['cover_image'] ?? ''); ?>"
                                            data-pdf-file="<?php echo htmlspecialchars($book['pdf_file'] ?? ''); ?>">
                                            <td><strong>#<?php echo $book['book_id']; ?></strong></td>
                                            <td>
                                                <?php if (!empty($book['cover_image'])): ?>
                                                    <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                                         alt="Cover" 
                                                         style="width: 50px; height: 70px; object-fit: cover; border-radius: 4px; cursor: pointer;"
                                                         onclick="window.open('<?php echo htmlspecialchars($book['cover_image']); ?>', '_blank')"
                                                         onerror="this.src='https://via.placeholder.com/50x70/faa405/ffffff?text=No+Cover'">
                                                <?php else: ?>
                                                    <div style="width: 50px; height: 70px; background: linear-gradient(135deg, #faa405, #e89400); border-radius: 4px; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px;">
                                                        <i class="fas fa-book"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div style="font-weight: 600; color: var(--gray-900);"><?php echo htmlspecialchars($book['title']); ?></div>
                                                <?php if (!empty($book['publisher'])): ?>
                                                    <div style="font-size: 12px; color: var(--gray-500);"><?php echo htmlspecialchars($book['publisher']); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($book['authors'] ?: 'Unknown'); ?></td>
                                            <td><code style="font-size: 12px;"><?php echo htmlspecialchars($book['isbn']); ?></code></td>
                                            <td><span class="badge badge-primary"><?php echo htmlspecialchars($book['category_name'] ?: 'N/A'); ?></span></td>
                                            <td>
                                                <div style="font-weight: 600;"><?php echo $book['total_copies'] ?? 0; ?></div>
                                                <div style="font-size: 11px; color: var(--gray-500);"><?php echo $book['available_copies'] ?? 0; ?> available</div>
                                            </td>
                                            <td>
                                                <?php if (!empty($book['pdf_file'])): ?>
                                                    <a href="<?php echo htmlspecialchars($book['pdf_file']); ?>" target="_blank" class="btn btn-sm btn-success" title="View PDF">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span style="color: var(--gray-400); font-size: 12px;">No PDF</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="actions">
                                                <button onclick="viewBook(<?php echo $book['book_id']; ?>)" class="btn btn-sm btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button onclick="editBook(<?php echo $book['book_id']; ?>)" class="btn btn-sm btn-secondary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="deleteBook(<?php echo $book['book_id']; ?>, '<?php echo htmlspecialchars($book['title'], ENT_QUOTES); ?>')" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="table-footer">
                        <div class="table-info">
                            <span id="tableInfo">Showing 0 books</span>
                        </div>
                        <div id="pagination" class="pagination"></div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Add/Edit Book Modal -->
    <div id="bookModal" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h2 id="modalTitle"><i class="fas fa-book"></i> Add New Book</h2>
                <button onclick="closeBookModal()" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="bookForm" method="POST" enctype="multipart/form-data" class="modal-body">
                <input type="hidden" id="bookId" name="book_id">
                
                <div class="form-section">
                    <h3 class="form-section-title">Basic Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-book"></i> Title *
                            </label>
                            <input type="text" id="title" name="title" class="form-control" required placeholder="Enter book title">
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-barcode"></i> ISBN *
                            </label>
                            <input type="text" id="isbn" name="isbn" class="form-control" required placeholder="978-0-123456-78-9">
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-user"></i> Author
                            </label>
                            <select id="authorId" name="author_id" class="form-control">
                                <option value="">Select Author</option>
                                <?php foreach ($authors as $author): ?>
                                    <option value="<?php echo $author['author_id']; ?>">
                                        <?php echo htmlspecialchars($author['first_name'] . ' ' . $author['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-tag"></i> Category *
                            </label>
                            <select id="categoryId" name="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>">
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="form-section-title">Publication Details</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-building"></i> Publisher
                            </label>
                            <input type="text" id="publisher" name="publisher" class="form-control" placeholder="Publisher name">
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-calendar"></i> Publication Year
                            </label>
                            <input type="number" id="publicationYear" name="publication_year" class="form-control" min="1000" max="2100" placeholder="2024">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="form-section-title">Description</h3>
                    <div class="form-group">
                        <textarea id="description" name="description" class="form-control" rows="4" placeholder="Enter book description..."></textarea>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="form-section-title">Inventory</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-boxes"></i> Total Copies *
                            </label>
                            <input type="number" id="totalCopies" name="total_quantity" class="form-control" min="1" required value="1">
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-check-circle"></i> Available Copies *
                            </label>
                            <input type="number" id="availableCopies" name="available_quantity" class="form-control" min="0" required value="1">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="form-section-title">Cover Image</h3>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-link"></i> Cover Image URL
                        </label>
                        <input type="url" id="coverImage" name="cover_image" class="form-control" placeholder="https://example.com/cover.jpg">
                        <small class="form-text">Or upload an image file below</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-upload"></i> Upload Cover Image
                        </label>
                        <input type="file" id="coverImageFile" name="cover_image_file" class="form-control" accept="image/*">
                        <small class="form-text">Supported: JPG, PNG, GIF (Max 5MB)</small>
                    </div>
                    <div id="coverPreview" class="image-preview" style="display: none;">
                        <img id="coverPreviewImg" src="" alt="Cover Preview">
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="form-section-title">PDF File</h3>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-link"></i> PDF File URL
                        </label>
                        <input type="url" id="pdfFile" name="pdf_file" class="form-control" placeholder="https://example.com/book.pdf">
                        <small class="form-text">Or upload a PDF file below</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-file-pdf"></i> Upload PDF File
                        </label>
                        <input type="file" id="pdfFileUpload" name="pdf_file_upload" class="form-control" accept=".pdf">
                        <small class="form-text">Supported: PDF only (Max 50MB)</small>
                    </div>
                    <div id="pdfPreview" class="file-preview" style="display: none;">
                        <i class="fas fa-file-pdf"></i>
                        <span id="pdfFileName"></span>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" onclick="closeBookModal()" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Book
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Define BASE_URL for JavaScript
        const BASE_URL = '<?php echo BASE_URL; ?>';
    </script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/utils.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/components.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/api.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/sidebar.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/admin/books.js"></script>
</body>
</html>
