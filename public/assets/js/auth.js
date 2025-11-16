function showTab(tab) {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const tabs = document.querySelectorAll('.tab-btn');
    
    tabs.forEach(btn => btn.classList.remove('active'));
    
    if (tab === 'login') {
        loginForm.style.display = 'block';
        registerForm.style.display = 'none';
        tabs[0].classList.add('active');
    } else {
        loginForm.style.display = 'none';
        registerForm.style.display = 'block';
        tabs[1].classList.add('active');
    }
    hideMessage();
}

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
                window.location.href = '/library-pro/public/admin/index.php';
            } else {
                window.location.href = '/library-pro/public/user/index.php';
            }
        }
    } catch (error) {
        showMessage(error.error?.message || 'Login failed', 'error');
    }
});

document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    hideMessage();
    
    const formData = {
        email: document.getElementById('reg-email').value,
        password: document.getElementById('reg-password').value,
        first_name: document.getElementById('reg-first-name').value,
        last_name: document.getElementById('reg-last-name').value,
        phone: document.getElementById('reg-phone').value,
        address: document.getElementById('reg-address').value
    };
    
    try {
        const response = await API.auth.register(formData);
        if (response.success) {
            showMessage('Registration successful! Redirecting...', 'success');
            setTimeout(() => {
                window.location.href = '/library-pro/public/user/index.php';
            }, 1500);
        }
    } catch (error) {
        const errorMsg = error.error?.details 
            ? Object.values(error.error.details).join(', ')
            : error.error?.message || 'Registration failed';
        showMessage(errorMsg, 'error');
    }
});
