// Browse Books Page JavaScript
let allBooks = [];
let allCategories = [];
let filteredBooks = [];
let currentPage = 1;
const booksPerPage = 12;

// Filters
let searchQuery = '';
let selectedCategory = '';
let selectedStatus = '';
let sortBy = 'newest';

// Load all data
async function loadData() {
    try {
        // Load books and categories in parallel
        const [booksResponse, categoriesResponse] = await Promise.all([
            API.books.getAll({}),
            API.categories.getAll({})
        ]);
        
        if (booksResponse.success) {
            allBooks = booksResponse.data;
            console.log('Loaded books:', allBooks.length);
            updateStats();
            applyFilters();
        }
        
        if (categoriesResponse.success) {
            allCategories = categoriesResponse.data;
            console.log('Loaded categories:', allCategories.length);
            renderCategoryFilters();
            renderCategoryPills();
        }
    } catch (error) {
        console.error('Error loading data:', error);
        showError('Failed to load books. Please try again.');
    }
}

// Update statistics
function updateStats() {
    const total = allBooks.length;
    const available = allBooks.filter(b => b.status === 'available').length;
    const categories = new Set(allBooks.map(b => b.category_id).filter(Boolean)).size;
    
    document.getElementById('totalBooks').textContent = total;
    document.getElementById('availableBooks').textContent = available;
    document.getElementById('totalCategories').textContent = categories;
}

// Render category filters
function renderCategoryFilters() {
    const categoryFilter = document.getElementById('categoryFilter');
    categoryFilter.innerHTML = '<option value="">All Categories</option>' +
        allCategories.map(cat => 
            `<option value="${cat.category_id}">${Utils.escapeHtml(cat.category_name)}</option>`
        ).join('');
}

// Render category pills
function renderCategoryPills() {
    const pillsContainer = document.getElementById('categoryPills');
    const pills = allCategories.slice(0, 8).map(cat => `
        <button class="category-pill" data-category="${cat.category_id}">
            <i class="fas fa-tag"></i> ${Utils.escapeHtml(cat.category_name)}
        </button>
    `).join('');
    
    pillsContainer.innerHTML = `
        <button class="category-pill active" data-category="">
            <i class="fas fa-th"></i> All Books
        </button>
        ${pills}
    `;
    
    // Add click handlers
    pillsContainer.querySelectorAll('.category-pill').forEach(pill => {
        pill.addEventListener('click', () => {
            selectedCategory = pill.dataset.category;
            document.getElementById('categoryFilter').value = selectedCategory;
            
            // Update active state
            pillsContainer.querySelectorAll('.category-pill').forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            
            applyFilters();
        });
    });
}

// Apply all filters
function applyFilters() {
    filteredBooks = [...allBooks];
    
    // Search filter
    if (searchQuery) {
        const query = searchQuery.toLowerCase();
        filteredBooks = filteredBooks.filter(book =>
            book.title.toLowerCase().includes(query) ||
            (book.authors && book.authors.toLowerCase().includes(query)) ||
            (book.isbn && book.isbn.toLowerCase().includes(query))
        );
    }
    
    // Category filter
    if (selectedCategory) {
        filteredBooks = filteredBooks.filter(book => book.category_id == selectedCategory);
    }
    
    // Status filter
    if (selectedStatus) {
        filteredBooks = filteredBooks.filter(book => book.status === selectedStatus);
    }
    
    // Sort
    sortBooks();
    
    // Reset to first page
    currentPage = 1;
    
    // Render
    renderBooks();
}

// Sort books
function sortBooks() {
    switch (sortBy) {
        case 'title-asc':
            filteredBooks.sort((a, b) => a.title.localeCompare(b.title));
            break;
        case 'title-desc':
            filteredBooks.sort((a, b) => b.title.localeCompare(a.title));
            break;
        case 'author':
            filteredBooks.sort((a, b) => (a.authors || '').localeCompare(b.authors || ''));
            break;
        case 'newest':
        default:
            filteredBooks.sort((a, b) => (b.book_id || 0) - (a.book_id || 0));
            break;
    }
}

