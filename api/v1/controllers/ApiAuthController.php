<?php
/**
 * API Authentication Controller
 * Handles user authentication, registration, and session management
 */

require_once __DIR__ . '/BaseApiController.php';
require_once dirname(__DIR__, 3) . '/src/models/User.php';
require_once dirname(__DIR__, 3) . '/src/services/AuthService.php';
require_once dirname(__DIR__, 3) . '/src/services/ValidationService.php';

class ApiAuthController extends BaseApiController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function route($method, $id, $action) {
        switch ($id) {
            case 'login':
                if ($method === 'POST') {
                    $this->login();
                } else {
                    $this->methodNotAllowed();
                }
                break;

            case 'register':
                if ($method === 'POST') {
                    $this->register();
                } else {
                    $this->methodNotAllowed();
                }
                break;

            case 'logout':
                if ($method === 'POST' || $method === 'GET') {
                    $this->logout();
                } else {
                    $this->methodNotAllowed();
                }
                break;

            case 'me':
            case 'current':
                if ($method === 'GET') {
                    $this->getCurrentUserInfo();
                } else {
                    $this->methodNotAllowed();
                }
                break;

            case 'refresh':
                if ($method === 'POST') {
                    $this->refreshSession();
                } else {
                    $this->methodNotAllowed();
                }
                break;

            default:
                $this->notFound('Endpoint');
                break;
        }
    }

    /**
     * User login
     * POST /api/v1/auth/login
     */
    private function login() {
        try {
            $data = $this->getJsonInput();
            
            // Validate required fields
            $this->validateRequired($data, ['email', 'password']);
            
            // Sanitize input
            $email = $this->sanitizeInput($data['email']);
            $password = $data['password']; // Don't sanitize password
            
            // Validate email format
            $emailValidation = ValidationService::validateEmail($email);
            if (!$emailValidation['valid']) {
                $this->sendError($emailValidation['message'], 400, 'VALIDATION_ERROR');
            }
            
            // Authenticate user
            $user = $this->userModel->authenticate($email, $password);
            
            if (!$user) {
                $this->sendError('Invalid email or password', 401, 'AUTH_FAILED');
            }
            
            // Check if account is active
            if ($user['status'] !== 'active') {
                $this->sendError('Account is inactive. Please contact administrator.', 403, 'ACCOUNT_INACTIVE');
            }
            
            // Create session
            AuthService::createSession($user);
            
            // Remove sensitive data
            unset($user['password']);
            
            $this->sendResponse([
                'user' => $user,
                'session' => [
                    'expires_in' => SESSION_TIMEOUT,
                    'created_at' => date(DATETIME_FORMAT)
                ]
            ], 200, 'Login successful');
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $this->sendError('Login failed', 500, 'SERVER_ERROR');
        }
    }

    /**
     * User registration
     * POST /api/v1/auth/register
     */
    private function register() {
        try {
            $data = $this->getJsonInput();
            
            // Validate required fields
            $this->validateRequired($data, ['email', 'password', 'first_name', 'last_name']);
            
            // Sanitize input
            $data = $this->sanitizeInput($data);
            
            // Set default values
            $data['user_type'] = $data['user_type'] ?? 'patron';
            $data['status'] = 'active';
            
            // Validate user type
            if (!in_array($data['user_type'], ['patron', 'admin'])) {
                $this->sendError('Invalid user type', 400, 'VALIDATION_ERROR');
            }
            
            // DEVELOPMENT MODE: Allow admin registration
            // TODO: In production, uncomment the lines below to restrict admin registration
            // if ($data['user_type'] === 'admin') {
            //     $this->sendError('Cannot register as admin via API', 403, 'PERMISSION_DENIED');
            // }
            
            // Create user
            $result = $this->userModel->create($data);
            
            if (!$result['success']) {
                $this->sendError('Registration failed', 400, 'VALIDATION_ERROR', $result['errors']);
            }
            
            // Get created user
            $user = $this->userModel->findById($result['id']);
            unset($user['password']);
            
            // Create session
            AuthService::createSession($user);
            
            $this->sendResponse([
                'user' => $user,
                'session' => [
                    'expires_in' => SESSION_TIMEOUT,
                    'created_at' => date(DATETIME_FORMAT)
                ]
            ], 201, 'Registration successful');
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $this->sendError('Registration failed', 500, 'SERVER_ERROR');
        }
    }

    /**
     * User logout
     * POST /api/v1/auth/logout
     */
    private function logout() {
        try {
            AuthService::destroySession();
            $this->sendResponse([], 200, 'Logout successful');
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            $this->sendError('Logout failed', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Get current user information
     * GET /api/v1/auth/me
     */
    private function getCurrentUserInfo() {
        try {
            $user = $this->getCurrentUser();
            
            if (!$user) {
                $this->sendError('Not authenticated', 401, 'AUTH_REQUIRED');
            }
            
            // Get full user details
            $fullUser = $this->userModel->findById($user['user_id']);
            
            if (!$fullUser) {
                $this->sendError('User not found', 404, 'NOT_FOUND');
            }
            
            unset($fullUser['password']);
            
            $this->sendResponse($fullUser, 200);
            
        } catch (Exception $e) {
            error_log("Get current user error: " . $e->getMessage());
            $this->sendError('Failed to get user information', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Refresh session
     * POST /api/v1/auth/refresh
     */
    private function refreshSession() {
        try {
            $user = $this->getCurrentUser();
            
            if (!$user) {
                $this->sendError('Not authenticated', 401, 'AUTH_REQUIRED');
            }
            
            // Get fresh user data
            $freshUser = $this->userModel->findById($user['user_id']);
            
            if (!$freshUser || $freshUser['status'] !== 'active') {
                AuthService::destroySession();
                $this->sendError('Session invalid', 401, 'SESSION_INVALID');
            }
            
            // Refresh session
            AuthService::createSession($freshUser);
            
            unset($freshUser['password']);
            
            $this->sendResponse([
                'user' => $freshUser,
                'session' => [
                    'expires_in' => SESSION_TIMEOUT,
                    'refreshed_at' => date(DATETIME_FORMAT)
                ]
            ], 200, 'Session refreshed');
            
        } catch (Exception $e) {
            error_log("Refresh session error: " . $e->getMessage());
            $this->sendError('Failed to refresh session', 500, 'SERVER_ERROR');
        }
    }
}
