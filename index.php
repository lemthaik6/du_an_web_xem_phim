<?php
/**
 * Entry point for the Movie Streaming Web Application
 * 
 * Stack: PHP + bramus/router + Doctrine DBAL + BladeOne + vlucas/phpdotenv
 * All requests are routed through this file via .htaccess rewrite
 */

// Define base path for easy reference
define('BASE_PATH', __DIR__);

// Load environment variables
require_once BASE_PATH . '/vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

// Set up error handling
error_reporting(E_ALL);
ini_set('display_errors', false);
ini_set('log_errors', true);
ini_set('error_log', BASE_PATH . '/storage/logs/php-errors.log');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configure BladeOne view engine
$blade = new \eftec\bladeone\BladeOne(
    BASE_PATH . '/views',           // views directory
    BASE_PATH . '/storage/compiles', // cache directory
    \eftec\bladeone\BladeOne::MODE_AUTO
);

// Make blade instance globally available for helpers
$GLOBALS['blade'] = $blade;

// Route and run the application
require_once BASE_PATH . '/routes/web.php';
