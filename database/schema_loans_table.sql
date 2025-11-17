-- Loans table schema for self-service borrowing system
-- This ensures the loans table has all necessary fields

-- Check if loans table exists and has the correct structure
-- Run this to verify your current structure: DESCRIBE loans;

-- Expected loans table structure:
CREATE TABLE IF NOT EXISTS `loans` (
  `loan_id` int(11) NOT NULL AUTO_INCREMENT,
  `book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `checkout_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `fine_amount` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`loan_id`),
  KEY `book_id` (`book_id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE,
  CONSTRAINT `loans_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- If your table already exists but is missing columns, use these ALTER statements:

-- Add status column if it doesn't exist
-- ALTER TABLE loans ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'pending';

-- Add created_at column if it doesn't exist
-- ALTER TABLE loans ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Add updated_at column if it doesn't exist
-- ALTER TABLE loans ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Make checkout_date and due_date nullable for pending requests
-- ALTER TABLE loans MODIFY COLUMN checkout_date DATE NULL;
-- ALTER TABLE loans MODIFY COLUMN due_date DATE NULL;

-- Add indexes for better performance
-- CREATE INDEX IF NOT EXISTS idx_loans_status ON loans(status);
-- CREATE INDEX IF NOT EXISTS idx_loans_user_id ON loans(user_id);
-- CREATE INDEX IF NOT EXISTS idx_loans_book_id ON loans(book_id);

-- Update existing loans to have proper status
UPDATE loans SET status = 'active' WHERE status IS NULL AND return_date IS NULL;
UPDATE loans SET status = 'returned' WHERE return_date IS NOT NULL AND status IS NULL;
