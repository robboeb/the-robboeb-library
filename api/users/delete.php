<?php
require_once __DIR__ . '/../../src/services/AuthService.php';
require_once __DIR__ . '/../../src/helpers/DatabaseHelper.php';

header('Content-Type: application/json');

// Require admin authentication
AuthService::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
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
    $currentUser = AuthService::getCurrentUser();
    
    // Prevent deleting yourself
    if ($userId == $currentUser['user_id']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => ['message' => 'Cannot delete your own account']]);
        exit;
    }
    
    // Check if user exists
    $checkSql = "SELECT user_id FROM users WHERE user_id = :user_id";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':user_id' => $userId]);
    
    if (!$checkStmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => ['message' => 'User not found']]);
        exit;
    }
    
    // Check if user has active loans
    $loanCheckSql = "SELECT COUNT(*) as count FROM loans WHERE user_id = :user_id AND status = 'active'";
    $loanCheckStmt = $pdo->prepare($loanCheckSql);
    $loanCheckStmt->execute([':user_id' => $userId]);
    $loanCount = $loanCheckStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($loanCount > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => ['message' => 'Cannot delete user with active loans']]);
        exit;
    }
    
    // Delete user
    $deleteSql = "DELETE FROM users WHERE user_id = :user_id";
    $deleteStmt = $pdo->prepare($deleteSql);
    $deleteStmt->execute([':user_id' => $userId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'User deleted successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => ['message' => 'Failed to delete user: ' . $e->getMessage()]
    ]);
}
