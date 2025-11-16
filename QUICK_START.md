# 🚀 ROBBOEB Libra - Quick Start Guide

## Get Your Library Running in 3 Minutes!

### Step 1: Start XAMPP (30 seconds)
1. Open **XAMPP Control Panel**
2. Click **Start** for **Apache**
3. Click **Start** for **MySQL**
4. Wait for green "Running" status

### Step 2: Setup Database (1 minute)
1. Open browser: `http://localhost/phpmyadmin`
2. Click **"New"** → Create database: `libra_db_sys`
3. Click **"Import"** tab
4. Import: `C:\xampp\htdocs\library-pro\database\schema.sql`
5. Import: `C:\xampp\htdocs\library-pro\database\seed.sql`

### Step 3: Add Book Covers (30 seconds)
1. Open: `http://localhost/library-pro/add-book-covers.php`
2. Wait for script to complete
3. See beautiful book covers added automatically!

### Step 4: Enjoy! (30 seconds)
1. Visit: `http://localhost/library-pro/public/home.php`
2. Browse books with cover images
3. Login to test features

---

## 🎯 Quick Links

### Public Pages (No Login)
- **Home**: http://localhost/library-pro/public/home.php
- **Browse Books**: http://localhost/library-pro/public/browse.php
- **About**: http://localhost/library-pro/public/about.php

### Login Credentials

**Admin Account:**
- Email: `admin@libra.com`
- Password: `password`
- Dashboard: http://localhost/library-pro/public/admin/index.php

**User Account:**
- Email: `john.doe@email.com`
- Password: `password`
- Dashboard: http://localhost/library-pro/public/user/index.php

---

## 🎨 What You'll See

### ✨ Features
- 📚 **Book Covers** - Real cover images for all books
- 🔍 **Search & Filter** - Find books by title, author, category
- 📱 **Responsive Design** - Works on desktop, tablet, mobile
- 🎨 **Modern UI** - ROBBOEB Libra orange branding
- ⚡ **Fast** - Direct database queries, no API delays
- 🔐 **Secure** - User authentication and role management

### 📖 Book Display
- Home page: Featured books with covers
- Browse page: Full catalog with filters
- Book details: Large cover image with full info
- User dashboard: Your borrowed books

---

## 🛠️ Troubleshooting

### Apache Won't Start
- **Problem**: Port 80 already in use
- **Solution**: Stop Skype or other apps using port 80
- **Or**: Change Apache port in XAMPP config

### MySQL Won't Start
- **Problem**: Port 3306 already in use
- **Solution**: Stop other MySQL services
- **Check**: Windows Services → Stop MySQL

### 404 Not Found
- **Problem**: Wrong URL
- **Solution**: Use `http://localhost/library-pro/public/home.php`
- **Check**: Folder is at `C:\xampp\htdocs\library-pro\`

### No Book Covers
- **Problem**: Covers not added yet
- **Solution**: Run `http://localhost/library-pro/add-book-covers.php`
- **Result**: All books get cover images

### Database Connection Error
- **Problem**: MySQL not running or wrong credentials
- **Solution**: 
  1. Check MySQL is running (green in XAMPP)
  2. Verify database exists: `libra_db_sys`
  3. Check `config/database.php` settings

---

## 📚 Documentation

- **HOW_TO_RUN.md** - Detailed setup instructions
- **BOOK_COVERS_UPDATE.md** - Book cover implementation guide
- **DATABASE_STRUCTURE.md** - Database schema reference
- **BEGINNER_SETUP_GUIDE.md** - Complete beginner guide
- **UPDATE_SUMMARY.md** - Latest features and updates

---

## 🎓 Learning Path

### For Beginners
1. Start with **BEGINNER_SETUP_GUIDE.md**
2. Follow **HOW_TO_RUN.md** step by step
3. Explore the public pages first
4. Try logging in as user
5. Then explore admin features

### For Developers
1. Check **DATABASE_STRUCTURE.md** for schema
2. Review **UPDATE_SUMMARY.md** for architecture
3. Explore `src/helpers/DatabaseHelper.php` for queries
4. Check `public/assets/css/` for styling
5. Modify and extend as needed

---

## 🎉 You're Ready!

Your library management system is now running with:
- ✅ Beautiful book covers
- ✅ Modern responsive design
- ✅ Full search and filtering
- ✅ User and admin dashboards
- ✅ Real-time database queries
- ✅ ROBBOEB Libra branding

**Enjoy your library! 📚**

---

## 💡 Tips

1. **Daily Use**: Just start Apache + MySQL in XAMPP
2. **Add Books**: Use admin dashboard → Books → Add New
3. **Add Covers**: Include cover URL when adding books
4. **Backup**: Export database regularly from phpMyAdmin
5. **Updates**: Check UPDATE_SUMMARY.md for new features

---

**Need Help?** Check the documentation files or review the troubleshooting section above.

**Project Location**: `C:\xampp\htdocs\library-pro\`
**Database**: `libra_db_sys`
**Version**: 2.0 with Book Covers
