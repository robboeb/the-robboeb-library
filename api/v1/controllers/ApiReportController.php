<?php
/**
 * API Report Controller
 * Handles reporting and analytics operations (Admin only)
 */

require_once __DIR__ . '/BaseApiController.php';
require_once dirname(__DIR__, 3) . '/src/services/ReportService.php';

class ApiReportController extends BaseApiController {
    private $reportService;

    public function __construct() {
        $this->reportService = new ReportService();
    }

    public function route($method, $id, $action) {
        // All report endpoints are admin-only and GET requests
        if ($method !== 'GET') {
            $this->methodNotAllowed();
        }
        
        switch ($id) {
            case 'dashboard':
                $this->getDashboard();
                break;
            
            case 'trends':
                $this->getTrends();
                break;
            
            case 'popular-books':
                $this->getPopularBooks();
                break;
            
            case 'categories':
                $this->getCategoryDistribution();
                break;
            
            case 'active-users':
                $this->getActiveUsers();
                break;
            
            case 'overdue-summary':
                $this->getOverdueSummary();
                break;
            
            case 'loan-statistics':
                $this->getLoanStatistics();
                break;
            
            default:
                $this->notFound('Report');
                break;
        }
    }

    /**
     * Get dashboard statistics
     * GET /api/v1/reports/dashboard
     */
    private function getDashboard() {
        try {
            $stats = $this->reportService->getDashboardStats();
            $this->sendResponse($stats, 200);
        } catch (Exception $e) {
            error_log("Dashboard report error: " . $e->getMessage());
            $this->sendError('Failed to fetch dashboard statistics', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Get loan trends
     * GET /api/v1/reports/trends?start_date=2024-01-01&end_date=2024-12-31
     */
    private function getTrends() {
        try {
            $params = $this->getQueryParams();
            $startDate = $params['start_date'] ?? null;
            $endDate = $params['end_date'] ?? null;
            
            $trends = $this->reportService->getLoanTrends($startDate, $endDate);
            
            $this->sendResponse([
                'trends' => $trends,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ], 200);
        } catch (Exception $e) {
            error_log("Trends report error: " . $e->getMessage());
            $this->sendError('Failed to fetch loan trends', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Get popular books
     * GET /api/v1/reports/popular-books?limit=10
     */
    private function getPopularBooks() {
        try {
            $params = $this->getQueryParams();
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            
            $books = $this->reportService->getPopularBooks($limit);
            
            $this->sendResponse([
                'books' => $books,
                'count' => count($books)
            ], 200);
        } catch (Exception $e) {
            error_log("Popular books report error: " . $e->getMessage());
            $this->sendError('Failed to fetch popular books', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Get category distribution
     * GET /api/v1/reports/categories
     */
    private function getCategoryDistribution() {
        try {
            $distribution = $this->reportService->getCategoryDistribution();
            
            $this->sendResponse([
                'distribution' => $distribution,
                'count' => count($distribution)
            ], 200);
        } catch (Exception $e) {
            error_log("Category distribution report error: " . $e->getMessage());
            $this->sendError('Failed to fetch category distribution', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Get most active users
     * GET /api/v1/reports/active-users?limit=10
     */
    private function getActiveUsers() {
        try {
            $params = $this->getQueryParams();
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            
            $users = $this->reportService->getMostActiveUsers($limit);
            
            $this->sendResponse([
                'users' => $users,
                'count' => count($users)
            ], 200);
        } catch (Exception $e) {
            error_log("Active users report error: " . $e->getMessage());
            $this->sendError('Failed to fetch active users', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Get overdue loans summary
     * GET /api/v1/reports/overdue-summary
     */
    private function getOverdueSummary() {
        try {
            require_once dirname(__DIR__, 3) . '/src/models/Loan.php';
            $loanModel = new Loan();
            
            $overdueLoans = $loanModel->findOverdueLoans();
            
            $totalFines = 0;
            foreach ($overdueLoans as $loan) {
                $totalFines += $loanModel->calculateFine($loan['due_date']);
            }
            
            $summary = [
                'total_overdue' => count($overdueLoans),
                'total_fines' => round($totalFines, 2),
                'overdue_loans' => $overdueLoans
            ];
            
            $this->sendResponse($summary, 200);
        } catch (Exception $e) {
            error_log("Overdue summary report error: " . $e->getMessage());
            $this->sendError('Failed to fetch overdue summary', 500, 'SERVER_ERROR');
        }
    }

    /**
     * Get loan statistics
     * GET /api/v1/reports/loan-statistics
     */
    private function getLoanStatistics() {
        try {
            require_once dirname(__DIR__, 3) . '/src/models/Database.php';
            $db = Database::getInstance()->getConnection();
            
            // Get various loan statistics
            $sql = "SELECT 
                    COUNT(*) as total_loans,
                    COUNT(CASE WHEN return_date IS NULL AND status = 'approved' THEN 1 END) as active_loans,
                    COUNT(CASE WHEN return_date IS NOT NULL THEN 1 END) as returned_loans,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_loans,
                    COUNT(CASE WHEN return_date IS NULL AND due_date < CURDATE() AND status = 'approved' THEN 1 END) as overdue_loans,
                    AVG(CASE WHEN return_date IS NOT NULL 
                        THEN DATEDIFF(return_date, loan_date) 
                        END) as avg_loan_duration
                    FROM loans";
            
            $stmt = $db->query($sql);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get monthly loan statistics for current year
            $monthlySql = "SELECT 
                            DATE_FORMAT(loan_date, '%Y-%m') as month,
                            COUNT(*) as loan_count
                            FROM loans
                            WHERE YEAR(loan_date) = YEAR(CURDATE())
                            GROUP BY DATE_FORMAT(loan_date, '%Y-%m')
                            ORDER BY month";
            
            $monthlyStmt = $db->query($monthlySql);
            $monthlyStats = $monthlyStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->sendResponse([
                'overall' => $stats,
                'monthly' => $monthlyStats
            ], 200);
        } catch (Exception $e) {
            error_log("Loan statistics report error: " . $e->getMessage());
            $this->sendError('Failed to fetch loan statistics', 500, 'SERVER_ERROR');
        }
    }
}
