<?php
/**
 * Check database schema using direct SQL
 */

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$connectionParams = [
    'user'      => $_ENV['DB_USERNAME'] ?? 'root',
    'password'  => $_ENV['DB_PASSWORD'] ?? '',
    'dbname'    => $_ENV['DB_NAME'] ?? 'du_an_web_xem_phim',
    'host'      => $_ENV['DB_HOST'] ?? 'localhost',
    'driver'    => $_ENV['DB_DRIVER'] ?? 'pdo_mysql',
    'port'      => $_ENV['DB_PORT'] ?? 3306,
];

try {
    $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
    
    // Get table structure
    $tables = ['users', 'movies', 'categories', 'episodes'];
    
    foreach ($tables as $table) {
        echo "\n📋 Table: " . strtoupper($table) . "\n";
        echo "=".str_repeat("=", 70)."\n";
        
        $result = $conn->query("DESCRIBE " . $table)->fetchAllAssociative();
        
        foreach ($result as $row) {
            printf("  %-20s %-25s %s\n",
                $row['Field'],
                $row['Type'],
                ($row['Null'] === 'NO' ? 'NOT NULL' : '')
            );
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
