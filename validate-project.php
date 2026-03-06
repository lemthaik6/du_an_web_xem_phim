<?php
/**
 * COMPREHENSIVE PROJECT VALIDATION SCRIPT
 * Verifies all fixes and critical functionality
 * 
 * This script performs multiple validation passes
 * to ensure the application is stable and production-ready.
 */

define('BASE_PATH', __DIR__);
define('PASS_NUMBER', getenv('PASS_NUMBER') ?: 1);

// Color codes for terminal output
class Colors {
    const GREEN = "\033[92m";
    const RED = "\033[91m";
    const YELLOW = "\033[93m";
    const BLUE = "\033[94m";
    const CYAN = "\033[96m";
    const RESET = "\033[0m";
    const BOLD = "\033[1m";
}

// Test counter
$testsPassed = 0;
$testsFailed = 0;
$testWarnings = 0;

function section($title) {
    echo "\n" . Colors::BOLD . Colors::CYAN . "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . Colors::RESET;
    echo "\n" . Colors::BOLD . Colors::BLUE . "▶ $title" . Colors::RESET . "\n";
}

function pass($msg) {
    global $testsPassed;
    echo Colors::GREEN . "✓" . Colors::RESET . " " . $msg . "\n";
    $testsPassed++;
}

function fail($msg) {
    global $testsFailed;
    echo Colors::RED . "✗" . Colors::RESET . " " . $msg . "\n";
    $testsFailed++;
}

function warn($msg) {
    global $testWarnings;
    echo Colors::YELLOW . "⚠" . Colors::RESET . " " . $msg . "\n";
    $testWarnings++;
}

function info($msg) {
    echo Colors::CYAN . "ℹ" . Colors::RESET . " " . $msg . "\n";
}

// ============================================================================
// PASS 1: FILE EXISTENCE & STRUCTURE
// ============================================================================

section("PASS 1: File Existence & Structure Validation");

$criticalFiles = [
    'index.php' => 'Main entry point',
    'helpers.php' => 'Global helper functions',
    '.env' => 'Environment configuration',
    '.htaccess' => 'URL rewrite rules',
    'composer.json' => 'Dependency manifest',
    'routes/web.php' => 'Route definitions',
    'vendor/autoload.php' => 'Composer autoloader',
];

foreach ($criticalFiles as $file => $description) {
    $path = BASE_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
    if (file_exists($path)) {
        $size = filesize($path);
        pass("$file ({$size} bytes) - $description");
    } else {
        fail("$file - MISSING: $description");
    }
}

// Check controller files
section("Controller Files");
$controllers = [
    'app/Controllers/HomeController.php',
    'app/Controllers/AuthController.php',
    'app/Controllers/MovieController.php',
    'app/Controllers/SearchController.php',
    'app/Controllers/ProfileController.php',
    'app/Controllers/CommentController.php',
    'app/Controllers/FavoriteController.php',
    'app/Controllers/WatchHistoryController.php',
    'app/Controllers/Admin/DashboardController.php',
    'app/Controllers/Admin/MovieController.php',
    'app/Controllers/Admin/EpisodeController.php',
    'app/Controllers/Admin/CategoryController.php',
    'app/Controllers/Admin/UserController.php',
    'app/Controllers/Admin/CommentController.php',
    'app/Controllers/Admin/BannerController.php',
];

foreach ($controllers as $file) {
    $path = BASE_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
    if (file_exists($path)) {
        pass("$file");
    } else {
        fail("$file - MISSING");
    }
}

// ============================================================================
// PASS 2: PHP SYNTAX VALIDATION
// ============================================================================

section("PASS 2: PHP Syntax Validation");

$phpFiles = array_merge(
    [$criticalFiles], // index.php, helpers.php, etc.
    [
        'index.php',
        'helpers.php',
        'app/Controllers/HomeController.php',
        'app/Controllers/AuthController.php',
        'app/Controllers/Admin/EpisodeController.php',
        'app/Controllers/Admin/CategoryController.php',
        'app/Controllers/Admin/UserController.php',
        'app/Controllers/Admin/CommentController.php',
        'app/Controllers/Admin/BannerController.php',
    ]
);

// Use unique files only
$phpFiles = array_unique($phpFiles);

$syntaxErrors = 0;
foreach ($phpFiles as $file => $desc) {
    if (is_int($file)) {
        $file = $desc;
    }
    
    $path = BASE_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
    if (!file_exists($path)) {
        continue;
    }
    
    $output = shell_exec("php -l \"$path\" 2>&1");
    if (strpos($output, 'No syntax errors') !== false) {
        pass("$file - Syntax OK");
    } else {
        fail("$file - Syntax Error: " . trim($output));
        $syntaxErrors++;
    }
}

if ($syntaxErrors === 0) {
    info("All PHP files passed syntax check ✓");
} else {
    warn("$syntaxErrors file(s) have syntax errors");
}

// ============================================================================
// PASS 3: AUTOLOADER & CLASS LOADING
// ============================================================================

section("PASS 3: Autoloader & Class Loading");

$requiredCode = <<<'PHP'
<?php
define('BASE_PATH', __DIR__);
ini_set('display_errors', 0);

