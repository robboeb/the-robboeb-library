<?php
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/services/AuthService.php';

class UserController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function index() {
        try {
            $limit = $_GET['limit'] ?? DEFAULT_PAGE_SIZE;
            $offset = $_GET['offset'] ?? 0;
            $userType = $_GET['user_type'] ?? null;
            $status = $_GET['status'] ?? null;

            if ($userType) {
                $users = $this->userModel->findByType($userType, $limit, $offset);
            } else {
                $users = $this->userModel->findAll($limit, $offset);
            }

            if ($status) {
                $users = array_filter($users, function($user) use ($status) {
                    return $user['status'] === $status;
                });
            }

            $this->sendResponse(array_values($users), 200);
        } catch (Exception $e) {
            $this->sendError('Failed to fetch users', 500, 'SERVER_ERROR');
        }
    }

    public function show($id) {
        try {
            // Get current user for ownership verification
            $currentUser = AuthService::getCurrentUser();
            
            // Patron users can only view their own profile
            if ($currentUser && $currentUser['user_type'] === 'patron' && $id != $currentUser['user_id']) {
                $this->sendError('You can only view your own profile', 403, 'PERMISSION_DENIED');
                return;
            }
            
            $user = $this->userModel->findById($id);
            
            if (!$user) {
                $this->sendError('User not found', 404, 'NOT_FOUND');
                return;
            }

            $this->sendResponse($user, 200);
        } catch (Exception $e) {
            $this->sendError('Failed to fetch user', 500, 'SERVER_ERROR');
        }
    }

    public function create() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $this->userModel->create($data);

            if (!$result['success']) {
                $this->sendError('Failed to create user', 400, 'VALIDATION_ERROR', $result['errors']);
                return;
            }

            $user = $this->userModel->findById($result['id']);
            $this->sendResponse($user, 201);
        } catch (Exception $e) {
            $this->sendError('Failed to create user', 500, 'SERVER_ERROR');
        }
    }

    public function update($id) {
        try {
            // Get current user for ownership verification
            $currentUser = AuthService::getCurrentUser();
            
            // Patron users can only update their own profile
            if ($currentUser && $currentUser['user_type'] === 'patron' && $id != $currentUser['user_id']) {
                $this->sendError('You can only update your own profile', 403, 'PERMISSION_DENIED');
                return;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Patron users cannot change their user_type
            if ($currentUser && $currentUser['user_type'] === 'patron' && isset($data['user_type'])) {
                unset($data['user_type']);
            }
            
            // Remove password from update data (use separate endpoint for password changes)
            unset($data['password']);
            
            $result = $this->userModel->update($id, $data);

            if (!$result['success']) {
                $this->sendError('Failed to update user', 400, 'VALIDATION_ERROR', $result['errors']);
                return;
            }

            $user = $this->userModel->findById($id);
            $this->sendResponse($user, 200);
        } catch (Exception $e) {
            $this->sendError('Failed to update user', 500, 'SERVER_ERROR');
        }
    }

    public function delete($id) {
        try {
            $result = $this->userModel->deactivate($id);
            $this->sendResponse(['message' => 'User deactivated successfully'], 200);
        } catch (Exception $e) {
            $this->sendError('Failed to deactivate user', 500, 'SERVER_ERROR');
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
