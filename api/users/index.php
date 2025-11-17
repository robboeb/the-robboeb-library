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

try {
    $pdo = DatabaseHelper::getConnection();
    
    // Get search query if provided
    $searchQuery = $_GET['q'] ?? '';
    
    $sql = "SELECT user_id, first_name, last_name, email, phone, address, user_type, status, created_at 
            FROM users";
    
    $params = [];
    
    if ($searchQuery) {
        $sql .= " WHERE first_name LIKE :search 
                  OR last_name LIKE :search 
                  OR email LIKE :search 
                  OR phone LIKE :search";
        $params[':search'] = '%' . $searchQuery . '%';
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $users
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => ['message' => 'Failed to fetch users: ' . $e->getMessage()]
    ]);
}
