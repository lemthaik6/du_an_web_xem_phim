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

    // Check movies count
    $movies = $connection->fetchAllAssociative("SELECT COUNT(*) as cnt FROM movies");
    echo "Movies: " . print_r($movies, true) . "\n";

    $episodes = $connection->fetchAllAssociative("SELECT COUNT(*) as cnt FROM episodes");
    echo "Episodes: " . print_r($episodes, true) . "\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}


