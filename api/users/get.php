<?php
require_once __DIR__ . '/../../src/services/AuthService.php';
require_once __DIR__ . '/../../src/helpers/DatabaseHelper.php';

header('Content-Type: application/json');

// Require admin authentication
AuthService::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => ['message' => 'Method not allowed']]);
    exit;
}

// Get user_id from URL path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));
$userId = null;

// Find user_id in path (format: /api/users/{id})
foreach ($pathParts as $i => $part) {
    if ($part === 'users' && isset($pathParts[$i + 1]) && is_numeric($pathParts[$i + 1])) {
        $userId = (int)$pathParts[$i + 1];
        break;
    }
}

if (!$userId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => ['message' => 'User ID is required']]);
    exit;
}

try {
    $pdo = DatabaseHelper::getConnection();
    
    $sql = "SELECT user_id, first_name, last_name, email, phone, address, user_type, status, created_at 
            FROM users 
            WHERE user_id = :user_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => ['message' => 'User not found']]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $user
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => ['message' => 'Failed to fetch user: ' . $e->getMessage()]
    ]);
}
