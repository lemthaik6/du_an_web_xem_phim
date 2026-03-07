<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Movie;
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
    
    // First check video_servers
    $servers = $connection->fetchAllAssociative("SELECT * FROM video_servers");
    echo "Current video servers: " . count($servers) . "\n";
    foreach ($servers as $s) {
        echo "- ID: {$s['id']}, Name: {$s['name']}\n";
    }
    
    // Test insert into video_servers manually
    echo "\nTesting manual video server insert...\n";
    $sql = "INSERT INTO video_servers (name, url, is_active, created_at, updated_at) VALUES ('Test', '', 1, NOW(), NOW())";
    $connection->executeStatement($sql);
    $newId = $connection->lastInsertId();
    echo "New server ID: $newId\n";
    
    // Now check if we can insert episode
    echo "\nTesting episode insert...\n";
    $movieId = 16; // Use existing movie
    $sql = "INSERT INTO episodes (movie_id, episode_number, title, duration_seconds, created_at, updated_at) VALUES (?, ?, ?, 0, NOW(), NOW())";
    $connection->executeStatement($sql, [$movieId, 99, 'Test Episode']);
    $episodeId = $connection->lastInsertId();
    echo "New episode ID: $episodeId\n";
    
    // Test insert into episode_video_sources
    if ($episodeId && $newId) {
        echo "\nTesting video source insert...\n";
        $sql = "INSERT INTO episode_video_sources (episode_id, video_server_id, quality, video_url, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())";
        $connection->executeStatement($sql, [$episodeId, $newId, '720p', 'https://example.com/test.mp4']);
        echo "Video source inserted successfully\n";
        
        // Verify
        $count = $connection->fetchOne("SELECT COUNT(*) FROM episode_video_sources WHERE episode_id = ?", [$episodeId]);
        echo "Video sources for episode $episodeId: $count\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
