-- Add pdf_file column to books table
ALTER TABLE books 
ADD COLUMN IF NOT EXISTS pdf_file VARCHAR(500) NULL AFTER cover_image;

-- Add publisher column if it doesn't exist
ALTER TABLE books 
ADD COLUMN IF NOT EXISTS publisher VARCHAR(255) NULL AFTER description;

-- Show updated structure
DESCRIBE books;
