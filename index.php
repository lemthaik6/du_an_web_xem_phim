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

// Set up error handling - use environment variable to control debug mode
$isDebugMode = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
$GLOBALS['isDebugMode'] = $isDebugMode; // Store in globals for error handler

error_reporting(E_ALL);
ini_set('display_errors', $isDebugMode ? 1 : 0);
ini_set('log_errors', true);
ini_set('error_log', BASE_PATH . '/storage/logs/php-errors.log');

// Custom error handler for catch-all
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if ($errno === 0) return false;
    $isDebug = $GLOBALS['isDebugMode'] ?? false;
    if (!$isDebug) return false; // Let default handler work in production
    
    error_log("[$errno] $errstr in $errfile:$errline");
    return false;
});

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
