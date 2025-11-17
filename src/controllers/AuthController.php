<?php
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/services/AuthService.php';
require_once dirname(__DIR__) . '/services/ValidationService.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['email']) || empty($data['password'])) {
                $this->sendError('Email and password are required', 400, 'VALIDATION_ERROR');
                return;
            }

            $user = $this->userModel->authenticate($data['email'], $data['password']);

            if (!$user) {
                $this->sendError('Invalid email or password', 401, 'AUTH_FAILED');
                return;
            }

            if ($user['status'] !== 'active') {
                $this->sendError('Account is inactive', 403, 'PERMISSION_DENIED');
                return;
            }

            AuthService::createSession($user);

            $this->sendResponse([
                'user' => $user,
                'message' => 'Login successful'
            ], 200);
        } catch (Exception $e) {
            $this->sendError('Login failed', 500, 'SERVER_ERROR');
        }
    }

    public function register() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            $data['user_type'] = $data['user_type'] ?? 'patron';
            $data['status'] = 'active';

            $result = $this->userModel->create($data);

            if (!$result['success']) {
                $this->sendError('Registration failed', 400, 'VALIDATION_ERROR', $result['errors']);
                return;
            }

            $user = $this->userModel->findById($result['id']);
            AuthService::createSession($user);

            $this->sendResponse([
                'user' => $user,
                'message' => 'Registration successful'
            ], 201);
        } catch (Exception $e) {
            $this->sendError('Registration failed', 500, 'SERVER_ERROR');
        }
    }

    public function logout() {
        try {
            AuthService::destroySession();
            
            // Send response with redirect instruction
            $this->sendResponse([
                'message' => 'Logout successful',
                'redirect' => '/the-robboeb-library/public/home.php'
            ], 200);
        } catch (Exception $e) {
            $this->sendError('Logout failed', 500, 'SERVER_ERROR');
        }
    }

    public function getCurrentUser() {
        try {
            $user = AuthService::getCurrentUser();
            
            if (!$user) {
                $this->sendError('Not authenticated', 401, 'AUTH_REQUIRED');
                return;
            }

            $this->sendResponse(['user' => $user], 200);
        } catch (Exception $e) {
            $this->sendError('Failed to get user', 500, 'SERVER_ERROR');
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
