-- Add Book Cover Images to Existing Books
-- This script adds cover image URLs from Open Library API

-- Update books with cover images based on common book titles
-- Using Open Library Covers API: https://covers.openlibrary.org/b/isbn/{ISBN}-L.jpg

-- Classic Literature
UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780451524935-L.jpg' 
WHERE title LIKE '%1984%' OR title LIKE '%Nineteen Eighty%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780061120084-L.jpg' 
WHERE title LIKE '%Kill a Mockingbird%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780743273565-L.jpg' 
WHERE title LIKE '%Great Gatsby%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780141439518-L.jpg' 
WHERE title LIKE '%Pride and Prejudice%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780486284736-L.jpg' 
WHERE title LIKE '%Moby Dick%' OR title LIKE '%Moby-Dick%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780486280615-L.jpg' 
WHERE title LIKE '%Jane Eyre%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780141439600-L.jpg' 
WHERE title LIKE '%Wuthering Heights%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780486415871-L.jpg' 
WHERE title LIKE '%Frankenstein%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780486270487-L.jpg' 
WHERE title LIKE '%Dracula%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780141439556-L.jpg' 
WHERE title LIKE '%Crime and Punishment%';

-- Harry Potter Series
UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780439708180-L.jpg' 
WHERE title LIKE '%Harry Potter%' AND title LIKE '%Sorcerer%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780439064873-L.jpg' 
WHERE title LIKE '%Harry Potter%' AND title LIKE '%Chamber%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780439136365-L.jpg' 
WHERE title LIKE '%Harry Potter%' AND title LIKE '%Prisoner%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780439139601-L.jpg' 
WHERE title LIKE '%Harry Potter%' AND title LIKE '%Goblet%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780439358071-L.jpg' 
WHERE title LIKE '%Harry Potter%' AND title LIKE '%Phoenix%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780439785969-L.jpg' 
WHERE title LIKE '%Harry Potter%' AND title LIKE '%Prince%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780545010221-L.jpg' 
WHERE title LIKE '%Harry Potter%' AND title LIKE '%Deathly%';

-- Lord of the Rings
UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780547928210-L.jpg' 
WHERE title LIKE '%Hobbit%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780544003415-L.jpg' 
WHERE title LIKE '%Fellowship%' AND title LIKE '%Ring%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780544003422-L.jpg' 
WHERE title LIKE '%Two Towers%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780544003439-L.jpg' 
WHERE title LIKE '%Return%' AND title LIKE '%King%';

-- Popular Fiction
UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780316769488-L.jpg' 
WHERE title LIKE '%Catcher in the Rye%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780062315007-L.jpg' 
WHERE title LIKE '%Alchemist%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780385490818-L.jpg' 
WHERE title LIKE '%Da Vinci Code%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780316015844-L.jpg' 
WHERE title LIKE '%Twilight%' AND author_id IN (SELECT author_id FROM authors WHERE last_name LIKE '%Meyer%');

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780439023481-L.jpg' 
WHERE title LIKE '%Hunger Games%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780062024039-L.jpg' 
WHERE title LIKE '%Divergent%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780142424179-L.jpg' 
WHERE title LIKE '%Fault in Our Stars%';

-- Science Fiction
UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780441172719-L.jpg' 
WHERE title LIKE '%Dune%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780345391803-L.jpg' 
WHERE title LIKE '%Hitchhiker%' AND title LIKE '%Galaxy%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780553293357-L.jpg' 
WHERE title LIKE '%Foundation%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780441013593-L.jpg' 
WHERE title LIKE '%Neuromancer%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780765342294-L.jpg' 
WHERE title LIKE '%Ender%' AND title LIKE '%Game%';

-- Mystery/Thriller
UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780307588371-L.jpg' 
WHERE title LIKE '%Girl with the Dragon Tattoo%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780307588364-L.jpg' 
WHERE title LIKE '%Gone Girl%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780307277671-L.jpg' 
WHERE title LIKE '%Girl on the Train%';

-- Non-Fiction
UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780385490818-L.jpg' 
WHERE title LIKE '%Sapiens%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9781476731711-L.jpg' 
WHERE title LIKE '%Educated%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780735211292-L.jpg' 
WHERE title LIKE '%Atomic Habits%';

-- Children's Books
UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780064401883-L.jpg' 
WHERE title LIKE '%Charlotte%' AND title LIKE '%Web%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780064404990-L.jpg' 
WHERE title LIKE '%Where the Wild Things Are%';

UPDATE books SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780590353427-L.jpg' 
WHERE title LIKE '%Giving Tree%';

-- For books with ISBNs but no cover yet, try to auto-generate from ISBN
UPDATE books 
SET cover_image = CONCAT('https://covers.openlibrary.org/b/isbn/', isbn, '-L.jpg')
WHERE isbn IS NOT NULL 
  AND isbn != '' 
  AND (cover_image IS NULL OR cover_image = '');

-- Add some generic placeholder covers for books without ISBNs
UPDATE books 
SET cover_image = 'https://via.placeholder.com/300x450/faa405/ffffff?text=Book+Cover'
WHERE (cover_image IS NULL OR cover_image = '')
  AND category_id = (SELECT category_id FROM categories WHERE name LIKE '%Fiction%' LIMIT 1);

UPDATE books 
SET cover_image = 'https://via.placeholder.com/300x450/ff6b6b/ffffff?text=Book+Cover'
WHERE (cover_image IS NULL OR cover_image = '')
  AND category_id = (SELECT category_id FROM categories WHERE name LIKE '%Science%' LIMIT 1);

UPDATE books 
SET cover_image = 'https://via.placeholder.com/300x450/4ecdc4/ffffff?text=Book+Cover'
WHERE (cover_image IS NULL OR cover_image = '')
  AND category_id = (SELECT category_id FROM categories WHERE name LIKE '%History%' LIMIT 1);

UPDATE books 
SET cover_image = 'https://via.placeholder.com/300x450/95e1d3/ffffff?text=Book+Cover'
WHERE (cover_image IS NULL OR cover_image = '')
  AND category_id = (SELECT category_id FROM categories WHERE name LIKE '%Biography%' LIMIT 1);

-- Final fallback for any remaining books
UPDATE books 
SET cover_image = 'https://via.placeholder.com/300x450/faa405/ffffff?text=No+Cover'
WHERE cover_image IS NULL OR cover_image = '';

-- Show results
SELECT 
    book_id,
    title,
    isbn,
    CASE 
        WHEN cover_image LIKE '%openlibrary%' THEN 'Open Library'
        WHEN cover_image LIKE '%placeholder%' THEN 'Placeholder'
        ELSE 'Other'
    END as cover_source,
    LEFT(cover_image, 50) as cover_url_preview
FROM books
ORDER BY book_id
LIMIT 20;
