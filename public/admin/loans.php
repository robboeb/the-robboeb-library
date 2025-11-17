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
                            <i class="fas fa-hourglass-half"></i>
                            <div>
                                <span class="stat-value" id="pendingRequestsCount">0</span>
                                <span class="stat-label">Pending Requests</span>
                            </div>
                        </div>
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

                <!-- Tabs -->
                <div style="margin-bottom: 20px; border-bottom: 2px solid #e2e8f0;">
                    <div style="display: flex; gap: 10px;">
                        <button onclick="switchTab('pending')" id="pendingTab" class="tab-button active" style="padding: 12px 24px; border: none; background: none; cursor: pointer; font-weight: 600; color: #667eea; border-bottom: 3px solid #667eea;">
                            <i class="fas fa-hourglass-half"></i> Pending Requests
                        </button>
                        <button onclick="switchTab('all')" id="allTab" class="tab-button" style="padding: 12px 24px; border: none; background: none; cursor: pointer; font-weight: 600; color: #718096; border-bottom: 3px solid transparent;">
                            <i class="fas fa-list"></i> All Loans
                        </button>
                    </div>
                </div>

                <!-- Pending Requests Section -->
                <div id="pendingSection" class="tab-content">
                    <div class="data-card">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th width="60">ID</th>
                                        <th>Book</th>
                                        <th>User</th>
                                        <th>Contact</th>
                                        <th width="150">Request Date</th>
                                        <th width="100">Availability</th>
                                        <th width="200">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="pendingTableBody">
                                    <tr>
                                        <td colspan="7" class="loading-cell">
                                            <div class="loading-spinner">
                                                <i class="fas fa-spinner fa-spin"></i>
                                                <span>Loading pending requests...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="table-footer">
                            <div class="table-info">
                                <span id="pendingTableInfo">Showing 0 requests</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- All Loans Section -->
                <div id="allSection" class="tab-content" style="display: none;">
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
            </div>
        </main>
    </div>
    
    <!-- Approve Loan Modal -->
    <div id="approveModal" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h2><i class="fas fa-check-circle"></i> Approve Loan Request</h2>
                <button onclick="closeApproveModal()" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="approveForm" class="modal-body">
                <input type="hidden" id="approveLoanId">
                <div id="approveBookInfo" style="background: #f7fafc; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <!-- Book and user info will be inserted here -->
                </div>
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-calendar"></i> Due Date *
                    </label>
                    <input type="date" id="approveDueDate" class="form-control" required>
                </div>
                
                <div class="modal-footer">
                    <button type="button" onclick="closeApproveModal()" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Approve
                    </button>
                </div>
            </form>
        </div>
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
        // Fallback for Utils if not loaded
        if (typeof Utils === 'undefined') {
            window.Utils = {
                escapeHtml: function(text) {
                    const div = document.createElement('div');
                    div.textContent = text;
                    return div.innerHTML;
                },
                formatDate: function(date) {
                    return new Date(date).toLocaleDateString();
                },
                formatDateTime: function(datetime) {
                    const d = new Date(datetime);
                    return d.toLocaleDateString() + ' ' + d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                },
                capitalize: function(text) {
                    return text.charAt(0).toUpperCase() + text.slice(1);
                }
            };
        }
        
        // Fallback for UIComponents if not loaded
        if (typeof UIComponents === 'undefined') {
            window.UIComponents = {
                showToast: function(message, type) {
                    alert(message);
                },
                confirm: function(message, title) {
                    return Promise.resolve(confirm(message));
                }
            };
        }
        
        let allLoans = [];
        let pendingRequests = [];
        let searchQuery = '';
        let statusFilter = '';
        let currentTab = 'pending';
        
        async function loadLoans() {
            try {
                console.log('Loading all loans...');
                const response = await fetch('<?php echo BASE_URL; ?>/api/loans', {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                console.log('All loans data:', data);
                
                if (data.success) {
                    allLoans = data.data || [];
                    console.log('All loans count:', allLoans.length);
                    filterAndDisplayLoans();
                    updateStats();
                } else {
                    console.error('API returned error:', data.error);
                    allLoans = [];
                    filterAndDisplayLoans();
                    updateStats();
                }
            } catch (error) {
                console.error('Error loading loans:', error);
                allLoans = [];
                filterAndDisplayLoans();
                updateStats();
            }
        }

        async function loadPendingRequests() {
            try {
                console.log('Loading pending requests...');
                const response = await fetch('<?php echo BASE_URL; ?>/api/loans/pending', {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                
                console.log('Response status:', response.status);
                const data = await response.json();
                console.log('Pending requests data:', data);
                
                if (data.success) {
                    pendingRequests = data.data || [];
                    console.log('Pending requests count:', pendingRequests.length);
                    displayPendingRequests();
                    updateStats();
                } else {
                    console.error('API returned error:', data.error);
                    pendingRequests = [];
                    displayPendingRequests();
                    updateStats();
                }
            } catch (error) {
                console.error('Error loading pending requests:', error);
                pendingRequests = [];
                displayPendingRequests();
                updateStats();
            }
        }

        function switchTab(tab) {
            currentTab = tab;
            document.getElementById('pendingTab').classList.toggle('active', tab === 'pending');
            document.getElementById('allTab').classList.toggle('active', tab === 'all');
            
            document.getElementById('pendingTab').style.color = tab === 'pending' ? '#667eea' : '#718096';
            document.getElementById('pendingTab').style.borderBottom = tab === 'pending' ? '3px solid #667eea' : '3px solid transparent';
            document.getElementById('allTab').style.color = tab === 'all' ? '#667eea' : '#718096';
            document.getElementById('allTab').style.borderBottom = tab === 'all' ? '3px solid #667eea' : '3px solid transparent';
            
            document.getElementById('pendingSection').style.display = tab === 'pending' ? 'block' : 'none';
            document.getElementById('allSection').style.display = tab === 'all' ? 'block' : 'none';
        }

        function displayPendingRequests() {
            const tbody = document.getElementById('pendingTableBody');
            const tableInfo = document.getElementById('pendingTableInfo');
            
            if (pendingRequests.length > 0) {
                tbody.innerHTML = pendingRequests.map(request => {
                    const availBadge = request.available_quantity > 0 ? 'badge-success' : 'badge-error';
                    const availText = request.available_quantity > 0 ? `${request.available_quantity} Available` : 'Not Available';
                    
                    return `
                        <tr>
                            <td><strong>#${request.loan_id}</strong></td>
                            <td>${Utils.escapeHtml(request.book_title || 'N/A')}</td>
                            <td>${Utils.escapeHtml(request.user_name || 'N/A')}</td>
                            <td>
                                <div style="font-size: 13px;">
                                    <div><i class="fas fa-envelope"></i> ${Utils.escapeHtml(request.email || 'N/A')}</div>
                                    ${request.phone ? `<div><i class="fas fa-phone"></i> ${Utils.escapeHtml(request.phone)}</div>` : ''}
                                </div>
                            </td>
                            <td>${Utils.formatDateTime(request.created_at)}</td>
                            <td><span class="badge ${availBadge}">${availText}</span></td>
                            <td class="actions">
                                ${request.available_quantity > 0 ? `
                                    <button onclick="showApproveModal(${request.loan_id}, '${Utils.escapeHtml(request.book_title)}', '${Utils.escapeHtml(request.user_name)}')" 
                                            class="btn btn-sm btn-success" title="Approve">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                ` : ''}
                                <button onclick="rejectRequest(${request.loan_id})" 
                                        class="btn btn-sm btn-danger" title="Reject">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');
                tableInfo.textContent = `Showing ${pendingRequests.length} request${pendingRequests.length !== 1 ? 's' : ''}`;
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="empty-cell">No pending requests</td></tr>';
                tableInfo.textContent = 'Showing 0 requests';
            }
        }

        function showApproveModal(loanId, bookTitle, userName) {
            document.getElementById('approveLoanId').value = loanId;
            document.getElementById('approveBookInfo').innerHTML = `
                <div style="margin-bottom: 10px;"><strong>Book:</strong> ${Utils.escapeHtml(bookTitle)}</div>
                <div><strong>User:</strong> ${Utils.escapeHtml(userName)}</div>
            `;
            
            // Set default due date (14 days from now)
            const dueDate = new Date();
            dueDate.setDate(dueDate.getDate() + 14);
            document.getElementById('approveDueDate').value = dueDate.toISOString().split('T')[0];
            
            document.getElementById('approveModal').style.display = 'flex';
        }

        function closeApproveModal() {
            document.getElementById('approveModal').style.display = 'none';
            document.getElementById('approveForm').reset();
        }

        document.getElementById('approveForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const loanId = document.getElementById('approveLoanId').value;
            const dueDate = document.getElementById('approveDueDate').value;
            
            try {
                const response = await fetch(`<?php echo BASE_URL; ?>/api/loans/${loanId}/approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ due_date: dueDate })
                });
                
                const data = await response.json();
                if (data.success) {
                    UIComponents.showToast('Loan request approved successfully', 'success');
                    closeApproveModal();
                    loadPendingRequests();
                    loadLoans();
                } else {
                    UIComponents.showToast(data.error?.message || 'Failed to approve request', 'error');
                }
            } catch (error) {
                UIComponents.showToast('Failed to approve request', 'error');
            }
        });

        async function rejectRequest(loanId) {
            const confirmed = await UIComponents.confirm('Are you sure you want to reject this request?', 'Reject Request');
            if (confirmed) {
                try {
                    const response = await fetch(`<?php echo BASE_URL; ?>/api/loans/${loanId}/reject`, {
                        method: 'POST',
                        credentials: 'same-origin'
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        UIComponents.showToast('Request rejected', 'success');
                        loadPendingRequests();
                    } else {
                        UIComponents.showToast(data.error?.message || 'Failed to reject request', 'error');
                    }
                } catch (error) {
                    UIComponents.showToast('Failed to reject request', 'error');
                }
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
            
            document.getElementById('pendingRequestsCount').textContent = pendingRequests.length;
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
                window.location.href = '/the-robboeb-library/public/home.php';
            });
        }
        
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                fetch('<?php echo BASE_URL; ?>/api/auth/logout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    window.location.href = '<?php echo BASE_URL; ?>/public/home.php';
                })
                .catch(error => {
                    console.error('Logout error:', error);
                    window.location.href = '<?php echo BASE_URL; ?>/public/home.php';
                });
            }
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            console.log('Page loaded, initializing...');
            loadPendingRequests();
            loadLoans();
        });
    </script>
</body>
</html>
