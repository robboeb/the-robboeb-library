<?php
require_once dirname(__DIR__) . '/models/Book.php';

class BookController {
    private $bookModel;

    public function __construct() {
        $this->bookModel = new Book();
    }

    public function index() {
        try {
            $limit = $_GET['limit'] ?? 1000; // Get all books
            $offset = $_GET['offset'] ?? 0;
            $categoryId = $_GET['category_id'] ?? null;

            // Get books with category names
            $sql = "SELECT b.*, c.category_name,
                    GROUP_CONCAT(CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ') as authors
                    FROM books b
                    LEFT JOIN categories c ON b.category_id = c.category_id
                    LEFT JOIN book_authors ba ON b.book_id = ba.book_id
                    LEFT JOIN authors a ON ba.author_id = a.author_id";
            
            if ($categoryId) {
                $sql .= " WHERE b.category_id = :category_id";
            }
            
            $sql .= " GROUP BY b.book_id ORDER BY b.created_at DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->bookModel->db->prepare($sql);
            if ($categoryId) {
                $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
            }
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->sendResponse($books, 200);
        } catch (Exception $e) {
            error_log("BookController::index error: " . $e->getMessage());
            $this->sendError('Failed to fetch books: ' . $e->getMessage(), 500, 'SERVER_ERROR');
        }
    }

    public function show($id) {
        try {
            $book = $this->bookModel->findById($id);
            
            if (!$book) {
                $this->sendError('Book not found', 404, 'NOT_FOUND');
                return;
            }

            $book['authors'] = $this->bookModel->getAuthors($id);
            $this->sendResponse($book, 200);
        } catch (Exception $e) {
            $this->sendError('Failed to fetch book', 500, 'SERVER_ERROR');
        }
    }

    public function create() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $authorId = $data['author_id'] ?? null;
            $authorIds = isset($data['author_ids']) ? $data['author_ids'] : ($authorId ? [$authorId] : []);
            unset($data['author_ids']);
            unset($data['author_id']);

            $result = $this->bookModel->create($data);

            if (!$result['success']) {
                $this->sendError('Failed to create book', 400, 'VALIDATION_ERROR', $result['errors']);
                return;
            }

            if (!empty($authorIds)) {
                $this->bookModel->addAuthors($result['id'], $authorIds);
            }

            $book = $this->bookModel->findById($result['id']);
            $authors = $this->bookModel->getAuthors($result['id']);
            $book['authors'] = implode(', ', array_map(function($a) {
                return $a['first_name'] . ' ' . $a['last_name'];
            }, $authors));
            
            $this->sendResponse($book, 201);
        } catch (Exception $e) {
            error_log("BookController::create error: " . $e->getMessage());
            $this->sendError('Failed to create book: ' . $e->getMessage(), 500, 'SERVER_ERROR');
        }
    }

    public function update($id) {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $authorId = $data['author_id'] ?? null;
            $authorIds = isset($data['author_ids']) ? $data['author_ids'] : ($authorId ? [$authorId] : null);
            unset($data['author_ids']);
            unset($data['author_id']);

            $result = $this->bookModel->update($id, $data);

            if (!$result['success']) {
                $this->sendError('Failed to update book', 400, 'VALIDATION_ERROR', $result['errors']);
                return;
            }

            if ($authorIds !== null) {
                $this->bookModel->removeAuthors($id);
                if (!empty($authorIds)) {
                    $this->bookModel->addAuthors($id, $authorIds);
                }
            }

            $book = $this->bookModel->findById($id);
            $authors = $this->bookModel->getAuthors($id);
            $book['authors'] = implode(', ', array_map(function($a) {
                return $a['first_name'] . ' ' . $a['last_name'];
            }, $authors));
            
            $this->sendResponse($book, 200);
        } catch (Exception $e) {
            error_log("BookController::update error: " . $e->getMessage());
            $this->sendError('Failed to update book: ' . $e->getMessage(), 500, 'SERVER_ERROR');
        }
    }

    public function delete($id) {
        try {
            $result = $this->bookModel->delete($id);
            
            if ($result['affected_rows'] === 0) {
                $this->sendError('Book not found', 404, 'NOT_FOUND');
                return;
            }

            $this->sendResponse(['message' => 'Book deleted successfully'], 200);
        } catch (Exception $e) {
            $this->sendError('Failed to delete book', 500, 'SERVER_ERROR');
        }
    }

    public function search() {
        try {
            $query = $_GET['q'] ?? '';
            $limit = $_GET['limit'] ?? DEFAULT_PAGE_SIZE;
            $offset = $_GET['offset'] ?? 0;

            if (empty($query)) {
                $this->sendError('Search query is required', 400, 'VALIDATION_ERROR');
                return;
            }

            $books = $this->bookModel->search($query, $limit, $offset);

            foreach ($books as &$book) {
                $book['authors'] = $this->bookModel->getAuthors($book['book_id']);
            }

            $this->sendResponse($books, 200);
        } catch (Exception $e) {
            $this->sendError('Search failed', 500, 'SERVER_ERROR');
        }
    }

    private function sendResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode(['success' => true, 'data' => $data]);
    }

    private function sendError($message, $statusCode = 400, $code = 'ERROR', $details = null) {
        http_response_code($statusCode);
        $error = ['code' => $code, 'message' => $message];
        if ($details) {
            $error['details'] = $details;
        }
        echo json_encode(['success' => false, 'error' => $error]);
    }
}
