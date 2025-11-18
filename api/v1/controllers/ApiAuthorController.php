<?php
/**
 * API Author Controller
 * Handles author management operations
 */

require_once __DIR__ . '/BaseApiController.php';
require_once dirname(__DIR__, 3) . '/src/models/Author.php';

class ApiAuthorController extends BaseApiController {
    private $authorModel;

    public function __construct() {
        $this->authorModel = new Author();
    }

    public function route($method, $id, $action) {
        // GET /api/v1/authors - List authors (public)
        if ($method === 'GET' && !$id) {
            $this->index();
        }
        // GET /api/v1/authors/{id} - Get author (public)
        elseif ($method === 'GET' && $id) {
            $this->show($id);
        }
        // POST /api/v1/authors - Create author (admin only)
        elseif ($method === 'POST' && !$id) {
            $this->requireAdmin();
            $this->create();
        }
        // PUT /api/v1/authors/{id} - Update author (admin only)
        elseif ($method === 'PUT' && $id) {
            $this->requireAdmin();
            $this->update($id);
        }
        // DELETE /api/v1/authors/{id} - Delete author (admin only)
        elseif ($method === 'DELETE' && $id) {
            $this->requireAdmin();
            $this->delete($id);
        }
        else {
            $this->methodNotAllowed();
        }
    }

    /**
     * List all authors
     * GET /api/v1/authors
     */
    private function index() {
        try {
            $authors = $this->authorModel->findAll();
            
            $this->sendResponse([
                'authors' => $authors,
                'count' => count($authors)
            ], 200);
            
        } catch (Exception $e) {
            error_log("Author index error: " . $e->getMessage());
            $this->sendError('Failed to fetch authors', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Get single author with books
     * GET /api/v1/authors/{id}
     */
    private function show($id) {
        try {
            if (!is_numeric($id)) {
                $this->sendError('Invalid author ID', 400, 'VALIDATION_ERROR');
            }
            
            $author = $this->authorModel->findById($id);
            
            if (!$author) {
                $this->notFound('Author');
            }
            
            // Get books by this author
            $sql = "SELECT b.* FROM books b
                    INNER JOIN book_authors ba ON b.book_id = ba.book_id
                    WHERE ba.author_id = :author_id
                    ORDER BY b.publication_year DESC";
            $stmt = $this->authorModel->db->prepare($sql);
            $stmt->bindValue(':author_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $author['books'] = $books;
            $author['book_count'] = count($books);
            
            $this->sendResponse($author, 200);
            
        } catch (Exception $e) {
            error_log("Author show error: " . $e->getMessage());
            $this->sendError('Failed to fetch author', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Create new author
     * POST /api/v1/authors
     */
    private function create() {
        try {
            $data = $this->getJsonInput();
            
            // Validate required fields
            $this->validateRequired($data, ['first_name', 'last_name']);
            
            // Sanitize input
            $data = $this->sanitizeInput($data);
            
            // Create author
            $result = $this->authorModel->create($data);
            
            if (!$result['success']) {
                $this->sendError('Failed to create author', 400, 'VALIDATION_ERROR', $result['errors']);
            }
            
            // Get created author
            $author = $this->authorModel->findById($result['id']);
            
            $this->sendResponse($author, 201, 'Author created successfully');
            
        } catch (Exception $e) {
            error_log("Author create error: " . $e->getMessage());
            $this->sendError('Failed to create author', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Update author
     * PUT /api/v1/authors/{id}
     */
    private function update($id) {
        try {
            if (!is_numeric($id)) {
                $this->sendError('Invalid author ID', 400, 'VALIDATION_ERROR');
            }
            
            // Check if author exists
            $existingAuthor = $this->authorModel->findById($id);
            if (!$existingAuthor) {
                $this->notFound('Author');
            }
            
            $data = $this->getJsonInput();
            
            // Sanitize input
            $data = $this->sanitizeInput($data);
            
            // Update author
            $result = $this->authorModel->update($id, $data);
            
            if (!$result['success']) {
                $this->sendError('Failed to update author', 400, 'VALIDATION_ERROR', $result['errors']);
            }
            
            // Get updated author
            $author = $this->authorModel->findById($id);
            
            $this->sendResponse($author, 200, 'Author updated successfully');
            
        } catch (Exception $e) {
            error_log("Author update error: " . $e->getMessage());
            $this->sendError('Failed to update author', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Delete author
     * DELETE /api/v1/authors/{id}
     */
    private function delete($id) {
        try {
            if (!is_numeric($id)) {
                $this->sendError('Invalid author ID', 400, 'VALIDATION_ERROR');
            }
            
            // Check if author exists
            $author = $this->authorModel->findById($id);
            if (!$author) {
                $this->notFound('Author');
            }
            
            // Check if author has books
            $sql = "SELECT COUNT(*) as count FROM book_authors WHERE author_id = :author_id";
            $stmt = $this->authorModel->db->prepare($sql);
            $stmt->bindValue(':author_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $bookCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($bookCount > 0) {
                $this->sendError('Cannot delete author with existing books', 400, 'VALIDATION_ERROR');
            }
            
            // Delete author
            $result = $this->authorModel->delete($id);
            
            $this->sendResponse([], 200, 'Author deleted successfully');
            
        } catch (Exception $e) {
            error_log("Author delete error: " . $e->getMessage());
            $this->sendError('Failed to delete author', 500, 'SERVER_ERROR');
        }
    }
}
