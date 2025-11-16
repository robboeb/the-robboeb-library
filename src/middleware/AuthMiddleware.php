<?php
require_once dirname(__DIR__) . '/services/AuthService.php';

class AuthMiddleware {
    
    public static function handle($requireAdmin = false) {
        AuthService::initSession();
        
        if (!AuthService::isAuthenticated()) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'AUTH_REQUIRED',
                    'message' => 'Authentication required'
                ]
            ]);
            exit;
        }
        
        if ($requireAdmin && !AuthService::checkPermission('admin')) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'PERMISSION_DENIED',
                    'message' => 'Admin permission required'
                ]
            ]);
            exit;
        }
        
        return true;
    }

    public static function checkAuth() {
        AuthService::initSession();
        return AuthService::isAuthenticated();
    }

    public static function checkAdmin() {
        AuthService::initSession();
        return AuthService::checkPermission('admin');
    }
}
