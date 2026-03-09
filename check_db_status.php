<?php
define('BASE_PATH', __DIR__);
require BASE_PATH . '/vendor/autoload.php';

use Doctrine\DBAL\DriverManager;

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
    
    echo '<p class="ok">✅ Database connected</p>';
    
    // Check users table
    $tables = $conn->fetchAllAssociative("SHOW TABLES");
    $tableList = array_map(fn($t) => array_values($t)[0], $tables);
    
    if (!in_array('users', $tableList)) {
        echo '<p class="error">❌ Users table NOT found. Available tables: ' . implode(', ', $tableList) . '</p>';
        exit;
    }
    
    echo '<p class="ok">✅ Users table exists</p>';
    
    // Count users
    $count = $conn->fetchOne("SELECT COUNT(*) as c FROM users");
    echo '<p>Total users: <strong>' . $count . '</strong></p>';
    
    // Check test user
    $testUser = $conn->fetchAssociative("SELECT id, username, email FROM users WHERE email = 'test@test.com' LIMIT 1");
    if ($testUser) {
        echo '<p class="ok">✅ Test user exists: test@test.com</p>';
    } else {
        echo '<p class="error">❌ Test user NOT found. Need to create it.</p>';
    }
    
} catch (\Exception $e) {
    echo '<p class="error">❌ Database Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>
