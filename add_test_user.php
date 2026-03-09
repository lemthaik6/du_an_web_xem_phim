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
    
    echo "<h1>🧪 Tạo User Test</h1>";
    
    // Hash password: 123456
    $passwordHash = password_hash('123456', PASSWORD_BCRYPT);
    
    echo "<h2>Thêm tài khoản test:</h2>";
    echo "<p><strong>Email:</strong> test@test.com</p>";
    echo "<p><strong>Mật khẩu:</strong> 123456</p>";
    echo "<p><strong>Tên:</strong> Test User</p>";
    
    $data = [
        'username'      => 'testuser123',
        'email'         => 'test@test.com',
        'password_hash' => $passwordHash,
        'display_name'  => 'Test User',
        'role_id'       => 2,
        'status'        => 'active',
        'created_at'    => date('Y-m-d H:i:s'),
        'updated_at'    => date('Y-m-d H:i:s'),
    ];
    
    try {
        $connection->insert('users', $data);
        echo "<h2 style='color: green;'>✅ Thêm user thành công!</h2>";
    } catch (\Exception $e) {
        // Có thể email đã tồn tại, cập nhật mật khẩu
        $connection->update('users', 
            ['password_hash' => $passwordHash],
            ['email' => 'test@test.com']
        );
        echo "<h2 style='color: green;'>✅ Cập nhật mật khẩu thành công!</h2>";
    }
    
    // Hiển thị các user hiện có
    echo "<h2>📋 Danh sách user hiện có:</h2>";
    $users = $connection->fetchAllAssociative("SELECT id, username, email, display_name, role_id FROM users ORDER BY id DESC LIMIT 10");
    
    echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Username</th><th>Email</th><th>Display Name</th><th>Role</th></tr>";
    foreach ($users as $user) {
        $role = $user['role_id'] == 1 ? 'Admin' : 'User';
        echo "<tr><td>{$user['id']}</td><td>{$user['username']}</td><td>{$user['email']}</td><td>{$user['display_name']}</td><td>{$role}</td></tr>";
    }
    echo "</table>";
    
    echo "<h2>🔗 Thử đăng nhập:</h2>";
    echo "<p><a href='/du_an_ca_nhan/du_an_web_xem_phim/dang-nhap' target='_blank'>";
    echo "👉 Đến trang đăng nhập (test@test.com / 123456)</a></p>";
    
} catch (\Exception $e) {
    echo "<h2 style='color: red;'>❌ Lỗi: {$e->getMessage()}</h2>";
}
?>
