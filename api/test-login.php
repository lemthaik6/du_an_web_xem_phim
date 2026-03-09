<?php
define('BASE_PATH', __DIR__);
require BASE_PATH . '/vendor/autoload.php';
require BASE_PATH . '/helpers.php';

$dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

// Test 1: Check if POST data received
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;
    
    header('Content-Type: application/json; charset=utf-8');
    
    if (!$email || !$password) {
        http_response_code(422);
        echo json_encode([
            'ok' => false,
            'error' => 'Missing email or password',
            'received' => ['email' => $email, 'password' => $password ? '***' : null],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Test 2: Check database connection
    try {
        use Doctrine\DBAL\DriverManager;
        
        $conn = DriverManager::getConnection([
            'user'      => $_ENV['DB_USERNAME'] ?? 'root',
            'password'  => $_ENV['DB_PASSWORD'] ?? '',
            'dbname'    => $_ENV['DB_NAME'] ?? 'du_an_web_xem_phim',
            'host'      => $_ENV['DB_HOST'] ?? 'localhost',
            'driver'    => $_ENV['DB_DRIVER'] ?? 'pdo_mysql',
            'port'      => $_ENV['DB_PORT'] ?? 3306,
        ]);
        
        // Test 3: Query user
        $user = $conn->fetchAssociative(
            'SELECT * FROM users WHERE email = ? LIMIT 1',
            [$email]
        );
        
        if (!$user) {
            http_response_code(401);
            echo json_encode([
                'ok' => false,
                'error' => 'User not found',
                'email' => $email,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Test 4: Verify password
        if (!password_verify($password, $user['password_hash'])) {
            http_response_code(401);
            echo json_encode([
                'ok' => false,
                'error' => 'Invalid password',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Success!
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
            'message' => 'Login successful',
            'user' => ['id' => $user['id'], 'email' => $user['email']],
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (\Exception $e) {
        error_log('Debug login error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'error' => $e->getMessage(),
        ], JSON_UNESCAPED_UNICODE);
    }
    
    exit;
}

// Show form
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Login</title>
    <style>
        body { font-family: Arial; max-width: 500px; margin: 50px auto; }
        form { border: 1px solid #ddd; padding: 20px; border-radius: 5px; }
        input, button { width: 100%; padding: 10px; margin: 10px 0; }
        button { background: #0066cc; color: white; cursor: pointer; border: none; }
    </style>
</head>
<body>
    <h1>🧪 Debug Login Endpoint</h1>
    <form method="POST">
        <h2>Test Login /api/test-login</h2>
        <input type="email" name="email" placeholder="Email (test@test.com)" value="test@test.com" required>
        <input type="password" name="password" placeholder="Password (123456)" value="123456" required>
        <button type="submit">Test Login (POST)</button>
    </form>
    <p>This endpoint tests the login process step by step.</p>
</body>
</html>
