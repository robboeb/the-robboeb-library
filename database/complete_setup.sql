-- ============================================
-- COMPLETE DATABASE SETUP
-- Database: khmer-dbase
-- Self-Service Library Borrowing System
-- ============================================

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `khmer-dbase` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE `khmer-dbase`;

-- ============================================
-- TABLE: users
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `user_type` enum('admin','librarian','patron') DEFAULT 'patron',
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_user_type` (`user_type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: categories
-- ============================================
CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: authors
-- ============================================
CREATE TABLE IF NOT EXISTS `authors` (
  `author_id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `biography` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`author_id`),
  KEY `idx_name` (`first_name`, `last_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: books
-- ============================================
CREATE TABLE IF NOT EXISTS `books` (
  `book_id` int(11) NOT NULL AUTO_INCREMENT,
  `isbn` varchar(20) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `publication_year` year(4) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `pdf_file` varchar(255) DEFAULT NULL,
  `total_quantity` int(11) DEFAULT 1,
  `available_quantity` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`book_id`),
  UNIQUE KEY `isbn` (`isbn`),
  KEY `idx_title` (`title`),
  KEY `idx_category_id` (`category_id`),
  CONSTRAINT `books_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: book_authors (junction table)
-- ============================================
CREATE TABLE IF NOT EXISTS `book_authors` (
  `book_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  PRIMARY KEY (`book_id`, `author_id`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `book_authors_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE,
  CONSTRAINT `book_authors_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `authors` (`author_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: loans
-- ============================================
CREATE TABLE IF NOT EXISTS `loans` (
  `loan_id` int(11) NOT NULL AUTO_INCREMENT,
  `book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `checkout_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `fine_amount` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`loan_id`),
  KEY `book_id` (`book_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_checkout_date` (`checkout_date`),
  KEY `idx_due_date` (`due_date`),
  CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE,
  CONSTRAINT `loans_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- ============================================
-- INSERT TEST DATA: Users
-- ============================================

-- Admin user (email: admin@test.com, password: admin123)
INSERT INTO `users` (`email`, `password`, `first_name`, `last_name`, `phone`, `address`, `user_type`, `status`)
VALUES 
('admin@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', '555-555-5555', '789 Admin Blvd', 'admin', 'active')
ON DUPLICATE KEY UPDATE `email`=`email`;

-- Patron users (email: patron@test.com, password: password123)
INSERT INTO `users` (`email`, `password`, `first_name`, `last_name`, `phone`, `address`, `user_type`, `status`)
VALUES 
('patron@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', '123-456-7890', '123 Main St', 'patron', 'active'),
('borrower@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Smith', '098-765-4321', '456 Oak Ave', 'patron', 'active')
ON DUPLICATE KEY UPDATE `email`=`email`;

-- ============================================
-- INSERT TEST DATA: Categories
-- ============================================
INSERT INTO `categories` (`name`, `description`)
VALUES 
('Fiction', 'Fictional literature and novels'),
('Non-Fiction', 'Non-fictional books and references'),
('Science', 'Scientific books and research'),
('History', 'Historical books and documents'),
('Technology', 'Technology and computer science books'),
('Biography', 'Biographies and memoirs'),
('Children', 'Children and young adult books')
ON DUPLICATE KEY UPDATE `name`=`name`;

-- ============================================
-- INSERT TEST DATA: Authors
-- ============================================
INSERT INTO `authors` (`first_name`, `last_name`, `biography`)
VALUES 
('F. Scott', 'Fitzgerald', 'American novelist and short story writer'),
('Harper', 'Lee', 'American novelist known for To Kill a Mockingbird'),
('George', 'Orwell', 'English novelist and essayist'),
('Yuval Noah', 'Harari', 'Israeli historian and author'),
('Tara', 'Westover', 'American memoirist and historian')
ON DUPLICATE KEY UPDATE `first_name`=`first_name`;

-- ============================================
-- INSERT TEST DATA: Books
-- ============================================
INSERT INTO `books` (`isbn`, `title`, `category_id`, `publisher`, `publication_year`, `description`, `cover_image`, `total_quantity`, `available_quantity`)
VALUES 
('9780743273565', 'The Great Gatsby', 1, 'Scribner', 1925, 'A classic American novel set in the Jazz Age', 'https://covers.openlibrary.org/b/isbn/9780743273565-L.jpg', 3, 3),
('9780061120084', 'To Kill a Mockingbird', 1, 'Harper Perennial', 1960, 'A gripping tale of racial injustice and childhood innocence', 'https://covers.openlibrary.org/b/isbn/9780061120084-L.jpg', 2, 2),
('9780451524935', '1984', 1, 'Signet Classic', 1949, 'A dystopian social science fiction novel', 'https://covers.openlibrary.org/b/isbn/9780451524935-L.jpg', 4, 4),
('9780062316097', 'Sapiens', 2, 'Harper', 2015, 'A brief history of humankind', 'https://covers.openlibrary.org/b/isbn/9780062316097-L.jpg', 2, 2),
('9780399590504', 'Educated', 6, 'Random House', 2018, 'A memoir about education and family', 'https://covers.openlibrary.org/b/isbn/9780399590504-L.jpg', 2, 2)
ON DUPLICATE KEY UPDATE `title`=`title`;

-- ============================================
-- INSERT TEST DATA: Book-Author Relationships
-- ============================================
INSERT INTO `book_authors` (`book_id`, `author_id`)
VALUES 
(1, 1), -- The Great Gatsby by F. Scott Fitzgerald
(2, 2), -- To Kill a Mockingbird by Harper Lee
(3, 3), -- 1984 by George Orwell
(4, 4), -- Sapiens by Yuval Noah Harari
(5, 5)  -- Educated by Tara Westover
ON DUPLICATE KEY UPDATE `book_id`=`book_id`;

-- ============================================
-- VERIFICATION QUERIES
-- ============================================

-- Check users
SELECT 'Users Table' as 'Table', COUNT(*) as 'Record Count' FROM `users`;

-- Check books
SELECT 'Books Table' as 'Table', COUNT(*) as 'Record Count' FROM `books`;

-- Check loans
SELECT 'Loans Table' as 'Table', COUNT(*) as 'Record Count' FROM `loans`;

-- Check categories
SELECT 'Categories Table' as 'Table', COUNT(*) as 'Record Count' FROM `categories`;

-- ============================================
-- SETUP COMPLETE!
-- ============================================
-- Database: khmer-dbase
-- 
-- Test Credentials:
-- Admin: admin@test.com / admin123
-- Patron: patron@test.com / password123
-- Patron: borrower@test.com / password123
-- 
-- Next Steps:
-- 1. Update config/database.php to use 'khmer-dbase'
-- 2. Test login with the credentials above
-- 3. Browse books and test borrowing flow
-- ============================================
