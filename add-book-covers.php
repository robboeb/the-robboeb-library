<?php
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/src/helpers/DatabaseHelper.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Add Book Covers - ROBBOEB Libra</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #faa405; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .book-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin: 20px 0; }
        .book-card { background: white; border-radius: 8px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .book-card img { width: 100%; height: 250px; object-fit: cover; border-radius: 5px; margin-bottom: 10px; }
        .book-title { font-weight: bold; font-size: 14px; margin-bottom: 5px; }
        .book-isbn { font-size: 12px; color: #666; }
        .stats { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #faa405; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #e89400; }
    </style>
</head>
<body>";

echo "<h1>📚 Add Book Covers to ROBBOEB Libra</h1>";

$pdo = DatabaseHelper::getConnection();

// Count books before
$stmt = $pdo->query("SELECT COUNT(*) as total, 
                     SUM(CASE WHEN cover_image IS NOT NULL AND cover_image != '' THEN 1 ELSE 0 END) as with_covers
                     FROM books");
$before = $stmt->fetch();

echo "<div class='info'>";
echo "<strong>Before Update:</strong><br>";
echo "Total Books: {$before['total']}<br>";
echo "Books with Covers: {$before['with_covers']}<br>";
echo "Books without Covers: " . ($before['total'] - $before['with_covers']);
echo "</div>";

// Popular book covers mapping
$coverMappings = [
    // Classic Literature
    ['pattern' => '%1984%', 'cover' => 'https://covers.openlibrary.org/b/isbn/9780451524935-L.jpg'],
    ['pattern' => '%Kill a Mockingbird%', 'cover' => 'https://covers.openlibrary.org/b/isbn/9780061120084-L.jpg'],
    ['pattern' => '%Great Gatsby%', 'cover' => 'https://covers.openlibrary.org/b/isbn/9780743273565-L.jpg'],
    ['pattern' => '%Pride and Prejudice%', 'cover' => 'https://covers.openlibrary.org/b/isbn/9780141439518-L.jpg'],
    ['pattern' => '%Moby%', 'cover' => 'https://covers.openlibrary.org/b/isbn/9780486284736-L.jpg'],
    ['pattern' => '%Jane Eyre%', 'cover' => 'https://covers.openlibrary.org/b/isbn/9780486280615-L.jpg'],
    ['pattern' => '%Wuthering Heights%', 'cover' => 'https://covers.openlibrary.org/b/isbn/9780141439600-L.jpg'],
    ['pattern' => '%Frankenstein%', 'cover' => 'https://covers.openlibrary.org/b/isbn/9780486415871-L.jpg'],
    ['pattern' => '%Dracula%', 'cover' => 'https://covers.openlibrary.org/b/isbn/9780486270487-L.jpg'],
    
    // Harry Potter
    ['pattern' => '%Harry Potter%Sorcerer%', 'cover' => 'https://covers.openlibrary.org/b/isbn/9780439708180-L.jpg'],
    ['pattern' => '%Harry Potter%Chamber%', 'cover' => 'https://covers.openlibrary.org/b/isbn/9780439064873-L.jpg'],
    ['pattern' => '%Harry Potter%Prisoner%', 'cover' => 'https://covers.openlibrary.org/b/isbn/9780439136365-L.jpg'],
    
    // LOTR
    ['pattern' => '%Hobbit%', 'cover' => 'https://covers.openlibrary.org/b/isbn/9780547928210-L.jpg'],
    ['pattern' => '%Fellowship%', 'cover' => 'https://covers.openlibrary.org/b/isbn/9780544003415-L.jpg'],
    
    // Popular Fiction
    ['pattern' => '%Catcher in the Rye%', 'cover' => 'https://covers.openlibrary.org/b/isbn/9780316769488-L.jpg'],
    ['pattern' => '%Alchemist%', 'cover' => 'https://covers.openlibrary.org/b/isbn/9780062315007-L.jpg'],
    ['pattern' => '%Hunger Games%', 'cover' => 'https://covers.openlibrary.org/b/isbn/9780439023481-L.jpg'],
    
    // Sci-Fi
    ['pattern' => '%Dune%', 'cover' => 'https://covers.openlibrary.org/b/isbn/9780441172719-L.jpg'],
    ['pattern' => '%Hitchhiker%', 'cover' => 'https://covers.openlibrary.org/b/isbn/9780345391803-L.jpg'],
];

$updated = 0;

// Apply cover mappings
foreach ($coverMappings as $mapping) {
    $stmt = $pdo->prepare("UPDATE books SET cover_image = :cover WHERE title LIKE :pattern AND (cover_image IS NULL OR cover_image = '')");
    $stmt->execute([':cover' => $mapping['cover'], ':pattern' => $mapping['pattern']]);
    $updated += $stmt->rowCount();
}

echo "<div class='success'>";
echo "✅ Updated $updated books with specific cover images";
echo "</div>";

// Auto-generate from ISBN
$stmt = $pdo->prepare("UPDATE books 
                       SET cover_image = CONCAT('https://covers.openlibrary.org/b/isbn/', isbn, '-L.jpg')
                       WHERE isbn IS NOT NULL 
                       AND isbn != '' 
                       AND LENGTH(isbn) >= 10
                       AND (cover_image IS NULL OR cover_image = '')");
$stmt->execute();
$isbnUpdated = $stmt->rowCount();

echo "<div class='success'>";
echo "✅ Auto-generated $isbnUpdated cover URLs from ISBNs";
echo "</div>";

// Add placeholder for remaining books
$stmt = $pdo->prepare("UPDATE books 
                       SET cover_image = 'https://via.placeholder.com/300x450/faa405/ffffff?text=Book+Cover'
                       WHERE cover_image IS NULL OR cover_image = ''");
$stmt->execute();
$placeholderUpdated = $stmt->rowCount();

echo "<div class='success'>";
echo "✅ Added placeholder covers to $placeholderUpdated books";
echo "</div>";

// Count books after
$stmt = $pdo->query("SELECT COUNT(*) as total, 
                     SUM(CASE WHEN cover_image IS NOT NULL AND cover_image != '' THEN 1 ELSE 0 END) as with_covers
                     FROM books");
$after = $stmt->fetch();

echo "<div class='stats'>";
echo "<h2>📊 Results</h2>";
echo "<strong>After Update:</strong><br>";
echo "Total Books: {$after['total']}<br>";
echo "Books with Covers: {$after['with_covers']}<br>";
echo "Books without Covers: " . ($after['total'] - $after['with_covers']) . "<br><br>";
echo "<strong>Changes:</strong><br>";
echo "New Covers Added: " . ($after['with_covers'] - $before['with_covers']);
echo "</div>";

// Show sample books with covers
echo "<h2>📖 Sample Books with Covers</h2>";
$stmt = $pdo->query("SELECT book_id, title, isbn, cover_image FROM books WHERE cover_image IS NOT NULL AND cover_image != '' ORDER BY RAND() LIMIT 12");
$sampleBooks = $stmt->fetchAll();

echo "<div class='book-grid'>";
foreach ($sampleBooks as $book) {
    echo "<div class='book-card'>";
    echo "<img src='" . htmlspecialchars($book['cover_image']) . "' alt='" . htmlspecialchars($book['title']) . "' onerror='this.src=\"https://via.placeholder.com/300x450/faa405/ffffff?text=No+Image\"'>";
    echo "<div class='book-title'>" . htmlspecialchars($book['title']) . "</div>";
    if ($book['isbn']) {
        echo "<div class='book-isbn'>ISBN: " . htmlspecialchars($book['isbn']) . "</div>";
    }
    echo "</div>";
}
echo "</div>";

echo "<div style='text-align: center; margin: 40px 0;'>";
echo "<a href='" . BASE_URL . "/public/home.php' class='btn'>🏠 Go to Home Page</a>";
echo "<a href='" . BASE_URL . "/public/browse.php' class='btn'>📚 Browse Books</a>";
echo "<a href='" . BASE_URL . "/test-book-covers.php' class='btn'>🔍 Test Covers</a>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>ℹ️ Next Steps</h3>";
echo "<ul>";
echo "<li>Visit the <a href='" . BASE_URL . "/public/home.php'>Home Page</a> to see book covers</li>";
echo "<li>Browse the <a href='" . BASE_URL . "/public/browse.php'>Book Catalog</a></li>";
echo "<li>Click on any book to see the large cover image</li>";
echo "<li>If some covers don't load, they'll show a nice placeholder</li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";
?>
