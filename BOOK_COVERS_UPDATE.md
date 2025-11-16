# Book Cover Images - Implementation Guide

## What Was Updated

All book cards across the system now display actual book cover images from the database `cover_image` field.

### Updated Pages

1. **Home Page** (`public/home.php`)
   - Book cards show cover images
   - Fallback to gradient placeholder with title if no image

2. **Browse Page** (`public/browse.php`)
   - All book cards display cover images
   - Status badge overlays on images
   - Fallback to placeholder if image fails to load

3. **Book Details Page** (`public/book-details.php`)
   - Large cover image display
   - Proper aspect ratio (2:3)
   - Fallback to styled placeholder

4. **User Dashboard** (`public/user/index.php`)
   - Available books show cover images
   - Consistent styling with other pages

### Updated CSS Files

1. **browse.css** - Book card cover image styles
2. **book-details.css** - Large cover image display
3. **main.css** - Global book cover styles and fallbacks

## How It Works

### Image Display Logic

```php
<?php if (!empty($book['cover_image'])): ?>
    <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" 
         alt="<?php echo htmlspecialchars($book['title']); ?>"
         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
    <div class="book-cover-fallback" style="display: none;">
        <i class="fas fa-book"></i>
        <span class="book-title-overlay"><?php echo htmlspecialchars($book['title']); ?></span>
    </div>
<?php else: ?>
    <div class="book-cover-fallback">
        <i class="fas fa-book"></i>
        <span class="book-title-overlay"><?php echo htmlspecialchars($book['title']); ?></span>
    </div>
<?php endif; ?>
```

### Features

✅ **Automatic Fallback** - If image URL is invalid or fails to load, shows styled placeholder
✅ **Responsive Design** - Images scale properly on all devices
✅ **Proper Aspect Ratio** - Maintains 2:3 book cover ratio
✅ **Performance** - Uses object-fit: cover for optimal display
✅ **Accessibility** - Includes alt text for screen readers

## Adding Cover Images to Books

### ⚡ Quick Method: Run the Auto-Update Script (RECOMMENDED)

**Easiest way to add covers to all your books:**

1. Open your browser
2. Go to: `http://localhost/library-pro/add-book-covers.php`
3. The script will automatically:
   - Add covers to popular books (Harry Potter, 1984, etc.)
   - Generate cover URLs from ISBNs using Open Library API
   - Add placeholder covers to remaining books
   - Show you a preview of updated books

**That's it!** All your books will now have cover images.

### Method 1: Manual Update via phpMyAdmin

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select database: `libra_db_sys`
3. Click on `books` table
4. Find the book you want to update
5. Click "Edit" (pencil icon)
6. In the `cover_image` field, paste the image URL
7. Click "Go" to save

### Method 2: Run SQL Script

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select database: `libra_db_sys`
3. Click "SQL" tab
4. Copy and paste contents from: `database/add-book-covers.sql`
5. Click "Go" to execute

### Method 3: Manual SQL Query

```sql
-- Update a specific book
UPDATE books 
SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780140328721-L.jpg' 
WHERE book_id = 1;

-- Update multiple books at once
UPDATE books SET cover_image = 'https://example.com/cover1.jpg' WHERE book_id = 1;
UPDATE books SET cover_image = 'https://example.com/cover2.jpg' WHERE book_id = 2;
UPDATE books SET cover_image = 'https://example.com/cover3.jpg' WHERE book_id = 3;
```

### Method 4: Auto-generate from ISBN (Open Library API)

```sql
-- Automatically set cover images using ISBN from Open Library
UPDATE books 
SET cover_image = CONCAT('https://covers.openlibrary.org/b/isbn/', isbn, '-L.jpg') 
WHERE isbn IS NOT NULL AND isbn != '';
```

## Free Cover Image Sources

### 1. Open Library Covers API
```
https://covers.openlibrary.org/b/isbn/{ISBN}-L.jpg
```
- Replace `{ISBN}` with the book's ISBN
- `-L.jpg` = Large, `-M.jpg` = Medium, `-S.jpg` = Small
- Example: `https://covers.openlibrary.org/b/isbn/9780140328721-L.jpg`

### 2. Google Books API
```
https://books.google.com/books/content?id={BOOK_ID}&printsec=frontcover&img=1&zoom=1
```

