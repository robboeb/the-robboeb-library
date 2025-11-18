<?php
/**
 * API Category Controller
 * Handles category management operations
 */

require_once __DIR__ . '/BaseApiController.php';
require_once dirname(__DIR__, 3) . '/src/models/Category.php';

class ApiCategoryController extends BaseApiController {
    private $categoryModel;

    public function __construct() {
        $this->categoryModel = new Category();
    }

    public function route($method, $id, $action) {
        // GET /api/v1/categories - List categories (public)
        if ($method === 'GET' && !$id) {
            $this->index();
        }
        // GET /api/v1/categories/{id} - Get category (public)
        elseif ($method === 'GET' && $id) {
            $this->show($id);
        }
        // POST /api/v1/categories - Create category (admin only)
        elseif ($method === 'POST' && !$id) {
            $this->requireAdmin();
            $this->create();
        }
        // PUT /api/v1/categories/{id} - Update category (admin only)
        elseif ($method === 'PUT' && $id) {
            $this->requireAdmin();
            $this->update($id);
        }
        // DELETE /api/v1/categories/{id} - Delete category (admin only)
        elseif ($method === 'DELETE' && $id) {
            $this->requireAdmin();
            $this->delete($id);
        }
        else {
            $this->methodNotAllowed();
        }
    }

    /**
     * List all categories
     * GET /api/v1/categories?with_count=true
     */
    private function index() {
        try {
            $params = $this->getQueryParams();
            $withCount = isset($params['with_count']) && ($params['with_count'] === 'true' || $params['with_count'] === '1');
            
            if ($withCount) {
                $categories = $this->categoryModel->findWithBookCount();
            } else {
                $categories = $this->categoryModel->findAll();
            }
            
            $this->sendResponse([
                'categories' => $categories,
                'count' => count($categories)
            ], 200);
            
        } catch (Exception $e) {
            error_log("Category index error: " . $e->getMessage());
            $this->sendError('Failed to fetch categories', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Get single category
     * GET /api/v1/categories/{id}
     */
    private function show($id) {
        try {
            if (!is_numeric($id)) {
                $this->sendError('Invalid category ID', 400, 'VALIDATION_ERROR');
            }
            
            $category = $this->categoryModel->findById($id);
            
            if (!$category) {
                $this->notFound('Category');
            }
            
            // Get book count for this category
            $sql = "SELECT COUNT(*) as book_count FROM books WHERE category_id = :category_id";
            $stmt = $this->categoryModel->db->prepare($sql);
            $stmt->bindValue(':category_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $category['book_count'] = (int)$result['book_count'];
            
            $this->sendResponse($category, 200);
            
        } catch (Exception $e) {
            error_log("Category show error: " . $e->getMessage());
            $this->sendError('Failed to fetch category', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Create new category
     * POST /api/v1/categories
     */
    private function create() {
        try {
            $data = $this->getJsonInput();
            
            // Validate required fields
            $this->validateRequired($data, ['category_name']);
            
            // Sanitize input
            $data = $this->sanitizeInput($data);
            
            // Create category
            $result = $this->categoryModel->create($data);
            
            if (!$result['success']) {
                $this->sendError('Failed to create category', 400, 'VALIDATION_ERROR', $result['errors']);
            }
            
            // Get created category
            $category = $this->categoryModel->findById($result['id']);
            
            $this->sendResponse($category, 201, 'Category created successfully');
            
        } catch (Exception $e) {
            error_log("Category create error: " . $e->getMessage());
            $this->sendError('Failed to create category', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Update category
     * PUT /api/v1/categories/{id}
     */
    private function update($id) {
        try {
            if (!is_numeric($id)) {
                $this->sendError('Invalid category ID', 400, 'VALIDATION_ERROR');
            }
            
            // Check if category exists
            $existingCategory = $this->categoryModel->findById($id);
            if (!$existingCategory) {
                $this->notFound('Category');
            }
            
            $data = $this->getJsonInput();
            
            // Sanitize input
            $data = $this->sanitizeInput($data);
            
            // Update category
            $result = $this->categoryModel->update($id, $data);
            
            if (!$result['success']) {
                $this->sendError('Failed to update category', 400, 'VALIDATION_ERROR', $result['errors']);
            }
            
            // Get updated category
            $category = $this->categoryModel->findById($id);
            
            $this->sendResponse($category, 200, 'Category updated successfully');
            
        } catch (Exception $e) {
            error_log("Category update error: " . $e->getMessage());
            $this->sendError('Failed to update category', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Delete category
     * DELETE /api/v1/categories/{id}
     */
    private function delete($id) {
        try {
            if (!is_numeric($id)) {
                $this->sendError('Invalid category ID', 400, 'VALIDATION_ERROR');
            }
            
            // Check if category exists
            $category = $this->categoryModel->findById($id);
            if (!$category) {
                $this->notFound('Category');
            }
            
            // Check if category has books
            $sql = "SELECT COUNT(*) as count FROM books WHERE category_id = :category_id";
            $stmt = $this->categoryModel->db->prepare($sql);
            $stmt->bindValue(':category_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $bookCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($bookCount > 0) {
                $this->sendError('Cannot delete category with existing books', 400, 'VALIDATION_ERROR');
            }
            
            // Delete category
            $result = $this->categoryModel->delete($id);
            
            $this->sendResponse([], 200, 'Category deleted successfully');
            
        } catch (Exception $e) {
            error_log("Category delete error: " . $e->getMessage());
            $this->sendError('Failed to delete category', 500, 'SERVER_ERROR');
        }
    }
}
