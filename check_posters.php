<?php
require_once __DIR__ . '/vendor/autoload.php';

use Doctrine\DBAL\DriverManager;

try {
    $connectionParams = [
        'user'      => 'root',
        'password'  => '',
        'dbname'    => 'du_an_web_xem_phim',
        'host'      => 'localhost',
        'driver'    => 'pdo_mysql',
        'port'      => 3306,
    ];

    $connection = DriverManager::getConnection($connectionParams);
    
    $movies = $connection->fetchAllAssociative("SELECT id, title, poster_url FROM movies LIMIT 5");
    
    echo "=== MOVIE POSTERS IN DATABASE ===\n\n";
    foreach ($movies as $m) {
        echo "ID: " . $m['id'] . "\n";
        echo "Title: " . $m['title'] . "\n";
        echo "Poster URL: " . ($m['poster_url'] ?? 'EMPTY') . "\n\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
