<?php
require_once dirname(__DIR__, 2) . '/config/constants.php';

class AuthService {
    
    public static function createSession($user) {
        if (session_status() === PHP_SESSION_NONE) {
            // Set session cookie parameters to expire when browser closes
            session_set_cookie_params([
                'lifetime' => 0, // Expire when browser closes
                'path' => '/',
                'domain' => '',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_name(SESSION_NAME);
            session_start();
        }
        
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['last_activity'] = time();
        $_SESSION['created_at'] = time();
        $_SESSION['logged_in'] = true;
        
        return true;
    }

    public static function destroySession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
        }
        
        // Clear all session variables
        $_SESSION = array();
        
        // Delete all session cookies
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Clear custom session cookie
        if (isset($_COOKIE[SESSION_NAME])) {
            setcookie(SESSION_NAME, '', time() - 42000, '/', '', false, true);
            unset($_COOKIE[SESSION_NAME]);
        }
        
        // Clear PHP session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 42000, '/', '', false, true);
            unset($_COOKIE[session_name()]);
        }
        
        // Destroy the session completely
        session_unset();
        session_destroy();
        
        return true;
    }

    public static function isAuthenticated() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
        }
        
        // Check if user_id and logged_in flag exist
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return false;
        }
        
        // Check if session has expired
        if (self::isSessionExpired()) {
            self::destroySession();
            return false;
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = time();
        return true;
    }

    public static function checkPermission($requiredType) {
        if (!self::isAuthenticated()) {
            return false;
        }
        
        if ($requiredType === 'admin' && $_SESSION['user_type'] !== 'admin') {
            return false;
        }
        
        return true;
    }

    public static function getCurrentUser() {
        if (!self::isAuthenticated()) {
            return null;
        }
        
        return [
            'user_id' => $_SESSION['user_id'],
            'email' => $_SESSION['email'],
            'user_type' => $_SESSION['user_type'],
            'first_name' => $_SESSION['first_name'],
            'last_name' => $_SESSION['last_name']
        ];
    }

    public static function isSessionExpired() {
        if (!isset($_SESSION['last_activity'])) {
            return true;
        }
        
        $inactive = time() - $_SESSION['last_activity'];
        
        if ($inactive > SESSION_TIMEOUT) {
            return true;
        }
        
        return false;
    }

    public static function requireAuth() {
        if (!self::isAuthenticated()) {
            header('Location: ' . BASE_URL . '/public/login.php');
            exit;
        }
    }

    public static function requireAdmin() {
        if (!self::checkPermission('admin')) {
            header('Location: ' . BASE_URL . '/public/login.php');
            exit;
        }
    }

    public static function initSession() {
        if (session_status() === PHP_SESSION_NONE) {
            // Set session cookie parameters - expire when browser closes
            session_set_cookie_params([
                'lifetime' => 0, // Session expires when browser closes
                'path' => '/',
                'domain' => '',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            
            session_name(SESSION_NAME);
            session_start();
            
            // Regenerate session ID to prevent fixation
            if (!isset($_SESSION['initiated'])) {
                session_regenerate_id(true);
                $_SESSION['initiated'] = true;
            }
        }
    }
}
