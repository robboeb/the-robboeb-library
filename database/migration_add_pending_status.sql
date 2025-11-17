-- Migration: Add pending status support for self-service borrowing
-- This migration adds support for users to request books themselves
-- Admin approval is required before the loan becomes active

-- Update loans table to support pending status
-- The status field should allow: 'pending', 'active', 'returned', 'rejected', 'overdue'

-- If your loans table doesn't have a status column, add it:
-- ALTER TABLE loans ADD COLUMN status VARCHAR(20) DEFAULT 'active';

-- If your loans table doesn't have a created_at column, add it:
-- ALTER TABLE loans ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Update existing loans to have 'active' status if NULL
UPDATE loans SET status = 'active' WHERE status IS NULL OR status = '';

-- Make checkout_date and due_date nullable for pending requests
-- ALTER TABLE loans MODIFY COLUMN checkout_date DATE NULL;
-- ALTER TABLE loans MODIFY COLUMN due_date DATE NULL;

-- Note: Run these ALTER TABLE commands only if the columns don't exist or need modification
-- Check your current schema first with: DESCRIBE loans;
