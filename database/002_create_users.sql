-- Create users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(255) UNIQUE NOT NULL,
  `email` VARCHAR(255) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `display_name` VARCHAR(255),
  `role_id` INT DEFAULT 2,
  `status` VARCHAR(50) DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`),
  INDEX `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert a test admin user (password: 123456)
INSERT INTO `users` (`username`, `email`, `password_hash`, `display_name`, `role_id`, `status`) 
VALUES ('admin', 'admin@test.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36jbMv3a', 'Admin User', 1, 'active')
ON DUPLICATE KEY UPDATE `email`=`email`;

-- Insert a test regular user (password: 123456)
INSERT INTO `users` (`username`, `email`, `password_hash`, `display_name`, `role_id`, `status`) 
VALUES ('testuser', 'user@test.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36jbMv3a', 'Test User', 2, 'active')
ON DUPLICATE KEY UPDATE `email`=`email`;
