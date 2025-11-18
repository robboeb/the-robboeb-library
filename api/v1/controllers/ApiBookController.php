<?php
/**
 * API Book Controller
 * Handles all book-related operations
 */

require_once __DIR__ . '/BaseApiController.php';
require_once dirname(__DIR__, 3) . '/src/models/Book.php';

class ApiBookController extends BaseApiController {
    private $bookModel;

    public function __construct() {
        $this->bookModel = new Book();
    }

    public function route($method, $id, $action) {
        // GET /api/v1/books - List all books
        if ($method === 'GET' && !$id) {
            $this->index();
        }
        // GET /api/v1/books/search - Search books
        elseif ($method === 'GET' && $id === 'search') {
            $this->search();
        }
        // GET /api/v1/books/{id} - Get single book
        elseif ($method === 'GET' && $id) {
            $this->show($id);
        }
        // POST /api/v1/books - Create new book
        elseif ($method === 'POST' && !$id) {
            $this->requireAdmin();
            $this->create();
        }
        // PUT /api/v1/books/{id} - Update book
        elseif ($method === 'PUT' && $id) {
            $this->requireAdmin();
            $this->update($id);
        }
        // DELETE /api/v1/books/{id} - Delete book
        elseif ($method === 'DELETE' && $id) {
            $this->requireAdmin();
            $this->delete($id);
        }
        else {
            $this->methodNotAllowed();
        }
    }

    /**
     * List all books with pagination and filtering
     * GET /api/v1/books?limit=10&offset=0&category_id=1&available=true
     */
    private function index() {
        try {
            $params = $this->getQueryParams();
            $limit = isset($params['limit']) ? (int)$params['limit'] : 1000;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            $categoryId = $params['category_id'] ?? null;
            $available = $params['available'] ?? null;
            
            // Build query
            $sql = "SELECT b.*, c.category_name,
                    GROUP_CONCAT(CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ') as authors,
                    (b.total_copies - COALESCE(
                        (SELECT COUNT(*) FROM loans l 
                         WHERE l.book_id = b.book_id 
                         AND l.return_date IS NULL 
                         AND l.status = 'approved'), 0
                    )) as available_copies
                    FROM books b
                    LEFT JOIN categories c ON b.category_id = c.category_id
                    LEFT JOIN book_authors ba ON b.book_id = ba.book_id
                    LEFT JOIN authors a ON ba.author_id = a.author_id";
            
            $conditions = [];
            $bindParams = [];
            
            if ($categoryId) {
                $conditions[] = "b.category_id = :category_id";
                $bindParams[':category_id'] = $categoryId;
            }
            
            if ($available === 'true' || $available === '1') {
                $conditions[] = "(b.total_copies - COALESCE(
                    (SELECT COUNT(*) FROM loans l 
                     WHERE l.book_id = b.book_id 
                     AND l.return_date IS NULL 
                     AND l.status = 'approved'), 0
                )) > 0";
            }
            
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(' AND ', $conditions);
            }
            
            $sql .= " GROUP BY b.book_id ORDER BY b.created_at DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->bookModel->db->prepare($sql);
            
