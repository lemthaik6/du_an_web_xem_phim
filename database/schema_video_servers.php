<?php
require_once __DIR__ . '/../vendor/autoload.php';

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

    // Get video_servers structure
    $columns = $connection->fetchAllAssociative("DESCRIBE video_servers");
    echo "=== VIDEO_SERVERS TABLE ===\n";
    foreach ($columns as $col) {
        echo $col['Field'] . " - " . $col['Type'] . "\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
