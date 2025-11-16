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
    <title>Categories - <?php echo APP_NAME; ?></title>
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
                    <h1><i class="fas fa-tags"></i> Categories</h1>
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
                        <h2>Manage Book Categories</h2>
                    </div>
                    <div class="toolbar-right">
                        <button onclick="showAddModal()" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Category
                        </button>
                    </div>
                </div>
                
                <div class="data-card">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th width="80">ID</th>
                                    <th>Category Name</th>
                                    <th>Description</th>
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
        <div class="modal-container" style="max-width: 500px;">
            <div class="modal-header">
                <h2 id="modalTitle"><i class="fas fa-tag"></i> Add Category</h2>
                <button onclick="closeModal()" class="modal-close"><i class="fas fa-times"></i></button>
            </div>
            <form id="form" class="modal-body">
                <input type="hidden" id="itemId">
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-tag"></i> Category Name *</label>
                    <input type="text" id="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-align-left"></i> Description</label>
                    <textarea id="description" class="form-control" rows="3"></textarea>
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
            const tbody = document.getElementById('tableBody');
            try {
                const response = await API.categories.getAll();
                console.log('Categories response:', response);
                if (response.success) {
                    items = response.data;
                    displayData();
                } else {
                    tbody.innerHTML = '<tr><td colspan="4" class="empty-cell" style="color: red;">Error: ' + (response.error?.message || 'Failed to load') + '</td></tr>';
                }
            } catch (error) {
                console.error('Error loading categories:', error);
                tbody.innerHTML = '<tr><td colspan="4" class="empty-cell" style="color: red;">Error: ' + (error.error?.message || error.message || 'Failed to load categories') + '</td></tr>';
            }
        }
        
        function displayData() {
            const tbody = document.getElementById('tableBody');
            if (items.length > 0) {
                tbody.innerHTML = items.map(item => `
                    <tr>
                        <td><strong>#${item.category_id}</strong></td>
                        <td><strong>${Utils.escapeHtml(item.category_name)}</strong></td>
                        <td>${Utils.escapeHtml(item.description || '-')}</td>
                        <td class="actions">
                            <button onclick="editItem(${item.category_id})" class="btn btn-sm btn-secondary"><i class="fas fa-edit"></i></button>
                            <button onclick="deleteItem(${item.category_id}, '${Utils.escapeHtml(item.category_name).replace(/'/g, "\\'")}' )" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="empty-cell">No categories found</td></tr>';
            }
        }
        
        function showAddModal() {
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-tag"></i> Add Category';
            document.getElementById('form').reset();
            document.getElementById('itemId').value = '';
            document.getElementById('modal').style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('modal').style.display = 'none';
        }
        
        async function editItem(id) {
            try {
                const response = await API.categories.getById(id);
                if (response.success) {
                    const item = response.data;
                    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-tag"></i> Edit Category';
                    document.getElementById('itemId').value = item.category_id;
                    document.getElementById('name').value = item.category_name;
                    document.getElementById('description').value = item.description || '';
                    document.getElementById('modal').style.display = 'flex';
                }
            } catch (error) {
                UIComponents.showToast('Failed to load category', 'error');
            }
        }
        
        async function deleteItem(id, name) {
            const confirmed = await UIComponents.confirm(`Delete category "${name}"?`, 'Delete Category');
            if (confirmed) {
                try {
                    await API.categories.delete(id);
                    UIComponents.showToast('Category deleted', 'success');
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
                category_name: document.getElementById('name').value,
                description: document.getElementById('description').value
            };
            
            try {
                if (id) {
                    await API.categories.update(id, data);
                    UIComponents.showToast('Category updated', 'success');
                } else {
                    await API.categories.create(data);
                    UIComponents.showToast('Category added', 'success');
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
