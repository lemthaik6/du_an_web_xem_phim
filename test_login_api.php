<?php
define('BASE_PATH', __DIR__);
require BASE_PATH . '/vendor/autoload.php';

use Doctrine\DBAL\DriverManager;

header('Content-Type: application/json; charset=utf-8');

$dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

$email = $_POST['email'] ?? null;
$password = $_POST['password'] ?? null;

if (!$email || !$password) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Email and password required'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $conn = DriverManager::getConnection([
        'user'      => $_ENV['DB_USERNAME'] ?? 'root',
        'password'  => $_ENV['DB_PASSWORD'] ?? '',
        'dbname'    => $_ENV['DB_NAME'] ?? 'du_an_web_xem_phim',
        'host'      => $_ENV['DB_HOST'] ?? 'localhost',
        'driver'    => $_ENV['DB_DRIVER'] ?? 'pdo_mysql',
        'port'      => $_ENV['DB_PORT'] ?? 3306,
    ]);
    
    // Find user
    $user = $conn->fetchAssociative("SELECT * FROM users WHERE email = ? LIMIT 1", [$email]);
    
    if (!$user) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Email hoặc mật khẩu không đúng'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $user['password_hash'] ?? '')) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Email hoặc mật khẩu không đúng'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Login success - start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['auth_user'] = [
        'id'    => $user['id'],
        'name'  => $user['display_name'] ?? $user['username'] ?? $user['email'],
        'email' => $user['email'],
        'role'  => (int)($user['role_id'] ?? 2),
    ];
    
    echo json_encode([
        'ok' => true,
        'message' => 'Đăng nhập thành công',
        'user' => ['id' => $user['id'], 'email' => $user['email'], 'name' => $_SESSION['auth_user']['name']]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (\Exception $e) {
    error_log('Direct login error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Lỗi hệ thống: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
