<?php
/**
 * Script cập nhật schema database
 * Chạy: php database/update_schema.php
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/vendor/autoload.php';

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
    
    echo "✓ Kết nối database thành công\n\n";

    // Update movies table
    $queries = [
        "ALTER TABLE `movies` ADD COLUMN IF NOT EXISTS `thumb_url` VARCHAR(500) AFTER `poster_url`",
        "ALTER TABLE `movies` ADD COLUMN IF NOT EXISTS `description` LONGTEXT AFTER `thumb_url`",
        "ALTER TABLE `movies` ADD COLUMN IF NOT EXISTS `release_year` INT AFTER `description`",
        "ALTER TABLE `movies` ADD COLUMN IF NOT EXISTS `country` VARCHAR(100) AFTER `release_year`",
        "ALTER TABLE `movies` ADD COLUMN IF NOT EXISTS `category` VARCHAR(255) AFTER `country`",
        "ALTER TABLE `movies` ADD COLUMN IF NOT EXISTS `views_count` INT DEFAULT 0 AFTER `category`",
        
        // Update episodes table
        "ALTER TABLE `episodes` ADD COLUMN IF NOT EXISTS `video_url` TEXT AFTER `title`",
        "ALTER TABLE `episodes` ADD COLUMN IF NOT EXISTS `views_count` INT DEFAULT 0",
        "ALTER TABLE `episodes` ADD COLUMN IF NOT EXISTS `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
        "ALTER TABLE `episodes` ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
    ];

    foreach ($queries as $i => $query) {
        try {
            echo "[$i] $query\n";
            $connection->executeQuery($query);
            echo "    ✓ OK\n";
        } catch (\Exception $e) {
            echo "    ⚠ " . $e->getMessage() . "\n";
        }
    }

    echo "\n✓ Cập nhật schema thành công!\n";
    $connection = null;

} catch (\Exception $e) {
    echo "✗ Lỗi: " . $e->getMessage() . "\n";
    exit(1);
}
?>
