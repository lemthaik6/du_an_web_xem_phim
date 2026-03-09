<?php
// Simple test to diagnose login/signup issues

define('BASE_PATH', __DIR__);
require BASE_PATH . '/vendor/autoload.php';
require BASE_PATH . '/helpers.php';

use Doctrine\DBAL\DriverManager;

echo "=== AUTHENTICATION SYSTEM DIAGNOSTIC ===\n\n";

// 1. Check database connection
echo "1️⃣ Checking database connection...\n";
try {
    $dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->safeLoad();
    
    $connectionParams = [
        'user'      => $_ENV['DB_USERNAME'] ?? 'root',
        'password'  => $_ENV['DB_PASSWORD'] ?? '',
        'dbname'    => $_ENV['DB_NAME'] ?? 'du_an_web_xem_phim',
        'host'      => $_ENV['DB_HOST'] ?? 'localhost',
        'driver'    => $_ENV['DB_DRIVER'] ?? 'pdo_mysql',
        'port'      => $_ENV['DB_PORT'] ?? 3306,
    ];

    $connection = DriverManager::getConnection($connectionParams);
    echo "✅ Database connected successfully\n";
    
    // 2. Check users table
    echo "\n2️⃣ Checking users table...\n";
    $tables = $connection->fetchAllAssociative("SHOW TABLES");
    $tableList = array_filter(
        array_map(fn($t) => array_values($t)[0], $tables),
        fn($t) => $t !== 'TABLES_IN_' . ($_ENV['DB_NAME'] ?? 'du_an_web_xem_phim')
    );
    
    echo "Tables found: " . implode(", ", $tableList) . "\n";
    
    if (!in_array('users', $tableList)) {
        echo "❌ PROBLEM: 'users' table does NOT exist!\n";
        echo "   Action: Create users table using the migration script\n\n";
        
        // Show the SQL needed
        echo "SQL to create users table:\n";
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(255) UNIQUE NOT NULL,
  `email` VARCHAR(255) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `display_name` VARCHAR(255),
  `role_id` INT DEFAULT 2,
  `status` VARCHAR(50) DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        echo $sql . "\n\n";
    } else {
        echo "✅ Users table exists\n";
        
        // Check columns
        $columns = $connection->fetchAllAssociative("DESCRIBE users");
        echo "   Columns: ";
        echo implode(", ", array_map(fn($c) => $c['Field'], $columns)) . "\n";
        
        // Check user count
        $count = $connection->fetchOne("SELECT COUNT(*) as c FROM users");
        echo "   Records: " . $count . " users\n";
    }
    
    // 3. Check auth form
    echo "\n3️⃣ Checking authentication form...\n";
    if (file_exists(BASE_PATH . '/views/auth/login.blade.php')) {
        echo "✅ Login view exists\n";
    } else {
        echo "❌ Login view missing\n";
    }
    
    // 4. Check for register view
    if (file_exists(BASE_PATH . '/views/auth/register.blade.php')) {
        echo "✅ Register view exists\n";
    } else {
        echo "⚠️  Register view NOT found (might be shown via modal)\n";
    }
    
    // 5. Test password hashing
    echo "\n4️⃣ Testing password hashing...\n";
    $testPass = '123456';
    $hash = password_hash($testPass, PASSWORD_BCRYPT);
    $verify = password_verify($testPass, $hash);
    echo "✅ Password hashing works: " . ($verify ? "PASS" : "FAIL") . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== END DIAGNOSTIC ===\n";
?>
