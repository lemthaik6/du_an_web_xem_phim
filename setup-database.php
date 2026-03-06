<?php
/**
 * Database Schema & Setup Script
 * This script creates the necessary tables for the application
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
    
    echo "✓ Database connected\n";
    
    // Disable foreign key checks temporarily
    $conn->executeQuery("SET FOREIGN_KEY_CHECKS=0");
    
    // Create users table
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    try {
        $conn->executeQuery($sql);
        echo "✓ Users table created/verified\n";
    } catch (\Exception $e) {
        echo "⚠ Users table: " . $e->getMessage() . "\n";
    }
    
    // Create movies table
    $sql = "
    CREATE TABLE IF NOT EXISTS movies (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        original_title VARCHAR(255),
        slug VARCHAR(255) NOT NULL UNIQUE,
        description LONGTEXT,
        poster_url VARCHAR(500),
        banner_url VARCHAR(500),
        release_year INT,
        country_id INT,
        is_published TINYINT DEFAULT 0,
        views_count INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_slug (slug),
        INDEX idx_published (is_published)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    try {
        $conn->executeQuery($sql);
        echo "✓ Movies table created/verified\n";
    } catch (\Exception $e) {
        echo "⚠ Movies table: " . $e->getMessage() . "\n";
    }
    
    // Create categories table
    $sql = "
    CREATE TABLE IF NOT EXISTS categories (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL UNIQUE,
        slug VARCHAR(255) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    try {
        $conn->executeQuery($sql);
        echo "✓ Categories table created/verified\n";
    } catch (\Exception $e) {
        echo "⚠ Categories table: " . $e->getMessage() . "\n";
    }
    
    // Create episodes table
    $sql = "
    CREATE TABLE IF NOT EXISTS episodes (
        id INT PRIMARY KEY AUTO_INCREMENT,
        movie_id INT NOT NULL,
        episode_number INT NOT NULL,
        title VARCHAR(255),
        video_url VARCHAR(500),
        duration INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_episode (movie_id, episode_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    try {
        $conn->executeQuery($sql);
        echo "✓ Episodes table created/verified\n";
    } catch (\Exception $e) {
        echo "⚠ Episodes table: " . $e->getMessage() . "\n";
    }
    
    // Create movie_category junction table
    $sql = "
    CREATE TABLE IF NOT EXISTS movie_category (
        movie_id INT NOT NULL,
        category_id INT NOT NULL,
        PRIMARY KEY (movie_id, category_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    try {
        $conn->executeQuery($sql);
        echo "✓ Movie-Category junction table created/verified\n";
    } catch (\Exception $e) {
        echo "⚠ Movie-Category table: " . $e->getMessage() . "\n";
    }
    
    // Create comments table
    $sql = "
    CREATE TABLE IF NOT EXISTS comments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        movie_id INT NOT NULL,
        user_id INT NOT NULL,
        content TEXT NOT NULL,
        rating INT,
        is_approved TINYINT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_movie (movie_id),
        INDEX idx_user (user_id),
        INDEX idx_approved (is_approved)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    try {
        $conn->executeQuery($sql);
        echo "✓ Comments table created/verified\n";
    } catch (\Exception $e) {
        echo "⚠ Comments table: " . $e->getMessage() . "\n";
    }
    
    // Create favorites table
    $sql = "
    CREATE TABLE IF NOT EXISTS favorites (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        movie_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_favorite (user_id, movie_id),
        INDEX idx_user (user_id),
        INDEX idx_movie (movie_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    try {
        $conn->executeQuery($sql);
        echo "✓ Favorites table created/verified\n";
    } catch (\Exception $e) {
        echo "⚠ Favorites table: " . $e->getMessage() . "\n";
    }
    
    // Create watch_history table
    $sql = "
    CREATE TABLE IF NOT EXISTS watch_history (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        movie_id INT NOT NULL,
        episode_number INT,
        watch_time INT DEFAULT 0,
        watched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user (user_id),
        INDEX idx_movie (movie_id),
        INDEX idx_watched_at (watched_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    try {
        $conn->executeQuery($sql);
        echo "✓ Watch History table created/verified\n";
    } catch (\Exception $e) {
        echo "⚠ Watch History table: " . $e->getMessage() . "\n";
    }
    
    // Create banners table
    $sql = "
    CREATE TABLE IF NOT EXISTS banners (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255),
        image_url VARCHAR(500),
        link_url VARCHAR(500),
        is_active TINYINT DEFAULT 1,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    try {
        $conn->executeQuery($sql);
        echo "✓ Banners table created/verified\n";
    } catch (\Exception $e) {
        echo "⚠ Banners table: " . $e->getMessage() . "\n";
    }
    
    // Enable foreign key checks again
    $conn->executeQuery("SET FOREIGN_KEY_CHECKS=1");
    
    echo "\n✅ All tables ready!\n";
    
    // Show current tables
    $tables = $conn->getSchemaManager()->listTableNames();
    echo "\n📋 Tables in database: " . implode(', ', $tables) . "\n";
    
    // Check if users table has any data
    $userCount = $conn->query("SELECT COUNT(*) as cnt FROM users")->fetch()['cnt'];
    echo "👥 Users in database: " . $userCount . "\n";
    
    if ($userCount === 0) {
        echo "\n💡 No users yet. You can register via the web interface.\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
