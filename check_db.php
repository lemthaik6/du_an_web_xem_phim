<?php
require 'vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable('.');
$dotenv->safeLoad();

use Doctrine\DBAL\DriverManager;

try {
    $conn = DriverManager::getConnection([
        'driver' => 'pdo_mysql',
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'root',
        'password' => '',
        'dbname' => 'du_an_web_xem_phim',
    ]);
    
    // Check if users table exists
    $tables = $conn->createSchemaManager()->listTableNames();
    echo "Tables: " . implode(", ", $tables) . "\n";
    
    // Try to describe users table
    if (in_array('users', $tables)) {
        $columns = $conn->createSchemaManager()->listTableColumns('users');
        echo "Users columns: " . implode(", ", array_keys($columns)) . "\n";
    } else {
        echo "Users table does NOT exist!\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
