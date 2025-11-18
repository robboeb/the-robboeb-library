<?php
/**
 * API Loan Controller
 * Handles loan/borrow operations with strict access controls
 */

require_once __DIR__ . '/BaseApiController.php';
require_once dirname(__DIR__, 3) . '/src/models/Loan.php';
require_once dirname(__DIR__, 3) . '/src/services/ValidationService.php';

class ApiLoanController extends BaseApiController {
    private $loanModel;

    public function __construct() {
        $this->loanModel = new Loan();
    }

    public function route($method, $id, $action) {
        // All loan endpoints require authentication
        $this->requireAuth();
        
        // GET /api/v1/loans - List loans
        if ($method === 'GET' && !$id) {
            $this->index();
        }
        // GET /api/v1/loans/overdue - Get overdue loans (admin only)
        elseif ($method === 'GET' && $id === 'overdue') {
            $this->requireAdmin();
            $this->getOverdue();
        }
        // GET /api/v1/loans/pending - Get pending loans (admin only)
        elseif ($method === 'GET' && $id === 'pending') {
            $this->requireAdmin();
            $this->getPending();
        }
        // GET /api/v1/loans/my - Get current user's loans
        elseif ($method === 'GET' && $id === 'my') {
            $this->getMyLoans();
        }
        // GET /api/v1/loans/{id} - Get single loan
        elseif ($method === 'GET' && $id) {
            $this->show($id);
        }
        // POST /api/v1/loans/request - Request to borrow a book
        elseif ($method === 'POST' && $id === 'request') {
            $this->requestBorrow();
        }
        // POST /api/v1/loans/checkout - Checkout book (admin only)
        elseif ($method === 'POST' && $id === 'checkout') {
            $this->requireAdmin();
            $this->checkout();
        }
        // PUT /api/v1/loans/{id}/approve - Approve loan request (admin only)
        elseif ($method === 'PUT' && $id && $action === 'approve') {
            $this->requireAdmin();
            $this->approveLoan($id);
        }
        // PUT /api/v1/loans/{id}/reject - Reject loan request (admin only)
        elseif ($method === 'PUT' && $id && $action === 'reject') {
            $this->requireAdmin();
            $this->rejectLoan($id);
        }
        // PUT /api/v1/loans/{id}/return - Return book (admin only)
        elseif ($method === 'PUT' && $id && $action === 'return') {
            $this->requireAdmin();
            $this->returnBook($id);
        }
        // POST /api/v1/loans/{id}/return - Return book (user can return their own)
        elseif ($method === 'POST' && $id && $action === 'return') {
            $this->returnBookByUser($id);
        }
        else {
            $this->methodNotAllowed();
        }
    }

