<?php
/**
 * Test the home page rendering
 */

define('BASE_PATH', __DIR__);

// Capture any output
ob_start();

try {
    // Load the index.php to trigger the route
    // We'll manually set up everything instead
    
    require_once BASE_PATH . '/vendor/autoload.php';
    
    // Load env
    $dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->safeLoad();
    
    // Set up Blade
    $blade = new \eftec\bladeone\BladeOne(
        BASE_PATH . '/views',
        BASE_PATH . '/storage/compiles',
        \eftec\bladeone\BladeOne::MODE_AUTO
    );
    
    $GLOBALS['blade'] = $blade;
    
    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Load helpers
    require_once BASE_PATH . '/helpers.php';
    
    // Test route function exists
    echo "Testing route() function: ";
    if (function_exists('route')) {
        echo "✓ EXISTS<br>";
        echo "route('/test'): " . route('/test') . "<br>";
    } else {
        echo "✗ MISSING<br>";
    }
    
    // Test file_url function exists
    echo "Testing file_url() function: ";
    if (function_exists('file_url')) {
        echo "✓ EXISTS<br>";
        echo "file_url('uploads/test.jpg'): " . file_url('uploads/test.jpg') . "<br>";
    } else {
        echo "✗ MISSING<br>";
    }
    
    // Test HomeController
    echo "<h2>Testing HomeController</h2>";
    $controller = new \App\Controllers\HomeController();
    echo "✓ HomeController instantiated<br>";
    
    // We can't call the method directly as it uses view() which exits, 
    // so we'll test the Movie model instead
    echo "<h2>Testing Movie Model</h2>";
    $movieModel = new \App\Models\Movie();
    echo "✓ Movie model instantiated<br>";
    
    $sections = $movieModel->getHomeSections();
    echo "Sections count: " . count($sections) . "<br>";
    foreach ($sections as $section) {
        echo "- " . $section['title'] . ": " . count($section['movies']) . " movies<br>";
    }
    
    echo "<h2>Rendering Home View</h2>";
    
    // Now try to render the view manually
    $output = $blade->run('home', ['sections' => $sections]);
    
    echo "View rendered successfully! Length: " . strlen($output) . " bytes<br>";
    echo "<h3>First 500 characters of HTML:</h3>";
    echo "<pre>" . htmlspecialchars(substr($output, 0, 500)) . "...</pre>";
    
    // Count the HTML elements
    $h1Count = substr_count($output, '<h1');
    $divCount = substr_count($output, '<div');
    echo "<p>HTML elements: " . $h1Count . " h1s, " . $divCount . " divs</p>";
    
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

$content = ob_get_clean();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Home Page Test</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 6px; }
        h2 { color: #333; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        pre { background: #f0f0f0; padding: 10px; border-left: 4px solid #666; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Home Page Rendering Test</h1>
        <?php echo $content; ?>
    </div>
</body>
</html>
