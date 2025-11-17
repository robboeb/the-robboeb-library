async function loadDashboardStats() {
    try {
        console.log('Loading dashboard stats...');
        const response = await API.reports.getDashboard();
        console.log('Dashboard response:', response);
        
        if (response.success) {
            const stats = response.data;
            console.log('Stats data:', stats);
            
            const totalBooksEl = document.getElementById('totalBooks');
            const activeLoansEl = document.getElementById('activeLoans');
            const overdueLoansEl = document.getElementById('overdueLoans');
            const totalUsersEl = document.getElementById('totalUsers');
            
            if (totalBooksEl) totalBooksEl.textContent = stats.total_books || 0;
            if (activeLoansEl) activeLoansEl.textContent = stats.active_loans || 0;
            if (overdueLoansEl) overdueLoansEl.textContent = stats.overdue_loans || 0;
            if (totalUsersEl) totalUsersEl.textContent = stats.total_users || 0;
            
            console.log('Dashboard stats loaded successfully');
        } else {
            console.error('Dashboard API returned success=false:', response);
        }
    } catch (error) {
        console.error('Failed to load dashboard stats:', error);
        console.error('Error details:', error.message, error.stack);
        
        // Show error in UI
        document.getElementById('totalBooks').textContent = '0';
        document.getElementById('activeLoans').textContent = '0';
        document.getElementById('overdueLoans').textContent = '0';
        document.getElementById('totalUsers').textContent = '0';
    }
}

async function loadRecentActivity() {
    const activityDiv = document.getElementById('recentActivity');
    if (!activityDiv) {
        console.error('recentActivity element not found');
        return;
    }
    
    try {
        console.log('Loading recent activity...');
        const response = await API.loans.getAll({ limit: 5 });
        console.log('Recent activity response:', response);
        
        if (response.success && response.data && response.data.length > 0) {
            activityDiv.innerHTML = response.data.slice(0, 5).map(loan => `
                <div class="activity-item" style="padding: 12px 0; border-bottom: 1px solid #eee;">
                    <div style="font-weight: 500;">${Utils.escapeHtml(loan.user_name || 'User')} borrowed ${Utils.escapeHtml(loan.book_title || 'Book')}</div>
                    <small style="color: #666;">Due: ${Utils.formatDate(loan.due_date)}</small>
                </div>
            `).join('');
            console.log('Recent activity loaded successfully');
        } else {
            activityDiv.innerHTML = '<p style="text-align: center; color: #999; padding: 20px;">No recent activity</p>';
        }
    } catch (error) {
        console.error('Failed to load recent activity:', error);
        activityDiv.innerHTML = '<p style="text-align: center; color: #999; padding: 20px;">Failed to load activity</p>';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadDashboardStats();
    loadRecentActivity();
});


function logout() {
    API.auth.logout().then(() => {
        window.location.href = '/the-robboeb-library/public/home.php';
    }).catch(() => {
        window.location.href = '/the-robboeb-library/public/home.php';
    });
}
