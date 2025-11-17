let searchQuery = '';

// Fallback toast notification if UIComponents is not available
function showToast(message, type = 'success') {
    if (typeof UIComponents !== 'undefined' && UIComponents.showToast) {
        UIComponents.showToast(message, type);
    } else {
        // Simple fallback toast
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            background: ${type === 'success' ? '#10b981' : '#ef4444'};
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            font-size: 14px;
            font-weight: 500;
        `;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}

async function loadUsers() {
    const tbody = document.getElementById('usersTableBody');
    tbody.innerHTML = `<tr><td colspan="7" class="loading-cell">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Loading users...</span>
        </div>
    </td></tr>`;
    
    try {
        const params = {};
        if (searchQuery) params.q = searchQuery;
        
        const response = await API.users.getAll(params);
        
        if (response.success && response.data.length > 0) {
            tbody.innerHTML = response.data.map(user => `
                <tr>
                    <td><strong>#${user.user_id}</strong></td>
                    <td><strong>${Utils.escapeHtml(user.first_name + ' ' + user.last_name)}</strong></td>
                    <td>${Utils.escapeHtml(user.email)}</td>
                    <td>${Utils.escapeHtml(user.phone || 'N/A')}</td>
                    <td><span class="badge ${user.user_type === 'admin' ? 'badge-warning' : 'badge-primary'}">${Utils.capitalize(user.user_type)}</span></td>
                    <td><span class="badge ${user.status === 'active' ? 'badge-success' : 'badge-gray'}">${Utils.capitalize(user.status)}</span></td>
                    <td class="actions">
                        <button onclick="editUser(${user.user_id})" class="btn btn-sm btn-secondary" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteUser(${user.user_id}, '${Utils.escapeHtml(user.first_name + ' ' + user.last_name)}')" class="btn btn-sm btn-danger" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="7" class="empty-cell">No users found</td></tr>';
        }
    } catch (error) {
        console.error('Error loading users:', error);
        tbody.innerHTML = '<tr><td colspan="7" class="empty-cell" style="color: var(--error-600);">Failed to load users</td></tr>';
        showToast('Failed to load users', 'error');
    }
}

function showCreateUserModal() {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-plus"></i> Create New User';
    document.getElementById('submitBtnText').textContent = 'Create User';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('password').required = true;
    document.getElementById('passwordHint').textContent = 'Required for new users';
    document.getElementById('userModal').style.display = 'flex';
}

function showAddUserModal() {
    showCreateUserModal();
}

function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
}

async function editUser(id) {
    try {
        const response = await API.users.getById(id);
        if (response.success) {
            const user = response.data;
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-edit"></i> Edit User';
            document.getElementById('submitBtnText').textContent = 'Update User';
            document.getElementById('userId').value = user.user_id;
            document.getElementById('firstName').value = user.first_name;
            document.getElementById('lastName').value = user.last_name;
            document.getElementById('email').value = user.email;
            document.getElementById('phone').value = user.phone || '';
            document.getElementById('userType').value = user.user_type;
            document.getElementById('address').value = user.address || '';
            document.getElementById('status').value = user.status;
            document.getElementById('password').required = false;
            document.getElementById('password').value = '';
            document.getElementById('passwordHint').textContent = 'Leave blank to keep current password';
            document.getElementById('userModal').style.display = 'flex';
        }
    } catch (error) {
        showToast('Failed to load user details', 'error');
    }
}

async function deleteUser(id, name) {
    const confirmed = await UIComponents.confirm(
        `Are you sure you want to delete user "${name}"?`,
        'Delete User'
    );
    
    if (confirmed) {
        try {
            const response = await API.users.delete(id);
            if (response.success) {
                showToast('User deleted successfully', 'success');
                loadUsers();
            }
        } catch (error) {
            showToast(error.error?.message || 'Failed to delete user', 'error');
        }
    }
}

document.getElementById('userForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    
    const userId = document.getElementById('userId').value;
    const userData = {
        first_name: document.getElementById('firstName').value,
        last_name: document.getElementById('lastName').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        user_type: document.getElementById('userType').value,
        address: document.getElementById('address').value,
        status: document.getElementById('status').value
    };
    
    const password = document.getElementById('password').value;
    if (password) {
        userData.password = password;
    }
    
    try {
        let response;
        if (userId) {
            response = await API.users.update(userId, userData);
        } else {
            response = await API.users.create(userData);
        }
        
        console.log('User save response:', response);
        
        if (response.success) {
            const message = userId ? 'User updated successfully' : 'User created successfully';
            showToast(message, 'success');
            closeUserModal();
            // Reload users after a short delay to ensure database is updated
            setTimeout(() => {
                loadUsers();
            }, 300);
        } else {
            throw new Error(response.error?.message || 'Failed to save user');
        }
    } catch (error) {
        console.error('Error saving user:', error);
        const errorMsg = error.error?.message || error.message || 'Failed to save user';
        showToast(errorMsg, 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    }
});

let searchTimeout;
document.getElementById('searchInput').addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        searchQuery = e.target.value;
        loadUsers();
    }, 500);
});

function logout() {
    API.auth.logout().then(() => {
        window.location.href = '/the-robboeb-library/public/home.php';
    });
}

document.addEventListener('DOMContentLoaded', () => {
    loadUsers();
});
