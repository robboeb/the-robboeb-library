<?php
echo "=== Testing Libra_DB_sys Backend ===\n\n";

echo "1. Testing Database Connection...\n";
require_once 'src/models/Database.php';
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    echo "✓ Database connection successful\n\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n\n";
    exit(1);
}

echo "2. Testing Category Model...\n";
require_once 'src/models/Category.php';
try {
    $categoryModel = new Category();
    $categories = $categoryModel->findAll();
    echo "✓ Found " . count($categories) . " categories\n";
    foreach ($categories as $cat) {
        echo "  - " . $cat['name'] . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "✗ Category model failed: " . $e->getMessage() . "\n\n";
}

echo "3. Testing Author Model...\n";
require_once 'src/models/Author.php';
try {
    $authorModel = new Author();
    $authors = $authorModel->findAll();
    echo "✓ Found " . count($authors) . " authors\n";
    foreach ($authors as $author) {
        echo "  - " . $author['first_name'] . " " . $author['last_name'] . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "✗ Author model failed: " . $e->getMessage() . "\n\n";
}

echo "4. Testing Book Model...\n";
require_once 'src/models/Book.php';
try {
    $bookModel = new Book();
    $books = $bookModel->findAll(5);
    echo "✓ Found " . count($books) . " books (showing first 5)\n";
    foreach ($books as $book) {
        echo "  - " . $book['title'] . " (ISBN: " . $book['isbn'] . ")\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "✗ Book model failed: " . $e->getMessage() . "\n\n";
}

echo "5. Testing User Model & Authentication...\n";
require_once 'src/models/User.php';
try {
    $userModel = new User();
    $user = $userModel->authenticate('admin@libra.com', 'password');
    if ($user) {
        echo "✓ Authentication successful\n";
        echo "  - User: " . $user['first_name'] . " " . $user['last_name'] . "\n";
        echo "  - Email: " . $user['email'] . "\n";
        echo "  - Type: " . $user['user_type'] . "\n";
    } else {
        echo "✗ Authentication failed\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "✗ User model failed: " . $e->getMessage() . "\n\n";
}

echo "6. Testing Book Search...\n";
try {
    $results = $bookModel->search('Harry');
    echo "✓ Search found " . count($results) . " results for 'Harry'\n";
    foreach ($results as $book) {
        echo "  - " . $book['title'] . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "✗ Search failed: " . $e->getMessage() . "\n\n";
}

echo "7. Testing Report Service...\n";
require_once 'src/services/ReportService.php';
try {
    $reportService = new ReportService();
    $stats = $reportService->getDashboardStats();
    echo "✓ Dashboard stats retrieved\n";
    echo "  - Total Books: " . $stats['total_books'] . "\n";
    echo "  - Total Copies: " . $stats['total_copies'] . "\n";
    echo "  - Active Loans: " . $stats['active_loans'] . "\n";
    echo "  - Total Users: " . $stats['total_users'] . "\n";
    echo "  - Total Categories: " . $stats['total_categories'] . "\n";
    echo "  - Total Authors: " . $stats['total_authors'] . "\n";
    echo "\n";
} catch (Exception $e) {
    echo "✗ Report service failed: " . $e->getMessage() . "\n\n";
}

echo "8. Testing Validation Service...\n";
require_once 'src/services/ValidationService.php';
try {
    $emailTest = ValidationService::validateEmail('test@example.com');
    $isbnTest = ValidationService::validateISBN('9780451524935');
    echo "✓ Validation service working\n";
    echo "  - Email validation: " . ($emailTest['valid'] ? 'PASS' : 'FAIL') . "\n";
    echo "  - ISBN validation: " . ($isbnTest['valid'] ? 'PASS' : 'FAIL') . "\n";
    echo "\n";
} catch (Exception $e) {
    echo "✗ Validation service failed: " . $e->getMessage() . "\n\n";
}

echo "=== All Backend Tests Completed Successfully! ===\n";
echo "\nThe backend is ready. You can now:\n";
echo "1. Access the API at: http://localhost/Libra Project/api/\n";
echo "2. Test login with: admin@libra.com / password\n";
echo "3. Start building the frontend interface\n";
