// Books Management JavaScript

let currentBookId = null;

// Show Add Book Modal
function showAddBookModal() {
    currentBookId = null;
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus"></i> Add New Book';
    document.getElementById('bookForm').reset();
    document.getElementById('bookId').value = '';
    document.getElementById('coverPreview').style.display = 'none';
    document.getElementById('pdfPreview').style.display = 'none';
    document.getElementById('bookModal').style.display = 'flex';
}

// Close Book Modal
function closeBookModal() {
    document.getElementById('bookModal').style.display = 'none';
    document.getElementById('bookForm').reset();
    currentBookId = null;
}

// View Book Details
function viewBook(bookId) {
    const baseUrl = window.BASE_URL || '/library-pro';
    window.location.href = `${baseUrl}/public/book-details.php?id=${bookId}`;
}

// Edit Book - Load data from page data attribute
function editBook(bookId) {
    // Find the book row
    const row = document.querySelector(`tr[data-book-id="${bookId}"]`);
    if (!row) {
        alert('Book not found');
        return;
    }
    
    // Get book data from data attributes
    const bookData = {
        book_id: row.dataset.bookId,
        title: row.dataset.title,
        isbn: row.dataset.isbn,
        category_id: row.dataset.categoryId,
        author_id: row.dataset.authorId,
        publication_year: row.dataset.publicationYear,
        description: row.dataset.description,
        publisher: row.dataset.publisher,
        total_quantity: row.dataset.totalQuantity,
        available_quantity: row.dataset.availableQuantity,
        cover_image: row.dataset.coverImage,
        pdf_file: row.dataset.pdfFile
    };
    
    currentBookId = bookId;
    
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Book';
    document.getElementById('bookId').value = bookData.book_id;
    document.getElementById('title').value = bookData.title || '';
    document.getElementById('isbn').value = bookData.isbn || '';
    document.getElementById('categoryId').value = bookData.category_id || '';
    document.getElementById('authorId').value = bookData.author_id || '';
    document.getElementById('publicationYear').value = bookData.publication_year || '';
    document.getElementById('description').value = bookData.description || '';
    document.getElementById('totalCopies').value = bookData.total_quantity || 1;
    document.getElementById('availableCopies').value = bookData.available_quantity || 0;
    document.getElementById('publisher').value = bookData.publisher || '';
    document.getElementById('coverImage').value = bookData.cover_image || '';
    document.getElementById('pdfFile').value = bookData.pdf_file || '';
    
    // Show cover preview if exists
    if (bookData.cover_image) {
        document.getElementById('coverPreviewImg').src = bookData.cover_image;
        document.getElementById('coverPreview').style.display = 'block';
    }
    
    // Show PDF preview if exists
    if (bookData.pdf_file) {
        document.getElementById('pdfFileName').textContent = 'Current PDF: ' + bookData.pdf_file.split('/').pop();
        document.getElementById('pdfPreview').style.display = 'flex';
    }
    
    document.getElementById('bookModal').style.display = 'flex';
}

// Delete Book - Direct form submission
function deleteBook(bookId, bookTitle) {
    if (!confirm(`Are you sure you want to delete "${bookTitle}"?\n\nThis action cannot be undone.`)) {
        return;
    }
    
    // Create a form and submit it
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '';
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'delete';
    form.appendChild(actionInput);
    
    const bookIdInput = document.createElement('input');
    bookIdInput.type = 'hidden';
    bookIdInput.name = 'book_id';
    bookIdInput.value = bookId;
    form.appendChild(bookIdInput);
    
    document.body.appendChild(form);
    form.submit();
}

// Handle Form Submit - Direct submission, no API
document.getElementById('bookForm').addEventListener('submit', function(e) {
    // Let the form submit naturally to PHP
    // No e.preventDefault() - form will submit to same page
    return true;
});

// Preview cover image when file is selected
document.getElementById('coverImageFile').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('coverPreviewImg').src = e.target.result;
            document.getElementById('coverPreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

// Preview PDF file name when selected
document.getElementById('pdfFileUpload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        document.getElementById('pdfFileName').textContent = file.name;
        document.getElementById('pdfPreview').style.display = 'flex';
    }
});

// Search functionality
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#booksTableBody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
    
    updateTableInfo();
});

// Update table info
function updateTableInfo() {
    const rows = document.querySelectorAll('#booksTableBody tr');
    const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
    document.getElementById('tableInfo').textContent = `Showing ${visibleRows.length} books`;
}

// Notification function
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;
    
    // Add to body
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => notification.classList.add('show'), 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Logout function
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        const baseUrl = window.BASE_URL || '/library-pro';
        window.location.href = `${baseUrl}/api/auth/logout.php`;
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateTableInfo();
    
    // Close modal when clicking outside
    document.getElementById('bookModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeBookModal();
        }
    });
});
