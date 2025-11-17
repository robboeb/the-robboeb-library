<?php
require_once __DIR__ . '/../../src/services/AuthService.php';
require_once __DIR__ . '/../../src/helpers/DatabaseHelper.php';

header('Content-Type: application/json');

// Require admin authentication
AuthService::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => ['message' => 'Method not allowed']]);
    exit;
}

// Get request body
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['first_name', 'last_name', 'email', 'password', 'user_type'];
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

// Validate password length
if (strlen($data['password']) < 8) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => ['message' => 'Password must be at least 8 characters']]);
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
    
    // Check if email already exists
    $checkSql = "SELECT user_id FROM users WHERE email = :email";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':email' => $data['email']]);
    
    if ($checkStmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => ['message' => 'Email already exists']]);
        exit;
    }
    
    // Hash password
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    
    // Insert new user
    $sql = "INSERT INTO users (first_name, last_name, email, password, phone, address, user_type, status, created_at) 
            VALUES (:first_name, :last_name, :email, :password, :phone, :address, :user_type, :status, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':first_name' => $data['first_name'],
        ':last_name' => $data['last_name'],
        ':email' => $data['email'],
        ':password' => $hashedPassword,
        ':phone' => $data['phone'] ?? null,
        ':address' => $data['address'] ?? null,
        ':user_type' => $data['user_type'],
        ':status' => $data['status'] ?? 'active'
    ]);
    
    $userId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'User created successfully',
        'data' => [
            'user_id' => $userId,
            'email' => $data['email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name']
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => ['message' => 'Failed to create user: ' . $e->getMessage()]
    ]);
}
