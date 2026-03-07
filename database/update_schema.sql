-- Update movies table schema
ALTER TABLE `movies` ADD COLUMN IF NOT EXISTS `thumb_url` VARCHAR(500) AFTER `poster_url`;
ALTER TABLE `movies` ADD COLUMN IF NOT EXISTS `description` LONGTEXT AFTER `thumb_url`;
ALTER TABLE `movies` ADD COLUMN IF NOT EXISTS `release_year` INT AFTER `description`;
ALTER TABLE `movies` ADD COLUMN IF NOT EXISTS `country` VARCHAR(100) AFTER `release_year`;
ALTER TABLE `movies` ADD COLUMN IF NOT EXISTS `category` VARCHAR(255) AFTER `country`;
ALTER TABLE `movies` ADD COLUMN IF NOT EXISTS `views_count` INT DEFAULT 0 AFTER `category`;

-- Update episodes table schema
ALTER TABLE `episodes` ADD COLUMN IF NOT EXISTS `video_url` TEXT AFTER `title`;
ALTER TABLE `episodes` ADD COLUMN IF NOT EXISTS `views_count` INT DEFAULT 0;
ALTER TABLE `episodes` ADD COLUMN IF NOT EXISTS `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `episodes` ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Create indexes
CREATE INDEX IF NOT EXISTS idx_slug ON `movies`(`slug`);
CREATE INDEX IF NOT EXISTS idx_movie_id ON `episodes`(`movie_id`);
