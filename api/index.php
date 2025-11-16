<?php
require_once __DIR__ . '/../src/middleware/CorsMiddleware.php';
require_once __DIR__ . '/../src/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';
require_once __DIR__ . '/../src/controllers/BookController.php';
require_once __DIR__ . '/../src/controllers/UserController.php';
require_once __DIR__ . '/../src/controllers/LoanController.php';
require_once __DIR__ . '/../src/controllers/CategoryController.php';
require_once __DIR__ . '/../src/controllers/AuthorController.php';

CorsMiddleware::handle();

header('Content-Type: application/json');

$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

$basePath = '/library-pro/api';
$path = str_replace($basePath, '', parse_url($requestUri, PHP_URL_PATH));
$path = trim($path, '/');

$pathParts = explode('/', $path);
$resource = $pathParts[0] ?? '';
$id = $pathParts[1] ?? null;
$action = $pathParts[2] ?? null;

$publicEndpoints = ['auth/login', 'auth/register'];
$isPublic = in_array($path, $publicEndpoints) || $path === 'auth/login' || $path === 'auth/register';

try {
    if (!$isPublic) {
        AuthMiddleware::handle();
    }

    switch ($resource) {
        case 'auth':
            $controller = new AuthController();
            if ($id === 'login' && $requestMethod === 'POST') {
                $controller->login();
            } elseif ($id === 'register' && $requestMethod === 'POST') {
                $controller->register();
            } elseif ($id === 'logout' && $requestMethod === 'POST') {
                $controller->logout();
            } elseif ($id === 'current' && $requestMethod === 'GET') {
                $controller->getCurrentUser();
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Endpoint not found']]);
            }
            break;

        case 'books':
            $controller = new BookController();
            if ($requestMethod === 'GET' && !$id) {
                $controller->index();
            } elseif ($requestMethod === 'GET' && $id === 'search') {
                $controller->search();
            } elseif ($requestMethod === 'GET' && $id) {
                $controller->show($id);
            } elseif ($requestMethod === 'POST' && !$id) {
                AuthMiddleware::handle(true);
                $controller->create();
            } elseif ($requestMethod === 'PUT' && $id) {
                AuthMiddleware::handle(true);
                $controller->update($id);
            } elseif ($requestMethod === 'DELETE' && $id) {
                AuthMiddleware::handle(true);
                $controller->delete($id);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Endpoint not found']]);
            }
            break;

        case 'users':
            AuthMiddleware::handle(true);
            $controller = new UserController();
            if ($requestMethod === 'GET' && !$id) {
                $controller->index();
            } elseif ($requestMethod === 'GET' && $id) {
                $controller->show($id);
            } elseif ($requestMethod === 'POST' && !$id) {
                $controller->create();
            } elseif ($requestMethod === 'PUT' && $id) {
                $controller->update($id);
            } elseif ($requestMethod === 'DELETE' && $id) {
                $controller->delete($id);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Endpoint not found']]);
            }
            break;

        case 'loans':
            $controller = new LoanController();
            if ($requestMethod === 'GET' && !$id) {
                $controller->index();
            } elseif ($requestMethod === 'GET' && $id === 'overdue') {
                AuthMiddleware::handle(true);
                $controller->findOverdue();
            } elseif ($requestMethod === 'GET' && $id) {
                $controller->show($id);
            } elseif ($requestMethod === 'POST' && $id === 'checkout') {
                AuthMiddleware::handle(true);
                $controller->checkout();
            } elseif ($requestMethod === 'POST' && $id && $action === 'return') {
                AuthMiddleware::handle(true);
                $controller->returnBook($id);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Endpoint not found']]);
            }
            break;

        case 'categories':
            $controller = new CategoryController();
            if ($requestMethod === 'GET' && !$id) {
                $controller->index();
            } elseif ($requestMethod === 'GET' && $id) {
                $controller->show($id);
            } elseif ($requestMethod === 'POST' && !$id) {
                AuthMiddleware::handle(true);
                $controller->create();
            } elseif ($requestMethod === 'PUT' && $id) {
                AuthMiddleware::handle(true);
                $controller->update($id);
            } elseif ($requestMethod === 'DELETE' && $id) {
                AuthMiddleware::handle(true);
                $controller->delete($id);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Endpoint not found']]);
            }
            break;

        case 'authors':
            $controller = new AuthorController();
            if ($requestMethod === 'GET' && !$id) {
                $controller->index();
            } elseif ($requestMethod === 'GET' && $id) {
                $controller->show($id);
            } elseif ($requestMethod === 'POST' && !$id) {
                AuthMiddleware::handle(true);
                $controller->create();
            } elseif ($requestMethod === 'PUT' && $id) {
                AuthMiddleware::handle(true);
                $controller->update($id);
            } elseif ($requestMethod === 'DELETE' && $id) {
                AuthMiddleware::handle(true);
                $controller->delete($id);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Endpoint not found']]);
            }
            break;

        case 'reports':
            AuthMiddleware::handle(true);
            require_once __DIR__ . '/../src/services/ReportService.php';
            $reportService = new ReportService();
            
            if ($id === 'dashboard') {
                $stats = $reportService->getDashboardStats();
                echo json_encode(['success' => true, 'data' => $stats]);
            } elseif ($id === 'trends') {
                $startDate = $_GET['start_date'] ?? null;
                $endDate = $_GET['end_date'] ?? null;
                $trends = $reportService->getLoanTrends($startDate, $endDate);
                echo json_encode(['success' => true, 'data' => $trends]);
            } elseif ($id === 'popular-books') {
                $limit = $_GET['limit'] ?? 10;
                $books = $reportService->getPopularBooks($limit);
                echo json_encode(['success' => true, 'data' => $books]);
            } elseif ($id === 'categories') {
                $distribution = $reportService->getCategoryDistribution();
                echo json_encode(['success' => true, 'data' => $distribution]);
            } elseif ($id === 'active-users') {
                $limit = $_GET['limit'] ?? 10;
                $users = $reportService->getMostActiveUsers($limit);
                echo json_encode(['success' => true, 'data' => $users]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Report not found']]);
            }
            break;

        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Resource not found']]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'SERVER_ERROR',
            'message' => 'Internal server error'
        ]
    ]);
}
