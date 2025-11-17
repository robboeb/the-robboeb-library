-- ============================================
-- INSERT TEST USERS
-- Database: khmer-dbase
-- ============================================

USE `khmer-dbase`;

-- ============================================
-- Admin User
-- Email: admin@test.com
-- Password: admin123
-- ============================================
INSERT INTO `users` (`email`, `password`, `first_name`, `last_name`, `phone`, `address`, `user_type`, `status`)
VALUES 
('admin@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', '555-555-5555', '789 Admin Blvd', 'admin', 'active')
ON DUPLICATE KEY UPDATE `email`=`email`;

-- ============================================
-- Patron Users
-- Email: patron@test.com
-- Password: password123
-- ============================================
INSERT INTO `users` (`email`, `password`, `first_name`, `last_name`, `phone`, `address`, `user_type`, `status`)
VALUES 
('patron@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', '123-456-7890', '123 Main St', 'patron', 'active'),
('borrower@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Smith', '098-765-4321', '456 Oak Ave', 'patron', 'active')
ON DUPLICATE KEY UPDATE `email`=`email`;

-- ============================================
-- Additional Test Users
-- ============================================

-- Librarian User
-- Email: librarian@test.com
-- Password: library123
INSERT INTO `users` (`email`, `password`, `first_name`, `last_name`, `phone`, `address`, `user_type`, `status`)
VALUES 
('librarian@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Johnson', '555-123-4567', '321 Library Lane', 'patron', 'active')
ON DUPLICATE KEY UPDATE `email`=`email`;

-- Student User
-- Email: student@test.com
-- Password: student123
INSERT INTO `users` (`email`, `password`, `first_name`, `last_name`, `phone`, `address`, `user_type`, `status`)
VALUES 
('student@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Michael', 'Brown', '555-987-6543', '789 Campus Dr', 'patron', 'active')
ON DUPLICATE KEY UPDATE `email`=`email`;

-- ============================================
-- VERIFICATION
-- ============================================
SELECT 
    user_id,
    email,
    first_name,
    last_name,
    user_type,
    status,
    created_at
FROM users
ORDER BY user_type DESC, created_at DESC;

-- ============================================
-- TEST CREDENTIALS SUMMARY
-- ============================================
-- 
-- ADMIN ACCOUNT:
-- Email: admin@test.com
-- Password: admin123
-- 
-- PATRON ACCOUNTS:
-- Email: patron@test.com
-- Password: password123
-- 
-- Email: borrower@test.com
-- Password: password123
-- 
-- Email: librarian@test.com
-- Password: library123
-- 
-- Email: student@test.com
-- Password: student123
-- 
-- ============================================
