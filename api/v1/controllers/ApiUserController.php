<?php
/**
 * API User Controller
 * Handles user management operations with strict access controls
 */

require_once __DIR__ . '/BaseApiController.php';
require_once dirname(__DIR__, 3) . '/src/models/User.php';

class ApiUserController extends BaseApiController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function route($method, $id, $action) {
        // All user endpoints require authentication
        $this->requireAuth();
        
        // GET /api/v1/users - List users (admin only)
        if ($method === 'GET' && !$id) {
            $this->requireAdmin();
            $this->index();
        }
        // GET /api/v1/users/{id} - Get user (admin or own profile)
        elseif ($method === 'GET' && $id) {
            $this->show($id);
        }
        // POST /api/v1/users - Create user (admin only)
        elseif ($method === 'POST' && !$id) {
            $this->requireAdmin();
            $this->create();
        }
        // PUT /api/v1/users/{id} - Update user (admin or own profile)
        elseif ($method === 'PUT' && $id) {
            $this->update($id);
        }
        // DELETE /api/v1/users/{id} - Deactivate user (admin only)
        elseif ($method === 'DELETE' && $id) {
            $this->requireAdmin();
            $this->delete($id);
        }
        // PUT /api/v1/users/{id}/password - Change password
        elseif ($method === 'PUT' && $id && $action === 'password') {
            $this->changePassword($id);
        }
        // PUT /api/v1/users/{id}/activate - Activate user (admin only)
        elseif ($method === 'PUT' && $id && $action === 'activate') {
            $this->requireAdmin();
            $this->activate($id);
        }
        else {
            $this->methodNotAllowed();
        }
    }

    /**
     * List all users with filtering
     * GET /api/v1/users?user_type=patron&status=active&limit=10&offset=0
     */
    private function index() {
        try {
            $params = $this->getQueryParams();
            $limit = isset($params['limit']) ? (int)$params['limit'] : DEFAULT_PAGE_SIZE;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            $userType = $params['user_type'] ?? null;
            $status = $params['status'] ?? null;
            
            // Build query
            $sql = "SELECT user_id, email, first_name, last_name, phone, address, 
                           user_type, status, created_at, updated_at 
                    FROM users WHERE 1=1";
            
            $bindParams = [];
            
            if ($userType) {
                $sql .= " AND user_type = :user_type";
                $bindParams[':user_type'] = $userType;
            }
            
            if ($status) {
                $sql .= " AND status = :status";
                $bindParams[':status'] = $status;
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->userModel->db->prepare($sql);
            
            foreach ($bindParams as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count
            $countSql = "SELECT COUNT(*) as total FROM users WHERE 1=1";
            if ($userType) {
                $countSql .= " AND user_type = :user_type";
            }
            if ($status) {
                $countSql .= " AND status = :status";
            }
            
            $countStmt = $this->userModel->db->prepare($countSql);
            foreach ($bindParams as $key => $value) {
                $countStmt->bindValue($key, $value);
            }
            $countStmt->execute();
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $this->sendResponse([
                'users' => $users,
                'pagination' => [
                    'total' => (int)$total,
                    'limit' => $limit,
                    'offset' => $offset,
                    'count' => count($users)
                ]
            ], 200);
            
        } catch (Exception $e) {
            error_log("User index error: " . $e->getMessage());
            $this->sendError('Failed to fetch users', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Get single user
     * GET /api/v1/users/{id}
     */
    private function show($id) {
        try {
            if (!is_numeric($id)) {
                $this->sendError('Invalid user ID', 400, 'VALIDATION_ERROR');
            }
            
            $currentUser = $this->getCurrentUser();
            
            // Patrons can only view their own profile
            if ($currentUser['user_type'] === 'patron' && $id != $currentUser['user_id']) {
                $this->sendError('You can only view your own profile', 403, 'PERMISSION_DENIED');
            }
            
            $user = $this->userModel->findById($id);
            
            if (!$user) {
                $this->notFound('User');
            }
            
            // Remove password
            unset($user['password']);
            
            // Get user statistics
            $sql = "SELECT 
                    COUNT(CASE WHEN return_date IS NULL THEN 1 END) as active_loans,
                    COUNT(CASE WHEN return_date IS NOT NULL THEN 1 END) as completed_loans,
                    COUNT(CASE WHEN return_date IS NULL AND due_date < CURDATE() THEN 1 END) as overdue_loans
                    FROM loans WHERE user_id = :user_id";
            $stmt = $this->userModel->db->prepare($sql);
            $stmt->bindValue(':user_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $user['statistics'] = $stats;
            
            $this->sendResponse($user, 200);
            
        } catch (Exception $e) {
            error_log("User show error: " . $e->getMessage());
            $this->sendError('Failed to fetch user', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Create new user
     * POST /api/v1/users
     */
    private function create() {
        try {
            $data = $this->getJsonInput();
            
            // Validate required fields
            $this->validateRequired($data, ['email', 'password', 'first_name', 'last_name', 'user_type']);
            
            // Sanitize input
            $data = $this->sanitizeInput($data);
            
            // Set default status
            $data['status'] = $data['status'] ?? 'active';
            
            // Validate user type
            if (!in_array($data['user_type'], ['patron', 'admin'])) {
                $this->sendError('Invalid user type', 400, 'VALIDATION_ERROR');
            }
            
            // Create user
            $result = $this->userModel->create($data);
            
            if (!$result['success']) {
                $this->sendError('Failed to create user', 400, 'VALIDATION_ERROR', $result['errors']);
            }
            
            // Get created user
            $user = $this->userModel->findById($result['id']);
            unset($user['password']);
            
            $this->sendResponse($user, 201, 'User created successfully');
            
        } catch (Exception $e) {
            error_log("User create error: " . $e->getMessage());
            $this->sendError('Failed to create user', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Update user
     * PUT /api/v1/users/{id}
     */
    private function update($id) {
        try {
            if (!is_numeric($id)) {
                $this->sendError('Invalid user ID', 400, 'VALIDATION_ERROR');
            }
            
            $currentUser = $this->getCurrentUser();
            
            // Patrons can only update their own profile
            if ($currentUser['user_type'] === 'patron' && $id != $currentUser['user_id']) {
                $this->sendError('You can only update your own profile', 403, 'PERMISSION_DENIED');
            }
            
            // Check if user exists
            $existingUser = $this->userModel->findById($id);
            if (!$existingUser) {
                $this->notFound('User');
            }
            
            $data = $this->getJsonInput();
            
            // Patrons cannot change their user_type or status
            if ($currentUser['user_type'] === 'patron') {
                unset($data['user_type'], $data['status']);
            }
            
            // Password should be changed via separate endpoint
            unset($data['password']);
            
            // Sanitize input
            $data = $this->sanitizeInput($data);
            
            // Update user
            $result = $this->userModel->update($id, $data);
            
            if (!$result['success']) {
                $this->sendError('Failed to update user', 400, 'VALIDATION_ERROR', $result['errors']);
            }
            
            // Get updated user
            $user = $this->userModel->findById($id);
            unset($user['password']);
            
            $this->sendResponse($user, 200, 'User updated successfully');
            
        } catch (Exception $e) {
            error_log("User update error: " . $e->getMessage());
            $this->sendError('Failed to update user', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Deactivate user
     * DELETE /api/v1/users/{id}
     */
    private function delete($id) {
        try {
            if (!is_numeric($id)) {
                $this->sendError('Invalid user ID', 400, 'VALIDATION_ERROR');
            }
            
            $currentUser = $this->getCurrentUser();
            
            // Cannot delete own account
            if ($id == $currentUser['user_id']) {
                $this->sendError('Cannot deactivate your own account', 400, 'VALIDATION_ERROR');
            }
            
            // Check if user exists
            $user = $this->userModel->findById($id);
            if (!$user) {
                $this->notFound('User');
            }
            
            // Deactivate user
            $result = $this->userModel->deactivate($id);
            
            $this->sendResponse([], 200, 'User deactivated successfully');
            
        } catch (Exception $e) {
            error_log("User delete error: " . $e->getMessage());
            $this->sendError('Failed to deactivate user', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Change user password
     * PUT /api/v1/users/{id}/password
     */
    private function changePassword($id) {
        try {
            if (!is_numeric($id)) {
                $this->sendError('Invalid user ID', 400, 'VALIDATION_ERROR');
            }
            
            $currentUser = $this->getCurrentUser();
            
            // Users can only change their own password (unless admin)
            if ($currentUser['user_type'] === 'patron' && $id != $currentUser['user_id']) {
                $this->sendError('You can only change your own password', 403, 'PERMISSION_DENIED');
            }
            
            $data = $this->getJsonInput();
            
            // Validate required fields
            $requiredFields = ['new_password'];
            if ($currentUser['user_type'] === 'patron' || $id == $currentUser['user_id']) {
                $requiredFields[] = 'current_password';
            }
            $this->validateRequired($data, $requiredFields);
            
            // Verify current password if required
            if (isset($data['current_password'])) {
                $user = $this->userModel->findById($id);
                if (!password_verify($data['current_password'], $user['password'])) {
                    $this->sendError('Current password is incorrect', 400, 'VALIDATION_ERROR');
                }
            }
            
            // Validate new password length
            if (strlen($data['new_password']) < MIN_PASSWORD_LENGTH) {
                $this->sendError('Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters', 400, 'VALIDATION_ERROR');
            }
            
            // Update password
            $hashedPassword = password_hash($data['new_password'], PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = :password, updated_at = NOW() WHERE user_id = :user_id";
            $stmt = $this->userModel->db->prepare($sql);
            $stmt->bindValue(':password', $hashedPassword);
            $stmt->bindValue(':user_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $this->sendResponse([], 200, 'Password changed successfully');
            
        } catch (Exception $e) {
            error_log("Change password error: " . $e->getMessage());
            $this->sendError('Failed to change password', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Activate user
     * PUT /api/v1/users/{id}/activate
     */
    private function activate($id) {
        try {
            if (!is_numeric($id)) {
                $this->sendError('Invalid user ID', 400, 'VALIDATION_ERROR');
            }
            
            // Check if user exists
            $user = $this->userModel->findById($id);
            if (!$user) {
                $this->notFound('User');
            }
            
            // Activate user
            $sql = "UPDATE users SET status = 'active', updated_at = NOW() WHERE user_id = :user_id";
            $stmt = $this->userModel->db->prepare($sql);
            $stmt->bindValue(':user_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Get updated user
            $user = $this->userModel->findById($id);
            unset($user['password']);
            
            $this->sendResponse($user, 200, 'User activated successfully');
            
        } catch (Exception $e) {
            error_log("User activate error: " . $e->getMessage());
            $this->sendError('Failed to activate user', 500, 'SERVER_ERROR');
        }
    }
}
