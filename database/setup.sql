-- ============================================
-- ROBBOEB LIBRARY - Complete Database Setup
-- ============================================
-- This file contains everything needed to set up the library database
-- Run this single file to create database, tables, and seed data
-- ============================================

-- Create and use database
CREATE DATABASE IF NOT EXISTS `library-db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `library-db`;

-- ============================================
-- CREATE TABLES
-- ============================================

CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    user_type ENUM('admin', 'patron') DEFAULT 'patron',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_user_type (user_type),
    INDEX idx_status (status)
);

CREATE TABLE IF NOT EXISTS categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
);

CREATE TABLE IF NOT EXISTS authors (
    author_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    biography TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (last_name, first_name)
);

CREATE TABLE IF NOT EXISTS books (
    book_id INT PRIMARY KEY AUTO_INCREMENT,
    isbn VARCHAR(13) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    category_id INT,
    publication_year YEAR,
    description TEXT,
    cover_image VARCHAR(255),
    pdf_file VARCHAR(500),
    publisher VARCHAR(255),
    total_quantity INT DEFAULT 1,
    available_quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL,
    INDEX idx_isbn (isbn),
    INDEX idx_title (title),
    INDEX idx_category (category_id),
    INDEX idx_availability (available_quantity)
);

CREATE TABLE IF NOT EXISTS book_authors (
    book_id INT,
    author_id INT,
    PRIMARY KEY (book_id, author_id),
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES authors(author_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS loans (
    loan_id INT PRIMARY KEY AUTO_INCREMENT,
    book_id INT NOT NULL,
    user_id INT NOT NULL,
    checkout_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE,
    status ENUM('active', 'returned', 'overdue') DEFAULT 'active',
    fine_amount DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE RESTRICT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE RESTRICT,
    INDEX idx_user (user_id),
    INDEX idx_book (book_id),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date)
);

CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT NOT NULL,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- SEED DATA
-- ============================================

-- Insert Users (password for all: "password")
INSERT INTO users (email, password_hash, first_name, last_name, user_type, status) VALUES
('admin@libra.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', 'active'),
('john.doe@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', 'patron', 'active'),
('jane.smith@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Smith', 'patron', 'active');

-- Insert Categories
INSERT INTO categories (name, description) VALUES
('Fiction', 'Fictional literature and novels'),
('Non-Fiction', 'Factual and informational books'),
('Science', 'Scientific and technical books'),
('History', 'Historical books and biographies'),
('Technology', 'Technology and computer science books'),
('Arts', 'Art, music, and creative works'),
('Business', 'Business and economics books'),
('Self-Help', 'Personal development and self-improvement');

-- Insert Authors
INSERT INTO authors (first_name, last_name, biography) VALUES
('George', 'Orwell', 'English novelist and essayist, journalist and critic'),
('Jane', 'Austen', 'English novelist known for her six major novels'),
('Stephen', 'King', 'American author of horror, supernatural fiction, suspense, and fantasy novels'),
('J.K.', 'Rowling', 'British author, best known for the Harry Potter series'),
('Agatha', 'Christie', 'English writer known for her detective novels'),
('Isaac', 'Asimov', 'American writer and professor of biochemistry'),
('Arthur', 'Conan Doyle', 'British writer best known for his detective fiction'),
('Mark', 'Twain', 'American writer, humorist, entrepreneur, publisher, and lecturer');

-- Insert Books with Cover Images
INSERT INTO books (isbn, title, category_id, publication_year, description, cover_image, total_quantity, available_quantity) VALUES
('9780451524935', '1984', 1, 1949, 'A dystopian social science fiction novel', 'https://covers.openlibrary.org/b/isbn/9780451524935-L.jpg', 5, 5),
('9780141439518', 'Pride and Prejudice', 1, 1813, 'A romantic novel of manners', 'https://covers.openlibrary.org/b/isbn/9780141439518-L.jpg', 3, 3),
('9780307743657', 'The Shining', 1, 1977, 'A horror novel', 'https://covers.openlibrary.org/b/isbn/9780307743657-L.jpg', 4, 4),
('9780439708180', 'Harry Potter and the Sorcerers Stone', 1, 1997, 'A fantasy novel', 'https://covers.openlibrary.org/b/isbn/9780439708180-L.jpg', 10, 10),
('9780062073488', 'Murder on the Orient Express', 1, 1934, 'A detective novel', 'https://covers.openlibrary.org/b/isbn/9780062073488-L.jpg', 3, 3),
('9780553293357', 'Foundation', 3, 1951, 'A science fiction novel', 'https://covers.openlibrary.org/b/isbn/9780553293357-L.jpg', 4, 4),
('9780143105428', 'The Adventures of Sherlock Holmes', 1, 1892, 'A collection of detective stories', 'https://covers.openlibrary.org/b/isbn/9780143105428-L.jpg', 5, 5),
('9780486280615', 'The Adventures of Tom Sawyer', 1, 1876, 'An adventure novel', 'https://covers.openlibrary.org/b/isbn/9780486280615-L.jpg', 3, 3);

-- Link Books to Authors
INSERT INTO book_authors (book_id, author_id) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5),
(6, 6),
(7, 7),
(8, 8);

-- Insert Settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('daily_fine_rate', '0.50', 'Daily fine rate for overdue books in dollars'),
('default_loan_period', '14', 'Default loan period in days'),
('max_loans_per_user', '5', 'Maximum number of active loans per user'),
('library_name', 'ROBBOEB LIBRARY', 'Name of the library'),
('library_email', 'info@libra.com', 'Library contact email');

-- ============================================
-- SETUP COMPLETE
-- ============================================

SELECT 'Database setup completed successfully!' as Status;
SELECT COUNT(*) as Total_Users FROM users;
SELECT COUNT(*) as Total_Books FROM books;
SELECT COUNT(*) as Total_Authors FROM authors;
SELECT COUNT(*) as Total_Categories FROM categories;
