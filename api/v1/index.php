<?php
/**
 * REST API v1 - Main Entry Point
 * Library Management System
 * 
 * This API provides complete CRUD operations for all resources
 * with strict authentication and authorization controls.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__, 2) . '/logs/error.log');

// Load dependencies
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/config/constants.php';
require_once dirname(__DIR__, 2) . '/src/middleware/CorsMiddleware.php';
require_once dirname(__DIR__, 2) . '/src/middleware/AuthMiddleware.php';
require_once __DIR__ . '/controllers/ApiAuthController.php';
require_once __DIR__ . '/controllers/ApiBookController.php';
require_once __DIR__ . '/controllers/ApiUserController.php';
require_once __DIR__ . '/controllers/ApiLoanController.php';
require_once __DIR__ . '/controllers/ApiCategoryController.php';
require_once __DIR__ . '/controllers/ApiAuthorController.php';
require_once __DIR__ . '/controllers/ApiReportController.php';

// Handle CORS
CorsMiddleware::handle();

// Set JSON response header
header('Content-Type: application/json; charset=utf-8');

// Get request details
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

// Parse the request path
$basePath = '/the-robboeb-library/api/v1';
$path = str_replace($basePath, '', parse_url($requestUri, PHP_URL_PATH));
$path = trim($path, '/');

// Split path into segments
$pathParts = explode('/', $path);
$resource = $pathParts[0] ?? '';
$id = $pathParts[1] ?? null;
$action = $pathParts[2] ?? null;

// Define public endpoints (no authentication required)
$publicEndpoints = [
    'auth/login',
    'auth/register',
    'books' => ['GET'],
    'books/search' => ['GET'],
    'categories' => ['GET'],
    'authors' => ['GET']
];

// Check if endpoint is public
$isPublic = false;
if ($resource === 'auth' && in_array($id, ['login', 'register'])) {
    $isPublic = true;
} elseif ($resource === 'books' && $requestMethod === 'GET' && (!$id || $id === 'search')) {
    $isPublic = true;
} elseif (in_array($resource, ['categories', 'authors']) && $requestMethod === 'GET' && !$action) {
    $isPublic = true;
}

try {
    // Apply authentication middleware for protected endpoints
    if (!$isPublic) {
        AuthMiddleware::handle();
    }

    // Route to appropriate controller
    switch ($resource) {
        case '':
            // API root - return API information
            echo json_encode([
                'success' => true,
                'data' => [
                    'name' => 'Library Management System API',
                    'version' => '1.0.0',
                    'endpoints' => [
                        'auth' => '/api/v1/auth',
                        'books' => '/api/v1/books',
                        'users' => '/api/v1/users',
                        'loans' => '/api/v1/loans',
                        'categories' => '/api/v1/categories',
                        'authors' => '/api/v1/authors',
                        'reports' => '/api/v1/reports'
                    ]
                ]
            ]);
            break;

        case 'auth':
            $controller = new ApiAuthController();
            $controller->route($requestMethod, $id, $action);
            break;

        case 'books':
            $controller = new ApiBookController();
            $controller->route($requestMethod, $id, $action);
            break;

        case 'users':
            $controller = new ApiUserController();
            $controller->route($requestMethod, $id, $action);
            break;

        case 'loans':
            $controller = new ApiLoanController();
            $controller->route($requestMethod, $id, $action);
            break;

        case 'categories':
            $controller = new ApiCategoryController();
            $controller->route($requestMethod, $id, $action);
            break;

        case 'authors':
            $controller = new ApiAuthorController();
            $controller->route($requestMethod, $id, $action);
            break;

        case 'reports':
            AuthMiddleware::handle(true); // Admin only
            $controller = new ApiReportController();
            $controller->route($requestMethod, $id, $action);
            break;

        default:
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'The requested resource does not exist',
                    'resource' => $resource
                ]
            ]);
            break;
    }

} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'INTERNAL_SERVER_ERROR',
            'message' => ENVIRONMENT === 'development' ? $e->getMessage() : 'An internal server error occurred'
        ]
    ]);
}
