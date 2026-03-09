<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Test & Setup</title>
    <style>
        body { font-family: Arial; max-width: 900px; margin: 0 auto; padding: 20px; background: #f5f5f5; }
        .box { background: white; border: 1px solid #ddd; border-radius: 5px; padding: 20px; margin: 20px 0; }
        h2 { border-bottom: 2px solid #0066cc; padding-bottom: 10px; }
        a { display: inline-block; padding: 10px 20px; margin: 5px 5px 5px 0; background: #0066cc; color: white; text-decoration: none; border-radius: 3px; }
        a:hover { background: #0052a3; }
        .ok { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h1>🔧 Test & Setup Dashboard</h1>

    <?php
    define('BASE_PATH', __DIR__);
    require BASE_PATH . '/vendor/autoload.php';
    require BASE_PATH . '/helpers.php';

    use Doctrine\DBAL\DriverManager;

    $dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->safeLoad();

    try {
        $conn = DriverManager::getConnection([
            'user'      => $_ENV['DB_USERNAME'] ?? 'root',
            'password'  => $_ENV['DB_PASSWORD'] ?? '',
            'dbname'    => $_ENV['DB_NAME'] ?? 'du_an_web_xem_phim',
            'host'      => $_ENV['DB_HOST'] ?? 'localhost',
            'driver'    => $_ENV['DB_DRIVER'] ?? 'pdo_mysql',
            'port'      => $_ENV['DB_PORT'] ?? 3306,
        ]);

        // Step 1: Check users table
        echo "<div class='box'>";
        echo "<h2>✅ Database Status</h2>";

        $tables = $conn->fetchAllAssociative("SHOW TABLES");
        $tableList = array_map(fn($t) => array_values($t)[0], $tables);
        
        if (in_array('users', $tableList)) {
            $count = $conn->fetchOne("SELECT COUNT(*) as c FROM users");
            echo "<p class='ok'>✅ Users table exists (" . $count . " users)</p>";
            
            // List test users
            $users = $conn->fetchAllAssociative("SELECT id, username, email FROM users LIMIT 5");
            if (!empty($users)) {
                echo "<table>";
                echo "<tr><th>ID</th><th>Username</th><th>Email</th></tr>";
                foreach ($users as $u) {
                    echo "<tr><td>{$u['id']}</td><td>{$u['username']}</td><td>{$u['email']}</td></tr>";
                }
                echo "</table>";
            }
        } else {
            echo "<p class='error'>❌ Users table does NOT exist</p>";
        }
        echo "</div>";

        // Step 2: Add test user if needed
        echo "<div class='box'>";
        echo "<h2>👤 Test User</h2>";
        
        $testUser = $conn->fetchAssociative("SELECT * FROM users WHERE email = 'test@test.com' LIMIT 1");
        
        if ($testUser) {
            echo "<p class='ok'>✅ Test user exists: test@test.com</p>";
            
            // Test password
            $verify = password_verify('123456', $testUser['password_hash']);
            echo "<p>Password '123456' verification: <span class='" . ($verify ? 'ok' : 'error') . "'>";
            echo $verify ? "✅ PASS" : "❌ FAIL (password is wrong)";
            echo "</span></p>";
        } else {
            echo "<p class='error'>❌ Test user does NOT exist</p>";
            echo "<p>You need to add one first. Try this SQL:</p>";
            echo "<pre>";
            $hash = password_hash('123456', PASSWORD_BCRYPT);
            echo "INSERT INTO users (username, email, password_hash, display_name, role_id, status, created_at, updated_at)
VALUES ('testuser123', 'test@test.com', '" . htmlspecialchars($hash) . "', 'Test User', 2, 'active', NOW(), NOW());";
            echo "</pre>";
        }
        echo "</div>";

        // Step 3: Test Routes
        echo "<div class='box'>";
        echo "<h2>🌐 Test Routes</h2>";
        echo "<p>Test different login methods:</p>";
        echo "<a href='/du_an_ca_nhan/du_an_web_xem_phim/'>Home Page</a>";
        echo "<a href='/du_an_ca_nhan/du_an_web_xem_phim/dang-nhap'>Login Page (/dang-nhap)</a>";
        echo "<a href='/du_an_ca_nhan/du_an_web_xem_phim/danh-sach-phim'>Movies List</a>";
        echo "</div>";

    } catch (\Exception $e) {
        echo "<div class='box'>";
        echo "<h2 class='error'>❌ Database Error</h2>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
    ?>

    <div class="box">
        <h2>📋 Instructions</h2>
        <ol>
            <li>Make sure <strong>usertable exists</strong> and has a test user</li>
            <li>Try clicking <strong>"Login Page"</strong> to test the route</li>
            <li>If you get 404, check the <strong>browser console</strong> (F12) for errors</li>
            <li>If form doesn't submit, open <strong>browser console</strong> and look for AJAX errors</li>
        </ol>
    </div>

</body>
</html>
