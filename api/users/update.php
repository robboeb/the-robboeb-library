<?php
require_once __DIR__ . '/../../src/services/AuthService.php';
require_once __DIR__ . '/../../src/helpers/DatabaseHelper.php';

header('Content-Type: application/json');

// Require admin authentication
AuthService::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
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

// Get request body
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['first_name', 'last_name', 'email', 'user_type'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => ['message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']]);
        exit;
    }
}

// Validate email format
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => ['message' => 'Invalid email format']]);
    exit;
}

// Validate user type
if (!in_array($data['user_type'], ['patron', 'admin'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => ['message' => 'Invalid user type']]);
    exit;
}

try {
    $pdo = DatabaseHelper::getConnection();
    
    // Check if user exists
    $checkSql = "SELECT user_id FROM users WHERE user_id = :user_id";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':user_id' => $userId]);
    
    if (!$checkStmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => ['message' => 'User not found']]);
        exit;
    }
    
    // Check if email is taken by another user
    $emailCheckSql = "SELECT user_id FROM users WHERE email = :email AND user_id != :user_id";
    $emailCheckStmt = $pdo->prepare($emailCheckSql);
    $emailCheckStmt->execute([':email' => $data['email'], ':user_id' => $userId]);
    
    if ($emailCheckStmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => ['message' => 'Email already exists']]);
        exit;
    }
    
    // Build update query
    $sql = "UPDATE users 
            SET first_name = :first_name, 
                last_name = :last_name, 
                email = :email, 
                phone = :phone, 
                address = :address, 
                user_type = :user_type, 
                status = :status";
    
    $params = [
        ':user_id' => $userId,
        ':first_name' => $data['first_name'],
        ':last_name' => $data['last_name'],
        ':email' => $data['email'],
        ':phone' => $data['phone'] ?? null,
        ':address' => $data['address'] ?? null,
        ':user_type' => $data['user_type'],
        ':status' => $data['status'] ?? 'active'
    ];
    
    // Update password if provided
    if (!empty($data['password'])) {
        if (strlen($data['password']) < 8) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => ['message' => 'Password must be at least 8 characters']]);
            exit;
        }
        $sql .= ", password = :password";
        $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    
    $sql .= " WHERE user_id = :user_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    echo json_encode([
        'success' => true,
        'message' => 'User updated successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => ['message' => 'Failed to update user: ' . $e->getMessage()]
    ]);
}
