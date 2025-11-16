# 🚀 Complete Beginner's Guide to Running Library Management System

This guide will walk you through everything you need to run this PHP project from scratch.

---

## 📋 What You Need

Before starting, you need to install:
1. **XAMPP** - A free software that includes Apache (web server), MySQL (database), and PHP
2. **A web browser** - Chrome, Firefox, or Edge

---

## 🔧 Step 1: Install XAMPP

### Download XAMPP:
1. Go to: https://www.apachefriends.org/
2. Click "Download" for Windows
3. Download the latest version (PHP 8.x recommended)

### Install XAMPP:
1. Run the downloaded installer
2. Click "Next" through the installation
3. Choose installation folder (default is `C:\xampp`)
4. Complete the installation
5. Launch XAMPP Control Panel

---

## 📁 Step 2: Place Your Project Files

### Your Project Location:
Your project is already located at:
```
C:\xampp\htdocs\library-pro\
```

**If you need to move it:**
1. Open File Explorer
2. Navigate to `C:\xampp\htdocs\`
3. Make sure your project folder is named `library-pro`

---

## 🚀 Step 3: Start XAMPP Services

### Start Apache and MySQL:
1. Open **XAMPP Control Panel**
2. Click **"Start"** button next to **Apache**
   - Wait until it shows green background with "Running"
3. Click **"Start"** button next to **MySQL**
   - Wait until it shows green background with "Running"

**Troubleshooting:**
- If Apache won't start (Port 80 busy):
  - Click "Config" → "Apache (httpd.conf)"
  - Find `Listen 80` and change to `Listen 8080`
  - Find `ServerName localhost:80` and change to `ServerName localhost:8080`
  - Save and restart Apache
  - Access project at: `http://localhost:8080/library-pro/public/`

- If MySQL won't start (Port 3306 busy):
  - Close Skype or other programs using port 3306
  - Or change MySQL port in XAMPP config

---

## 🗄️ Step 4: Create the Database

### Option A: Using phpMyAdmin (Recommended for Beginners)

1. **Open phpMyAdmin:**
   - Open your browser
   - Go to: `http://localhost/phpmyadmin`

2. **Create Database:**
   - Click "New" in the left sidebar
   - Database name: `libra_db_sys`
   - Collation: `utf8mb4_general_ci`
   - Click "Create"

3. **Import Database Structure:**
   - Click on `libra_db_sys` database (left sidebar)
   - Click "Import" tab at the top
   - Click "Choose File"
   - Navigate to: `C:\xampp\htdocs\library-pro\database\schema.sql`
   - Click "Go" at the bottom
   - Wait for success message

4. **Import Sample Data:**
   - Still in "Import" tab
   - Click "Choose File"
   - Navigate to: `C:\xampp\htdocs\library-pro\database\seed.sql`
   - Click "Go"
   - Wait for success message

### Option B: Using Command Line

1. Open Command Prompt (cmd)
2. Navigate to XAMPP MySQL bin folder:
   ```
   cd C:\xampp\mysql\bin
   ```
3. Login to MySQL:
   ```
   mysql -u root -p
   ```
   (Press Enter when asked for password - default is empty)
4. Create database:
   ```
   CREATE DATABASE libra_db_sys;
   USE libra_db_sys;
   ```
5. Import schema:
   ```
   source C:/xampp/htdocs/library-pro/database/schema.sql
   ```
6. Import seed data:
   ```
   source C:/xampp/htdocs/library-pro/database/seed.sql
   ```
7. Exit:
   ```
   exit
   ```

---

## 🌐 Step 5: Access Your Project

### Open in Browser:
1. Open your web browser
2. Go to: `http://localhost/library-pro/public/`
3. You should see the login page

### Login Credentials:

**Admin Account:**
- Email: `admin@libra.com`
- Password: `password`

**Regular User Accounts:**
- Email: `john.doe@email.com`
- Password: `password`

OR

- Email: `jane.smith@email.com`
- Password: `password`

---

## ✅ Step 6: Verify Everything Works

### Test the Application:
1. **Login as Admin:**
   - Use admin credentials
   - You should see the admin dashboard
   - Check statistics are showing

2. **Browse Books:**
   - Click "Books" in sidebar
   - You should see sample books

3. **Test Search:**
   - Use the search bar
   - Search for a book title

4. **Logout and Login as User:**
   - Logout from admin
   - Login with user credentials
   - You should see user interface

---

## 🔍 Common Issues and Solutions

