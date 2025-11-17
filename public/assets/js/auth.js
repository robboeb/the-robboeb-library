// Registration removed - only admins can create accounts

function showMessage(message, type = 'error') {
    const messageDiv = document.getElementById('message');
    messageDiv.textContent = message;
    messageDiv.className = `message ${type}`;
    messageDiv.style.display = 'block';
}

function hideMessage() {
    const messageDiv = document.getElementById('message');
    messageDiv.style.display = 'none';
}

document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    hideMessage();
    
    const email = document.getElementById('login-email').value;
    const password = document.getElementById('login-password').value;
    
    try {
        const response = await API.auth.login(email, password);
        if (response.success) {
            const userType = response.data.user.user_type;
            if (userType === 'admin') {
                window.location.href = '/the-robboeb-library/public/admin/index.php';
            } else {
                window.location.href = '/the-robboeb-library/public/user/index.php';
            }
        }
    } catch (error) {
        showMessage(error.error?.message || 'Login failed', 'error');
    }
});

// Public registration disabled - contact administrator to create an account