// Render books
function renderBooks() {
    const grid = document.getElementById('booksGrid');
    const paginationContainer = document.getElementById('paginationContainer');
    const paginationInfo = document.getElementById('paginationInfo');
    
    if (filteredBooks.length === 0) {
        grid.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <h3>No books found</h3>
                <p>Try adjusting your search or filters</p>
            </div>
        `;
        paginationContainer.style.display = 'none';
        return;
    }
    
    // Calculate pagination
    const totalPages = Math.ceil(filteredBooks.length / booksPerPage);
    const startIndex = (currentPage - 1) * booksPerPage;
    const endIndex = startIndex + booksPerPage;
    const booksToShow = filteredBooks.slice(startIndex, endIndex);
    
    // Render books
    grid.innerHTML = booksToShow.map(book => `
        <div class="book-card" onclick="showBookDetails(${book.book_id})">
            <div class="book-cover">
                <i class="fas fa-book"></i>
                <span class="book-status-badge ${book.status === 'available' ? 'badge-success' : 'badge-warning'}">
                    ${Utils.capitalize(book.status)}
                </span>
            </div>
            <div class="book-info">
                <h3 class="book-title">${Utils.escapeHtml(book.title)}</h3>
                <div class="book-author">
                    <i class="fas fa-user"></i>
                    ${Utils.escapeHtml(book.authors || 'Unknown Author')}
                </div>
                ${book.category_name ? `<span class="book-category">${Utils.escapeHtml(book.category_name)}</span>` : ''}
                ${book.isbn ? `<div class="book-isbn">ISBN: ${Utils.escapeHtml(book.isbn)}</div>` : ''}
            </div>
            <div class="book-footer">
                <span style="font-size: 12px; color: var(--gray-500);">
                    <i class="fas fa-copy"></i> ${book.total_copies || 0} copies
                </span>
                <button class="book-action-btn" onclick="event.stopPropagation(); borrowBook(${book.book_id})">
                    <i class="fas fa-book-reader"></i> Borrow
                </button>
            </div>
        </div>
    `).join('');
    
    // Update pagination
    paginationInfo.textContent = `Showing ${startIndex + 1}-${Math.min(endIndex, filteredBooks.length)} of ${filteredBooks.length} books`;
    paginationContainer.style.display = 'flex';
    renderPagination(totalPages);
}

// Render pagination
function renderPagination(totalPages) {
    const controls = document.getElementById('paginationControls');
    
    if (totalPages <= 1) {
        controls.innerHTML = '';
        return;
    }
    
    let html = `
        <button class="pagination-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(${currentPage - 1})">
            <i class="fas fa-chevron-left"></i>
        </button>
    `;
    
    // Page numbers
    for (let i = 1; i <= Math.min(totalPages, 5); i++) {
        html += `
            <button class="pagination-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">
                ${i}
            </button>
        `;
    }
    
    if (totalPages > 5) {
        html += `<span style="padding: 0 8px;">...</span>`;
        html += `
            <button class="pagination-btn" onclick="changePage(${totalPages})">
                ${totalPages}
            </button>
        `;
    }
    
    html += `
        <button class="pagination-btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="changePage(${currentPage + 1})">
            <i class="fas fa-chevron-right"></i>
        </button>
    `;
    
    controls.innerHTML = html;
}

// Change page
function changePage(page) {
    currentPage = page;
    renderBooks();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Show book details
async function showBookDetails(bookId) {
    const modal = document.getElementById('bookModal');
    const modalBody = document.getElementById('modalBody');
    
    modal.style.display = 'flex';
    modalBody.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div>';
    
    try {
        const response = await API.books.getById(bookId);
        if (response.success) {
            const book = response.data;
            modalBody.innerHTML = `
                <div class="modal-book-cover">
                    <i class="fas fa-book"></i>
                </div>
                <h2 class="modal-book-title">${Utils.escapeHtml(book.title)}</h2>
                <div class="modal-book-meta">
                    <div class="meta-item">
                        <i class="fas fa-user"></i>
                        <span><strong>Author:</strong> ${Utils.escapeHtml(book.authors || 'Unknown')}</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-tag"></i>
                        <span><strong>Category:</strong> ${Utils.escapeHtml(book.category_name || 'N/A')}</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-barcode"></i>
                        <span><strong>ISBN:</strong> ${Utils.escapeHtml(book.isbn || 'N/A')}</span>
                    </div>
                    ${book.publisher ? `
                        <div class="meta-item">
                            <i class="fas fa-building"></i>
                            <span><strong>Publisher:</strong> ${Utils.escapeHtml(book.publisher)}</span>
                        </div>
                    ` : ''}
                    ${book.publication_year ? `
                        <div class="meta-item">
                            <i class="fas fa-calendar"></i>
                            <span><strong>Year:</strong> ${book.publication_year}</span>
                        </div>
                    ` : ''}
                    <div class="meta-item">
                        <i class="fas fa-copy"></i>
                        <span><strong>Copies:</strong> ${book.total_copies || 0} total, ${book.available_copies || 0} available</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-info-circle"></i>
                        <span><strong>Status:</strong> <span class="badge ${Utils.getStatusBadge(book.status)}">${Utils.capitalize(book.status)}</span></span>
                    </div>
                </div>
                ${book.description ? `
                    <div class="modal-book-description">
                        <h3>Description</h3>
                        <p>${Utils.escapeHtml(book.description)}</p>
                    </div>
                ` : ''}
                <div class="modal-actions">
                    <button class="btn btn-secondary" onclick="closeBookModal()">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <button class="btn btn-primary" onclick="borrowBook(${book.book_id})">
                        <i class="fas fa-book-reader"></i> Borrow This Book
                    </button>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading book details:', error);
        modalBody.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>Error</h3><p>Failed to load book details</p></div>';
    }
}

