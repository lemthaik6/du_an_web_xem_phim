<?php
/**
 * Quick bootstrap test to verify app loads correctly
 */

define('BASE_PATH', __DIR__);

echo "[TEST] Loading autoloader...\n";
try {
    require_once BASE_PATH . '/vendor/autoload.php';
    echo "✓ Autoloader loaded\n";
} catch (Exception $e) {
    echo "✗ Autoloader error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "[TEST] Loading environment variables...\n";
try {
    $dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->safeLoad();
    echo "✓ Environment loaded\n";
} catch (Exception $e) {
    echo "✗ Environment error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "[TEST] Loading helpers...\n";
try {
    require_once BASE_PATH . '/helpers.php';
    echo "✓ Helpers loaded\n";
} catch (Exception $e) {
    echo "✗ Helpers error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "[TEST] Testing helper functions...\n";
if (!function_exists('view')) {
    echo "✗ view() function not found\n";
    exit(1);
}
if (!function_exists('json')) {
    echo "✗ json() function not found\n";
    exit(1);
}
if (!function_exists('getFlash')) {
    echo "✗ getFlash() function not found\n";
    exit(1);
}
if (!function_exists('setFlash')) {
    echo "✗ setFlash() function not found\n";
    exit(1);
}
if (!function_exists('redirect')) {
    echo "✗ redirect() function not found\n";
    exit(1);
}
echo "✓ All helper functions found\n";

echo "[TEST] Testing class loading...\n";
$classes = [
    'App\Controllers\HomeController',
    'App\Controllers\AuthController',
    'App\Controllers\Admin\DashboardController',
    'App\Controllers\Admin\MovieController',
    'App\Controllers\Admin\EpisodeController',
    'App\Controllers\Admin\CategoryController',
    'App\Controllers\Admin\UserController',
    'App\Controllers\Admin\CommentController',
    'App\Controllers\Admin\BannerController',
    'App\Middlewares\AuthMiddleware',
    'App\Middlewares\RoleMiddleware',
];

foreach ($classes as $class) {
    if (!class_exists($class)) {
        echo "✗ Class not found: $class\n";
        exit(1);
    }
}
echo "✓ All reference classes found\n";

echo "\n✅ All bootstrap tests passed!\n";
