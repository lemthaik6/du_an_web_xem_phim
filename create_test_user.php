<?php
define('BASE_PATH', __DIR__);
require BASE_PATH . '/vendor/autoload.php';

use Doctrine\DBAL\DriverManager;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

$dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

try {
    $conn = DriverManager::getConnection([
        'user'      => $_ENV['DB_USERNAME'] ?? 'root',
        'password'  => $_ENV['DB_PASSWORD'] ?? '',
        'dbname'    => $_ENV['DB_NAME'] ?? 'du_an_web_xem_phim',
        'host'      => $_ENV['DB_HOST'] ?? 'localhost',
        'driver'    => $_ENV['DB_DRIVER'] ?? 'pdo_mysql',
        'port'      => $_ENV['DB_PORT'] ?? 3306,
    ]);
    
    // Step 1: Create users table if not exists
    $tableExists = false;
    try {
        $result = $conn->fetchOne("SELECT 1 FROM users LIMIT 1");
        $tableExists = true;
    } catch (\Exception $e) {
        // Table doesn't exist
    }
    
    if (!$tableExists) {
        $conn->executeStatement(<<<SQL
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
SQL);
    }
    
    // Step 2: Add test user
    $hash = password_hash('123456', PASSWORD_BCRYPT);
    $testUser = $conn->fetchAssociative("SELECT id FROM users WHERE email = 'test@test.com' LIMIT 1");
    
    if (!$testUser) {
        $conn->insert('users', [
            'username'      => 'testuser123',
            'email'         => 'test@test.com',
            'password_hash' => $hash,
            'display_name'  => 'Test User',
            'role_id'       => 2,
            'status'        => 'active',
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
        
        echo json_encode([
            'ok' => true,
            'message' => 'Test user created: test@test.com / 123456'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // Update password
        $conn->update('users', 
            ['password_hash' => $hash],
            ['email' => 'test@test.com']
        );
        
        echo json_encode([
            'ok' => true,
            'message' => 'Test user already exists (password updated): test@test.com / 123456'
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (\Exception $e) {
    error_log('Create test user error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
