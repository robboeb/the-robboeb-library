-- QUICK SETUP FOR SELF-SERVICE BORROWING SYSTEM
-- Run these commands in your MySQL database

-- ============================================
-- STEP 1: Update Loans Table Structure
-- ============================================

-- Add status column if it doesn't exist
ALTER TABLE loans ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'pending';

-- Add created_at column if it doesn't exist
ALTER TABLE loans ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Add updated_at column if it doesn't exist
ALTER TABLE loans ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Make checkout_date nullable (for pending requests)
ALTER TABLE loans MODIFY COLUMN checkout_date DATE NULL;

-- Make due_date nullable (for pending requests)
ALTER TABLE loans MODIFY COLUMN due_date DATE NULL;

-- ============================================
-- STEP 2: Update Existing Data
-- ============================================

-- Set status for existing active loans
UPDATE loans 
SET status = 'active' 
WHERE status IS NULL 
  AND return_date IS NULL 
  AND checkout_date IS NOT NULL;

-- Set status for existing returned loans
UPDATE loans 
SET status = 'returned' 
WHERE status IS NULL 
  AND return_date IS NOT NULL;

-- ============================================
-- STEP 3: Add Indexes for Performance
-- ============================================

-- Add index on status for faster queries
CREATE INDEX IF NOT EXISTS idx_loans_status ON loans(status);

-- Add index on user_id for faster user queries
CREATE INDEX IF NOT EXISTS idx_loans_user_id ON loans(user_id);

-- Add index on book_id for faster book queries
CREATE INDEX IF NOT EXISTS idx_loans_book_id ON loans(book_id);

-- ============================================
-- STEP 4: Verify Setup
-- ============================================

-- Check the table structure
DESCRIBE loans;

-- Check existing loans
SELECT loan_id, book_id, user_id, status, checkout_date, due_date, created_at 
FROM loans 
LIMIT 5;

-- Count loans by status
SELECT status, COUNT(*) as count 
FROM loans 
GROUP BY status;

-- ============================================
-- DONE! Your database is ready for self-service borrowing
-- ============================================

-- Next steps:
-- 1. Test as a patron user: Browse books and request to borrow
-- 2. Test as an admin: Approve or reject pending requests
-- 3. Check the USER_BORROWING_FLOW.md file for complete testing guide
