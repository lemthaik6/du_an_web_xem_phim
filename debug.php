<?php
/**
 * Debug script to check what's happening with the application
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', false);

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

echo "<h2>Environment Variables</h2>";
echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'not set') . "<br>";
echo "DB_NAME: " . ($_ENV['DB_NAME'] ?? 'not set') . "<br>";
echo "DB_USERNAME: " . ($_ENV['DB_USERNAME'] ?? 'not set') . "<br>";
echo "DB_DRIVER: " . ($_ENV['DB_DRIVER'] ?? 'not set') . "<br>";

echo "<h2>Database Connection Test</h2>";
try {
    $connectionParams = [
        'user'      => $_ENV['DB_USERNAME'] ?? 'root',
        'password'  => $_ENV['DB_PASSWORD'] ?? '',
        'dbname'    => $_ENV['DB_NAME'] ?? 'du_an_web_xem_phim',
        'host'      => $_ENV['DB_HOST'] ?? 'localhost',
        'driver'    => $_ENV['DB_DRIVER'] ?? 'pdo_mysql',
        'port'      => $_ENV['DB_PORT'] ?? 3306,
    ];
    
    $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
    echo "✓ Database connected successfully<br>";
    
    // Check tables
    echo "<h2>Database Tables</h2>";
    $tables = $conn->getSchemaManager()->listTableNames();
    echo "Tables found: " . count($tables) . "<br>";
    foreach ($tables as $table) {
        echo "- " . $table . "<br>";
    }
    
    // Check if movies table has data
    echo "<h2>Movies Table Data</h2>";
    try {
        $result = $conn->query("SELECT COUNT(*) as total FROM movies")->fetch();
        echo "Total movies: " . ($result ? $result['total'] : 'N/A') . "<br>";
    } catch (Exception $e) {
        echo "✗ Error checking movies: " . $e->getMessage() . "<br>";
    }
    
} catch (\Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>BladeOne Test</h2>";
try {
    $blade = new \eftec\bladeone\BladeOne(__DIR__ . '/views', __DIR__ . '/storage/compiles', \eftec\bladeone\BladeOne::MODE_AUTO);
    echo "✓ BladeOne initialized successfully<br>";
    
    // Check if home.blade.php exists
    if (file_exists(__DIR__ . '/views/home.blade.php')) {
        echo "✓ home.blade.php exists<br>";
    } else {
        echo "✗ home.blade.php not found<br>";
    }
    
    // Check if layouts/app.blade.php exists
    if (file_exists(__DIR__ . '/views/layouts/app.blade.php')) {
        echo "✓ layouts/app.blade.php exists<br>";
    } else {
        echo "✗ layouts/app.blade.php not found<br>";
    }
    
} catch (\Exception $e) {
    echo "✗ BladeOne error: " . $e->getMessage() . "<br>";
}

echo "<h2>HomeController Test</h2>";
try {
    $controller = new \App\Controllers\HomeController();
    echo "✓ HomeController instantiated<br>";
} catch (\Exception $e) {
    echo "✗ HomeController error: " . $e->getMessage() . "<br>";
}

echo "<h2>Movie Model Test</h2>";
try {
    $movieModel = new \App\Models\Movie();
    echo "✓ Movie model instantiated<br>";
    
    $sections = $movieModel->getHomeSections();
    echo "Home sections returned: " . count($sections) . "<br>";
    foreach ($sections as $section) {
        echo "- " . $section['title'] . ": " . count($section['movies']) . " movies<br>";
    }
    
} catch (\Exception $e) {
    echo "✗ Movie model error: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
