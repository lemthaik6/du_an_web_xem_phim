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

    echo "=== IMPORTED DATA SUMMARY ===\n\n";
    
    // Get movies with episode counts
    $movies = $connection->fetchAllAssociative(
        "SELECT m.id, m.title, COUNT(e.id) as episode_count 
         FROM movies m 
         LEFT JOIN episodes e ON m.id = e.movie_id 
         GROUP BY m.id, m.title 
         ORDER BY m.id DESC"
    );
    
    echo "Movies and Episodes:\n";
    foreach ($movies as $movie) {
        echo "- " . $movie['title'] . " (ID: {$movie['id']}, Episodes: {$movie['episode_count']})\n";
    }
    
    // Get video sources
    $sources = $connection->fetchAllAssociative("SELECT COUNT(*) as cnt FROM episode_video_sources");
    echo "\nTotal video sources: " . $sources[0]['cnt'] . "\n";
    
    // Get sample episode with video source
    echo "\nSample episode data:\n";
    $sample = $connection->fetchAssociative(
        "SELECT e.id, e.movie_id, e.title, e.episode_number, 
                COUNT(vs.id) as source_count
         FROM episodes e
         LEFT JOIN episode_video_sources vs ON e.id = vs.episode_id
         GROUP BY e.id
         LIMIT 1"
    );
    if ($sample) {
        echo "- Episode: {$sample['title']} (Ep#: {$sample['episode_number']}, Video Sources: {$sample['source_count']})\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
