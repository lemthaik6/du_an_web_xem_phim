<?php
/**
 * Add Test Accounts
 */

define('BASE_PATH', __DIR__);
require BASE_PATH . '/vendor/autoload.php';

use Doctrine\DBAL\DriverManager;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->safeLoad();

    $conn = DriverManager::getConnection([
        'user'      => $_ENV['DB_USERNAME'] ?? 'root',
        'password'  => $_ENV['DB_PASSWORD'] ?? '',
        'dbname'    => $_ENV['DB_NAME'] ?? 'du_an_web_xem_phim',
        'host'      => $_ENV['DB_HOST'] ?? 'localhost',
        'driver'    => $_ENV['DB_DRIVER'] ?? 'pdo_mysql',
        'port'      => $_ENV['DB_PORT'] ?? 3306,
    ]);

    $hash = password_hash('123456', PASSWORD_BCRYPT);
    $accounts = [
        ['username' => 'test1', 'email' => 'test1@test.com', 'name' => 'Test User 1'],
        ['username' => 'test2', 'email' => 'test2@test.com', 'name' => 'Test User 2'],
        ['username' => 'test3', 'email' => 'test3@test.com', 'name' => 'Test User 3'],
    ];

    $created = 0;
    $updated = 0;

    foreach ($accounts as $acc) {
        $exists = $conn->fetchAssociative(
            'SELECT id FROM users WHERE email = ?',
            [$acc['email']]
        );

        if ($exists) {
            // Update password
            $conn->update('users', 
                ['password_hash' => $hash],
                ['email' => $acc['email']]
            );
            $updated++;
        } else {
            // Create new
            $conn->insert('users', [
                'username'      => $acc['username'],
                'email'         => $acc['email'],
                'password_hash' => $hash,
                'display_name'  => $acc['name'],
                'role_id'       => 2,
                'status'        => 'active',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
            $created++;
        }
    }

    echo json_encode([
        'ok'      => true,
        'message' => "Tạo thành công: $created tài khoản, Cập nhật: $updated tài khoản. Mật khẩu tất cả: 123456",
    ], JSON_UNESCAPED_UNICODE);

} catch (\Exception $e) {
    error_log('Add accounts error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
?>
