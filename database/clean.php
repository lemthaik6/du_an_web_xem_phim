<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\DBAL\DriverManager;

try {
    $connectionParams = [
        'user'      => $_ENV['DB_USERNAME'] ?? $_SERVER['DB_USERNAME'] ?? 'root',
        'password'  => $_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD'] ?? '',
        'dbname'    => $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? 'du_an_web_xem_phim',
        'host'      => $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? 'localhost',
        'driver'    => $_ENV['DB_DRIVER'] ?? $_SERVER['DB_DRIVER'] ?? 'pdo_mysql',
        'port'      => $_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? 3306,
    ];

    $connection = DriverManager::getConnection($connectionParams);

    echo "Step 1: Deleting episodes...\n";
    $deleted = $connection->executeStatement("DELETE FROM episodes");
    echo "Deleted $deleted episodes\n";
    
    echo "Step 2: Deleting movies...\n";
    $deleted = $connection->executeStatement("DELETE FROM movies");
    echo "Deleted $deleted movies\n";
    
    // Verify
    $movieCount = $connection->fetchOne("SELECT COUNT(*) as cnt FROM movies");
    $episodeCount = $connection->fetchOne("SELECT COUNT(*) as cnt FROM episodes");
    echo "\nAfter clean:\n";
    echo "Movies: $movieCount\n";
    echo "Episodes: $episodeCount\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

