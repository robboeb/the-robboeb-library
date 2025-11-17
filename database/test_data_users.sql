-- Test users for borrowing system
-- Use these to test the self-service borrowing flow

-- Create a test patron user (regular borrower)
-- Password: password123
INSERT INTO users (email, password, first_name, last_name, phone, address, user_type, status, created_at)
VALUES 
('patron@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', '123-456-7890', '123 Main St', 'patron', 'active', NOW()),
('borrower@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Smith', '098-765-4321', '456 Oak Ave', 'patron', 'active', NOW())
ON DUPLICATE KEY UPDATE email=email;

-- Create a test admin user
-- Password: admin123
INSERT INTO users (email, password, first_name, last_name, phone, address, user_type, status, created_at)
VALUES 
('admin@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', '555-555-5555', '789 Admin Blvd', 'admin', 'active', NOW())
ON DUPLICATE KEY UPDATE email=email;

-- Note: All test passwords are hashed using bcrypt
-- Plain text passwords:
-- patron@test.com: password123
-- borrower@test.com: password123
-- admin@test.com: admin123

-- To create your own password hash in PHP:
-- echo password_hash('your_password', PASSWORD_DEFAULT);