    /**
     * List loans with filtering
     * GET /api/v1/loans?user_id=1&status=active&limit=10&offset=0
     */
    private function index() {
        try {
            $currentUser = $this->getCurrentUser();
            $params = $this->getQueryParams();
            
            $limit = isset($params['limit']) ? (int)$params['limit'] : 1000;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            $userId = $params['user_id'] ?? null;
            $status = $params['status'] ?? null;
            
            // Build query
            $sql = "SELECT l.*, 
                    b.title as book_title, b.isbn, b.cover_image,
                    CONCAT(u.first_name, ' ', u.last_name) as user_name, 
                    u.email as user_email,
                    CASE 
                        WHEN l.return_date IS NOT NULL THEN 'returned'
                        WHEN l.status = 'pending' THEN 'pending'
                        WHEN l.status = 'rejected' THEN 'rejected'
                        WHEN l.due_date < CURDATE() THEN 'overdue'
                        ELSE 'active'
                    END as loan_status
                    FROM loans l
                    INNER JOIN books b ON l.book_id = b.book_id
                    INNER JOIN users u ON l.user_id = u.user_id
                    WHERE 1=1";
            
            $bindParams = [];
            
            // Patrons can only see their own loans
            if ($currentUser['user_type'] === 'patron') {
                $sql .= " AND l.user_id = :current_user_id";
                $bindParams[':current_user_id'] = $currentUser['user_id'];
            } elseif ($userId) {
                // Admin can filter by user_id
                $sql .= " AND l.user_id = :user_id";
                $bindParams[':user_id'] = $userId;
            }
            
            if ($status) {
                if ($status === 'active') {
                    $sql .= " AND l.return_date IS NULL AND l.status = 'approved' AND l.due_date >= CURDATE()";
                } elseif ($status === 'overdue') {
                    $sql .= " AND l.return_date IS NULL AND l.status = 'approved' AND l.due_date < CURDATE()";
                } elseif ($status === 'returned') {
                    $sql .= " AND l.return_date IS NOT NULL";
                } elseif (in_array($status, ['pending', 'rejected'])) {
                    $sql .= " AND l.status = :status";
                    $bindParams[':status'] = $status;
                }
            }
            
            $sql .= " ORDER BY l.loan_date DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->loanModel->db->prepare($sql);
            
            foreach ($bindParams as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate fines for overdue loans
            foreach ($loans as &$loan) {
                if ($loan['loan_status'] === 'overdue') {
                    $loan['fine_amount'] = $this->loanModel->calculateFine($loan['due_date']);
                }
            }
            
            $this->sendResponse([
                'loans' => $loans,
                'count' => count($loans)
            ], 200);
            
        } catch (Exception $e) {
            error_log("Loan index error: " . $e->getMessage());
            $this->sendError('Failed to fetch loans', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Get single loan
     * GET /api/v1/loans/{id}
     */
    private function show($id) {
        try {
            if (!is_numeric($id)) {
                $this->sendError('Invalid loan ID', 400, 'VALIDATION_ERROR');
            }
            
            $currentUser = $this->getCurrentUser();
            
            $loan = $this->loanModel->findById($id);
            
            if (!$loan) {
                $this->notFound('Loan');
            }
            
            // Patrons can only view their own loans
            if ($currentUser['user_type'] === 'patron' && $loan['user_id'] != $currentUser['user_id']) {
                $this->sendError('You can only view your own loans', 403, 'PERMISSION_DENIED');
            }
            
            // Calculate fine if overdue
            if (!isset($loan['return_date']) && isset($loan['due_date'])) {
                $loan['fine_amount'] = $this->loanModel->calculateFine($loan['due_date']);
            }
            
            $this->sendResponse($loan, 200);
            
        } catch (Exception $e) {
            error_log("Loan show error: " . $e->getMessage());
            $this->sendError('Failed to fetch loan', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Get current user's loans
     * GET /api/v1/loans/my
     */
    private function getMyLoans() {
        try {
            $currentUser = $this->getCurrentUser();
            $params = $this->getQueryParams();
            
            $status = $params['status'] ?? null;
            
            $sql = "SELECT l.*, 
                    b.title as book_title, b.isbn, b.cover_image,
                    CASE 
                        WHEN l.return_date IS NOT NULL THEN 'returned'
                        WHEN l.status = 'pending' THEN 'pending'
                        WHEN l.status = 'rejected' THEN 'rejected'
                        WHEN l.due_date < CURDATE() THEN 'overdue'
                        ELSE 'active'
                    END as loan_status
                    FROM loans l
                    INNER JOIN books b ON l.book_id = b.book_id
                    WHERE l.user_id = :user_id";
            
            $bindParams = [':user_id' => $currentUser['user_id']];
            
            if ($status) {
                if ($status === 'active') {
                    $sql .= " AND l.return_date IS NULL AND l.status = 'approved' AND l.due_date >= CURDATE()";
                } elseif ($status === 'overdue') {
                    $sql .= " AND l.return_date IS NULL AND l.status = 'approved' AND l.due_date < CURDATE()";
                } elseif ($status === 'returned') {
                    $sql .= " AND l.return_date IS NOT NULL";
                } elseif (in_array($status, ['pending', 'rejected'])) {
                    $sql .= " AND l.status = :status";
                    $bindParams[':status'] = $status;
                }
            }
            
            $sql .= " ORDER BY l.loan_date DESC";
            
            $stmt = $this->loanModel->db->prepare($sql);
            
            foreach ($bindParams as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate fines
            foreach ($loans as &$loan) {
                if ($loan['loan_status'] === 'overdue') {
                    $loan['fine_amount'] = $this->loanModel->calculateFine($loan['due_date']);
                }
            }
            
            $this->sendResponse([
                'loans' => $loans,
                'count' => count($loans)
            ], 200);
            
        } catch (Exception $e) {
            error_log("Get my loans error: " . $e->getMessage());
            $this->sendError('Failed to fetch loans', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Request to borrow a book
     * POST /api/v1/loans/request
     */
    private function requestBorrow() {
        try {
            $currentUser = $this->getCurrentUser();
            $data = $this->getJsonInput();
            
            // Validate required fields
            $this->validateRequired($data, ['book_id']);
            
            if (!is_numeric($data['book_id'])) {
                $this->sendError('Invalid book ID', 400, 'VALIDATION_ERROR');
            }
            
            // Process borrow request
            $result = $this->loanModel->requestBorrow((int)$data['book_id'], $currentUser['user_id']);
            
            if (!$result['success']) {
                $this->sendError($result['error'], 400, 'VALIDATION_ERROR');
            }
            
            // Get created loan
            $loan = $this->loanModel->findById($result['loan_id']);
            
            $this->sendResponse($loan, 201, 'Borrow request submitted successfully');
            
        } catch (Exception $e) {
            error_log("Request borrow error: " . $e->getMessage());
            $this->sendError('Failed to submit borrow request', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Checkout book (admin only)
     * POST /api/v1/loans/checkout
     */
    private function checkout() {
        try {
            $data = $this->getJsonInput();
            
            // Validate required fields
            $this->validateRequired($data, ['book_id', 'user_id', 'due_date']);
            
            if (!is_numeric($data['book_id']) || !is_numeric($data['user_id'])) {
                $this->sendError('Invalid book ID or user ID', 400, 'VALIDATION_ERROR');
            }
            
            // Validate date format
            $dateValidation = ValidationService::validateDate($data['due_date']);
            if (!$dateValidation['valid']) {
                $this->sendError($dateValidation['message'], 400, 'VALIDATION_ERROR');
            }
            
            // Process checkout
            $result = $this->loanModel->checkoutBook(
                (int)$data['book_id'],
                (int)$data['user_id'],
                $data['due_date']
            );
            
            if (!$result['success']) {
                $this->sendError($result['error'], 400, 'VALIDATION_ERROR');
            }
            
            // Get created loan
            $loan = $this->loanModel->findById($result['loan_id']);
            
            $this->sendResponse($loan, 201, 'Book checked out successfully');
            
        } catch (Exception $e) {
            error_log("Checkout error: " . $e->getMessage());
            $this->sendError('Checkout failed', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Approve loan request
     * PUT /api/v1/loans/{id}/approve
     */
    private function approveLoan($id) {
        try {
            if (!is_numeric($id)) {
                $this->sendError('Invalid loan ID', 400, 'VALIDATION_ERROR');
            }
            
            $data = $this->getJsonInput();
            
            // Validate required fields
            $this->validateRequired($data, ['due_date']);
            
            // Validate date format
            $dateValidation = ValidationService::validateDate($data['due_date']);
            if (!$dateValidation['valid']) {
                $this->sendError($dateValidation['message'], 400, 'VALIDATION_ERROR');
            }
            
            // Approve loan
            $result = $this->loanModel->approveLoan($id, $data['due_date']);
            
            if (!$result['success']) {
                $this->sendError($result['error'], 400, 'VALIDATION_ERROR');
            }
            
            // Get updated loan
            $loan = $this->loanModel->findById($id);
            
            $this->sendResponse($loan, 200, 'Loan approved successfully');
            
        } catch (Exception $e) {
            error_log("Approve loan error: " . $e->getMessage());
            $this->sendError('Failed to approve loan', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Reject loan request
     * PUT /api/v1/loans/{id}/reject
     */
    private function rejectLoan($id) {
        try {
            if (!is_numeric($id)) {
                $this->sendError('Invalid loan ID', 400, 'VALIDATION_ERROR');
            }
            
            // Reject loan
            $result = $this->loanModel->rejectLoan($id);
            
            if (!$result['success']) {
                $this->sendError($result['error'], 400, 'VALIDATION_ERROR');
            }
            
            // Get updated loan
            $loan = $this->loanModel->findById($id);
            
            $this->sendResponse($loan, 200, 'Loan rejected successfully');
            
        } catch (Exception $e) {
            error_log("Reject loan error: " . $e->getMessage());
            $this->sendError('Failed to reject loan', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Return book (admin only)
     * PUT /api/v1/loans/{id}/return
     */
    private function returnBook($id) {
        try {
            if (!is_numeric($id)) {
                $this->sendError('Invalid loan ID', 400, 'VALIDATION_ERROR');
            }
            
            // Return book
            $result = $this->loanModel->returnBook($id);
            
            if (!$result['success']) {
                $this->sendError($result['error'], 400, 'VALIDATION_ERROR');
            }
            
            // Get updated loan
            $loan = $this->loanModel->findById($id);
            
            // Calculate final fine if any
            if (isset($loan['due_date']) && $loan['return_date']) {
                $loan['fine_amount'] = $this->loanModel->calculateFine($loan['due_date'], $loan['return_date']);
            }
            
            $this->sendResponse($loan, 200, 'Book returned successfully');
            
        } catch (Exception $e) {
            error_log("Return book error: " . $e->getMessage());
            $this->sendError('Failed to return book', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Return book by user (user can return their own books)
     * POST /api/v1/loans/{id}/return
     */
    private function returnBookByUser($id) {
        try {
            if (!is_numeric($id)) {
                $this->sendError('Invalid loan ID', 400, 'VALIDATION_ERROR');
            }
            
            $currentUser = $this->getCurrentUser();
            
            // Get loan to verify ownership
            $loan = $this->loanModel->findById($id);
            
            if (!$loan) {
                $this->notFound('Loan');
            }
            
            // Verify user owns this loan
            if ($loan['user_id'] != $currentUser['user_id']) {
                $this->sendError('You can only return your own books', 403, 'PERMISSION_DENIED');
            }
            
            // Return book
            $result = $this->loanModel->returnBook($id);
            
            if (!$result['success']) {
                $this->sendError($result['error'], 400, 'VALIDATION_ERROR');
            }
            
            // Get updated loan
            $loan = $this->loanModel->findById($id);
            
            // Calculate final fine if any
            if (isset($loan['due_date']) && $loan['return_date']) {
                $loan['fine_amount'] = $this->loanModel->calculateFine($loan['due_date'], $loan['return_date']);
            }
            
            $this->sendResponse($loan, 200, 'Book returned successfully');
            
        } catch (Exception $e) {
            error_log("Return book by user error: " . $e->getMessage());
            $this->sendError('Failed to return book', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Get overdue loans
     * GET /api/v1/loans/overdue
     */
    private function getOverdue() {
        try {
            $loans = $this->loanModel->findOverdueLoans();
            
            // Calculate fines
            foreach ($loans as &$loan) {
                $loan['fine_amount'] = $this->loanModel->calculateFine($loan['due_date']);
            }
            
            $this->sendResponse([
                'loans' => $loans,
                'count' => count($loans)
            ], 200);
            
        } catch (Exception $e) {
            error_log("Get overdue loans error: " . $e->getMessage());
            $this->sendError('Failed to fetch overdue loans', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Get pending loan requests
     * GET /api/v1/loans/pending
     */
    private function getPending() {
        try {
            $loans = $this->loanModel->findPendingLoans();
            
            $this->sendResponse([
                'loans' => $loans,
                'count' => count($loans)
            ], 200);
            
        } catch (Exception $e) {
            error_log("Get pending loans error: " . $e->getMessage());
            $this->sendError('Failed to fetch pending loans', 500, 'SERVER_ERROR');
        }
    }
}
