-- Simple fix: Update existing admin user password
-- Copy and paste this into phpMyAdmin SQL tab

USE `khmer-dbase`;

-- Update admin password to: admin123
UPDATE `users` 
SET `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE `email` = 'admin@test.com';

-- Verify the update
SELECT user_id, email, first_name, last_name, user_type, status 
FROM users 
WHERE email = 'admin@test.com';
