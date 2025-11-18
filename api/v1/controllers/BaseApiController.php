<?php
/**
 * Base API Controller
 * Provides common functionality for all API controllers
 */

abstract class BaseApiController {
    
    /**
     * Route the request to the appropriate method
     */
    abstract public function route($method, $id, $action);
    
    /**
     * Send successful JSON response
     */
    protected function sendResponse($data, $statusCode = 200, $message = null) {
        http_response_code($statusCode);
        $response = ['success' => true];
        
        if ($message) {
            $response['message'] = $message;
        }
        
        $response['data'] = $data;
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    /**
     * Send error JSON response
     */
    protected function sendError($message, $statusCode = 400, $code = 'ERROR', $details = null) {
        http_response_code($statusCode);
        $error = [
            'code' => $code,
            'message' => $message
        ];
        
        if ($details !== null) {
            $error['details'] = $details;
        }
        
        echo json_encode([
            'success' => false,
            'error' => $error
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    /**
     * Get JSON input from request body
     */
    protected function getJsonInput() {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendError('Invalid JSON in request body', 400, 'INVALID_JSON');
        }
        
        return $data ?? [];
    }
    
    /**
     * Get query parameters
     */
    protected function getQueryParams() {
        return $_GET;
    }
    
    /**
     * Validate required fields
     */
    protected function validateRequired($data, $requiredFields) {
        $missing = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            $this->sendError(
                'Missing required fields',
                400,
                'VALIDATION_ERROR',
                ['missing_fields' => $missing]
            );
        }
        
        return true;
    }
    
    /**
     * Sanitize input data
     */
    protected function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        
        return is_string($data) ? htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8') : $data;
    }
    
    /**
     * Check if user is authenticated
     */
    protected function requireAuth() {
        if (!AuthService::isAuthenticated()) {
            $this->sendError('Authentication required', 401, 'AUTH_REQUIRED');
        }
    }
    
    /**
     * Check if user is admin
     */
    protected function requireAdmin() {
        if (!AuthService::checkPermission('admin')) {
            $this->sendError('Admin permission required', 403, 'PERMISSION_DENIED');
        }
    }
    
    /**
     * Get current authenticated user
     */
    protected function getCurrentUser() {
        return AuthService::getCurrentUser();
    }
    
    /**
     * Check if current user owns the resource
     */
    protected function checkOwnership($resourceUserId) {
        $currentUser = $this->getCurrentUser();
        
        if (!$currentUser) {
            $this->sendError('Authentication required', 401, 'AUTH_REQUIRED');
        }
        
        // Admin can access all resources
        if ($currentUser['user_type'] === 'admin') {
            return true;
        }
        
        // Check if user owns the resource
        if ($currentUser['user_id'] != $resourceUserId) {
            $this->sendError('You can only access your own resources', 403, 'PERMISSION_DENIED');
        }
        
        return true;
    }
    
    /**
     * Handle method not allowed
     */
    protected function methodNotAllowed() {
        $this->sendError('Method not allowed', 405, 'METHOD_NOT_ALLOWED');
    }
    
    /**
     * Handle not found
     */
    protected function notFound($resource = 'Resource') {
        $this->sendError("{$resource} not found", 404, 'NOT_FOUND');
    }
}
