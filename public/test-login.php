<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/models/User.php';

echo "<h1>Login Test</h1>";

try {
    $userModel = new User();
    
    echo "<h2>Testing Database Connection</h2>";
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p>✓ Database connected</p>";
    
    echo "<h2>Checking Admin User</h2>";
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute(['admin@libra.com']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "<p>✓ Admin user found</p>";
        echo "<pre>";
        print_r([
            'user_id' => $admin['user_id'],
            'email' => $admin['email'],
            'user_type' => $admin['user_type'],
            'status' => $admin['status'],
            'password_hash' => substr($admin['password_hash'], 0, 20) . '...'
        ]);
        echo "</pre>";
        
        echo "<h2>Testing Password Verification</h2>";
        $testPassword = 'password';
        if (password_verify($testPassword, $admin['password_hash'])) {
            echo "<p>✓ Password 'password' is correct</p>";
        } else {
            echo "<p>✗ Password 'password' is incorrect</p>";
            echo "<p>Trying to create new hash...</p>";
            $newHash = password_hash($testPassword, PASSWORD_DEFAULT);
            echo "<p>New hash: " . substr($newHash, 0, 30) . "...</p>";
        }
        
        echo "<h2>Testing Authentication Method</h2>";
        $user = $userModel->authenticate('admin@libra.com', 'password');
        if ($user) {
            echo "<p>✓ Authentication successful</p>";
            echo "<pre>";
            print_r($user);
            echo "</pre>";
        } else {
            echo "<p>✗ Authentication failed</p>";
        }
    } else {
        echo "<p>✗ Admin user not found</p>";
        echo "<p>Creating admin user...</p>";
        
        $stmt = $pdo->prepare("
            INSERT INTO users (email, password_hash, first_name, last_name, user_type, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            'admin@libra.com',
            password_hash('password', PASSWORD_DEFAULT),
            'Admin',
            'User',
            'admin',
            'active'
        ]);
        echo "<p>✓ Admin user created</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
