<?php
/**
 * Application Status Check
 * Access at: http://localhost/du_an_ca_nhan/du_an_web_xem_phim/status.php
 */

header('Content-Type: text/html; charset=utf-8');

// Enable all errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('BASE_PATH', __DIR__);

$status = [];

// 1. Check environment
$status['environment'] = [
    'APP_URL' => getenv('APP_URL'),
    'DB_HOST' => getenv('DB_HOST'),
    'DB_NAME' => getenv('DB_NAME'),
];

// 2. Check .env file
$status['env_file'] = [
    'exists' => file_exists(BASE_PATH . '/.env'),
    'readable' => is_readable(BASE_PATH . '/.env'),
];

// 3. Check views directory
$status['views_dir'] = [
    'exists' => is_dir(BASE_PATH . '/views'),
    'home.blade.php' => file_exists(BASE_PATH . '/views/home.blade.php'),
    'layouts/app.blade.php' => file_exists(BASE_PATH . '/views/layouts/app.blade.php'),
];

// 4. Check routes
$status['routes'] = [
    'web.php' => file_exists(BASE_PATH . '/routes/web.php'),
];

// 5. Load helpers and test functions
require_once BASE_PATH . '/vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

$status['helpers'] = [];
foreach (['view', 'json', 'route', 'file_url', 'auth', 'hasRole', 'formatDate', 'truncate', 'env', 'esc', 'titleToSlug'] as $fn) {
    $status['helpers'][$fn] = function_exists($fn) ? 'OK' : 'MISSING';
}

// 6. Test Blade
try {
    $blade = new \eftec\bladeone\BladeOne(
        BASE_PATH . '/views',
        BASE_PATH . '/storage/compiles',
        \eftec\bladeone\BladeOne::MODE_AUTO
    );
    $status['blade'] = 'OK - initialized';
} catch (\Exception $e) {
    $status['blade'] = 'ERROR: ' . $e->getMessage();
}

// 7. Test database
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
    
    $tables = $conn->getSchemaManager()->listTableNames();
    $status['database'] = [
        'status' => 'CONNECTED',
        'tables' => count($tables),
        'has_movies_table' => in_array('movies', $tables),
    ];
    
    if (in_array('movies', $tables)) {
        $count = $conn->query("SELECT COUNT(*) as cnt FROM movies")->fetch()['cnt'];
        $status['database']['movies_count'] = $count;
    }
} catch (\Exception $e) {
    $status['database'] = 'ERROR: ' . $e->getMessage();
}

// 8. Test HomeController
try {
    $controller = new \App\Controllers\HomeController();
    $status['controller'] = 'OK - can instantiate';
} catch (\Exception $e) {
    $status['controller'] = 'ERROR: ' . $e->getMessage();
}

// HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status</title>
    <style>
        body { font-family: monospace; background: #1a1a1a; color: #e0e0e0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #4CAF50; }
        .section { margin: 20px 0; padding: 15px; background: #2a2a2a; border-left: 4px solid #4CAF50; }
        .section h2 { margin-top: 0; color: #66BB6A; }
        .status-ok { color: #4CAF50; }
        .status-error { color: #f44336; }
        .status-warn { color: #ff9800; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 8px; border-bottom: 1px solid #444; }
        td:first-child { width: 30%; color: #90CAF9; }
        pre { background: #0a0a0a; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Application Status Check</h1>
        
        <div class="section">
            <h2>Environment Variables</h2>
            <table>
                <?php foreach ($status['environment'] as $key => $value): ?>
                <tr>
                    <td><?= htmlspecialchars($key) ?></td>
                    <td><code><?= htmlspecialchars($value ?? 'not set') ?></code></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="section">
            <h2>.env File</h2>
            <table>
                <tr>
                    <td>Exists</td>
                    <td><?= $status['env_file']['exists'] ? '<span class="status-ok">✓</span>' : '<span class="status-error">✗</span>' ?></td>
                </tr>
                <tr>
                    <td>Readable</td>
                    <td><?= $status['env_file']['readable'] ? '<span class="status-ok">✓</span>' : '<span class="status-error">✗</span>' ?></td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>Views</h2>
            <table>
                <?php foreach ($status['views_dir'] as $key => $value): ?>
                <tr>
                    <td><?= htmlspecialchars($key) ?></td>
                    <td><?= $value ? '<span class="status-ok">✓</span>' : '<span class="status-error">✗</span>' ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="section">
            <h2>Helper Functions</h2>
            <table>
                <?php foreach ($status['helpers'] as $fn => $status_text): ?>
                <tr>
                    <td><?= htmlspecialchars($fn) ?>()</td>
                    <td><?= $status_text === 'OK' ? '<span class="status-ok">✓</span>' : '<span class="status-error">' . htmlspecialchars($status_text) . '</span>' ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="section">
            <h2>Templates (BladeOne)</h2>
            <table>
                <tr>
                    <td>Status</td>
                    <td><?= strpos($status['blade'], 'ERROR') === false ? '<span class="status-ok">' . htmlspecialchars($status['blade']) . '</span>' : '<span class="status-error">' . htmlspecialchars($status['blade']) . '</span>' ?></td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>Database</h2>
            <?php if (is_array($status['database'])): ?>
            <table>
                <?php foreach ($status['database'] as $key => $value): ?>
                <tr>
                    <td><?= htmlspecialchars($key) ?></td>
                    <td><?= htmlspecialchars(is_array($value) ? json_encode($value) : $value) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php else: ?>
            <p><span class="status-error"><?= htmlspecialchars($status['database']) ?></span></p>
            <p><strong>Note:</strong> Database connection is optional. Application will show placeholder content if database is unavailable.</p>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2>Controller</h2>
            <table>
                <tr>
                    <td>HomeController</td>
                    <td><?= strpos($status['controller'], 'ERROR') === false ? '<span class="status-ok">' . htmlspecialchars($status['controller']) . '</span>' : '<span class="status-error">' . htmlspecialchars($status['controller']) . '</span>' ?></td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>Actions</h2>
            <p>
                <a href="/" style="color: #66BB6A;">← Go to Home</a> | 
                <a href="/debug.php" style="color: #66BB6A;">View Full Debug →</a>
            </p>
        </div>
    </div>
</body>
</html>
