<?php
echo "=== Libra_DB_sys API Test ===\n\n";

$baseUrl = 'http://localhost/Libra Project/api';

function testEndpoint($name, $url, $method = 'GET', $data = null) {
    echo "Testing: $name\n";
    echo "URL: $url\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Status: $httpCode\n";
    $decoded = json_decode($response, true);
    echo "Response: " . json_encode($decoded, JSON_PRETTY_PRINT) . "\n";
    echo str_repeat('-', 80) . "\n\n";
    
    return $decoded;
}

echo "1. Test Login\n";
$loginResult = testEndpoint(
    'Login as Admin',
    "$baseUrl/auth/login",
    'POST',
    ['email' => 'admin@libra.com', 'password' => 'password']
);

echo "2. Test Get Current User\n";
testEndpoint('Get Current User', "$baseUrl/auth/current");

echo "3. Test Get All Books\n";
testEndpoint('Get All Books', "$baseUrl/books?limit=5");

echo "4. Test Get All Categories\n";
testEndpoint('Get All Categories', "$baseUrl/categories?with_count=true");

echo "5. Test Get All Authors\n";
testEndpoint('Get All Authors', "$baseUrl/authors");

echo "6. Test Search Books\n";
testEndpoint('Search Books', "$baseUrl/books/search?q=Harry");

echo "7. Test Dashboard Stats\n";
testEndpoint('Dashboard Stats', "$baseUrl/reports/dashboard");

echo "8. Test Get Active Loans\n";
testEndpoint('Get Active Loans', "$baseUrl/loans");

echo "\n=== All Tests Completed ===\n";
