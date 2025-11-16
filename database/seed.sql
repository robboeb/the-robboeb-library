USE libra_db_sys;

INSERT INTO users (email, password_hash, first_name, last_name, user_type, status) VALUES
('admin@libra.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', 'active'),
('john.doe@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', 'patron', 'active'),
('jane.smith@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Smith', 'patron', 'active');

INSERT INTO categories (name, description) VALUES
('Fiction', 'Fictional literature and novels'),
('Non-Fiction', 'Factual and informational books'),
('Science', 'Scientific and technical books'),
('History', 'Historical books and biographies'),
('Technology', 'Technology and computer science books'),
('Arts', 'Art, music, and creative works'),
('Business', 'Business and economics books'),
('Self-Help', 'Personal development and self-improvement');

INSERT INTO authors (first_name, last_name, biography) VALUES
('George', 'Orwell', 'English novelist and essayist, journalist and critic'),
('Jane', 'Austen', 'English novelist known for her six major novels'),
('Stephen', 'King', 'American author of horror, supernatural fiction, suspense, and fantasy novels'),
('J.K.', 'Rowling', 'British author, best known for the Harry Potter series'),
('Agatha', 'Christie', 'English writer known for her detective novels'),
('Isaac', 'Asimov', 'American writer and professor of biochemistry'),
('Arthur', 'Conan Doyle', 'British writer best known for his detective fiction'),
('Mark', 'Twain', 'American writer, humorist, entrepreneur, publisher, and lecturer');

INSERT INTO books (isbn, title, category_id, publication_year, description, total_quantity, available_quantity) VALUES
('9780451524935', '1984', 1, 1949, 'A dystopian social science fiction novel', 5, 5),
('9780141439518', 'Pride and Prejudice', 1, 1813, 'A romantic novel of manners', 3, 3),
('9780307743657', 'The Shining', 1, 1977, 'A horror novel', 4, 4),
('9780439708180', 'Harry Potter and the Sorcerers Stone', 1, 1997, 'A fantasy novel', 10, 10),
('9780062073488', 'Murder on the Orient Express', 1, 1934, 'A detective novel', 3, 3),
('9780553293357', 'Foundation', 3, 1951, 'A science fiction novel', 4, 4),
('9780143105428', 'The Adventures of Sherlock Holmes', 1, 1892, 'A collection of detective stories', 5, 5),
('9780486280615', 'The Adventures of Tom Sawyer', 1, 1876, 'An adventure novel', 3, 3);

INSERT INTO book_authors (book_id, author_id) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5),
(6, 6),
(7, 7),
(8, 8);

INSERT INTO settings (setting_key, setting_value, description) VALUES
('daily_fine_rate', '0.50', 'Daily fine rate for overdue books in dollars'),
('default_loan_period', '14', 'Default loan period in days'),
('max_loans_per_user', '5', 'Maximum number of active loans per user'),
('library_name', 'Libra Library System', 'Name of the library'),
('library_email', 'info@libra.com', 'Library contact email');
