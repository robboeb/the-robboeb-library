<?php
require_once dirname(__DIR__) . '/models/Author.php';

class AuthorController {
    private $authorModel;

    public function __construct() {
        $this->authorModel = new Author();
    }

    public function index() {
        try {
            $authors = $this->authorModel->findAll();
            $this->sendResponse($authors, 200);
        } catch (Exception $e) {
            $this->sendError('Failed to fetch authors', 500, 'SERVER_ERROR');
        }
    }

    public function show($id) {
        try {
            $author = $this->authorModel->findById($id);
            
            if (!$author) {
                $this->sendError('Author not found', 404, 'NOT_FOUND');
                return;
            }

            $books = $this->authorModel->findWithBooks($id);
            $author['books'] = array_filter($books, function($item) {
                return isset($item['book_id']);
            });

            $this->sendResponse($author, 200);
        } catch (Exception $e) {
            $this->sendError('Failed to fetch author', 500, 'SERVER_ERROR');
        }
    }

    public function create() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $this->authorModel->create($data);

            if (!$result['success']) {
                $this->sendError('Failed to create author', 400, 'VALIDATION_ERROR', $result['errors']);
                return;
            }

            $author = $this->authorModel->findById($result['id']);
            $this->sendResponse($author, 201);
        } catch (Exception $e) {
            $this->sendError('Failed to create author', 500, 'SERVER_ERROR');
        }
    }

    public function update($id) {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $this->authorModel->update($id, $data);

            if (!$result['success']) {
                $this->sendError('Failed to update author', 400, 'VALIDATION_ERROR', $result['errors']);
                return;
            }

            $author = $this->authorModel->findById($id);
            $this->sendResponse($author, 200);
        } catch (Exception $e) {
            $this->sendError('Failed to update author', 500, 'SERVER_ERROR');
        }
    }

    public function delete($id) {
        try {
            $result = $this->authorModel->delete($id);
            
            if ($result['affected_rows'] === 0) {
                $this->sendError('Author not found', 404, 'NOT_FOUND');
                return;
            }

            $this->sendResponse(['message' => 'Author deleted successfully'], 200);
        } catch (Exception $e) {
            $this->sendError('Failed to delete author', 500, 'SERVER_ERROR');
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
