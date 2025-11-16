let searchQuery = '';

async function loadUsers() {
    const tbody = document.getElementById('usersTableBody');
    tbody.innerHTML = '<tr><td colspan="7" class="loading"><i class="fas fa-spinner fa-spin"></i> Loading users...</td></tr>';
    
    try {
        const params = {};
        if (searchQuery) params.q = searchQuery;
        
        const response = await API.users.getAll(params);
        
        if (response.success && response.data.length > 0) {
            tbody.innerHTML = response.data.map(user => `
                <tr>
                    <td>${user.user_id}</td>
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
            tbody.innerHTML = '<tr><td colspan="7" class="empty">No users found</td></tr>';
        }
    } catch (error) {
        console.error('Error loading users:', error);
        tbody.innerHTML = '<tr><td colspan="7" class="error">Failed to load users</td></tr>';
        UIComponents.showToast('Failed to load users', 'error');
    }
}

function showAddUserModal() {
    document.getElementById('modalTitle').textContent = 'Add New User';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('password').required = true;
    document.getElementById('userModal').style.display = 'flex';
}

function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
}

async function editUser(id) {
    try {
        const response = await API.users.getById(id);
        if (response.success) {
            const user = response.data;
            document.getElementById('modalTitle').textContent = 'Edit User';
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
            document.getElementById('userModal').style.display = 'flex';
        }
    } catch (error) {
        UIComponents.showToast('Failed to load user details', 'error');
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
                UIComponents.showToast('User deleted successfully', 'success');
                loadUsers();
            }
        } catch (error) {
            UIComponents.showToast(error.error?.message || 'Failed to delete user', 'error');
        }
    }
}

document.getElementById('userForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
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
        
        if (response.success) {
            UIComponents.showToast(userId ? 'User updated successfully' : 'User added successfully', 'success');
            closeUserModal();
            loadUsers();
        }
    } catch (error) {
        UIComponents.showToast(error.error?.message || 'Failed to save user', 'error');
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
        window.location.href = '/library-pro/public/login.php';
    });
}

document.addEventListener('DOMContentLoaded', () => {
    loadUsers();
});
