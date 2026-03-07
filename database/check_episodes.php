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

    // Check episodes count
    $result = $connection->fetchOne("SELECT COUNT(*) as cnt FROM episodes");
    echo "Total episodes in database: " . $result . "\n";

    // Show episodes for first movie
    $episodes = $connection->fetchAllAssociative(
        "SELECT e.*, m.title as movie_title FROM episodes e 
         LEFT JOIN movies m ON e.movie_id = m.id 
         ORDER BY e.id DESC LIMIT 10"
    );
    
    echo "\n=== Last 10 Episodes ===\n";
    if (count($episodes) === 0) {
        echo "No episodes found!\n";
    } else {
        foreach ($episodes as $ep) {
            echo "ID: {$ep['id']}, Movie ID: {$ep['movie_id']}, Movie: {$ep['movie_title']}, Title: {$ep['title']}\n";
        }
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

