<!DOCTYPE html>
<html>
<head>
    <title>Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        pre { background: #f0f0f0; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔧 Database Setup & Diagnostics</h1>
    
    <?php
    define('BASE_PATH', __DIR__);
    require BASE_PATH . '/vendor/autoload.php';

    use Doctrine\DBAL\DriverManager;

    $dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->safeLoad();

    try {
        $connectionParams = [
            'user'      => $_ENV['DB_USERNAME'] ?? 'root',
            'password'  => $_ENV['DB_PASSWORD'] ?? '',
            'dbname'    => $_ENV['DB_NAME'] ?? 'du_an_web_xem_phim',
            'host'      => $_ENV['DB_HOST'] ?? 'localhost',
            'driver'    => $_ENV['DB_DRIVER'] ?? 'pdo_mysql',
            'port'      => $_ENV['DB_PORT'] ?? 3306,
        ];

        $connection = DriverManager::getConnection($connectionParams);
        
        echo "<h2>Database Connection</h2>";
        echo "<p class='success'>✅ Connected to {$connectionParams['dbname']} at {$connectionParams['host']}</p>";

        // Get all tables
        $tables = $connection->fetchAllAssociative("SHOW TABLES");
        $tableList = array_map(fn($t) => array_values($t)[0], $tables);
        
        echo "<h2>📊 Database Tables</h2>";
        echo "<p>Found: " . implode(", ", $tableList) . "</p>";

        // Check users table
        if (!in_array('users', $tableList)) {
            echo "<h2 class='error'>❌ Users table MISSING</h2>";
            echo "<p>The 'users' table does not exist. Click the button below to create it:</p>";
            
            if ($_POST['action'] ?? null === 'create_users') {
                echo "<h3>Creating users table...</h3>";
                
                $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(255) UNIQUE NOT NULL,
  `email` VARCHAR(255) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `display_name` VARCHAR(255),
  `role_id` INT DEFAULT 2,
  `status` VARCHAR(50) DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`),
  INDEX `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
                
                $connection->executeStatement($sql);
                echo "<p class='success'>✅ Users table created!</p>";
                
                // Insert test data
                echo "<p>Adding test users...</p>";
                
                $testAdmin = [
                    'username' => 'admin',
                    'email' => 'admin@test.com',
                    'password_hash' => password_hash('123456', PASSWORD_BCRYPT),
                    'display_name' => 'Admin User',
                    'role_id' => 1,
                    'status' => 'active'
                ];
                
                $testUser = [
                    'username' => 'testuser',
                    'email' => 'user@test.com',
                    'password_hash' => password_hash('123456', PASSWORD_BCRYPT),
                    'display_name' => 'Test User',
                    'role_id' => 2,
                    'status' => 'active'
                ];
                
                try {
                    $connection->insert('users', $testAdmin);
                    echo "<p class='success'>✅ Added admin account: admin@test.com / 123456</p>";
                } catch (\Exception $e) {
                    echo "<p class='info'>ℹ️ Admin account already exists</p>";
                }
                
                try {
                    $connection->insert('users', $testUser);
                    echo "<p class='success'>✅ Added test account: user@test.com / 123456</p>";
                } catch (\Exception $e) {
                    echo "<p class='info'>ℹ️ Test account already exists</p>";
                }
                
                echo "<p><a href='{$_SERVER['REQUEST_URI']}'>Reload</a></p>";
            } else {
                echo "<form method='POST'>";
                echo "<input type='hidden' name='action' value='create_users'>";
                echo "<button type='submit' style='padding: 10px 20px; font-size: 16px;'>Create Users Table & Add Test Data</button>";
                echo "</form>";
            }
        } else {
            echo "<h2 class='success'>✅ Users table exists</h2>";
            
            // Show user count
            $count = $connection->fetchOne("SELECT COUNT(*) as c FROM users");
            echo "<p>Total users: " . $count . "</p>";
            
            // List users
            $users = $connection->fetchAllAssociative("SELECT id, username, email, display_name, role_id FROM users ORDER BY id");
            if (!empty($users)) {
                echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
                echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Username</th><th>Email</th><th>Display Name</th><th>Role</th></tr>";
                foreach ($users as $user) {
                    $role = $user['role_id'] == 1 ? 'Admin' : 'User';
                    echo "<tr><td>{$user['id']}</td><td>{$user['username']}</td><td>{$user['email']}</td><td>{$user['display_name']}</td><td>{$role}</td></tr>";
                }
                echo "</table>";
            }
        }
        
        // Check auth views
        echo "<h2>📄 Authentication Views</h2>";
        $loginExists = file_exists(BASE_PATH . '/views/auth/login.blade.php');
        $registerExists = file_exists(BASE_PATH . '/views/auth/register.blade.php');
        
        echo "<p>Login view: " . ($loginExists ? "<span class='success'>✅ Exists</span>" : "<span class='error'>❌ Missing</span>") . "</p>";
        echo "<p>Register view: " . ($registerExists ? "<span class='success'>✅ Exists</span>" : "<span class='error'>❌ Missing</span>") . "</p>";
        
        echo "<h2>🧪 Test Access</h2>";
        echo "<p><a href='/du_an_ca_nhan/du_an_web_xem_phim/dang-nhap'>Go to Login Page</a></p>";
        
    } catch (\Exception $e) {
        echo "<h2 class='error'>❌ Error</h2>";
        echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
        echo "<p>Make sure Laragon MySQL server is running.</p>";
    }
    ?>
</body>
</html>