// Close modal
function closeBookModal() {
    document.getElementById('bookModal').style.display = 'none';
}

// Borrow book (requires login)
function borrowBook(bookId) {
    closeBookModal();
    UIComponents.showModal({
        title: 'Login Required',
        content: '<p>You need to login to borrow books. Would you like to login now?</p>',
        buttons: [
            {
                text: 'Cancel',
                class: 'btn-secondary',
                onClick: (close) => close()
            },
            {
                text: 'Login',
                class: 'btn-primary',
                onClick: (close) => {
                    window.location.href = '/library-pro/public/login.php';
                }
            }
        ]
    });
}

// Show error
function showError(message) {
    const grid = document.getElementById('booksGrid');
    grid.innerHTML = `
        <div class="empty-state">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Error</h3>
            <p>${message}</p>
        </div>
    `;
}

// Event listeners
document.getElementById('searchInput').addEventListener('input', Utils.debounce((e) => {
    searchQuery = e.target.value;
    applyFilters();
}, 300));

document.getElementById('categoryFilter').addEventListener('change', (e) => {
    selectedCategory = e.target.value;
    
    // Update pills
    document.querySelectorAll('.category-pill').forEach(pill => {
        pill.classList.toggle('active', pill.dataset.category === selectedCategory);
    });
    
    applyFilters();
});

document.getElementById('statusFilter').addEventListener('change', (e) => {
    selectedStatus = e.target.value;
    applyFilters();
});

document.getElementById('sortFilter').addEventListener('change', (e) => {
    sortBy = e.target.value;
    applyFilters();
});

// Mobile menu toggle
document.querySelector('.mobile-menu-toggle')?.addEventListener('click', function() {
    document.querySelector('.nav-links').classList.toggle('active');
});

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    console.log('Browse page loaded');
    loadData();
});
