-- Database migration for ophim1.com integration
-- Run these queries to prepare your database for the import system

-- ============================================================
-- 1. Ensure movies table has all required columns
-- ============================================================

ALTER TABLE `movies` ADD COLUMN `slug` VARCHAR(255) UNIQUE AFTER `id` IF NOT EXISTS;
ALTER TABLE `movies` ADD COLUMN `poster_url` TEXT AFTER `slug` IF NOT EXISTS;
ALTER TABLE `movies` ADD COLUMN `thumb_url` TEXT AFTER `poster_url` IF NOT EXISTS;
ALTER TABLE `movies` ADD COLUMN `description` LONGTEXT AFTER `thumb_url` IF NOT EXISTS;
ALTER TABLE `movies` ADD COLUMN `release_year` INT AFTER `description` IF NOT EXISTS;
ALTER TABLE `movies` ADD COLUMN `country` VARCHAR(100) AFTER `release_year` IF NOT EXISTS;
ALTER TABLE `movies` ADD COLUMN `category` VARCHAR(255) AFTER `country` IF NOT EXISTS;
ALTER TABLE `movies` ADD COLUMN `views_count` INT DEFAULT 0 AFTER `category` IF NOT EXISTS;
ALTER TABLE `movies` ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `views_count` IF NOT EXISTS;
ALTER TABLE `movies` ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at` IF NOT EXISTS;

-- Create unique index on slug for faster lookup
CREATE UNIQUE INDEX idx_movies_slug ON `movies`(`slug`);

-- ============================================================
-- 2. Ensure episodes table has all required columns
-- ============================================================

ALTER TABLE `episodes` ADD COLUMN `episode_name` VARCHAR(255) AFTER `id` IF NOT EXISTS;
ALTER TABLE `episodes` ADD COLUMN `video_url` TEXT AFTER `episode_name` IF NOT EXISTS;
ALTER TABLE `episodes` ADD COLUMN `views_count` INT DEFAULT 0 AFTER `video_url` IF NOT EXISTS;
ALTER TABLE `episodes` ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `views_count` IF NOT EXISTS;
ALTER TABLE `episodes` ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at` IF NOT EXISTS;

-- Create foreign key constraint if not exists
ALTER TABLE `episodes` ADD CONSTRAINT `fk_episodes_movie_id` 
    FOREIGN KEY (`movie_id`) REFERENCES `movies`(`id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE;

-- Create index on movie_id for faster queries
CREATE INDEX idx_episodes_movie_id ON `episodes`(`movie_id`);

-- ============================================================
-- 3. Or CREATE FRESH TABLES (if moving to new database)
-- ============================================================

-- Uncomment below if you want to create fresh tables

/*

DROP TABLE IF EXISTS `episodes`;
DROP TABLE IF EXISTS `movies`;

CREATE TABLE `movies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(255) UNIQUE NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `poster_url` VARCHAR(500),
  `thumb_url` VARCHAR(500),
  `description` LONGTEXT,
  `release_year` INT,
  `country` VARCHAR(100),
  `category` VARCHAR(255),
  `rating` DECIMAL(3,1) DEFAULT 0,
  `views_count` INT DEFAULT 0,
  `is_published` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_slug` (`slug`),
  INDEX `idx_title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `episodes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `movie_id` INT NOT NULL,
  `episode_name` VARCHAR(255),
  `video_url` TEXT NOT NULL,
  `views_count` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`movie_id`) REFERENCES `movies`(`id`) ON DELETE CASCADE,
  INDEX `idx_movie_id` (`movie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

*/
