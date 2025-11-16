# Database Structure - ROBBOEB Libra

## Correct Column Names

### Users Table
- `user_id` (PK)
- `email`
- `password_hash`
- `first_name`
- `last_name`
- `phone`
- `address`
- `user_type` (admin, patron)
- `status` (active, inactive) ✓
- `created_at`
- `updated_at`

### Books Table
- `book_id` (PK)
- `isbn`
- `title`
- `category_id` (FK)
- `publication_year`
- `description`
- `cover_image`
- `total_quantity` → Use as `total_copies`
- `available_quantity` → Use as `available_copies`
- `created_at`
- `updated_at`
- **Note:** No `status` column - calculate from `available_quantity`

### Categories Table
- `category_id` (PK)
- `name` → Use as `category_name`
- `description`
- `created_at`

### Authors Table
- `author_id` (PK)
- `first_name`
- `last_name`
- `biography`
- `created_at`

### Loans Table
- `loan_id` (PK)
- `book_id` (FK)
- `user_id` (FK)
- `checkout_date` → Use as `loan_date`
- `due_date`
- `return_date`
- `status` (active, returned, overdue) ✓
- `fine_amount`
- `created_at`
- `updated_at`

### Book_Authors Table (Junction)
- `book_id` (FK)
- `author_id` (FK)

## Status Mapping

### Book Status (Calculated)
```sql
CASE 
    WHEN available_quantity > 0 THEN 'available'
    ELSE 'borrowed'
END as status
```

### Loan Status (Calculated)
```sql
CASE 
    WHEN return_date IS NOT NULL THEN 'returned'
    WHEN due_date < CURDATE() THEN 'overdue'
    ELSE 'active'
END as status
```

## All Pages Now Use Live Database Data

✅ **Frontend (Public)**
- home.php - Live books and stats
- browse.php - Live filtering and pagination
- All data from SQL queries

✅ **Backend (Admin)**
- Dashboard - Live statistics
- Books - Live book list
- Users - Live user list
- Loans - Live loan list
- Categories - Live categories
- Authors - Live authors
- Reports - Live analytics

## No API Calls
All pages fetch data directly from MySQL using PHP queries.
