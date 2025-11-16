// UI Component Library

class UIComponents {
    // Toast Notifications
    static showToast(message, type = 'info', duration = 3000) {
        const container = this.getToastContainer();
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <span class="toast-icon">${this.getToastIcon(type)}</span>
                <span class="toast-message">${message}</span>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">×</button>
        `;
        
        container.appendChild(toast);
        
        // Animate in
        setTimeout(() => toast.classList.add('show'), 10);
        
        // Auto remove
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);
        
        return toast;
    }
    
    static getToastContainer() {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        return container;
    }
    
    static getToastIcon(type) {
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        return icons[type] || icons.info;
    }
    
    // Modal
    static showModal(options) {
        const {
            title = 'Modal',
            content = '',
            buttons = [],
            size = 'md',
            onClose = null
        } = options;
        
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop';
        backdrop.innerHTML = `
            <div class="modal modal-${size}">
                <div class="modal-header">
                    <h3 class="modal-title">${title}</h3>
                    <button class="btn btn-icon modal-close">×</button>
                </div>
                <div class="modal-body">${content}</div>
                ${buttons.length ? `
                    <div class="modal-footer">
                        ${buttons.map(btn => `
                            <button class="btn ${btn.class || 'btn-secondary'}" data-action="${btn.action || ''}">${btn.text}</button>
                        `).join('')}
                    </div>
                ` : ''}
            </div>
        `;
        
        document.body.appendChild(backdrop);
        
        // Close handlers
        const closeModal = () => {
            backdrop.remove();
            if (onClose) onClose();
        };
        
        backdrop.querySelector('.modal-close').addEventListener('click', closeModal);
        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop) closeModal();
        });
        
        // Button handlers
        buttons.forEach((btn, index) => {
            const btnElement = backdrop.querySelectorAll('.modal-footer .btn')[index];
            if (btnElement && btn.onClick) {
                btnElement.addEventListener('click', () => {
                    btn.onClick(closeModal);
                });
            }
        });
        
        return backdrop;
    }
    
    // Confirm Dialog
    static confirm(message, title = 'Confirm') {
        return new Promise((resolve) => {
            this.showModal({
                title,
                content: `<p>${message}</p>`,
                buttons: [
                    {
                        text: 'Cancel',
                        class: 'btn-secondary',
                        onClick: (close) => {
                            close();
                            resolve(false);
                        }
                    },
                    {
                        text: 'Confirm',
                        class: 'btn-primary',
                        onClick: (close) => {
                            close();
                            resolve(true);
                        }
                    }
                ]
            });
        });
    }
    
    // Loading Overlay
    static showLoading(message = 'Loading...') {
        const overlay = document.createElement('div');
        overlay.id = 'loading-overlay';
        overlay.className = 'loading-overlay';
        overlay.innerHTML = `
            <div class="loading-content">
                <div class="spinner spinner-lg"></div>
                <p class="loading-message">${message}</p>
            </div>
        `;
        document.body.appendChild(overlay);
        return overlay;
    }
    
    static hideLoading() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) overlay.remove();
    }
    
    // Dropdown
    static initDropdowns() {
        document.addEventListener('click', (e) => {
            const trigger = e.target.closest('[data-dropdown-trigger]');
            
            if (trigger) {
                e.preventDefault();
                const dropdownId = trigger.dataset.dropdownTrigger;
                const dropdown = document.getElementById(dropdownId);
                
                if (dropdown) {
                    // Close other dropdowns
                    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                        if (menu !== dropdown) menu.classList.remove('show');
                    });
                    
                    dropdown.classList.toggle('show');
                }
            } else {
                // Close all dropdowns when clicking outside
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });
    }
    
    // Tabs
    static initTabs() {
        document.querySelectorAll('[data-tab-trigger]').forEach(trigger => {
            trigger.addEventListener('click', () => {
                const tabId = trigger.dataset.tabTrigger;
                const tabGroup = trigger.closest('[data-tab-group]');
                
                if (tabGroup) {
                    // Update triggers
                    tabGroup.querySelectorAll('[data-tab-trigger]').forEach(t => {
                        t.classList.remove('active');
                    });
                    trigger.classList.add('active');
                    
                    // Update content
                    tabGroup.querySelectorAll('[data-tab-content]').forEach(content => {
                        content.classList.add('hidden');
                    });
                    const activeContent = tabGroup.querySelector(`[data-tab-content="${tabId}"]`);
                    if (activeContent) {
                        activeContent.classList.remove('hidden');
                    }
                }
            });
        });
    }
    
    // Form Validation
    static validateForm(form) {
        const errors = [];
        const inputs = form.querySelectorAll('[required]');
        
        inputs.forEach(input => {
            const value = input.value.trim();
            const label = input.previousElementSibling?.textContent || input.name;
            
            if (!value) {
                errors.push(`${label} is required`);
                input.classList.add('error');
            } else {
                input.classList.remove('error');
            }
            
            // Email validation
            if (input.type === 'email' && value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    errors.push(`${label} must be a valid email`);
                    input.classList.add('error');
                }
            }
            
            // Min length validation
            if (input.minLength && value.length < input.minLength) {
                errors.push(`${label} must be at least ${input.minLength} characters`);
                input.classList.add('error');
            }
        });
        
        return {
            isValid: errors.length === 0,
            errors
        };
    }
    
    // Pagination
    static renderPagination(container, currentPage, totalPages, onPageChange) {
        const maxVisible = 5;
        const pages = [];
        
        let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);
        
        if (endPage - startPage < maxVisible - 1) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }
        
        for (let i = startPage; i <= endPage; i++) {
            pages.push(i);
        }
        
        const html = `
            <div class="pagination">
                <button class="pagination-btn" ${currentPage === 1 ? 'disabled' : ''} data-page="${currentPage - 1}">
                    ← Previous
                </button>
                ${pages.map(page => `
                    <button class="pagination-btn ${page === currentPage ? 'active' : ''}" data-page="${page}">
                        ${page}
                    </button>
                `).join('')}
                <button class="pagination-btn" ${currentPage === totalPages ? 'disabled' : ''} data-page="${currentPage + 1}">
                    Next →
                </button>
            </div>
        `;
        
        container.innerHTML = html;
        
        container.querySelectorAll('.pagination-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const page = parseInt(btn.dataset.page);
                if (page && page !== currentPage) {
                    onPageChange(page);
                }
            });
        });
    }
    
    // Data Table
    static renderTable(container, data, columns, options = {}) {
        const {
            sortable = true,
            searchable = true,
            pagination = true,
            itemsPerPage = 10,
            emptyMessage = 'No data available'
        } = options;
        
        let currentPage = 1;
        let sortColumn = null;
        let sortDirection = 'asc';
        let searchQuery = '';
        
        const render = () => {
            let filteredData = [...data];
            
            // Search
            if (searchQuery) {
                filteredData = filteredData.filter(row => {
                    return columns.some(col => {
                        const value = col.render ? col.render(row) : row[col.field];
                        return String(value).toLowerCase().includes(searchQuery.toLowerCase());
                    });
                });
            }
            
            // Sort
            if (sortColumn) {
                filteredData.sort((a, b) => {
                    const aVal = a[sortColumn];
                    const bVal = b[sortColumn];
                    const modifier = sortDirection === 'asc' ? 1 : -1;
                    return aVal > bVal ? modifier : -modifier;
                });
            }
            
            // Pagination
            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
            const startIndex = (currentPage - 1) * itemsPerPage;
            const paginatedData = filteredData.slice(startIndex, startIndex + itemsPerPage);
            
            let html = '';
            
            // Search bar
            if (searchable) {
                html += `
                    <div class="table-toolbar">
                        <div class="search-bar">
                            <input type="text" class="search-input" placeholder="Search..." value="${searchQuery}">
                        </div>
                    </div>
                `;
            }
            
            // Table
            html += `
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                ${columns.map(col => `
                                    <th ${sortable && col.sortable !== false ? `class="sortable" data-field="${col.field}"` : ''}>
                                        ${col.label}
                                        ${sortColumn === col.field ? (sortDirection === 'asc' ? '↑' : '↓') : ''}
                                    </th>
                                `).join('')}
                            </tr>
                        </thead>
                        <tbody>
                            ${paginatedData.length ? paginatedData.map(row => `
                                <tr>
                                    ${columns.map(col => `
                                        <td>${col.render ? col.render(row) : row[col.field]}</td>
                                    `).join('')}
                                </tr>
                            `).join('') : `
                                <tr>
                                    <td colspan="${columns.length}" class="text-center text-gray-500">
                                        ${emptyMessage}
                                    </td>
                                </tr>
                            `}
                        </tbody>
                    </table>
                </div>
            `;
            
            // Pagination
            if (pagination && totalPages > 1) {
                html += '<div class="table-pagination"></div>';
            }
            
            container.innerHTML = html;
            
            // Event listeners
            if (searchable) {
                container.querySelector('.search-input').addEventListener('input', (e) => {
                    searchQuery = e.target.value;
                    currentPage = 1;
                    render();
                });
            }
            
            if (sortable) {
                container.querySelectorAll('th.sortable').forEach(th => {
                    th.addEventListener('click', () => {
                        const field = th.dataset.field;
                        if (sortColumn === field) {
                            sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
                        } else {
                            sortColumn = field;
                            sortDirection = 'asc';
                        }
                        render();
                    });
                });
            }
            
            if (pagination && totalPages > 1) {
                const paginationContainer = container.querySelector('.table-pagination');
                this.renderPagination(paginationContainer, currentPage, totalPages, (page) => {
                    currentPage = page;
                    render();
                });
            }
        };
        
        render();
    }
    
    // Initialize all components
    static init() {
        this.initDropdowns();
        this.initTabs();
    }
}

// Auto-initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => UIComponents.init());
} else {
    UIComponents.init();
}
