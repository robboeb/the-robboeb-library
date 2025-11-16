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
    <title>Authors - <?php echo APP_NAME; ?></title>
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
                    <h1><i class="fas fa-user-edit"></i> Authors</h1>
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
                <div class="toolbar">
                    <div class="toolbar-left">
                        <h2>Manage Authors</h2>
                    </div>
                    <div class="toolbar-right">
                        <button onclick="showAddModal()" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Author
                        </button>
                    </div>
                </div>
                
                <div class="data-card">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th width="80">ID</th>
                                    <th>Name</th>
                                    <th>Biography</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <tr><td colspan="4" class="loading-cell"><div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i><span>Loading...</span></div></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <div id="modal" class="modal-overlay" style="display: none;">
        <div class="modal-container" style="max-width: 600px;">
            <div class="modal-header">
                <h2 id="modalTitle"><i class="fas fa-user-edit"></i> Add Author</h2>
                <button onclick="closeModal()" class="modal-close"><i class="fas fa-times"></i></button>
            </div>
            <form id="form" class="modal-body">
                <input type="hidden" id="itemId">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-user"></i> First Name *</label>
                        <input type="text" id="firstName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-user"></i> Last Name *</label>
                        <input type="text" id="lastName" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-align-left"></i> Biography</label>
                    <textarea id="biography" class="form-control" rows="4"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeModal()" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="<?php echo BASE_URL; ?>/public/assets/js/utils.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/components.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/api.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/sidebar.js"></script>
    <script>
        let items = [];
        
        async function loadData() {
            try {
                const response = await API.authors.getAll();
                if (response.success) {
                    items = response.data;
                    displayData();
                }
            } catch (error) {
                UIComponents.showToast('Failed to load authors', 'error');
            }
        }
        
        function displayData() {
            const tbody = document.getElementById('tableBody');
            if (items.length > 0) {
                tbody.innerHTML = items.map(item => `
                    <tr>
                        <td><strong>#${item.author_id}</strong></td>
                        <td><strong>${Utils.escapeHtml(item.first_name + ' ' + item.last_name)}</strong></td>
                        <td>${Utils.truncate(Utils.escapeHtml(item.biography || '-'), 100)}</td>
                        <td class="actions">
                            <button onclick="editItem(${item.author_id})" class="btn btn-sm btn-secondary"><i class="fas fa-edit"></i></button>
                            <button onclick="deleteItem(${item.author_id}, '${Utils.escapeHtml(item.first_name + ' ' + item.last_name).replace(/'/g, "\\'")}' )" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="empty-cell">No authors found</td></tr>';
            }
        }
        
        function showAddModal() {
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-edit"></i> Add Author';
            document.getElementById('form').reset();
            document.getElementById('itemId').value = '';
            document.getElementById('modal').style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('modal').style.display = 'none';
        }
        
        async function editItem(id) {
            try {
                const response = await API.authors.getById(id);
                if (response.success) {
                    const item = response.data;
                    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-edit"></i> Edit Author';
                    document.getElementById('itemId').value = item.author_id;
                    document.getElementById('firstName').value = item.first_name;
                    document.getElementById('lastName').value = item.last_name;
                    document.getElementById('biography').value = item.biography || '';
                    document.getElementById('modal').style.display = 'flex';
                }
            } catch (error) {
                UIComponents.showToast('Failed to load author', 'error');
            }
        }
        
        async function deleteItem(id, name) {
            const confirmed = await UIComponents.confirm(`Delete author "${name}"?`, 'Delete Author');
            if (confirmed) {
                try {
                    await API.authors.delete(id);
                    UIComponents.showToast('Author deleted', 'success');
                    loadData();
                } catch (error) {
                    UIComponents.showToast(error.error?.message || 'Failed to delete', 'error');
                }
            }
        }
        
        document.getElementById('form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('itemId').value;
            const data = {
                first_name: document.getElementById('firstName').value,
                last_name: document.getElementById('lastName').value,
                biography: document.getElementById('biography').value
            };
            
            try {
                if (id) {
                    await API.authors.update(id, data);
                    UIComponents.showToast('Author updated', 'success');
                } else {
                    await API.authors.create(data);
                    UIComponents.showToast('Author added', 'success');
                }
                closeModal();
                loadData();
            } catch (error) {
                UIComponents.showToast(error.error?.message || 'Failed to save', 'error');
            }
        });
        
        function logout() {
            API.auth.logout().then(() => window.location.href = '/library-pro/public/login.php');
        }
        
        document.addEventListener('DOMContentLoaded', loadData);
    </script>
</body>
</html>
