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

try {
    echo "Connecting to database...\n";
    $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
    echo "✓ Connected\n\n";
    
    $username = 'testuser_' . rand(1000, 9999);
    $email = 'test' . rand(1000, 9999) . '@example.com';
    $passwordHash = password_hash('password123', PASSWORD_BCRYPT);
    
    echo "Creating user:\n";
    echo "- Username: $username\n";
    echo "- Email: $email\n\n";
    
    $conn->insert('users', [
        'username'      => $username,
        'email'         => $email,
        'password_hash' => $passwordHash,
        'display_name'  => $username,
        'role_id'       => 2,
        'status'        => 'active',
        'created_at'    => date('Y-m-d H:i:s'),
        'updated_at'    => date('Y-m-d H:i:s'),
    ]);
    
    echo "✓ User inserted successfully!\n\n";
    
    // Retrieve it
    $user = $conn->query("SELECT * FROM users WHERE email = '$email'")->fetchAssociative();
    echo "✓ User retrieved:\n";
    echo "  ID: " . $user['id'] . "\n";
    echo "  Username: " . $user['username'] . "\n";
    echo "  Email: " . $user['email'] . "\n\n";
    
    // Test password
    if (password_verify('password123', $user['password_hash'])) {
        echo "✓ Password verification works\n";
    }
    
    // Count all users
    $count = $conn->query("SELECT COUNT(*) as cnt FROM users")->fetch()['cnt'];
    echo "\nTotal users: $count\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
