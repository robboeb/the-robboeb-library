# 📚 ROBBOEB Libra - Library Management System

A modern, feature-rich library management system with beautiful book covers, real-time search, and responsive design.

![ROBBOEB Libra](https://via.placeholder.com/1200x300/faa405/ffffff?text=ROBBOEB+Libra+Library+Management+System)

## ✨ Features

### 🎨 Modern Design
- **Book Cover Images** - Display real book covers from database
- **ROBBOEB Branding** - Consistent orange theme (#faa405)
- **Responsive Layout** - Works on desktop, tablet, and mobile
- **Smooth Animations** - Professional transitions and hover effects

### 📖 Book Management
- **Browse Catalog** - View all books with covers and details
- **Advanced Search** - Filter by title, author, category, status
- **Book Details** - Large cover image with full information
- **Real-time Availability** - Live stock and borrowing status

### 👥 User Features
- **User Dashboard** - View borrowed books and due dates
- **Loan Tracking** - See active loans with overdue warnings
- **Browse & Search** - Find books without logging in
- **Secure Login** - Authentication with role-based access

### 🔧 Admin Features
- **Dashboard** - Live statistics and recent activity
- **Book Management** - Add, edit, delete books
- **User Management** - Manage library members
- **Loan Management** - Track all borrowing activity
- **Category & Author Management** - Organize your collection

### ⚡ Technical Features
- **Direct Database Queries** - Fast, real-time data access
- **No API Dependencies** - All data from MySQL
- **Clean Code** - Well-organized PHP and CSS
- **Error Handling** - Graceful fallbacks for missing images
- **Security** - Prepared statements, input validation

## 🚀 Quick Start

### Prerequisites
- XAMPP (Apache + MySQL)
- Web browser
- 5 minutes of your time

### Installation

1. **Start XAMPP**
   ```
   Open XAMPP Control Panel
   Start Apache and MySQL
   ```

2. **Create Database**
   ```
   Visit: http://localhost/phpmyadmin
   Create database: libra_db_sys
   Import: database/schema.sql
   Import: database/seed.sql
   ```

3. **Add Book Covers** (Recommended)
   ```
   Visit: http://localhost/library-pro/add-book-covers.php
   Wait for automatic cover image setup
   ```

4. **Access Application**
   ```
   Visit: http://localhost/library-pro/public/home.php
   ```

**That's it!** Your library is ready to use.

## 📖 Documentation

- **[QUICK_START.md](QUICK_START.md)** - Get running in 3 minutes
- **[HOW_TO_RUN.md](HOW_TO_RUN.md)** - Detailed setup guide
- **[BOOK_COVERS_UPDATE.md](BOOK_COVERS_UPDATE.md)** - Book cover implementation
- **[DATABASE_STRUCTURE.md](DATABASE_STRUCTURE.md)** - Database schema
- **[UPDATE_SUMMARY.md](UPDATE_SUMMARY.md)** - Latest features
- **[BEGINNER_SETUP_GUIDE.md](BEGINNER_SETUP_GUIDE.md)** - Complete beginner guide

## 🔑 Login Credentials

### Admin Account
- **Email**: admin@libra.com
- **Password**: password
- **Access**: Full system management

### User Account
- **Email**: john.doe@email.com
- **Password**: password
- **Access**: Browse and borrow books

## 📱 Pages

### Public Pages (No Login Required)
- **Home** - Featured books with covers
- **Browse** - Full catalog with search and filters
- **About** - System information and statistics
- **Book Details** - Detailed book information

### User Pages (Login Required)
- **Dashboard** - Active loans and due dates
- **Browse Books** - Search and borrow

### Admin Pages (Admin Login Required)
- **Dashboard** - Statistics and analytics
- **Books** - Manage book catalog
- **Users** - Manage library members
- **Loans** - Track borrowing activity
- **Categories** - Organize book categories
- **Authors** - Manage author information

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Icons**: Font Awesome 6.4
- **Server**: Apache (XAMPP)

## 📂 Project Structure

```
library-pro/
├── api/                    # API endpoints (legacy)
├── config/                 # Configuration files
│   ├── constants.php      # App constants and BASE_URL
│   └── database.php       # Database connection
├── database/              # Database files
│   ├── schema.sql         # Database structure
│   ├── seed.sql           # Sample data
│   └── add-book-covers.sql # Book cover SQL
├── public/                # Public web files
│   ├── home.php          # Homepage
│   ├── browse.php        # Book catalog
│   ├── about.php         # About page
│   ├── book-details.php  # Book details
│   ├── login.php         # Login/Register
│   ├── admin/            # Admin pages
│   ├── user/             # User pages
│   └── assets/           # CSS, JS, images
├── src/                   # Source code
│   ├── helpers/          # Helper classes
│   │   └── DatabaseHelper.php
│   └── services/         # Service classes
│       └── AuthService.php
├── logs/                  # Error logs
├── add-book-covers.php   # Cover image setup script
├── test-book-covers.php  # Test cover images
└── Documentation files
```

## 🎨 Book Covers

### Adding Cover Images

**Automatic (Recommended)**:
```
Visit: http://localhost/library-pro/add-book-covers.php
```

**Manual via Database**:
```sql
UPDATE books 
SET cover_image = 'https://covers.openlibrary.org/b/isbn/9780451524935-L.jpg' 
WHERE book_id = 1;
```

**Free Cover Sources**:
- Open Library: `https://covers.openlibrary.org/b/isbn/{ISBN}-L.jpg`
- Google Books API
- Placeholder: `https://via.placeholder.com/300x450`

See [BOOK_COVERS_UPDATE.md](BOOK_COVERS_UPDATE.md) for complete guide.

## 🔧 Configuration

### Database Settings
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'libra_db_sys');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### Base URL
Edit `config/constants.php`:
```php
define('BASE_URL', '/library-pro');
```

## 🐛 Troubleshooting

### Common Issues

**404 Not Found**
- Check Apache is running
- Verify URL: `http://localhost/library-pro/public/home.php`
- Check folder: `C:\xampp\htdocs\library-pro\`

**Database Connection Error**
- Check MySQL is running
- Verify database exists: `libra_db_sys`
- Check credentials in `config/database.php`

**No Book Covers**
- Run: `http://localhost/library-pro/add-book-covers.php`
- Check `cover_image` field in database
- Verify image URLs are accessible

**CSS Not Loading**
- Clear browser cache (Ctrl + Shift + Delete)
- Hard refresh (Ctrl + F5)
- Check BASE_URL in `config/constants.php`

See [HOW_TO_RUN.md](HOW_TO_RUN.md) for more troubleshooting.

## 📊 Database Schema

### Main Tables
- **books** - Book catalog with cover images
- **users** - Library members and admins
- **loans** - Borrowing records
- **categories** - Book categories
- **authors** - Author information
- **book_authors** - Book-author relationships

See [DATABASE_STRUCTURE.md](DATABASE_STRUCTURE.md) for complete schema.

## 🎯 Key Features Explained

### Book Cover Display
- Shows real cover images from database
- Automatic fallback to styled placeholder
- Responsive sizing for all devices
- Error handling for broken images

### Direct Database Queries
- No API dependencies
- Real-time data access
- Fast page loads
- Simplified architecture

### Responsive Design
- Mobile-first approach
- Works on all screen sizes
- Touch-friendly navigation
- Optimized for tablets

### ROBBOEB Branding
- Consistent orange theme
- Professional logo integration
- Modern color palette
- Clean typography

## 🚀 Future Enhancements

- [ ] Book reviews and ratings
- [ ] Email notifications for due dates
- [ ] Advanced reporting
- [ ] Book reservations
- [ ] Reading history
- [ ] Barcode scanning
- [ ] Fine management
- [ ] Multi-language support

## 📝 License

This project is for educational purposes.

## 👥 Credits

**ROBBOEB Libra** - Modern Library Management System
- Version: 2.0
- Last Updated: November 16, 2025
- Features: Book covers, responsive design, real-time data

## 🆘 Support

For help and documentation:
1. Check [QUICK_START.md](QUICK_START.md) for quick setup
2. Read [HOW_TO_RUN.md](HOW_TO_RUN.md) for detailed instructions
3. Review [BEGINNER_SETUP_GUIDE.md](BEGINNER_SETUP_GUIDE.md) for complete guide
4. Check troubleshooting sections in documentation

## 🎉 Getting Started

Ready to start? Follow these steps:

1. **Read**: [QUICK_START.md](QUICK_START.md)
2. **Setup**: Follow the 3-minute guide
3. **Add Covers**: Run the cover image script
4. **Explore**: Visit the home page
5. **Login**: Try admin and user accounts
6. **Enjoy**: Your library is ready!

---

**Project Location**: `C:\xampp\htdocs\library-pro\`  
**Homepage**: `http://localhost/library-pro/public/home.php`  
**Database**: `libra_db_sys`  
**Version**: 2.0 with Book Covers

**Happy Reading! 📚**
