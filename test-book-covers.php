<?php
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/src/helpers/DatabaseHelper.php';

// Get some books to check cover images
$books = DatabaseHelper::getAllBooks(['limit' => 5]);

echo "<h1>Book Cover Test</h1>";
echo "<p>Checking first 5 books for cover images:</p>";

foreach ($books as $book) {
    echo "<div style='margin: 20px; padding: 20px; border: 1px solid #ccc;'>";
    echo "<h3>" . htmlspecialchars($book['title']) . "</h3>";
    echo "<p><strong>Cover Image:</strong> " . ($book['cover_image'] ? htmlspecialchars($book['cover_image']) : 'No cover image') . "</p>";
    
    if ($book['cover_image']) {
        echo "<img src='" . htmlspecialchars($book['cover_image']) . "' alt='Cover' style='max-width: 200px; height: auto;' onerror='this.style.display=\"none\"; this.nextElementSibling.style.display=\"block\";'>";
        echo "<p style='display: none; color: red;'>Image failed to load</p>";
    }
    
    echo "</div>";
}

echo "<hr>";
echo "<h2>Sample Cover Image URLs</h2>";
echo "<p>You can update books with cover images using these sample URLs:</p>";
echo "<ul>";
echo "<li>https://covers.openlibrary.org/b/isbn/9780140328721-L.jpg</li>";
echo "<li>https://covers.openlibrary.org/b/isbn/9780743273565-L.jpg</li>";
echo "<li>https://covers.openlibrary.org/b/isbn/9780061120084-L.jpg</li>";
echo "<li>https://covers.openlibrary.org/b/isbn/9780451524935-L.jpg</li>";
echo "</ul>";

echo "<h2>SQL to Add Cover Images</h2>";
echo "<pre>";
echo "UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/YOUR_ISBN-L.jpg' WHERE book_id = 1;\n";
echo "-- Or use the book's actual ISBN:\n";
echo "UPDATE books SET cover_image = CONCAT('https://covers.openlibrary.org/b/isbn/', isbn, '-L.jpg') WHERE isbn IS NOT NULL;\n";
echo "</pre>";
?>