### 3. Sample Cover URLs for Testing

```
1984 by George Orwell:
https://covers.openlibrary.org/b/isbn/9780451524935-L.jpg

To Kill a Mockingbird:
https://covers.openlibrary.org/b/isbn/9780061120084-L.jpg

The Great Gatsby:
https://covers.openlibrary.org/b/isbn/9780743273565-L.jpg

Pride and Prejudice:
https://covers.openlibrary.org/b/isbn/9780141439518-L.jpg

Harry Potter and the Sorcerer's Stone:
https://covers.openlibrary.org/b/isbn/9780439708180-L.jpg
```

## Testing the Implementation

### Quick Test

1. Visit: `http://localhost/library-pro/test-book-covers.php`
2. This page shows current books and their cover image status
3. Provides sample URLs and SQL queries

### Visual Test

1. **Home Page**: `http://localhost/library-pro/public/home.php`
   - Check if book cards show images or placeholders

2. **Browse Page**: `http://localhost/library-pro/public/browse.php`
   - Verify all books display correctly
   - Test image loading and fallbacks

3. **Book Details**: Click any book
   - Large cover image should display
   - Check fallback if no image

4. **User Dashboard**: Login and check
   - Available books section shows covers

## Troubleshooting

### Images Not Showing

**Problem**: All books show placeholder icons
**Solution**: 
- Check if `cover_image` field has valid URLs in database
- Run: `SELECT book_id, title, cover_image FROM books LIMIT 5;`
- Add cover images using methods above

### Images Broken/404

**Problem**: Image URLs return 404 errors
**Solution**:
- Verify the URL is accessible in browser
- Use Open Library API with valid ISBNs
- Update with working image URLs

### Images Too Large/Small

**Problem**: Images don't fit properly
**Solution**:
- CSS automatically handles sizing with `object-fit: cover`
- Images maintain 2:3 aspect ratio
- Clear browser cache (Ctrl + F5)

### Mixed Content Warning

**Problem**: HTTPS site loading HTTP images
**Solution**:
- Use HTTPS URLs for cover images
- Example: `https://covers.openlibrary.org/...` (not http://)

## Database Schema

The `books` table includes:

```sql
CREATE TABLE books (
    book_id INT PRIMARY KEY AUTO_INCREMENT,
    isbn VARCHAR(20),
    title VARCHAR(255) NOT NULL,
    category_id INT,
    publication_year INT,
    description TEXT,
    cover_image VARCHAR(500),  -- ← This field stores the image URL
    total_quantity INT DEFAULT 1,
    available_quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Example: Bulk Update Script

Create a file `update-covers.php` in your project root:

```php
<?php
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/src/helpers/DatabaseHelper.php';

$pdo = DatabaseHelper::getConnection();

// Sample books with cover URLs
$updates = [
    1 => 'https://covers.openlibrary.org/b/isbn/9780451524935-L.jpg',
    2 => 'https://covers.openlibrary.org/b/isbn/9780061120084-L.jpg',
    3 => 'https://covers.openlibrary.org/b/isbn/9780743273565-L.jpg',
    // Add more book_id => cover_url pairs
];

$stmt = $pdo->prepare("UPDATE books SET cover_image = :cover WHERE book_id = :id");

foreach ($updates as $bookId => $coverUrl) {
    $stmt->execute([':cover' => $coverUrl, ':id' => $bookId]);
    echo "Updated book ID $bookId\n";
}

echo "Done! Updated " . count($updates) . " books.\n";
```

Run it: `http://localhost/library-pro/update-covers.php`

## Benefits

✅ **Professional Look** - Real book covers make the system look polished
✅ **Better UX** - Users can visually identify books
✅ **Automatic Fallback** - No broken images, always shows something
✅ **Easy to Update** - Just add URL to database
✅ **No Storage Needed** - Uses external URLs (no file uploads)
✅ **Fast Loading** - Images cached by browser

## Next Steps

1. Add cover images to your books using one of the methods above
2. Test on all pages to see the improvements
3. Consider adding image upload functionality in admin panel (future enhancement)
4. Use consistent image sources for best quality

---

**Last Updated**: November 16, 2025
**Status**: ✅ Fully Implemented
**Test File**: `test-book-covers.php`
