<!DOCTYPE html>
<html>
<head>
    <title>Auth Debug</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 20px auto; }
        .section { border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f0f0f0; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; font-size: 12px; }
        .log { background: #fafafa; border-left: 4px solid #ccc; padding: 10px; margin: 10px 0; }
        button { padding: 10px 20px; font-size: 14px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>🔍 Authentication System Debug</h1>
    
    <?php
    define('BASE_PATH', __DIR__);
    require BASE_PATH . '/vendor/autoload.php';

    use Doctrine\DBAL\DriverManager;

    $dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->safeLoad();

    try {
        echo "<div class='section'>";
        echo "<h2>1️⃣ Database Connection</h2>";

        $connectionParams = [
            'user'      => $_ENV['DB_USERNAME'] ?? 'root',
            'password'  => $_ENV['DB_PASSWORD'] ?? '',
            'dbname'    => $_ENV['DB_NAME'] ?? 'du_an_web_xem_phim',
            'host'      => $_ENV['DB_HOST'] ?? 'localhost',
            'driver'    => $_ENV['DB_DRIVER'] ?? 'pdo_mysql',
            'port'      => $_ENV['DB_PORT'] ?? 3306,
        ];

        echo "<pre>";
        echo "Host: {$connectionParams['host']}\n";
        echo "Database: {$connectionParams['dbname']}\n";
        echo "User: {$connectionParams['user']}\n";
        echo "Driver: {$connectionParams['driver']}\n";
        echo "</pre>";

        $connection = DriverManager::getConnection($connectionParams);
        echo "<p class='success'>✅ Connected to database</p>";
        echo "</div>";

        // Check users table
        echo "<div class='section'>";
        echo "<h2>2️⃣ Users Table</h2>";

        $tables = $connection->fetchAllAssociative("SHOW TABLES");
        $tableList = array_map(fn($t) => array_values($t)[0], $tables);
        
        if (!in_array('users', $tableList)) {
            echo "<p class='error'>❌ Users table NOT found</p>";
            echo "<p>Available tables: " . implode(", ", $tableList) . "</p>";
        } else {
            echo "<p class='success'>✅ Users table exists</p>";
            
            // Show table structure
            echo "<h3>Table Structure:</h3>";
            $columns = $connection->fetchAllAssociative("DESCRIBE users");
            echo "<table>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
            foreach ($columns as $col) {
                echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td></tr>";
            }
            echo "</table>";
            
            // Count users
            $count = $connection->fetchOne("SELECT COUNT(*) as c FROM users");
            echo "<p>Total users: <strong>$count</strong></p>";
            
            // List users
            echo "<h3>Users List:</h3>";
            $users = $connection->fetchAllAssociative("SELECT id, username, email, display_name, role_id, status FROM users ORDER BY id DESC LIMIT 10");
            echo "<table>";
            echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Display Name</th><th>Role</th><th>Status</th></tr>";
            foreach ($users as $user) {
                $roleName = $user['role_id'] == 1 ? 'Admin' : 'User';
                echo "<tr><td>{$user['id']}</td><td>{$user['username']}</td><td>{$user['email']}</td><td>{$user['display_name']}</td><td>{$roleName}</td><td>{$user['status']}</td></tr>";
            }
            echo "</table>";
        }
        echo "</div>";

        // Test password hashing
        echo "<div class='section'>";
        echo "<h2>3️⃣ Password Hashing Test</h2>";
        $testPass = '123456';
        $hash = password_hash($testPass, PASSWORD_BCRYPT);
        $verify = password_verify($testPass, $hash);
        echo "<p>Test password: <code>$testPass</code></p>";
        echo "<p>Hash: <code>" . substr($hash, 0, 50) . "...</code></p>";
        echo "<p>Verify result: <span class='" . ($verify ? 'success' : 'error') . "'>" . ($verify ? '✅ PASS' : '❌ FAIL') . "</span></p>";
        echo "</div>";

        // Test login function
        echo "<div class='section'>";
        echo "<h2>4️⃣ Test Login</h2>";
        
        // Check if there's a test user
        $testUser = $connection->fetchAssociative("SELECT * FROM users WHERE email = 'test@test.com' LIMIT 1");
        
        if ($testUser) {
            echo "<p>Found test user: <strong>{$testUser['email']}</strong></p>";
            $testPassVerify = password_verify('123456', $testUser['password_hash']);
            echo "<p>Test password '123456' verification: <span class='" . ($testPassVerify ? 'success' : 'error') . "'>" . ($testPassVerify ? '✅ PASS' : '❌ FAIL') . "</span></p>";
        } else {
            echo "<p class='info'>ℹ️ No test user found with email 'test@test.com'</p>";
        }
        
        echo "<h3>Credentials to test:</h3>";
        echo "<p><strong>Email:</strong> test@test.com</p>";
        echo "<p><strong>Password:</strong> 123456</p>";
        echo "<p><a href='/du_an_ca_nhan/du_an_web_xem_phim/add_test_user.php' target='_blank'>👉 Add Test User</a></p>";
        
        echo "</div>";

        // Check error logs
        echo "<div class='section'>";
        echo "<h2>5️⃣ Error Logs (Last 20 lines)</h2>";
        $logFile = BASE_PATH . '/storage/logs/php-errors.log';
        if (file_exists($logFile)) {
            $lines = file($logFile);
            $lastLines = array_tail($lines, 20);
            echo "<pre>";
            echo implode("", $lastLines);
            echo "</pre>";
        } else {
            echo "<p class='info'>ℹ️ No log file found</p>";
        }
        echo "</div>";

        // Test auth modal form
        echo "<div class='section'>";
        echo "<h2>6️⃣ Test Modal Form</h2>";
        echo "<p><a href='/du_an_ca_nhan/du_an_web_xem_phim/' target='_blank'>👉 Go to Home (Modal should appear)</a></p>";
        echo "<p><a href='/du_an_ca_nhan/du_an_web_xem_phim/dang-nhap' target='_blank'>👉 Go to Login Page</a></p>";
        echo "</div>";

    } catch (\Exception $e) {
        echo "<div class='section'>";
        echo "<h2 class='error'>❌ Error</h2>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "</div>";
    }
    
    // Helper function
    if (!function_exists('array_tail')) {
        function array_tail($array, $length = 10) {
            return array_slice($array, max(0, count($array) - $length));
        }
    }
    ?>
</body>
</html>
