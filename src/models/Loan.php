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

    public function findPendingLoans() {
        try {
            $sql = "SELECT l.*, b.title as book_title, b.isbn, b.available_quantity,
                    CONCAT(u.first_name, ' ', u.last_name) as user_name, u.email, u.phone
                    FROM {$this->table} l 
                    INNER JOIN books b ON l.book_id = b.book_id 
                    INNER JOIN users u ON l.user_id = u.user_id 
                    WHERE l.status = 'pending'
                    ORDER BY l.created_at ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Error fetching pending loans");
        }
    }

    public function findByUser($userId, $limit = null, $offset = 0) {
        try {
            $sql = "SELECT l.*, b.title as book_title, b.isbn 
                    FROM {$this->table} l 
                    INNER JOIN books b ON l.book_id = b.book_id 
                    WHERE l.user_id = :user_id 
                    ORDER BY l.loan_date DESC";
            
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

    public function requestBorrow($bookId, $userId) {
        try {
            // Validate inputs
            if (!$bookId || !$userId) {
                return ['success' => false, 'error' => 'Invalid book ID or user ID'];
            }

            // Check if book exists and is available
            $checkSql = "SELECT book_id, available_quantity, title FROM books WHERE book_id = :book_id";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
            $checkStmt->execute();
            $book = $checkStmt->fetch();

            if (!$book) {
                return ['success' => false, 'error' => 'Book not found'];
            }

            if ($book['available_quantity'] <= 0) {
                return ['success' => false, 'error' => 'Book is currently not available. All copies are borrowed.'];
            }

            // Check if user already has a pending or active request for this book
            $existingSql = "SELECT loan_id, status FROM {$this->table} 
                           WHERE book_id = :book_id AND user_id = :user_id 
                           AND status IN ('pending', 'active')";
            $existingStmt = $this->db->prepare($existingSql);
            $existingStmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
            $existingStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $existingStmt->execute();
            
            $existing = $existingStmt->fetch();
            if ($existing) {
                $statusText = $existing['status'] === 'pending' ? 'pending approval' : 'currently borrowed';
                return ['success' => false, 'error' => "You already have this book $statusText"];
            }

            // Create pending loan request
            $loanData = [
                'book_id' => $bookId,
                'user_id' => $userId,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->create($loanData);
            if (!$result['success']) {
                $this->logError("Failed to create loan: " . json_encode($result));
                return ['success' => false, 'error' => 'Failed to create borrow request'];
            }

            return ['success' => true, 'loan_id' => $result['id']];
            
        } catch (PDOException $e) {
            $this->logError("Database error in requestBorrow: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error occurred'];
        } catch (Exception $e) {
            $this->logError("Error in requestBorrow: " . $e->getMessage());
            return ['success' => false, 'error' => 'An error occurred while processing your request'];
        }
    }

    public function approveLoan($loanId, $dueDate) {
        try {
            $this->db->beginTransaction();

            // Get loan details
            $loanSql = "SELECT * FROM {$this->table} WHERE loan_id = :loan_id FOR UPDATE";
            $loanStmt = $this->db->prepare($loanSql);
            $loanStmt->bindValue(':loan_id', $loanId, PDO::PARAM_INT);
            $loanStmt->execute();
            $loan = $loanStmt->fetch();

            if (!$loan) {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'Loan not found'];
            }

            if ($loan['status'] !== 'pending') {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'Loan is not pending'];
            }

            // Check book availability
            $checkSql = "SELECT available_quantity FROM books WHERE book_id = :book_id FOR UPDATE";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->bindValue(':book_id', $loan['book_id'], PDO::PARAM_INT);
            $checkStmt->execute();
            $book = $checkStmt->fetch();

            if (!$book || $book['available_quantity'] <= 0) {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'Book no longer available'];
            }

            // Update loan to active
            $updateLoanSql = "UPDATE {$this->table} 
                             SET status = 'borrowed', 
                                 loan_date = :loan_date,
                                 due_date = :due_date
                             WHERE loan_id = :loan_id";
            $updateLoanStmt = $this->db->prepare($updateLoanSql);
            $updateLoanStmt->bindValue(':loan_date', date('Y-m-d'));
            $updateLoanStmt->bindValue(':due_date', $dueDate);
            $updateLoanStmt->bindValue(':loan_id', $loanId, PDO::PARAM_INT);
            $updateLoanStmt->execute();

            // Decrease available quantity
            $updateBookSql = "UPDATE books SET available_quantity = available_quantity - 1 WHERE book_id = :book_id";
            $updateBookStmt = $this->db->prepare($updateBookSql);
            $updateBookStmt->bindValue(':book_id', $loan['book_id'], PDO::PARAM_INT);
            $updateBookStmt->execute();

            $this->db->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->logError($e->getMessage());
            throw new Exception("Error approving loan");
        }
    }

    public function rejectLoan($loanId) {
        try {
            $loanSql = "SELECT * FROM {$this->table} WHERE loan_id = :loan_id";
            $loanStmt = $this->db->prepare($loanSql);
            $loanStmt->bindValue(':loan_id', $loanId, PDO::PARAM_INT);
            $loanStmt->execute();
            $loan = $loanStmt->fetch();

            if (!$loan) {
                return ['success' => false, 'error' => 'Loan not found'];
            }

            if ($loan['status'] !== 'pending') {
                return ['success' => false, 'error' => 'Loan is not pending'];
            }

            $updateSql = "UPDATE {$this->table} SET status = 'rejected' WHERE loan_id = :loan_id";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->bindValue(':loan_id', $loanId, PDO::PARAM_INT);
            $updateStmt->execute();

            return ['success' => true];
        } catch (Exception $e) {
            $this->logError($e->getMessage());
            throw new Exception("Error rejecting loan");
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
                'loan_date' => date('Y-m-d'),
                'due_date' => $dueDate,
                'status' => 'borrowed'
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

        // Book ID and User ID are always required
        if (empty($data['book_id'])) {
            $errors['book_id'] = 'Book ID is required';
        }

        if (empty($data['user_id'])) {
            $errors['user_id'] = 'User ID is required';
        }

        // For pending requests, loan_date and due_date are not required
        // They will be set when admin approves the request
        $isPending = isset($data['status']) && $data['status'] === 'pending';

        if (!$isPending) {
            // For borrowed loans, loan_date and due_date are required
            if (empty($data['loan_date'])) {
                $errors['loan_date'] = 'Loan date is required';
            }

            if (empty($data['due_date'])) {
                $errors['due_date'] = 'Due date is required';
            } elseif (!empty($data['loan_date']) && strtotime($data['due_date']) < strtotime($data['loan_date'])) {
                $errors['due_date'] = 'Due date must be after loan date';
            }
        }

        return $errors;
    }
}
