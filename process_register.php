<?php
/**
 * Process Register - Direct endpoint (bypass routing system)
 */

define('BASE_PATH', __DIR__);
require BASE_PATH . '/vendor/autoload.php';
require BASE_PATH . '/helpers.php';

use Doctrine\DBAL\DriverManager;
use Rakit\Validation\Validator;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Validate input
    $validator = new Validator();
    $validation = $validator->make($_POST, [
        'username'              => 'required|min:3',
        'email'                 => 'required|email',
        'password'              => 'required|min:6',
        'password_confirmation' => 'required|same:password',
    ]);
    $validation->validate();

    if ($validation->fails()) {
        http_response_code(422);
        echo json_encode([
            'ok'     => false,
            'error'  => 'Dữ liệu không hợp lệ',
            'fields' => $validation->errors()->firstOfAll(),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $username = $_POST['username'];
    $email = $_POST['email'];
    $passwordHash = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Load environment
    $dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->safeLoad();

    // Connect database
    $conn = DriverManager::getConnection([
        'user'      => $_ENV['DB_USERNAME'] ?? 'root',
        'password'  => $_ENV['DB_PASSWORD'] ?? '',
        'dbname'    => $_ENV['DB_NAME'] ?? 'du_an_web_xem_phim',
        'host'      => $_ENV['DB_HOST'] ?? 'localhost',
        'driver'    => $_ENV['DB_DRIVER'] ?? 'pdo_mysql',
        'port'      => $_ENV['DB_PORT'] ?? 3306,
    ]);

    // Check if email exists
    $emailExists = $conn->fetchOne(
        'SELECT COUNT(*) as c FROM users WHERE email = ?',
        [$email]
    );

    if ((int)$emailExists > 0) {
        http_response_code(422);
        echo json_encode([
            'ok'    => false,
            'error' => 'Email này đã được đăng ký',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Check if username exists
    $usernameExists = $conn->fetchOne(
        'SELECT COUNT(*) as c FROM users WHERE username = ?',
        [$username]
    );

    if ((int)$usernameExists > 0) {
        http_response_code(422);
        echo json_encode([
            'ok'    => false,
            'error' => 'Tên tài khoản này đã được sử dụng',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Create user
    $conn->insert('users', [
        'username'     => $username,
        'email'        => $email,
        'password_hash' => $passwordHash,
        'display_name' => $username,
        'role_id'      => 2,
        'status'       => 'active',
        'created_at'   => date('Y-m-d H:i:s'),
        'updated_at'   => date('Y-m-d H:i:s'),
    ]);

    echo json_encode([
        'ok'      => true,
        'message' => 'Đăng ký thành công, vui lòng đăng nhập',
    ], JSON_UNESCAPED_UNICODE);

} catch (\Exception $e) {
    error_log('Register error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => 'Lỗi hệ thống: ' . $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
?>
