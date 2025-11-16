async function loadBooks() {
    const booksGrid = document.getElementById('booksGrid');
    try {
        const response = await API.books.getAll({ limit: 12 });
        if (response.success && response.data.length > 0) {
            booksGrid.innerHTML = response.data.map(book => {
                const authors = book.authors && book.authors.length > 0
                    ? book.authors.map(a => `${a.first_name} ${a.last_name}`).join(', ')
                    : 'Unknown Author';
                const available = book.available_quantity > 0;
                
                return `
                    <div class="book-card">
                        <div class="book-cover">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="book-info">
                            <div class="book-title">${book.title}</div>
                            <div class="book-author">by ${authors}</div>
                            <div class="book-availability">
                                <span class="availability-badge ${available ? 'available' : 'unavailable'}">
                                    ${available ? `${book.available_quantity} Available` : 'Not Available'}
                                </span>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            booksGrid.innerHTML = '<p class="loading">No books available</p>';
        }
    } catch (error) {
        console.error('Failed to load books:', error);
        booksGrid.innerHTML = '<p class="loading">Failed to load books</p>';
    }
}

async function searchBooks() {
    const query = document.getElementById('searchInput').value.trim();
    if (!query) {
        loadBooks();
        return;
    }
    
    const booksGrid = document.getElementById('booksGrid');
    booksGrid.innerHTML = '<p class="loading">Searching...</p>';
    
    try {
        const response = await API.books.search(query);
        if (response.success && response.data.length > 0) {
            booksGrid.innerHTML = response.data.map(book => {
                const authors = book.authors && book.authors.length > 0
                    ? book.authors.map(a => `${a.first_name} ${a.last_name}`).join(', ')
                    : 'Unknown Author';
                const available = book.available_quantity > 0;
                
                return `
                    <div class="book-card">
                        <div class="book-cover">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="book-info">
                            <div class="book-title">${book.title}</div>
                            <div class="book-author">by ${authors}</div>
                            <div class="book-availability">
                                <span class="availability-badge ${available ? 'available' : 'unavailable'}">
                                    ${available ? `${book.available_quantity} Available` : 'Not Available'}
                                </span>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            booksGrid.innerHTML = '<p class="loading">No books found</p>';
        }
    } catch (error) {
        console.error('Search failed:', error);
        booksGrid.innerHTML = '<p class="loading">Search failed</p>';
    }
}

document.getElementById('searchInput')?.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        searchBooks();
    }
});

async function logout() {
    try {
        await API.auth.logout();
        window.location.href = '/Libra Project/public/login.php';
    } catch (error) {
        console.error('Logout failed:', error);
        window.location.href = '/Libra Project/public/login.php';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadBooks();
});
