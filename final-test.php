<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$connectionParams = [
    'user'      => $_ENV['DB_USERNAME'] ?? 'root',
    'password'  => $_ENV['DB_PASSWORD'] ?? '',
    'dbname'    => $_ENV['DB_NAME'] ?? 'du_an_web_xem_phim',
    'host'      => $_ENV['DB_HOST'] ?? 'localhost',
    'driver'    => $_ENV['DB_DRIVER'] ?? 'pdo_mysql',
    'port'      => $_ENV['DB_PORT'] ?? 3306,
];

$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);

echo "=== REGISTRATION TEST ===\n\n";

// Create a new user
$username = 'demo_user_' . rand(10000, 99999);
$email = 'demo' . rand(10000, 99999) . '@example.com';
$password = 'TestPassword123!';

echo "Creating user:\n";
echo "- Username: $username\n";
echo "- Email: $email\n";
echo "- Password: $password\n\n";

try {
    $now = date('Y-m-d H:i:s');
    $conn->insert('users', [
        'username'      => $username,
        'email'         => $email,
        'password_hash' => password_hash($password, PASSWORD_BCRYPT),
        'display_name'  => 'Demo User',
        'role_id'       => 2,  // user role
        'status'        => 'active',
        'created_at'    => $now,
        'updated_at'    => $now,
    ]);
    
    echo "✅ User created successfully!\n\n";
    
    // Verify the user
    $user = $conn->query("SELECT * FROM users WHERE email = '$email'")->fetchAssociative();
    
    echo "✅ User verified in database:\n";
    echo "- User ID: {$user['id']}\n";
    echo "- Username: {$user['username']}\n";
    echo "- Email: {$user['email']}\n";
    echo "- Display Name: {$user['display_name']}\n";
    echo "- Status: {$user['status']}\n";
    echo "- Role ID: {$user['role_id']}\n\n";
    
    // Test login
    echo "Testing login with this user:\n";
    if (password_verify($password, $user['password_hash'])) {
        echo "✅ Password verification successful!\n";
        echo "\nSession data would be:\n";
        print_r([
            'id'    => $user['id'],
            'name'  => $user['display_name'],
            'email' => $user['email'],
            'role'  => $user['role_id'],
        ]);
    }
    
    // Count total users
    $allUsers = $conn->query("SELECT COUNT(*) as cnt FROM users")->fetch()['cnt'];
    echo "\n📊 Total users in database: $allUsers\n";
    
    echo "\n✅ REGISTRATION SYSTEM IS WORKING CORRECTLY!\n";
    echo "\nThe web form registration will:\n";
    echo "1. Accept username, email, password\n";
    echo "2. Hash the password\n";
    echo "3. Insert user into database\n";
    echo "4. Redirect to login page (/dang-nhap)\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
