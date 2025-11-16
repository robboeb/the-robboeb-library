# Admin Books Management - Complete Guide

## Overview

The admin books management system now includes full CRUD operations with cover image and PDF file upload capabilities.

## Features

### ✅ Complete CRUD Operations
- **Create** - Add new books with all details
- **Read** - View all books in a table with cover images
- **Update** - Edit existing books and update files
- **Delete** - Remove books and associated files

### 📸 Cover Image Management
- Upload cover images directly (JPG, PNG, GIF)
- Or provide external URL (Open Library, etc.)
- Preview images before saving
- Automatic file management

### 📄 PDF File Management
- Upload PDF files for digital books
- Or provide external PDF URL
- View PDF directly from table
- Automatic file cleanup on delete

### 📊 Enhanced Table View
- Book cover thumbnails in table
- Click cover to view full size
- PDF file indicator with direct link
- Search and filter functionality
- Responsive design

## Setup Instructions

### Step 1: Update Database

Run the database update script to add new columns:

```
http://localhost/library-pro/update-database.php
```

This will:
- Add `pdf_file` column to books table
- Add `publisher` column to books table
- Create upload directories
- Show current table structure

### Step 2: Access Books Management

Login as admin and go to:
```
http://localhost/library-pro/public/admin/books.php
```

**Admin Credentials:**
- Email: admin@libra.com
- Password: password

## How to Use

### Adding a New Book

1. Click **"Add New Book"** button
2. Fill in the form:
   - **Basic Information**
     - Title (required)
     - ISBN (required)
     - Author (select from dropdown)
     - Category (select from dropdown)
   
   - **Publication Details**
     - Publisher
     - Publication Year
   
   - **Description**
     - Book description/summary
   
   - **Inventory**
     - Total Copies (required)
     - Available Copies (required)
   
   - **Cover Image** (choose one)
     - Enter URL (e.g., from Open Library)
     - OR upload image file
   
   - **PDF File** (choose one)
     - Enter PDF URL
     - OR upload PDF file

3. Click **"Save Book"**

### Editing a Book

1. Click the **Edit** button (pencil icon) on any book row
2. Modal opens with current book data
3. Modify any fields
4. Upload new cover or PDF if needed
5. Click **"Save Book"**

### Viewing a Book

1. Click the **View** button (eye icon)
2. Opens book details page with large cover image
3. Shows all book information

### Deleting a Book

1. Click the **Delete** button (trash icon)
2. Confirm deletion
3. Book and associated files are removed

## File Upload Specifications

### Cover Images
- **Supported Formats**: JPG, JPEG, PNG, GIF
- **Max Size**: 5MB
- **Recommended Size**: 300x450 pixels (2:3 ratio)
- **Storage**: `/public/uploads/covers/`

### PDF Files
- **Supported Format**: PDF only
- **Max Size**: 50MB
- **Storage**: `/public/uploads/pdfs/`

## Table Columns

| Column | Description |
|--------|-------------|
| ID | Book ID number |
| Cover | Thumbnail of book cover (click to enlarge) |
| Title | Book title and publisher |
| Author(s) | Book authors |
| ISBN | ISBN code |
| Category | Book category badge |
| Copies | Total and available copies |
| Files | PDF file link (if available) |
| Actions | View, Edit, Delete buttons |

## Search and Filter

### Search
- Type in search box to filter books
- Searches: Title, Author, ISBN, Publisher
- Real-time filtering

### Filters
- **Status Filter**: All, Available, Borrowed, Reserved, Maintenance
- **Category Filter**: Filter by book category

## API Endpoints

### Get Book
```
GET /api/books/get.php?book_id={id}
```

### Create Book
```
POST /api/books/create.php
Content-Type: multipart/form-data

Fields:
- title, isbn, category_id, author_id
- publication_year, description, publisher
- total_quantity, available_quantity
- cover_image (URL or file)
- pdf_file (URL or file)
```

### Update Book
```
POST /api/books/update.php
Content-Type: multipart/form-data

Fields: Same as create + book_id
```

### Delete Book
```
POST /api/books/delete.php
Fields: book_id
```

## File Management

### Automatic File Handling
- Files are stored with unique names (e.g., `cover_abc123.jpg`)
- Old files are deleted when updating
- All files removed when deleting book

### Manual File Management
- Cover images: `/public/uploads/covers/`
- PDF files: `/public/uploads/pdfs/`
- Can manually delete unused files from these folders

## Database Schema

### Books Table (Updated)

```sql
CREATE TABLE books (
    book_id INT PRIMARY KEY AUTO_INCREMENT,
    isbn VARCHAR(20),
    title VARCHAR(255) NOT NULL,
    category_id INT,
    publication_year INT,
    description TEXT,
    publisher VARCHAR(255),          -- NEW
    cover_image VARCHAR(500),
    pdf_file VARCHAR(500),           -- NEW
    total_quantity INT DEFAULT 1,
    available_quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Tips and Best Practices

### Cover Images
1. **Use Open Library API** for free covers:
   ```
   https://covers.openlibrary.org/b/isbn/{ISBN}-L.jpg
   ```

2. **Upload your own** for better quality
3. **Optimize images** before upload (compress to reduce size)
4. **Use consistent dimensions** (300x450 recommended)

### PDF Files
1. **Compress PDFs** before upload to save space
2. **Check file size** - keep under 50MB
3. **Use descriptive names** when uploading
4. **Test PDF** after upload to ensure it works

### Data Entry
1. **Always fill required fields** (marked with *)
2. **Use correct ISBN format** (with or without hyphens)
3. **Select appropriate category** for better organization
4. **Add descriptions** to help users find books
5. **Set correct copy counts** for inventory tracking

## Troubleshooting

### Upload Fails
**Problem**: File upload doesn't work
**Solutions**:
- Check file size (max 5MB for images, 50MB for PDFs)
- Verify file format (JPG/PNG/GIF for images, PDF only for documents)
- Ensure upload directories exist and are writable
- Check PHP upload settings in php.ini

### Images Don't Display
**Problem**: Cover images show placeholder
**Solutions**:
- Verify image URL is accessible
- Check uploaded file exists in `/public/uploads/covers/`
- Clear browser cache
- Check file permissions

### PDF Won't Open
**Problem**: PDF link doesn't work
**Solutions**:
- Verify PDF file exists in `/public/uploads/pdfs/`
- Check file permissions
- Try re-uploading the PDF
- Verify PDF is not corrupted

### Database Errors
**Problem**: Can't save book
**Solutions**:
- Run `update-database.php` to ensure columns exist
- Check database connection
- Verify all required fields are filled
- Check error logs in `/logs/error.log`

## Security Notes

### File Upload Security
- Only authenticated admins can upload files
- File types are validated (images and PDFs only)
- Files are renamed with unique IDs
- Upload directories are outside web root where possible

### Data Validation
- All inputs are sanitized
- SQL injection prevention with prepared statements
- XSS protection with htmlspecialchars
- CSRF protection via session validation

## Future Enhancements

Possible additions:
- [ ] Bulk book import from CSV
- [ ] Multiple author selection
- [ ] Book series management
- [ ] Advanced search with filters
- [ ] Book ratings and reviews
- [ ] Barcode generation
- [ ] Export to Excel/PDF
- [ ] Book recommendations

## Support

For issues or questions:
1. Check this guide first
2. Review error messages in browser console
3. Check `/logs/error.log` for PHP errors
4. Verify database structure with `update-database.php`
5. Test with sample data first

---

**Last Updated**: November 16, 2025
**Version**: 2.0 with File Uploads
**Status**: ✅ Fully Functional
