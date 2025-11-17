<?php
require_once dirname(__DIR__) . '/models/Database.php';

class ReportService {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getDashboardStats() {
        try {
            $stats = [];

            // Total books
            $booksSql = "SELECT COUNT(*) as total_books FROM books";
            $booksStmt = $this->db->query($booksSql);
            $booksData = $booksStmt->fetch();
            $stats['total_books'] = (int)$booksData['total_books'];

            // Active loans
            $loansSql = "SELECT COUNT(*) as active_loans FROM loans WHERE status = 'active'";
            $loansStmt = $this->db->query($loansSql);
            $loansData = $loansStmt->fetch();
            $stats['active_loans'] = (int)$loansData['active_loans'];

            // Overdue loans
            $overdueSql = "SELECT COUNT(*) as overdue_loans FROM loans WHERE status = 'active' AND due_date < CURDATE()";
            $overdueStmt = $this->db->query($overdueSql);
            $overdueData = $overdueStmt->fetch();
            $stats['overdue_loans'] = (int)$overdueData['overdue_loans'];

            // Total users
            $usersSql = "SELECT COUNT(*) as total_users FROM users WHERE status = 'active'";
            $usersStmt = $this->db->query($usersSql);
            $usersData = $usersStmt->fetch();
            $stats['total_users'] = (int)$usersData['total_users'];

            // Total categories
            $categoriesSql = "SELECT COUNT(*) as total_categories FROM categories";
            $categoriesStmt = $this->db->query($categoriesSql);
            $categoriesData = $categoriesStmt->fetch();
            $stats['total_categories'] = (int)$categoriesData['total_categories'];

            // Total authors
            $authorsSql = "SELECT COUNT(*) as total_authors FROM authors";
            $authorsStmt = $this->db->query($authorsSql);
            $authorsData = $authorsStmt->fetch();
            $stats['total_authors'] = (int)$authorsData['total_authors'];

            return $stats;
        } catch (PDOException $e) {
            error_log("ReportService::getDashboardStats error: " . $e->getMessage());
            return [
                'total_books' => 0,
                'active_loans' => 0,
                'overdue_loans' => 0,
                'total_users' => 0,
                'total_categories' => 0,
                'total_authors' => 0
            ];
        }
    }

    public function getLoanTrends($startDate = null, $endDate = null) {
        try {
            $startDate = $startDate ?? date('Y-m-d', strtotime('-30 days'));
            $endDate = $endDate ?? date('Y-m-d');

            $sql = "SELECT DATE(created_at) as date, COUNT(*) as count 
                    FROM loans 
                    WHERE created_at BETWEEN :start_date AND :end_date 
                    GROUP BY DATE(created_at) 
                    ORDER BY date";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':start_date', $startDate);
            $stmt->bindValue(':end_date', $endDate);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("ReportService::getLoanTrends error: " . $e->getMessage());
            return [];
        }
    }

    public function getPopularBooks($limit = 10) {
        try {
            $sql = "SELECT b.book_id, b.title, b.isbn, COUNT(l.loan_id) as loan_count 
                    FROM books b 
                    LEFT JOIN loans l ON b.book_id = l.book_id 
                    GROUP BY b.book_id, b.title, b.isbn 
                    ORDER BY loan_count DESC 
                    LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("ReportService::getPopularBooks error: " . $e->getMessage());
            return [];
        }
    }

    public function getCategoryDistribution() {
        try {
            $sql = "SELECT c.name as category_name, COUNT(b.book_id) as book_count 
                    FROM categories c 
                    LEFT JOIN books b ON c.category_id = b.category_id 
                    GROUP BY c.category_id, c.name 
                    ORDER BY book_count DESC";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("ReportService::getCategoryDistribution error: " . $e->getMessage());
            return [];
        }
    }

    public function getMostActiveUsers($limit = 10) {
        try {
            $sql = "SELECT u.user_id, CONCAT(u.first_name, ' ', u.last_name) as user_name, 
                    u.email, COUNT(l.loan_id) as loan_count 
                    FROM users u 
                    LEFT JOIN loans l ON u.user_id = l.user_id 
                    WHERE u.user_type = 'patron' 
                    GROUP BY u.user_id, u.first_name, u.last_name, u.email 
                    ORDER BY loan_count DESC 
                    LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("ReportService::getMostActiveUsers error: " . $e->getMessage());
            return [];
        }
    }

    public function exportToCSV($data, $headers) {
        $output = fopen('php://temp', 'r+');
        
        fputcsv($output, $headers);
        
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    public function getRecentActivity($limit = 10) {
        try {
            $sql = "SELECT l.loan_id, l.checkout_date, l.return_date, l.status, 
                    b.title as book_title, 
                    CONCAT(u.first_name, ' ', u.last_name) as user_name 
                    FROM loans l 
                    INNER JOIN books b ON l.book_id = b.book_id 
                    INNER JOIN users u ON l.user_id = u.user_id 
                    ORDER BY l.created_at DESC 
                    LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error fetching recent activity");
        }
    }
}
