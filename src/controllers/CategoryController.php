<?php
require_once dirname(__DIR__) . '/models/Category.php';

class CategoryController {
    private $categoryModel;

    public function __construct() {
        $this->categoryModel = new Category();
    }

    public function index() {
        try {
            $withCount = $_GET['with_count'] ?? false;
            
            if ($withCount) {
                $categories = $this->categoryModel->findWithBookCount();
            } else {
                $categories = $this->categoryModel->findAll();
            }

            $this->sendResponse($categories, 200);
        } catch (Exception $e) {
            $this->sendError('Failed to fetch categories', 500, 'SERVER_ERROR');
        }
    }

    public function show($id) {
        try {
            $category = $this->categoryModel->findById($id);
            
            if (!$category) {
                $this->sendError('Category not found', 404, 'NOT_FOUND');
                return;
            }

            $this->sendResponse($category, 200);
        } catch (Exception $e) {
            $this->sendError('Failed to fetch category', 500, 'SERVER_ERROR');
        }
    }

    public function create() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $this->categoryModel->create($data);

            if (!$result['success']) {
                $this->sendError('Failed to create category', 400, 'VALIDATION_ERROR', $result['errors']);
                return;
            }

            $category = $this->categoryModel->findById($result['id']);
            $this->sendResponse($category, 201);
        } catch (Exception $e) {
            $this->sendError('Failed to create category', 500, 'SERVER_ERROR');
        }
    }

    public function update($id) {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $this->categoryModel->update($id, $data);

            if (!$result['success']) {
                $this->sendError('Failed to update category', 400, 'VALIDATION_ERROR', $result['errors']);
                return;
            }

            $category = $this->categoryModel->findById($id);
            $this->sendResponse($category, 200);
        } catch (Exception $e) {
            $this->sendError('Failed to update category', 500, 'SERVER_ERROR');
        }
    }

    public function delete($id) {
        try {
            $result = $this->categoryModel->delete($id);
            
            if ($result['affected_rows'] === 0) {
                $this->sendError('Category not found', 404, 'NOT_FOUND');
                return;
            }

            $this->sendResponse(['message' => 'Category deleted successfully'], 200);
        } catch (Exception $e) {
            $this->sendError('Failed to delete category', 500, 'SERVER_ERROR');
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
