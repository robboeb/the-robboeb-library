<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';

echo "<h1>Reset Users - ROBBOEB Libra</h1>";

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<p>✓ Database connected</p>";
    
    // Delete existing users
    echo "<h2>Clearing existing users...</h2>";
    $pdo->exec("DELETE FROM users");
    echo "<p>✓ Existing users cleared</p>";
    
    // Create admin user
    echo "<h2>Creating Admin User...</h2>";
    $stmt = $pdo->prepare("
        INSERT INTO users (email, password_hash, first_name, last_name, user_type, status, phone, address, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $adminPassword = password_hash('password', PASSWORD_DEFAULT);
    $stmt->execute([
        'admin@libra.com',
        $adminPassword,
        'Admin',
        'User',
        'admin',
        'active',
        '+1234567890',
        '123 Admin Street'
    ]);
    echo "<p>✓ Admin user created</p>";
    echo "<p><strong>Email:</strong> admin@libra.com</p>";
    echo "<p><strong>Password:</strong> password</p>";
    
    // Create regular users
    echo "<h2>Creating Regular Users...</h2>";
    
    $users = [
        [
            'email' => 'john.doe@email.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+1234567891',
            'address' => '456 User Avenue'
        ],
        [
            'email' => 'jane.smith@email.com',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'phone' => '+1234567892',
            'address' => '789 Patron Road'
        ],
        [
            'email' => 'bob.wilson@email.com',
            'first_name' => 'Bob',
            'last_name' => 'Wilson',
            'phone' => '+1234567893',
            'address' => '321 Reader Lane'
        ]
    ];
    
    $userPassword = password_hash('password', PASSWORD_DEFAULT);
    
    foreach ($users as $user) {
        $stmt->execute([
            $user['email'],
            $userPassword,
            $user['first_name'],
            $user['last_name'],
            'patron',
            'active',
            $user['phone'],
            $user['address']
        ]);
        echo "<p>✓ User created: {$user['email']}</p>";
    }
    
    echo "<h2>All Users Created Successfully!</h2>";
    echo "<div style='background: #e8f5e9; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>Login Credentials:</h3>";
    echo "<p><strong>Admin Account:</strong></p>";
    echo "<p>Email: admin@libra.com<br>Password: password</p>";
    echo "<hr>";
    echo "<p><strong>User Accounts:</strong></p>";
    echo "<p>Email: john.doe@email.com<br>Password: password</p>";
    echo "<p>Email: jane.smith@email.com<br>Password: password</p>";
    echo "<p>Email: bob.wilson@email.com<br>Password: password</p>";
    echo "</div>";
    
    // Verify users
    echo "<h2>Verification:</h2>";
    $stmt = $pdo->query("SELECT user_id, email, first_name, last_name, user_type, status FROM users ORDER BY user_type DESC, email");
    $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f5f5f5;'>";
    echo "<th>ID</th><th>Email</th><th>Name</th><th>Type</th><th>Status</th>";
    echo "</tr>";
    
    foreach ($allUsers as $u) {
        $bgColor = $u['user_type'] === 'admin' ? '#fff3e0' : '#e3f2fd';
        echo "<tr style='background: {$bgColor};'>";
        echo "<td>{$u['user_id']}</td>";
        echo "<td>{$u['email']}</td>";
        echo "<td>{$u['first_name']} {$u['last_name']}</td>";
        echo "<td><strong>{$u['user_type']}</strong></td>";
        echo "<td>{$u['status']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<div style='margin-top: 30px; padding: 20px; background: #fff3cd; border-radius: 8px;'>";
    echo "<h3>✓ Setup Complete!</h3>";
    echo "<p>You can now login at: <a href='/library-pro/public/login.php'>Login Page</a></p>";
    echo "<p>Or go to: <a href='/library-pro/public/home.php'>Home Page</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #ffebee; padding: 20px; border-radius: 8px; color: #c62828;'>";
    echo "<h3>✗ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
?>
