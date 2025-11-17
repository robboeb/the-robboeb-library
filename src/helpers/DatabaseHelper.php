<?php
class DatabaseHelper {
    private static $pdo = null;
    
    public static function getConnection() {
        if (self::$pdo === null) {
            require_once __DIR__ . '/../../config/database.php';
            try {
                self::$pdo = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
    
    // Get dashboard statistics
    public static function getDashboardStats() {
        $pdo = self::getConnection();
        $stmt = $pdo->query("
            SELECT 
                (SELECT COUNT(*) FROM books) as total_books,
                (SELECT SUM(available_quantity) FROM books) as available_books,
                (SELECT COUNT(*) FROM loans WHERE status = 'active') as active_loans,
                (SELECT COUNT(*) FROM loans WHERE status = 'pending') as pending_requests,
                (SELECT COUNT(*) FROM loans WHERE status = 'active' AND due_date < CURDATE()) as overdue_loans,
                (SELECT COUNT(*) FROM users) as total_users,
                (SELECT COUNT(*) FROM categories) as total_categories,
                (SELECT COUNT(*) FROM authors) as total_authors
        ");
        return $stmt->fetch();
    }
    
    // Get all books with details
    public static function getAllBooks($filters = []) {
        $pdo = self::getConnection();
        
        $where = [];
        $params = [];
        
        if (!empty($filters['search'])) {
            $where[] = "(b.title LIKE :search OR b.isbn LIKE :search OR CONCAT(a.first_name, ' ', a.last_name) LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }
        
        if (!empty($filters['category_id'])) {
            $where[] = "b.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }
        
        if (!empty($filters['status'])) {
            // Map status to available_quantity
            if ($filters['status'] === 'available') {
                $where[] = "b.available_quantity > 0";
            } elseif ($filters['status'] === 'borrowed') {
                $where[] = "b.available_quantity = 0";
            }
        }
        
        $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $order_by = match($filters['sort'] ?? 'newest') {
            'title-asc' => 'b.title ASC',
            'title-desc' => 'b.title DESC',
            'author' => 'authors ASC',
            default => 'b.created_at DESC'
        };
        
        $limit = isset($filters['limit']) ? "LIMIT " . (int)$filters['limit'] : '';
        $offset = isset($filters['offset']) ? "OFFSET " . (int)$filters['offset'] : '';
        
        // Check if pdf_file and publisher columns exist
        $columns_check = $pdo->query("SHOW COLUMNS FROM books")->fetchAll(PDO::FETCH_COLUMN);
        $has_pdf_file = in_array('pdf_file', $columns_check);
        $has_publisher = in_array('publisher', $columns_check);
        
        $pdf_select = $has_pdf_file ? ', b.pdf_file' : ", '' as pdf_file";
        $publisher_select = $has_publisher ? ', b.publisher' : ", '' as publisher";
        
        $sql = "SELECT b.book_id, b.isbn, b.title, b.category_id, b.publication_year, 
                b.description, b.cover_image, b.total_quantity, b.available_quantity,
                b.created_at, b.updated_at
                $pdf_select
                $publisher_select,
                c.name as category_name,
                b.total_quantity as total_copies,
                b.available_quantity as available_copies,
                CASE 
                    WHEN b.available_quantity > 0 THEN 'available'
                    ELSE 'borrowed'
                END as status,
                GROUP_CONCAT(CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ') as authors
                FROM books b
                LEFT JOIN categories c ON b.category_id = c.category_id
                LEFT JOIN book_authors ba ON b.book_id = ba.book_id
                LEFT JOIN authors a ON ba.author_id = a.author_id
                $where_clause
                GROUP BY b.book_id
                ORDER BY $order_by
                $limit $offset";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Get all loans with details
    public static function getAllLoans($limit = null) {
        $pdo = self::getConnection();
        $limit_clause = $limit ? "LIMIT " . (int)$limit : '';
        
        $stmt = $pdo->query("
            SELECT l.*, 
                   b.title as book_title, b.isbn,
                   CONCAT(u.first_name, ' ', u.last_name) as user_name, u.email as user_email,
                   l.checkout_date as loan_date,
                   CASE 
                       WHEN l.return_date IS NOT NULL THEN 'returned'
                       WHEN l.due_date < CURDATE() THEN 'overdue'
                       ELSE 'active'
                   END as status
            FROM loans l
            INNER JOIN books b ON l.book_id = b.book_id
            INNER JOIN users u ON l.user_id = u.user_id
            ORDER BY l.checkout_date DESC
            $limit_clause
        ");
        return $stmt->fetchAll();
    }
    
    // Get all users
    public static function getAllUsers() {
        $pdo = self::getConnection();
        $stmt = $pdo->query("
            SELECT * FROM users 
            ORDER BY created_at DESC
        ");
        return $stmt->fetchAll();
    }
    
    // Get all categories
    public static function getAllCategories() {
        $pdo = self::getConnection();
        $stmt = $pdo->query("
            SELECT c.*, c.name as category_name, COUNT(b.book_id) as book_count
            FROM categories c
            LEFT JOIN books b ON c.category_id = b.category_id
            GROUP BY c.category_id
            ORDER BY c.name
        ");
        return $stmt->fetchAll();
    }
    
    // Get all authors
    public static function getAllAuthors() {
        $pdo = self::getConnection();
        $stmt = $pdo->query("
            SELECT a.*, COUNT(ba.book_id) as book_count
            FROM authors a
            LEFT JOIN book_authors ba ON a.author_id = ba.author_id
            GROUP BY a.author_id
            ORDER BY a.first_name, a.last_name
        ");
        return $stmt->fetchAll();
    }
    
    // Get book by ID
    public static function getBookById($id) {
        $pdo = self::getConnection();
        
        // Check if pdf_file and publisher columns exist
        $columns_check = $pdo->query("SHOW COLUMNS FROM books")->fetchAll(PDO::FETCH_COLUMN);
        $has_pdf_file = in_array('pdf_file', $columns_check);
        $has_publisher = in_array('publisher', $columns_check);
        
        $pdf_select = $has_pdf_file ? ', b.pdf_file' : ", '' as pdf_file";
        $publisher_select = $has_publisher ? ', b.publisher' : ", '' as publisher";
        
        $stmt = $pdo->prepare("
            SELECT b.book_id, b.isbn, b.title, b.category_id, b.publication_year, 
                   b.description, b.cover_image, b.total_quantity, b.available_quantity,
                   b.created_at, b.updated_at
                   $pdf_select
                   $publisher_select,
                   c.name as category_name,
                   b.total_quantity as total_copies,
                   b.available_quantity as available_copies,
                   CASE 
                       WHEN b.available_quantity > 0 THEN 'available'
                       ELSE 'borrowed'
                   END as status,
                   GROUP_CONCAT(CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ') as authors
            FROM books b
            LEFT JOIN categories c ON b.category_id = c.category_id
            LEFT JOIN book_authors ba ON b.book_id = ba.book_id
            LEFT JOIN authors a ON ba.author_id = a.author_id
            WHERE b.book_id = :id
            GROUP BY b.book_id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    // Get recent activity
    public static function getRecentActivity($limit = 10) {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("
            SELECT l.*, 
                   l.checkout_date as loan_date,
                   b.title as book_title,
                   CONCAT(u.first_name, ' ', u.last_name) as user_name
            FROM loans l
            INNER JOIN books b ON l.book_id = b.book_id
            INNER JOIN users u ON l.user_id = u.user_id
            ORDER BY l.checkout_date DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Get popular books
    public static function getPopularBooks($limit = 10) {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("
            SELECT b.book_id, b.title, b.isbn, COUNT(l.loan_id) as loan_count
            FROM books b
            LEFT JOIN loans l ON b.book_id = l.book_id
            GROUP BY b.book_id
            ORDER BY loan_count DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Get active users
    public static function getActiveUsers($limit = 10) {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("
            SELECT u.user_id, CONCAT(u.first_name, ' ', u.last_name) as user_name, 
                   u.email, COUNT(l.loan_id) as loan_count
            FROM users u
            LEFT JOIN loans l ON u.user_id = l.user_id
            WHERE u.user_type = 'patron'
            GROUP BY u.user_id
            ORDER BY loan_count DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Get category distribution
    public static function getCategoryDistribution() {
        $pdo = self::getConnection();
        $stmt = $pdo->query("
            SELECT c.name as category_name, COUNT(b.book_id) as book_count
            FROM categories c
            LEFT JOIN books b ON c.category_id = b.category_id
            GROUP BY c.category_id, c.name
            ORDER BY book_count DESC
        ");
        return $stmt->fetchAll();
    }
}
