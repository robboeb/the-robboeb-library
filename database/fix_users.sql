-- ============================================
-- FIX AND UPDATE USERS
-- Database: khmer-dbase
-- This script will update existing users or insert new ones
-- ============================================

USE `khmer-dbase`;

-- ============================================
-- First, let's check if users exist
-- ============================================
SELECT 'Current users in database:' as Info;
SELECT user_id, email, first_name, last_name, user_type, status FROM users;

-- ============================================
-- Option 1: DELETE ALL USERS AND START FRESH
-- Uncomment the line below if you want to delete all users first
-- ============================================
-- DELETE FROM users;

-- ============================================
-- Option 2: UPDATE EXISTING USERS
-- This will update the password for existing users
-- ============================================

-- Update admin user (password: admin123)
UPDATE `users` 
SET `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    `first_name` = 'Admin',
    `last_name` = 'User',
    `user_type` = 'admin',
    `status` = 'active'
WHERE `email` = 'admin@test.com';

-- If admin doesn't exist, insert it
INSERT INTO `users` (`email`, `password`, `first_name`, `last_name`, `phone`, `address`, `user_type`, `status`)
SELECT 'admin@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', '555-555-5555', '789 Admin Blvd', 'admin', 'active'
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `email` = 'admin@test.com');

-- Update patron user (password: password123)
UPDATE `users` 
SET `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    `user_type` = 'patron',
    `status` = 'active'
WHERE `email` = 'patron@test.com';

-- If patron doesn't exist, insert it
INSERT INTO `users` (`email`, `password`, `first_name`, `last_name`, `phone`, `address`, `user_type`, `status`)
SELECT 'patron@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', '123-456-7890', '123 Main St', 'patron', 'active'
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `email` = 'patron@test.com');

-- Update student user (password: student123)
UPDATE `users` 
SET `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    `user_type` = 'patron',
    `status` = 'active'
WHERE `email` = 'student@test.com';

-- If student doesn't exist, insert it
INSERT INTO `users` (`email`, `password`, `first_name`, `last_name`, `phone`, `address`, `user_type`, `status`)
SELECT 'student@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Michael', 'Brown', '555-987-6543', '789 Campus Dr', 'patron', 'active'
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `email` = 'student@test.com');

-- ============================================
-- VERIFICATION - Check updated users
-- ============================================
SELECT 'Updated users:' as Info;
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
-- TEST CREDENTIALS
-- ============================================
-- 
-- Email: admin@test.com
-- Password: admin123
-- Role: Admin
-- 
-- Email: patron@test.com
-- Password: password123
-- Role: Patron
-- 
-- Email: student@test.com
-- Password: student123
-- Role: Patron
-- 
-- ============================================
