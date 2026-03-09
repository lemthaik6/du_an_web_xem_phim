<?php
/**
 * Migration runner for users table
 */

define('BASE_PATH', __DIR__ . '/..');
require BASE_PATH . '/vendor/autoload.php';

use Doctrine\DBAL\DriverManager;

$dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

try {
    $connectionParams = [
        'user'      => $_ENV['DB_USERNAME'] ?? 'root',
        'password'  => $_ENV['DB_PASSWORD'] ?? '',
        'dbname'    => $_ENV['DB_NAME'] ?? 'du_an_web_xem_phim',
        'host'      => $_ENV['DB_HOST'] ?? 'localhost',
        'driver'    => $_ENV['DB_DRIVER'] ?? 'pdo_mysql',
        'port'      => $_ENV['DB_PORT'] ?? 3306,
    ];

    $connection = DriverManager::getConnection($connectionParams);
    
    echo "Connecting to database...\n";
    echo "Host: {$connectionParams['host']}\n";
    echo "Database: {$connectionParams['dbname']}\n\n";

    // Read and execute migration
    $sql = file_get_contents(__DIR__ . '/002_create_users.sql');
    
    // Split SQL statements
    $statements = array_filter(
        array_map('trim', preg_split('/;/', $sql)),
        fn($s) => !empty($s) && !str_starts_with($s, '--')
    );

    foreach ($statements as $statement) {
        echo "Executing: " . substr($statement, 0, 60) . "...\n";
        $connection->executeStatement($statement);
    }

    echo "\n✅ Migration completed successfully!\n";
    
    // List users
    echo "\n=== USERS TABLE CREATED ===\n";
    $users = $connection->fetchAllAssociative('SELECT id, username, email, display_name, role_id FROM users');
    foreach ($users as $user) {
        echo "ID: {$user['id']}, Username: {$user['username']}, Email: {$user['email']}, Role: {$user['role_id']}\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
}
?>