            foreach ($bindParams as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }
            
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count
            $countSql = "SELECT COUNT(DISTINCT b.book_id) as total FROM books b";
            if (!empty($conditions)) {
                $countSql .= " WHERE " . implode(' AND ', $conditions);
            }
            $countStmt = $this->bookModel->db->prepare($countSql);
            foreach ($bindParams as $key => $value) {
                $countStmt->bindValue($key, $value, PDO::PARAM_INT);
            }
            $countStmt->execute();
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $this->sendResponse([
                'books' => $books,
                'pagination' => [
                    'total' => (int)$total,
                    'limit' => $limit,
                    'offset' => $offset,
                    'count' => count($books)
                ]
            ], 200);
            
        } catch (Exception $e) {
            error_log("Book index error: " . $e->getMessage());
            $this->sendError('Failed to fetch books', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Get single book by ID
     * GET /api/v1/books/{id}
     */
    private function show($id) {
        try {
            if (!is_numeric($id)) {
                $this->sendError('Invalid book ID', 400, 'VALIDATION_ERROR');
            }
            
            $book = $this->bookModel->findById($id);
            
            if (!$book) {
                $this->notFound('Book');
            }
            
            // Get authors
            $book['authors'] = $this->bookModel->getAuthors($id);
            
            // Get available copies
            $sql = "SELECT (b.total_copies - COALESCE(
                        (SELECT COUNT(*) FROM loans l 
                         WHERE l.book_id = b.book_id 
                         AND l.return_date IS NULL 
                         AND l.status = 'approved'), 0
                    )) as available_copies
                    FROM books b WHERE b.book_id = :book_id";
            $stmt = $this->bookModel->db->prepare($sql);
            $stmt->bindValue(':book_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $book['available_copies'] = $result['available_copies'];
            
            $this->sendResponse($book, 200);
            
        } catch (Exception $e) {
            error_log("Book show error: " . $e->getMessage());
            $this->sendError('Failed to fetch book', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Create new book
     * POST /api/v1/books
     */
    private function create() {
        try {
            $data = $this->getJsonInput();
            
            // Validate required fields
            $this->validateRequired($data, ['title', 'isbn', 'publication_year', 'total_copies']);
            
            // Extract author IDs
            $authorIds = $data['author_ids'] ?? ($data['author_id'] ? [$data['author_id']] : []);
            unset($data['author_ids'], $data['author_id']);
            
            // Sanitize input
            $data = $this->sanitizeInput($data);
            
            // Create book
            $result = $this->bookModel->create($data);
            
            if (!$result['success']) {
                $this->sendError('Failed to create book', 400, 'VALIDATION_ERROR', $result['errors']);
            }
            
            // Add authors
            if (!empty($authorIds)) {
                $this->bookModel->addAuthors($result['id'], $authorIds);
            }
            
            // Get created book
            $book = $this->bookModel->findById($result['id']);
            $book['authors'] = $this->bookModel->getAuthors($result['id']);
            
            $this->sendResponse($book, 201, 'Book created successfully');
            
        } catch (Exception $e) {
            error_log("Book create error: " . $e->getMessage());
            $this->sendError('Failed to create book', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Update book
     * PUT /api/v1/books/{id}
     */
    private function update($id) {
        try {
            if (!is_numeric($id)) {
                $this->sendError('Invalid book ID', 400, 'VALIDATION_ERROR');
            }
            
            // Check if book exists
            $existingBook = $this->bookModel->findById($id);
            if (!$existingBook) {
                $this->notFound('Book');
            }
            
            $data = $this->getJsonInput();
            
            // Extract author IDs
            $authorIds = $data['author_ids'] ?? ($data['author_id'] ? [$data['author_id']] : null);
            unset($data['author_ids'], $data['author_id']);
            
            // Sanitize input
            $data = $this->sanitizeInput($data);
            
            // Update book
            $result = $this->bookModel->update($id, $data);
            
            if (!$result['success']) {
                $this->sendError('Failed to update book', 400, 'VALIDATION_ERROR', $result['errors']);
            }
            
            // Update authors if provided
            if ($authorIds !== null) {
                $this->bookModel->removeAuthors($id);
                if (!empty($authorIds)) {
                    $this->bookModel->addAuthors($id, $authorIds);
                }
            }
            
            // Get updated book
            $book = $this->bookModel->findById($id);
            $book['authors'] = $this->bookModel->getAuthors($id);
            
            $this->sendResponse($book, 200, 'Book updated successfully');
            
        } catch (Exception $e) {
            error_log("Book update error: " . $e->getMessage());
            $this->sendError('Failed to update book', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Delete book
     * DELETE /api/v1/books/{id}
     */
    private function delete($id) {
        try {
            if (!is_numeric($id)) {
                $this->sendError('Invalid book ID', 400, 'VALIDATION_ERROR');
            }
            
            // Check if book exists
            $book = $this->bookModel->findById($id);
            if (!$book) {
                $this->notFound('Book');
            }
            
            // Check if book has active loans
            $sql = "SELECT COUNT(*) as count FROM loans 
                    WHERE book_id = :book_id AND return_date IS NULL";
            $stmt = $this->bookModel->db->prepare($sql);
            $stmt->bindValue(':book_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $activeLoans = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($activeLoans > 0) {
                $this->sendError('Cannot delete book with active loans', 400, 'VALIDATION_ERROR');
            }
            
            // Delete book
            $result = $this->bookModel->delete($id);
            
            $this->sendResponse([], 200, 'Book deleted successfully');
            
        } catch (Exception $e) {
            error_log("Book delete error: " . $e->getMessage());
            $this->sendError('Failed to delete book', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Search books
     * GET /api/v1/books/search?q=query&limit=10&offset=0
     */
    private function search() {
        try {
            $params = $this->getQueryParams();
            $query = $params['q'] ?? '';
            $limit = isset($params['limit']) ? (int)$params['limit'] : DEFAULT_PAGE_SIZE;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            
            if (empty($query)) {
                $this->sendError('Search query is required', 400, 'VALIDATION_ERROR');
            }
            
            $books = $this->bookModel->search($query, $limit, $offset);
            
            // Add authors to each book
            foreach ($books as &$book) {
                $book['authors'] = $this->bookModel->getAuthors($book['book_id']);
            }
            
            $this->sendResponse([
                'books' => $books,
                'query' => $query,
                'count' => count($books)
            ], 200);
            
        } catch (Exception $e) {
            error_log("Book search error: " . $e->getMessage());
            $this->sendError('Search failed', 500, 'SERVER_ERROR');
        }
    }
}
