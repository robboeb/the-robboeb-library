<?php
require_once __DIR__ . '/../../src/services/AuthService.php';
require_once __DIR__ . '/../../config/constants.php';

AuthService::requireAdmin();
$currentUser = AuthService::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loans Management - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <header class="top-bar">
                <div class="top-bar-left">
                    <h1><i class="fas fa-exchange-alt"></i> Loans Management</h1>
                </div>
                <div class="top-bar-right">
                    <div class="user-info">
                        <div class="user-avatar"><?php echo strtoupper(substr($currentUser['first_name'], 0, 1)); ?></div>
                        <div class="user-details">
                            <span class="user-name"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></span>
                            <span class="user-role">Administrator</span>
                        </div>
                    </div>
                    <button onclick="logout()" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </div>
            </header>
            
            <div class="content-area">
                <div class="page-header-section">
                    <div class="page-stats">
                        <div class="stat-item">
                            <i class="fas fa-book-reader"></i>
                            <div>
                                <span class="stat-value" id="activeLoansCount">0</span>
                                <span class="stat-label">Active Loans</span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <span class="stat-value" id="overdueLoansCount">0</span>
                                <span class="stat-label">Overdue</span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <span class="stat-value" id="returnedLoansCount">0</span>
                                <span class="stat-label">Returned Today</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="toolbar">
                    <div class="toolbar-left">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" placeholder="Search by book or user...">
                        </div>
                        <div class="filter-group">
                            <select id="statusFilter" class="filter-select">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="returned">Returned</option>
                                <option value="overdue">Overdue</option>
                            </select>
                        </div>
                    </div>
                    <div class="toolbar-right">
                        <button onclick="showCheckoutModal()" class="btn btn-primary">
                            <i class="fas fa-book-reader"></i> Checkout Book
                        </button>
                    </div>
                </div>
                
                <div class="data-card">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th width="60">ID</th>
                                    <th>Book</th>
                                    <th>User</th>
                                    <th width="120">Loan Date</th>
                                    <th width="120">Due Date</th>
                                    <th width="120">Return Date</th>
                                    <th width="100">Status</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="loansTableBody">
                                <tr>
                                    <td colspan="8" class="loading-cell">
                                        <div class="loading-spinner">
                                            <i class="fas fa-spinner fa-spin"></i>
                                            <span>Loading loans...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="table-footer">
                        <div class="table-info">
                            <span id="tableInfo">Showing 0 loans</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Checkout Modal -->
    <div id="checkoutModal" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h2><i class="fas fa-book-reader"></i> Checkout Book</h2>
                <button onclick="closeCheckoutModal()" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="checkoutForm" class="modal-body">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-user"></i> User *
                    </label>
                    <select id="userId" class="form-control" required>
                        <option value="">Select User</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-book"></i> Book *
                    </label>
                    <select id="bookId" class="form-control" required>
                        <option value="">Select Book</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-calendar"></i> Due Date *
                    </label>
                    <input type="date" id="dueDate" class="form-control" required>
                </div>
                
                <div class="modal-footer">
                    <button type="button" onclick="closeCheckoutModal()" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Checkout
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="<?php echo BASE_URL; ?>/public/assets/js/utils.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/components.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/api.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/sidebar.js"></script>
    <script>
        let allLoans = [];
        let searchQuery = '';
        let statusFilter = '';
        
        async function loadLoans() {
            try {
                const response = await API.loans.getAll({});
                if (response.success) {
                    allLoans = response.data;
                    filterAndDisplayLoans();
                    updateStats();
                }
            } catch (error) {
                console.error('Error loading loans:', error);
                UIComponents.showToast('Failed to load loans', 'error');
            }
        }
        
        function filterAndDisplayLoans() {
            let filtered = [...allLoans];
            
            if (searchQuery) {
                filtered = filtered.filter(loan => 
                    (loan.book_title && loan.book_title.toLowerCase().includes(searchQuery.toLowerCase())) ||
                    (loan.user_name && loan.user_name.toLowerCase().includes(searchQuery.toLowerCase()))
                );
            }
            
            if (statusFilter) {
                filtered = filtered.filter(loan => loan.status === statusFilter);
            }
            
            displayLoans(filtered);
        }
        
        function displayLoans(loans) {
            const tbody = document.getElementById('loansTableBody');
            const tableInfo = document.getElementById('tableInfo');
            
            if (loans.length > 0) {
                tbody.innerHTML = loans.map(loan => {
                    const isOverdue = loan.status === 'active' && new Date(loan.due_date) < new Date();
                    const statusBadge = isOverdue ? 'badge-error' : 
                                       loan.status === 'returned' ? 'badge-success' : 'badge-warning';
                    const statusText = isOverdue ? 'Overdue' : Utils.capitalize(loan.status);
                    
                    return `
                        <tr>
                            <td><strong>#${loan.loan_id}</strong></td>
                            <td>${Utils.escapeHtml(loan.book_title || 'N/A')}</td>
                            <td>${Utils.escapeHtml(loan.user_name || 'N/A')}</td>
                            <td>${Utils.formatDate(loan.loan_date)}</td>
                            <td>${Utils.formatDate(loan.due_date)}</td>
                            <td>${loan.return_date ? Utils.formatDate(loan.return_date) : '-'}</td>
                            <td><span class="badge ${statusBadge}">${statusText}</span></td>
                            <td class="actions">
                                ${loan.status === 'active' ? `
                                    <button onclick="returnBook(${loan.loan_id})" class="btn btn-sm btn-success" title="Return">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                ` : ''}
                            </td>
                        </tr>
                    `;
                }).join('');
                tableInfo.textContent = `Showing ${loans.length} loan${loans.length !== 1 ? 's' : ''}`;
            } else {
                tbody.innerHTML = '<tr><td colspan="8" class="empty-cell">No loans found</td></tr>';
                tableInfo.textContent = 'Showing 0 loans';
            }
        }
        
        function updateStats() {
            const active = allLoans.filter(l => l.status === 'active').length;
            const overdue = allLoans.filter(l => l.status === 'active' && new Date(l.due_date) < new Date()).length;
            const today = new Date().toISOString().split('T')[0];
            const returned = allLoans.filter(l => l.return_date && l.return_date.startsWith(today)).length;
            
            document.getElementById('activeLoansCount').textContent = active;
            document.getElementById('overdueLoansCount').textContent = overdue;
            document.getElementById('returnedLoansCount').textContent = returned;
        }
        
        async function returnBook(loanId) {
            const confirmed = await UIComponents.confirm('Mark this book as returned?', 'Return Book');
            if (confirmed) {
                try {
                    const response = await API.loans.return(loanId);
                    if (response.success) {
                        UIComponents.showToast('Book returned successfully', 'success');
                        loadLoans();
                    }
                } catch (error) {
                    UIComponents.showToast(error.error?.message || 'Failed to return book', 'error');
                }
            }
        }
        
        function showCheckoutModal() {
            document.getElementById('checkoutModal').style.display = 'flex';
            loadUsersAndBooks();
            
            // Set default due date (14 days from now)
            const dueDate = new Date();
            dueDate.setDate(dueDate.getDate() + 14);
            document.getElementById('dueDate').value = dueDate.toISOString().split('T')[0];
        }
        
        function closeCheckoutModal() {
            document.getElementById('checkoutModal').style.display = 'none';
            document.getElementById('checkoutForm').reset();
        }
        
        async function loadUsersAndBooks() {
            try {
                const [usersRes, booksRes] = await Promise.all([
                    API.users.getAll({}),
                    API.books.getAll({})
                ]);
                
                if (usersRes.success) {
                    const userSelect = document.getElementById('userId');
                    userSelect.innerHTML = '<option value="">Select User</option>' +
                        usersRes.data.filter(u => u.user_type === 'patron').map(u => 
                            `<option value="${u.user_id}">${Utils.escapeHtml(u.first_name + ' ' + u.last_name)}</option>`
                        ).join('');
                }
                
                if (booksRes.success) {
                    const bookSelect = document.getElementById('bookId');
                    bookSelect.innerHTML = '<option value="">Select Book</option>' +
                        booksRes.data.filter(b => b.status === 'available').map(b => 
                            `<option value="${b.book_id}">${Utils.escapeHtml(b.title)}</option>`
                        ).join('');
                }
            } catch (error) {
                console.error('Error loading users/books:', error);
            }
        }
        
        document.getElementById('checkoutForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const data = {
                user_id: document.getElementById('userId').value,
                book_id: document.getElementById('bookId').value,
                due_date: document.getElementById('dueDate').value
            };
            
            try {
                const response = await API.loans.checkout(data);
                if (response.success) {
                    UIComponents.showToast('Book checked out successfully', 'success');
                    closeCheckoutModal();
                    loadLoans();
                }
            } catch (error) {
                UIComponents.showToast(error.error?.message || 'Failed to checkout book', 'error');
            }
        });
        
        document.getElementById('searchInput').addEventListener('input', (e) => {
            searchQuery = e.target.value;
            filterAndDisplayLoans();
        });
        
        document.getElementById('statusFilter').addEventListener('change', (e) => {
            statusFilter = e.target.value;
            filterAndDisplayLoans();
        });
        
        function logout() {
            API.auth.logout().then(() => {
                window.location.href = '/library-pro/public/login.php';
            });
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            loadLoans();
        });
    </script>
</body>
</html>
