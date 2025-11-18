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
    <title>Users Management - <?php echo APP_NAME; ?></title>
    <link rel="icon" type="image/svg+xml" href="https://s3.ca-central-1.amazonaws.com/logojoy/logos/231703335/symbol.svg?1537014.9000000358">
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
                    <h1><i class="fas fa-users"></i> Users Management</h1>
                </div>
            </header>
            
            <div class="content-area">
                <div class="toolbar">
                    <div class="toolbar-left">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" placeholder="Search users by name or email...">
                        </div>
                    </div>
                    <div class="toolbar-right">
                        <button onclick="showCreateUserModal()" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Create New User
                        </button>
                    </div>
                </div>
                
                <div class="data-card">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th width="60">ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th width="100">Type</th>
                                    <th width="100">Status</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                <tr>
                                    <td colspan="7" class="loading-cell">
                                        <div class="loading-spinner">
                                            <i class="fas fa-spinner fa-spin"></i>
                                            <span>Loading users...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Create/Edit User Modal -->
    <div id="userModal" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h2 id="modalTitle"><i class="fas fa-user-plus"></i> Create New User</h2>
                <button onclick="closeUserModal()" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="userForm" class="modal-body">
                <input type="hidden" id="userId">
                
                <div class="form-section">
                    <div class="form-section-title">Personal Information</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-user"></i> First Name *
                            </label>
                            <input type="text" id="firstName" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-user"></i> Last Name *
                            </label>
                            <input type="text" id="lastName" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="form-section-title">Contact Information</div>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-envelope"></i> Email *
                        </label>
                        <input type="email" id="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-phone"></i> Phone Number
                        </label>
                        <input type="tel" id="phone" class="form-control" placeholder="+1 (555) 123-4567">
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-map-marker-alt"></i> Address
                        </label>
                        <textarea id="address" class="form-control" rows="3" placeholder="Street address, city, state, zip code"></textarea>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="form-section-title">Account Settings</div>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-lock"></i> Password *
                        </label>
                        <input type="password" id="password" class="form-control" minlength="8" placeholder="Minimum 8 characters">
                        <small style="color: var(--gray-600); font-size: 12px; display: block; margin-top: 4px;">
                            <span id="passwordHint">Required for new users</span>
                        </small>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-user-tag"></i> User Type *
                            </label>
                            <select id="userType" class="form-control" required>
                                <option value="patron">Patron (Regular User)</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-toggle-on"></i> Account Status
                            </label>
                            <select id="status" class="form-control">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" onclick="closeUserModal()" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <span id="submitBtnText">Create User</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="<?php echo BASE_URL; ?>/public/assets/js/utils.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/components.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/api.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/sidebar.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/admin/users.js"></script>
</body>
</html>
