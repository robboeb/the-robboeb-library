-- ============================================
-- Complete Database Setup with Sample Data
-- Database: khmer-mysql-library-db
-- ============================================

-- Create Database
CREATE DATABASE IF NOT EXISTS `khmer-mysql-library-db` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `khmer-mysql-library-db`;

-- ============================================
-- TABLE: categories
-- ============================================
CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================
-- TABLE: authors
-- ============================================
CREATE TABLE IF NOT EXISTS `authors` (
  `author_id` INT(11) NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `biography` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`author_id`),
  KEY `idx_name` (`first_name`, `last_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: books
-- ============================================
CREATE TABLE IF NOT EXISTS `books` (
  `book_id` INT(11) NOT NULL AUTO_INCREMENT,
  `isbn` VARCHAR(20) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `category_id` INT(11) DEFAULT NULL,
  `publisher` VARCHAR(255) DEFAULT NULL,
  `publication_year` YEAR(4) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `cover_image` VARCHAR(500) DEFAULT NULL,
  `pdf_file` VARCHAR(255) DEFAULT NULL,
  `total_quantity` INT(11) DEFAULT 1,
  `available_quantity` INT(11) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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
  `book_id` INT(11) NOT NULL,
  `author_id` INT(11) NOT NULL,
  PRIMARY KEY (`book_id`, `author_id`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `book_authors_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE,
  CONSTRAINT `book_authors_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `authors` (`author_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: users
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `user_type` ENUM('admin','librarian','patron') DEFAULT 'patron',
  `status` ENUM('active','inactive','suspended') DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: loans
-- ============================================
CREATE TABLE IF NOT EXISTS `loans` (
  `loan_id` INT(11) NOT NULL AUTO_INCREMENT,
  `book_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `loan_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `return_date` DATE DEFAULT NULL,
  `status` ENUM('pending','approved','borrowed','returned','rejected','overdue') DEFAULT 'pending',
  `fine_amount` DECIMAL(10,2) DEFAULT 0.00,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`loan_id`),
  KEY `idx_book_id` (`book_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE,
  CONSTRAINT `loans_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERT DATA: Categories
-- ============================================
INSERT INTO `categories` (`category_id`, `name`, `description`) VALUES
(1, 'Self-Development', 'Books about personal growth, motivation, and self-improvement'),
(2, 'Psychology', 'Books about mental health, mindfulness, and psychological concepts'),
(3, 'Philosophy', 'Books about life philosophy, wisdom, and thinking'),
(4, 'Science', 'Books about scientific knowledge and human understanding'),
(5, 'Biography', 'Books about life stories and personal experiences'),
(6, 'Business', 'Books about business strategies and success principles'),
(7, 'Health', 'Books about physical and mental health');

-- ============================================
-- INSERT DATA: Authors
-- ============================================
INSERT INTO `authors` (`author_id`, `first_name`, `last_name`, `biography`) VALUES
(1, 'Khmer', 'Author', 'Cambodian author specializing in self-development'),
(2, 'Scholarly', 'Library', 'Publisher and author collective'),
(3, 'Unknown', 'Author', 'Author information not available');


-- ============================================
-- INSERT DATA: Books (with English titles and cover URLs)
-- ============================================
INSERT INTO `books` (`book_id`, `isbn`, `title`, `category_id`, `publisher`, `publication_year`, `description`, `cover_image`, `total_quantity`, `available_quantity`) VALUES
(1, '978-99963-001-01', 'Role Model Building Good Character Through Good Deeds', 1, 'Scholarly Library', 2023, 'A book about building character and becoming a role model through positive actions and good deeds', 'https://books.scholarlibrary.com/web/image/product.template/18725/image_512/%E1%9E%94%E1%9E%BB%E1%9E%82%E1%9F%92%E1%9E%82%E1%9E%9B%E1%9E%82%E1%9F%86%E1%9E%9A%E1%9E%BC%E1%9E%9F%E1%9E%B6%E1%9E%84%E1%9E%97%E1%9E%B6%E1%9E%96%E1%9E%92%E1%9F%86%E1%9E%92%E1%9F%81%E1%9E%84%E1%9E%8A%E1%9F%84%E1%9E%99%E1%9E%A2%E1%9F%86%E1%9E%96%E1%9E%BE%E1%9E%9B%E1%9F%92%E1%9E%A2?unique=0296154', 10, 10),

(2, '978-99963-001-02', 'Direct Thinking Mental Strength', 2, 'Scholarly Library', 2023, 'A guide to developing mental strength through direct and focused thinking patterns', 'https://books.scholarlibrary.com/web/image/product.template/18726/image_512/%E1%9E%82%E1%9F%86%E1%9E%93%E1%9E%B7%E1%9E%8F%E1%9E%95%E1%9F%92%E1%9E%8A%E1%9E%9B%E1%9F%8B%E1%9E%80%E1%9E%98%E1%9F%92%E1%9E%9B%E1%9E%B6%E1%9F%86%E1%9E%84%E1%9E%85%E1%9E%B7%E1%9E%8F%E1%9F%92%E1%9E%8F?unique=0296154', 10, 10),

(3, '978-99963-001-03', 'Life Formula', 3, 'Scholarly Library', 2023, 'Discover the essential formulas and principles for living a successful and meaningful life', 'https://books.scholarlibrary.com/web/image/product.template/18727/image_512/%E1%9E%9A%E1%9E%BC%E1%9E%94%E1%9E%98%E1%9E%93%E1%9F%92%E1%9E%8A%E1%9E%87%E1%9E%B8%E1%9E%9C%E1%9E%B7%E1%9E%8F?unique=0296154', 10, 10),

(4, '978-99963-001-04', 'Science of Understanding Humans', 4, 'Scholarly Library', 2023, 'A scientific approach to understanding human behavior, psychology, and nature', 'https://books.scholarlibrary.com/web/image/product.template/18744/image_512/%E1%9E%9C%E1%9E%B7%E1%9E%92%E1%9E%B8%E1%9E%9F%E1%9E%B6%E1%9E%9F%E1%9F%92%E1%9E%9A%E1%9F%92%E1%9E%8F%E1%9E%98%E1%9E%BE%E1%9E%9B%E1%9E%98%E1%9E%93%E1%9E%BB%E1%9E%9F%E1%9F%92%E1%9E%9F?unique=0296154', 10, 10),

(5, '978-99963-001-05', '100 Wise Thoughts to Achieve Success', 1, 'Scholarly Library', 2023, 'A collection of 100 wisdom principles and thoughts to guide you toward success', 'https://books.scholarlibrary.com/web/image/product.template/18728/image_512/%E1%9F%A1%E1%9F%A0%E1%9F%A0%E1%9E%82%E1%9E%8F%E1%9E%B7%E1%9E%94%E1%9E%8E%E1%9F%92%E1%9E%8C%E1%9E%B7%E1%9E%8F%E1%9E%8A%E1%9E%BE%E1%9E%98%E1%9F%92%E1%9E%94%E1%9E%B8%E1%9E%91%E1%9E%91%E1%9E%BD%E1%9E%9B%E1%9E%94%E1%9E%B6%E1%9E%93%E1%9E%87%E1%9F%84%E1%9E%82%E1%9E%87%E1%9F%90%E1%9E%99?unique=0296154', 10, 10),

(6, '978-99963-001-06', 'Think Like Pepkok', 2, 'Scholarly Library', 2023, 'Learn to think strategically and wisely using proven mental frameworks', 'https://books.scholarlibrary.com/web/image/product.template/18743/image_512/%E1%9E%82%E1%9E%B7%E1%9E%8F%E1%9E%94%E1%9F%82%E1%9E%94%E1%9E%8F%E1%9E%80%E1%9F%92%E1%9E%80%E1%9F%88?unique=0296154', 10, 10),

(7, '978-99963-001-07', 'Think Like Chinese Prosperity and Dynasty', 6, 'Scholarly Library', 2023, 'Ancient Chinese wisdom on building wealth, prosperity, and lasting legacy', 'https://books.scholarlibrary.com/web/image/product.template/18745/image_512/%E1%9E%82%E1%9E%B7%E1%9E%8F%E1%9E%9A%E1%9E%94%E1%9F%80%E1%9E%94%E1%9E%85%E1%9E%B7%E1%9E%93%E1%9E%9A%E1%9E%BB%E1%9E%84%E1%9E%9A%E1%9E%BF%E1%9E%84%E1%9E%98%E1%9E%B6%E1%9E%93%E1%9E%94%E1%9E%B6%E1%9E%93%E1%9E%82%E1%9E%84%E1%9F%8B%E1%9E%9C%E1%9E%84%E1%9F%92%E1%9E%9F?unique=0296154', 10, 10),

(8, '978-99963-001-08', 'Life Can Be Changed', 1, 'Scholarly Library', 2023, 'Inspiring stories and practical advice on how to transform your life for the better', 'https://books.scholarlibrary.com/web/image/product.template/18739/image_512/%E1%9E%87%E1%9E%B8%E1%9E%9C%E1%9E%B7%E1%9E%8F%E1%9E%A2%E1%9E%B6%E1%9E%85%E1%9E%95%E1%9F%92%E1%9E%9B%E1%9E%B6%E1%9E%9F%E1%9F%8B%E1%9E%94%E1%9F%92%E1%9E%8A%E1%9E%BC%E1%9E%9A%E1%9E%94%E1%9E%B6%E1%9E%93?unique=0296154', 10, 10),

(9, '978-99963-001-09', 'Feng Shui by Yourself', 3, 'Scholarly Library', 2023, 'A practical guide to understanding and applying Feng Shui principles in your daily life', 'https://books.scholarlibrary.com/web/image/product.template/18729/image_512/%5BSl%5D%20%E1%9E%98%E1%9E%BE%E1%9E%9B%E1%9E%A0%E1%9E%BB%E1%9E%84%E1%9E%9F%E1%9F%8A%E1%9E%BB%E1%9E%99%E1%9E%8A%E1%9F%84%E1%9E%99%E1%9E%81%E1%9F%92%E1%9E%9B%E1%9E%BD%E1%9E%93?unique=f75f17d', 10, 10),

(10, '978-99963-001-10', 'Without Understanding Suffering You Cannot Understand Happiness', 2, 'Scholarly Library', 2023, 'Philosophical insights on the relationship between suffering and happiness in life', 'https://books.scholarlibrary.com/web/image/product.template/18742/image_512/%E1%9E%94%E1%9E%BE%E1%9E%98%E1%9E%B7%E1%9E%93%E1%9E%99%E1%9E%9B%E1%9F%8B%E1%9E%96%E1%9E%B8%E1%9E%91%E1%9E%BB%E1%9E%80%E1%9F%92%E1%9E%81%E1%9E%92%E1%9F%92%E1%9E%9C%E1%9E%BE%E1%9E%98%E1%9F%89%E1%9F%81%E1%9E%85%E1%9E%93%E1%9E%B9%E1%9E%84%E1%9E%99%E1%9E%9B%E1%9F%8B%E1%9E%96%E1%9E%B8%E1%9E%9F%E1%9E%BB%E1%9E%81%E1%9E%94%E1%9E%B6%E1%9E%93?unique=0296154', 10, 10),

(11, '978-99963-001-11', 'There is Life Without Giving Up and Giving Up Happiness', 7, 'Scholarly Library', 2023, 'Motivational guide about perseverance and finding happiness through resilience', 'https://books.scholarlibrary.com/web/image/product.template/18738/image_512/%E1%9E%98%E1%9E%B6%E1%9E%93%E1%9E%87%E1%9E%B8%E1%9E%9C%E1%9E%B7%E1%9E%8F%E1%9E%98%E1%9E%B7%E1%9E%93%E1%9E%98%E1%9F%82%E1%9E%93%E1%9E%8A%E1%9E%BE%E1%9E%98%E1%9F%92%E1%9E%94%E1%9E%B8%E1%9E%85%E1%9E%BB%E1%9F%87%E1%9E%85%E1%9E%B6%E1%9E%89%E1%9F%8B?unique=0296154', 10, 10),

(12, '978-99963-001-12', '33 Situations to Avoid in Leadership', 6, 'Scholarly Library', 2023, 'Essential leadership lessons identifying common pitfalls and how to avoid them', 'https://books.scholarlibrary.com/web/image/product.template/18741/image_512/%E1%9F%A3%E1%9F%A3%E1%9E%9F%E1%9E%98%E1%9F%92%E1%9E%90%E1%9E%97%E1%9E%B6%E1%9E%96%E1%9E%94%E1%9E%84%E1%9F%92%E1%9E%80%E1%9E%BE%E1%9E%8F%E1%9E%B1%E1%9E%80%E1%9E%B6%E1%9E%9F%E1%9E%91%E1%9F%85%E1%9E%80%E1%9E%B6%E1%9E%93%E1%9F%8B%E1%9E%97%E1%9E%B6%E1%9E%96%E1%9E%87%E1%9F%84%E1%9E%82%E1%9E%87%E1%9F%90%E1%9E%99?unique=0296154', 10, 10),

(13, '978-99963-001-13', 'Extraordinary Character', 1, 'Scholarly Library', 2023, 'Building exceptional character traits that lead to personal and professional success', 'https://books.scholarlibrary.com/web/image/product.template/18730/image_512/%E1%9E%85%E1%9E%9A%E1%9E%B7%E1%9E%8F%E1%9F%A6%E1%9E%99%E1%9F%89%E1%9E%B6%E1%9E%84?unique=0296154', 10, 10),

(14, '978-99963-001-14', 'Secrets of Health and Happiness', 7, 'Scholarly Library', 2023, 'Discover the hidden connections between physical health and mental happiness', 'https://books.scholarlibrary.com/web/image/product.template/18737/image_512/%E1%9E%80%E1%9F%86%E1%9E%96%E1%9E%BC%E1%9E%9B%E1%9E%97%E1%9F%92%E1%9E%93%E1%9F%86%E1%9E%93%E1%9F%83%E1%9E%9F%E1%9F%81%E1%9E%85%E1%9E%80%E1%9F%92%E1%9E%8A%E1%9E%B8%E1%9E%9F%E1%9E%BB%E1%9E%81%E1%9E%87%E1%9E%BE%E1%9E%84%E1%9E%97%E1%9F%92%E1%9E%93%E1%9F%86%E1%9E%93%E1%9F%83%E1%9E%A7%E1%9E%94%E1%9E%9F%E1%9E%82%E1%9F%92%E1%9E%82?unique=0296154', 10, 10),

(15, '978-99963-001-15', 'Art of Learning Goals', 1, 'Scholarly Library', 2023, 'Master the art of setting and achieving meaningful goals in life', 'https://books.scholarlibrary.com/web/image/product.template/18731/image_512/%E1%9E%9F%E1%9E%B7%E1%9E%9B%E1%9F%92%E1%9E%94%E1%9F%88%E1%9E%9A%E1%9F%80%E1%9E%94%E1%9E%85%E1%9F%86%E1%9E%82%E1%9F%84%E1%9E%9B%E1%9E%8A%E1%9F%85?unique=0296154', 10, 10),

(16, '978-99963-001-16', 'Human Life and Wisdom', 5, 'Scholarly Library', 2023, 'Exploring the depths of human existence through wisdom and life experiences', 'https://books.scholarlibrary.com/web/image/product.template/18732/image_512/%E1%9E%87%E1%9E%B8%E1%9E%9C%E1%9E%B7%E1%9E%8F%E1%9E%98%E1%9E%93%E1%9E%BB%E1%9E%9F%E1%9F%92%E1%9E%9F%E1%9E%93%E1%9E%B7%E1%9E%84%E1%9E%94%E1%9E%89%E1%9F%92%E1%9E%A0%E1%9E%B6?unique=0296154', 10, 10),

(17, '978-99963-001-17', 'Life Must Be True', 3, 'Scholarly Library', 2023, 'Living authentically and truthfully in a complex world', 'https://books.scholarlibrary.com/web/image/product.template/18736/image_512/%E1%9E%87%E1%9E%B8%E1%9E%9C%E1%9E%B7%E1%9E%8F%E1%9E%8F%E1%9F%92%E1%9E%9A%E1%9E%BC%E1%9E%9C%E1%9E%8F%E1%9F%82%E1%9E%8F%E1%9E%9F%E1%9F%8A%E1%9E%BC?unique=0296154', 10, 10),

(18, '978-99963-001-18', 'One Hundred Public Stories', 5, 'Scholarly Library', 2023, 'A collection of inspiring public stories and life lessons', 'https://books.scholarlibrary.com/web/image/product.template/18733/image_512/%E1%9E%9A%E1%9E%B6%E1%9E%94%E1%9F%8B%E1%9E%98%E1%9E%BD%E1%9E%99%E1%9E%9F%E1%9E%B6%E1%9E%87%E1%9E%B6%E1%9E%90%E1%9F%92%E1%9E%98%E1%9E%B8?unique=0296154', 10, 10),

(19, '978-99963-001-19', 'Mindfulness Practice', 2, 'Scholarly Library', 2023, 'Practical guide to developing mindfulness and mental clarity', 'https://books.scholarlibrary.com/web/image/product.template/18734/image_512/%E1%9E%92%E1%9F%92%E1%9E%9C%E1%9E%BE%E1%9E%85%E1%9E%B7%E1%9E%8F%E1%9F%92%E1%9E%8F%E1%9E%B2%E1%9F%92%E1%9E%99%E1%9E%8F%E1%9F%92%E1%9E%9A%E1%9E%87%E1%9E%B6%E1%9E%80%E1%9F%8B?unique=0296154', 10, 10),

(20, '978-99963-001-20', 'Science of Reading Minds', 4, 'Scholarly Library', 2023, 'Understanding human psychology and the science behind reading people', 'https://books.scholarlibrary.com/web/image/product.template/18735/image_512/%E1%9E%9C%E1%9E%B7%E1%9E%92%E1%9E%B8%E1%9E%A2%E1%9E%B6%E1%9E%93%E1%9E%85%E1%9E%B7%E1%9E%8F%E1%9F%92%E1%9E%8F%E1%9E%A2%E1%9F%92%E1%9E%93%E1%9E%80%E1%9E%93%E1%9F%85%E1%9E%80%E1%9F%92%E1%9E%94%E1%9F%82%E1%9E%9A%E1%9E%81%E1%9F%92%E1%9E%9B%E1%9E%BD%E1%9E%93?unique=0296154', 10, 10);


-- ============================================
-- INSERT DATA: Book-Author Relationships
-- ============================================
INSERT INTO `book_authors` (`book_id`, `author_id`) VALUES
(1, 1), (2, 1), (3, 1), (4, 1), (5, 1),
(6, 2), (7, 2), (8, 2), (9, 2), (10, 2),
(11, 1), (12, 2), (13, 1), (14, 2), (15, 1),
(16, 1), (17, 1), (18, 2), (19, 1), (20, 2);

-- ============================================
-- INSERT DATA: Sample Admin User
-- Password: admin123 (hashed with bcrypt)
-- ============================================
INSERT INTO `users` (`user_id`, `email`, `password`, `first_name`, `last_name`, `phone`, `user_type`, `status`) VALUES
(1, 'admin@library.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', '012-345-678', 'admin', 'active'),
(2, 'librarian@library.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Librarian', 'User', '012-345-679', 'librarian', 'active'),
(3, 'patron@library.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Patron', 'User', '012-345-680', 'patron', 'active');

-- ============================================
-- COMPLETE! Database Setup Finished
-- ============================================
-- Database: khmer-mysql-library-db
-- Tables Created: 6 (categories, authors, books, book_authors, users, loans)
-- Sample Data Inserted:
--   - 7 Categories
--   - 3 Authors
--   - 20 Books (with English titles and cover URLs)
--   - 20 Book-Author relationships
--   - 3 Sample users (admin, librarian, patron)
-- 
-- Default Login Credentials:
--   Admin: admin@library.com / admin123
--   Librarian: librarian@library.com / admin123
--   Patron: patron@library.com / admin123
-- ============================================
