<?php
require_once __DIR__ . '/BaseModel.php';
require_once dirname(__DIR__, 2) . '/config/constants.php';

class Loan extends BaseModel {
    protected $table = 'loans';
    protected $primaryKey = 'loan_id';

    public function findActiveLoans($limit = null, $offset = 0) {
        try {
            $sql = "SELECT l.*, b.title as book_title, b.isbn, 
                    CONCAT(u.first_name, ' ', u.last_name) as user_name, u.email 
                    FROM {$this->table} l 
                    INNER JOIN books b ON l.book_id = b.book_id 
                    INNER JOIN users u ON l.user_id = u.user_id 
                    WHERE l.status = 'active' OR l.status = 'overdue'";
            
            if ($limit !== null) {
                $sql .= " LIMIT :limit OFFSET :offset";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            } else {
                $stmt = $this->db->prepare($sql);
            }
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Error fetching active loans");
        }
    }

    public function findOverdueLoans() {
        try {
            $sql = "SELECT l.*, b.title as book_title, b.isbn, 
                    CONCAT(u.first_name, ' ', u.last_name) as user_name, u.email 
                    FROM {$this->table} l 
                    INNER JOIN books b ON l.book_id = b.book_id 
                    INNER JOIN users u ON l.user_id = u.user_id 
                    WHERE l.due_date < CURDATE() AND l.return_date IS NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Error fetching overdue loans");
        }
    }

    public function findByUser($userId, $limit = null, $offset = 0) {
        try {
            $sql = "SELECT l.*, b.title as book_title, b.isbn 
                    FROM {$this->table} l 
                    INNER JOIN books b ON l.book_id = b.book_id 
                    WHERE l.user_id = :user_id 
                    ORDER BY l.checkout_date DESC";
            
            if ($limit !== null) {
                $sql .= " LIMIT :limit OFFSET :offset";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
                $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            } else {
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Error fetching user loans");
        }
    }

    public function checkoutBook($bookId, $userId, $dueDate) {
        try {
            $this->db->beginTransaction();

            $checkSql = "SELECT available_quantity FROM books WHERE book_id = :book_id FOR UPDATE";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
            $checkStmt->execute();
            $book = $checkStmt->fetch();

            if (!$book || $book['available_quantity'] <= 0) {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'Book not available'];
            }

            $loanData = [
                'book_id' => $bookId,
                'user_id' => $userId,
                'checkout_date' => date('Y-m-d'),
                'due_date' => $dueDate,
                'status' => 'active'
            ];

            $result = $this->create($loanData);
            if (!$result['success']) {
                $this->db->rollBack();
                return $result;
            }

            $updateSql = "UPDATE books SET available_quantity = available_quantity - 1 WHERE book_id = :book_id";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
            $updateStmt->execute();

            $this->db->commit();
            return ['success' => true, 'loan_id' => $result['id']];
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->logError($e->getMessage());
            throw new Exception("Error checking out book");
        }
    }

    public function returnBook($loanId) {
        try {
            $this->db->beginTransaction();

            $loanSql = "SELECT * FROM {$this->table} WHERE loan_id = :loan_id FOR UPDATE";
            $loanStmt = $this->db->prepare($loanSql);
            $loanStmt->bindValue(':loan_id', $loanId, PDO::PARAM_INT);
            $loanStmt->execute();
            $loan = $loanStmt->fetch();

            if (!$loan) {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'Loan not found'];
            }

            if ($loan['return_date'] !== null) {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'Book already returned'];
            }

            $returnDate = date('Y-m-d');
            $fine = $this->calculateFine($loan['due_date'], $returnDate);

            $updateLoanSql = "UPDATE {$this->table} 
                             SET return_date = :return_date, status = 'returned', fine_amount = :fine 
                             WHERE loan_id = :loan_id";
            $updateLoanStmt = $this->db->prepare($updateLoanSql);
            $updateLoanStmt->bindValue(':return_date', $returnDate);
            $updateLoanStmt->bindValue(':fine', $fine);
            $updateLoanStmt->bindValue(':loan_id', $loanId, PDO::PARAM_INT);
            $updateLoanStmt->execute();

            $updateBookSql = "UPDATE books SET available_quantity = available_quantity + 1 WHERE book_id = :book_id";
            $updateBookStmt = $this->db->prepare($updateBookSql);
            $updateBookStmt->bindValue(':book_id', $loan['book_id'], PDO::PARAM_INT);
            $updateBookStmt->execute();

            $this->db->commit();
            return ['success' => true, 'fine' => $fine];
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->logError($e->getMessage());
            throw new Exception("Error returning book");
        }
    }

    public function calculateFine($dueDate, $returnDate = null) {
        $returnDate = $returnDate ?? date('Y-m-d');
        $due = new DateTime($dueDate);
        $returned = new DateTime($returnDate);
        
        if ($returned <= $due) {
            return 0.00;
        }
        
        $daysOverdue = $due->diff($returned)->days;
        return $daysOverdue * DAILY_FINE_RATE;
    }

    public function updateOverdueStatus() {
        try {
            $sql = "UPDATE {$this->table} 
                    SET status = 'overdue' 
                    WHERE due_date < CURDATE() AND return_date IS NULL AND status = 'active'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return ['success' => true, 'updated' => $stmt->rowCount()];
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Error updating overdue status");
        }
    }

    protected function validate($data, $id = null) {
        $errors = [];

        if (empty($data['book_id'])) {
            $errors['book_id'] = 'Book ID is required';
        }

        if (empty($data['user_id'])) {
            $errors['user_id'] = 'User ID is required';
        }

        if (empty($data['checkout_date'])) {
            $errors['checkout_date'] = 'Checkout date is required';
        }

        if (empty($data['due_date'])) {
            $errors['due_date'] = 'Due date is required';
        } elseif (strtotime($data['due_date']) < strtotime($data['checkout_date'])) {
            $errors['due_date'] = 'Due date must be after checkout date';
        }

        return $errors;
    }
}
