# 🚀 How to Run Library Management System

## Quick Start (5 Minutes)

### Prerequisites
- XAMPP installed at `C:\xampp\`
- Project located at `C:\xampp\htdocs\library-pro\`

### Steps

1. **Start XAMPP Services**
   ```
   - Open XAMPP Control Panel
   - Click "Start" for Apache
   - Click "Start" for MySQL
   - Wait for both to show green "Running" status
   ```

2. **Create Database (First Time Only)**
   ```
   - Open browser: http://localhost/phpmyadmin
   - Click "New" → Create database: libra_db_sys
   - Click "Import" tab
   - Import: C:\xampp\htdocs\library-pro\database\schema.sql
   - Import: C:\xampp\htdocs\library-pro\database\seed.sql
   ```

3. **Add Book Cover Images (Recommended)**
   ```
   - Open browser: http://localhost/library-pro/add-book-covers.php
   - Script will automatically add cover images to all books
   - This makes the website look much better!
   ```

4. **Access Application**
   ```
   - Open browser
   - Go to: http://localhost/library-pro/public/home.php
   ```

5. **Explore the System**
   ```
   Public Pages (No Login Required):
   - Home: http://localhost/library-pro/public/home.php
   - Browse Books: http://localhost/library-pro/public/browse.php
   - About: http://localhost/library-pro/public/about.php
   - Book Details: Click any book to view details
   ```

6. **Login**
   ```
   Admin:
   - Email: admin@libra.com
   - Password: password
   - Dashboard: http://localhost/library-pro/public/admin/index.php
   
   User:
   - Email: john.doe@email.com
   - Password: password
   - Dashboard: http://localhost/library-pro/public/user/index.php
   ```

## Project Information

**Location:** `C:\xampp\htdocs\library-pro\`
**Homepage:** `http://localhost/library-pro/public/home.php`
**Database:** `libra_db_sys`

## Key Features

- **Public Pages:** Browse books, view details, search and filter without login
- **User Dashboard:** View borrowed books, due dates, and browse available titles
- **Admin Dashboard:** Manage books, users, categories, authors, and loans
- **Direct Database Queries:** Fast, real-time data access
- **ROBBOEB Libra Branding:** Modern orange theme with consistent design
- **Responsive Design:** Works on desktop, tablet, and mobile devices

## Troubleshooting

### 404 Not Found
- Check Apache is running (green in XAMPP)
- Verify URL: `http://localhost/library-pro/public/`
- Check folder exists: `C:\xampp\htdocs\library-pro\`

### Database Connection Error
- Check MySQL is running (green in XAMPP)
- Verify database exists in phpMyAdmin
- Check `config/database.php` settings

### CSS Not Loading
- Clear browser cache (Ctrl + Shift + Delete)
- Hard refresh (Ctrl + F5)
- Check `config/constants.php` has: `define('BASE_URL', '/library-pro');`

### Login Not Working
- Make sure you imported `seed.sql`
- Check users exist in phpMyAdmin → libra_db_sys → users table

## Daily Workflow

```
1. Open XAMPP Control Panel
2. Start Apache + MySQL
3. Open: http://localhost/library-pro/public/
4. Work on project
5. Stop Apache + MySQL when done
```

## Important Files

- `config/constants.php` - BASE_URL configuration
- `config/database.php` - Database settings
- `database/schema.sql` - Database structure
- `database/seed.sql` - Sample data
- `logs/error.log` - PHP error logs

## Need More Help?

See `BEGINNER_SETUP_GUIDE.md` for detailed instructions.