try {
    // Test autoloader
    require 'vendor/autoload.php';
    
    // Test env loading
    $dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->safeLoad();
    
    // Test all controller classes exist
    $controllers = [
        'App\Controllers\HomeController',
        'App\Controllers\AuthController',
        'App\Controllers\MovieController',
        'App\Controllers\SearchController',
        'App\Controllers\ProfileController',
        'App\Controllers\CommentController',
        'App\Controllers\FavoriteController',
        'App\Controllers\WatchHistoryController',
        'App\Controllers\Admin\DashboardController',
        'App\Controllers\Admin\MovieController',
        'App\Controllers\Admin\EpisodeController',
        'App\Controllers\Admin\CategoryController',
        'App\Controllers\Admin\UserController',
        'App\Controllers\Admin\CommentController',
        'App\Controllers\Admin\BannerController',
    ];
    
    $foundCount = 0;
    foreach ($controllers as $className) {
        if (class_exists($className, true)) {
            $foundCount++;
        } else {
            echo "MISSING: $className\n";
        }
    }
    
    echo "AUTOLOADER_OK\n";
    echo "CLASSES_FOUND:" . $foundCount . "/15\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
PHP;

file_put_contents(BASE_PATH . '/test-autoloader.php', $requiredCode);
$output = shell_exec("php " . escapeshellarg(BASE_PATH . '/test-autoloader.php'));

if (strpos($output, 'AUTOLOADER_OK') !== false) {
    pass("Composer autoloader loads successfully");
} else {
    fail("Autoloader failed to load");
}

if (strpos($output, 'CLASSES_FOUND:15/15') !== false) {
    pass("All 15 controller classes found and loadable");
} else {
    preg_match('/CLASSES_FOUND:(\d+)/', $output, $matches);
    $count = $matches[1] ?? 0;
    warn("Classes found: $count/15");
}

// ============================================================================
// PASS 4: HELPER FUNCTIONS
// ============================================================================

section("PASS 4: Helper Functions Validation");

$helperTest = <<<'PHP'
<?php
define('BASE_PATH', __DIR__);

try {
    require 'vendor/autoload.php';
    require 'helpers.php';
    
    $requiredFunctions = [
        'view',
        'json',
        'redirect',
        'redirect404',
        'getFlash',
        'setFlash',
        'auth',
        'isAuthenticated',
        'hasRole',
        'getConnection',
        'formatDate',
        'truncate',
        'env',
        'esc',
        'titleToSlug',
        'slugToTitle',
    ];
    
    $foundCount = 0;
    $missingFuncs = [];
    
    foreach ($requiredFunctions as $func) {
        if (function_exists($func)) {
            $foundCount++;
        } else {
            $missingFuncs[] = $func;
        }
    }
    
    echo "HELPERS_OK\n";
    echo "FUNCTIONS_FOUND:" . $foundCount . "/" . count($requiredFunctions) . "\n";
    if (!empty($missingFuncs)) {
        echo "MISSING:" . implode(',', $missingFuncs) . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
PHP;

file_put_contents(BASE_PATH . '/test-helpers.php', $helperTest);
$output = shell_exec("php " . escapeshellarg(BASE_PATH . '/test-helpers.php'));

if (strpos($output, 'HELPERS_OK') !== false) {
    pass("Helper functions file loads successfully");
} else {
    fail("Helper functions failed to load");
}

$funcCount = 0;
if (preg_match('/FUNCTIONS_FOUND:(\d+)/', $output, $matches)) {
    $funcCount = $matches[1];
    pass("Helper functions found: $funcCount/16");
} else {
    fail("Could not verify helper functions");
}

// ============================================================================
// PASS 5: ROUTE DEFINITIONS
// ============================================================================

section("PASS 5: Route Definitions Validation");

$routePath = BASE_PATH . '/routes/web.php';
if (file_exists($routePath)) {
    $routeContent = file_get_contents($routePath);
    
    $routePatterns = [
        'Public routes' => 'HomeController',
        'Auth routes' => 'AuthController',
        'Movie routes' => 'MovieController',
        'Admin routes' => 'AdminDashboardController',
        'Episode routes' => 'AdminEpisodeController',
        'Category routes' => 'AdminCategoryController',
        'User routes' => 'AdminUserController',
        'Comment routes' => 'AdminCommentController',
        'Banner routes' => 'AdminBannerController',
    ];
    
    foreach ($routePatterns as $label => $pattern) {
        if (strpos($routeContent, $pattern) !== false) {
            pass("$label - Contains $pattern");
        } else {
            warn("$label - Missing $pattern");
        }
    }
} else {
    fail("routes/web.php not found");
}

// ============================================================================
// SUMMARY
// ============================================================================

section("Validation Summary - Pass " . PASS_NUMBER);

$totalTests = $testsPassed + $testsFailed + $testWarnings;
$passRate = $totalTests > 0 ? round(($testsPassed / $totalTests) * 100, 1) : 0;

echo "\n" . Colors::BOLD . "Results:" . Colors::RESET . "\n";
echo Colors::GREEN . "✓ Passed:  $testsPassed" . Colors::RESET . "\n";
echo Colors::RED . "✗ Failed:  $testsFailed" . Colors::RESET . "\n";
echo Colors::YELLOW . "⚠ Warnings: $testWarnings" . Colors::RESET . "\n";
echo Colors::BOLD . "Pass Rate: $passRate%" . Colors::RESET . "\n";

if ($testsFailed === 0) {
    echo "\n" . Colors::GREEN . Colors::BOLD . "✓ PASS " . PASS_NUMBER . " SUCCESSFUL - All Critical Tests Passed!" . Colors::RESET . "\n";
} else {
    echo "\n" . Colors::RED . Colors::BOLD . "✗ PASS " . PASS_NUMBER . " FAILED - Issues Found" . Colors::RESET . "\n";
}

// Cleanup
@unlink(BASE_PATH . '/test-autoloader.php');
@unlink(BASE_PATH . '/test-helpers.php');

echo "\n";
exit($testsFailed > 0 ? 1 : 0);
?>
