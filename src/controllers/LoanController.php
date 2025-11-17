<?php
require_once dirname(__DIR__) . '/models/Loan.php';

class LoanController {
    private $loanModel;

    public function __construct() {
        $this->loanModel = new Loan();
    }

    public function index() {
        try {
            $limit = $_GET['limit'] ?? 1000;
            $offset = $_GET['offset'] ?? 0;
            $userId = $_GET['user_id'] ?? null;

            // Get loans with book and user information
            $sql = "SELECT l.*, 
                    b.title as book_title, b.isbn,
                    CONCAT(u.first_name, ' ', u.last_name) as user_name, u.email as user_email,
                    CASE 
                        WHEN l.return_date IS NOT NULL THEN 'returned'
                        WHEN l.due_date < CURDATE() THEN 'overdue'
                        ELSE 'active'
                    END as status
                    FROM loans l
                    INNER JOIN books b ON l.book_id = b.book_id
                    INNER JOIN users u ON l.user_id = u.user_id";
            
            if ($userId) {
                $sql .= " WHERE l.user_id = :user_id";
            }
            
            $sql .= " ORDER BY l.loan_date DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->loanModel->db->prepare($sql);
            if ($userId) {
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            }
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($loans as &$loan) {
                if (!isset($loan['return_date']) && isset($loan['due_date'])) {
                    $loan['fine_amount'] = $this->loanModel->calculateFine($loan['due_date']);
                }
            }

            $this->sendResponse($loans, 200);
        } catch (Exception $e) {
            error_log("LoanController::index error: " . $e->getMessage());
            $this->sendError('Failed to fetch loans: ' . $e->getMessage(), 500, 'SERVER_ERROR');
        }
    }

    public function show($id) {
        try {
            $loan = $this->loanModel->findById($id);
            
            if (!$loan) {
                $this->sendError('Loan not found', 404, 'NOT_FOUND');
                return;
            }

            if (!isset($loan['return_date'])) {
                $loan['fine_amount'] = $this->loanModel->calculateFine($loan['due_date']);
            }

            $this->sendResponse($loan, 200);
        } catch (Exception $e) {
            $this->sendError('Failed to fetch loan', 500, 'SERVER_ERROR');
        }
    }

    public function checkout() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['book_id']) || empty($data['user_id']) || empty($data['due_date'])) {
                $this->sendError('Book ID, User ID, and Due Date are required', 400, 'VALIDATION_ERROR');
                return;
            }

            $result = $this->loanModel->checkoutBook($data['book_id'], $data['user_id'], $data['due_date']);

            if (!$result['success']) {
                $this->sendError($result['error'], 400, 'VALIDATION_ERROR');
                return;
            }

            $loan = $this->loanModel->findById($result['loan_id']);
            $this->sendResponse($loan, 201);
        } catch (Exception $e) {
            $this->sendError('Checkout failed', 500, 'SERVER_ERROR');
        }
    }

    public function returnBook($id) {
        try {
            $result = $this->loanModel->returnBook($id);

            if (!$result['success']) {
                $this->sendError($result['error'], 400, 'VALIDATION_ERROR');
                return;
            }

            $loan = $this->loanModel->findById($id);
            $this->sendResponse($loan, 200);
        } catch (Exception $e) {
            $this->sendError('Return failed', 500, 'SERVER_ERROR');
        }
    }

    public function findOverdue() {
        try {
            $loans = $this->loanModel->findOverdueLoans();

            foreach ($loans as &$loan) {
                $loan['fine_amount'] = $this->loanModel->calculateFine($loan['due_date']);
            }

            $this->sendResponse($loans, 200);
        } catch (Exception $e) {
            $this->sendError('Failed to fetch overdue loans', 500, 'SERVER_ERROR');
        }
    }

    public function requestBorrow() {
        try {
            // Start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Check authentication
            if (!isset($_SESSION['user_id'])) {
                $this->sendError('User not authenticated. Please login first.', 401, 'UNAUTHORIZED');
                return;
            }

            // Get and validate request data
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                $this->sendError('Invalid request data', 400, 'VALIDATION_ERROR');
                return;
            }

            if (empty($data['book_id'])) {
                $this->sendError('Book ID is required', 400, 'VALIDATION_ERROR');
                return;
            }

            // Validate book_id is numeric
            if (!is_numeric($data['book_id'])) {
                $this->sendError('Invalid book ID format', 400, 'VALIDATION_ERROR');
                return;
            }

            // Process the borrow request
            $result = $this->loanModel->requestBorrow((int)$data['book_id'], $_SESSION['user_id']);

            if (!$result['success']) {
                $this->sendError($result['error'], 400, 'VALIDATION_ERROR');
                return;
            }

            $this->sendResponse([
                'loan_id' => $result['loan_id'], 
                'message' => 'Borrow request submitted successfully'
            ], 201);
            
        } catch (Exception $e) {
            error_log("LoanController::requestBorrow error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->sendError('Request failed: ' . $e->getMessage(), 500, 'SERVER_ERROR');
        }
    }

    public function findPending() {
        try {
            $loans = $this->loanModel->findPendingLoans();
            $this->sendResponse($loans, 200);
        } catch (Exception $e) {
            $this->sendError('Failed to fetch pending loans', 500, 'SERVER_ERROR');
        }
    }

    public function approveLoan($id) {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['due_date'])) {
                $this->sendError('Due date is required', 400, 'VALIDATION_ERROR');
                return;
            }

            $result = $this->loanModel->approveLoan($id, $data['due_date']);

            if (!$result['success']) {
                $this->sendError($result['error'], 400, 'VALIDATION_ERROR');
                return;
            }

            $loan = $this->loanModel->findById($id);
            $this->sendResponse($loan, 200);
        } catch (Exception $e) {
            error_log("LoanController::approveLoan error: " . $e->getMessage());
            $this->sendError('Approval failed', 500, 'SERVER_ERROR');
        }
    }

    public function rejectLoan($id) {
        try {
            $result = $this->loanModel->rejectLoan($id);

            if (!$result['success']) {
                $this->sendError($result['error'], 400, 'VALIDATION_ERROR');
                return;
            }

            $loan = $this->loanModel->findById($id);
            $this->sendResponse($loan, 200);
        } catch (Exception $e) {
            error_log("LoanController::rejectLoan error: " . $e->getMessage());
            $this->sendError('Rejection failed', 500, 'SERVER_ERROR');
        }
    }

    private function sendResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode(['success' => true, 'data' => $data]);
    }

    private function sendError($message, $statusCode = 400, $code = 'ERROR', $details = null) {
        http_response_code($statusCode);
        $error = ['code' => $code, 'message' => $message];
        if ($details) {
            $error['details'] = $details;
        }
        echo json_encode(['success' => false, 'error' => $error]);
    }
}