### Issue 1: "Page Not Found" (404 Error)
**Solution:**
- Make sure Apache is running (green in XAMPP)
- Check URL is correct: `http://localhost/library-pro/public/`
- Verify project folder is at `C:\xampp\htdocs\library-pro\`
- Check `config/constants.php` has: `define('BASE_URL', '/library-pro');`

### Issue 2: "Database Connection Failed"
**Solution:**
- Make sure MySQL is running (green in XAMPP)
- Verify database `libra_db_sys` exists in phpMyAdmin
- Check `config/database.php` has correct settings:
  ```php
  DB_HOST: localhost
  DB_NAME: libra_db_sys
  DB_USER: root
  DB_PASS: (empty)
  ```

### Issue 3: Blank White Page
**Solution:**
- Check PHP errors in: `C:\xampp\htdocs\library-pro\logs\error.log`
- Or enable error display:
  - Open `config/database.php`
  - Change `ENVIRONMENT` to `'development'`

### Issue 4: CSS/Styles Not Loading
**Solution:**
- Clear browser cache (Ctrl + Shift + Delete)
- Hard refresh page (Ctrl + F5)
- Check if files exist in `public/assets/css/`
- Verify `config/constants.php` has correct BASE_URL: `/library-pro`

### Issue 5: Login Not Working
**Solution:**
- Make sure you imported `seed.sql` (contains user accounts)
- Check database has data:
  - Go to phpMyAdmin
  - Click `libra_db_sys` → `users` table
  - Should see 3 users

---

## 📱 Project Structure Overview

```
library-pro/
├── config/              # Configuration files
│   ├── database.php     # Database settings
│   └── constants.php    # App constants (BASE_URL here)
├── database/            # Database files
│   ├── schema.sql       # Database structure
│   └── seed.sql         # Sample data
├── public/              # Web accessible files
│   ├── assets/          # CSS, JS, images
│   │   ├── css/         # Stylesheets
│   │   └── js/          # JavaScript files
│   ├── admin/           # Admin pages
│   ├── user/            # User pages
│   ├── index.php        # Entry point
│   └── login.php        # Login page
├── src/                 # PHP source code
│   ├── controllers/     # Business logic
│   ├── models/          # Database models
│   └── services/        # Helper services
└── logs/                # Error logs
```

---

## 🎯 What to Do Next

1. **Explore Admin Panel:**
   - Add new books
   - Manage users
   - Process loans
   - View reports

2. **Test User Features:**
   - Browse books
   - Search functionality
   - View book details

3. **Customize:**
   - Change colors in `public/assets/css/variables.css`
   - Add your own books
   - Modify settings

---

## 📞 Need Help?

### Check Logs:
- PHP Errors: `C:\xampp\htdocs\library-pro\logs\error.log`
- Apache Errors: `C:\xampp\apache\logs\error.log`
- MySQL Errors: `C:\xampp\mysql\data\mysql_error.log`

### Useful XAMPP Locations:
- XAMPP Control Panel: `C:\xampp\xampp-control.exe`
- Apache Config: `C:\xampp\apache\conf\httpd.conf`
- PHP Config: `C:\xampp\php\php.ini`
- MySQL Data: `C:\xampp\mysql\data\`

### Test PHP Installation:
1. Create file: `C:\xampp\htdocs\test.php`
2. Add content:
   ```php
   <?php
   phpinfo();
   ?>
   ```
3. Open: `http://localhost/test.php`
4. Should see PHP information page

---

## 🎉 Success!

If you can login and see the dashboard, congratulations! Your PHP project is running successfully.

**Quick Access URLs:**
- Application: `http://localhost/library-pro/public/`
- phpMyAdmin: `http://localhost/phpmyadmin`
- XAMPP Dashboard: `http://localhost/dashboard`

---

## 💡 Tips for Beginners

1. **Always start XAMPP services** before accessing the project
2. **Don't close XAMPP Control Panel** while using the application
3. **Backup your database** regularly from phpMyAdmin (Export tab)
4. **Check error logs** if something doesn't work
5. **Use Chrome DevTools** (F12) to debug frontend issues

---

## 🔄 Daily Workflow

1. Open XAMPP Control Panel
2. Start Apache and MySQL
3. Open browser → `http://localhost/library-pro/public/`
4. Work on your project
5. When done, stop Apache and MySQL in XAMPP
6. Close XAMPP Control Panel

---

## ⚙️ Important Configuration

Your project is configured with:
- **Project Folder:** `C:\xampp\htdocs\library-pro\`
- **Access URL:** `http://localhost/library-pro/public/`
- **Database Name:** `libra_db_sys`
- **BASE_URL Setting:** `/library-pro` (in `config/constants.php`)

**If you rename the folder, you MUST update:**
1. `config/constants.php` → Change `BASE_URL` to match folder name
2. Access URL in browser

---

**Happy Coding! 🚀**
